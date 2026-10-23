<?php

namespace App\Modules\Holiday\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Persists wizard session data across sessions so users can
 * resume a batch creation wizard after their session expires.
 *
 * Drafts are saved on each step advancement and cleaned up
 * after successful submission or explicit deletion.
 */
class HolidayBatchDraft extends Model
{
    protected $table = 'holiday_batch_drafts';

    protected $fillable = [
        'token',
        'user_id',
        'wizard_id',
        'data',
        'expires_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Save the current wizard state as a draft.
     *
     * @param string $wizardId  The wizard's session-based ID
     * @param int|null $userId  The authenticated user ID
     * @return string The draft token (for URL parameter)
     */
    public static function saveDraft(string $wizardId, ?int $userId = null): string
    {
        // Collect all session keys for this wizard
        $data = [];
        $prefix = $wizardId . '-';
        foreach (session()->all() as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $data[$key] = $value;
            }
        }

        // Also include the main wizard key
        if (session()->has($wizardId)) {
            $data[$wizardId] = session()->get($wizardId);
        }

        if (empty($data)) {
            return '';
        }

        // Upsert by wizard_id + user_id
        $draft = static::updateOrCreate(
            [
                'wizard_id' => $wizardId,
                'user_id'   => $userId,
            ],
            [
                'token'      => Str::random(32),
                'data'       => $data,
                'expires_at' => now()->addDays(7),
            ]
        );

        // Keep only one draft per user — delete any stale drafts from
        // previous sessions (different wizard_id) for the same user.
        if ($userId) {
            static::where('user_id', $userId)
                ->where('id', '!=', $draft->id)
                ->delete();
        }

        return $draft->token;
    }

    /**
     * Restore wizard state from a draft token.
     *
     * @param string $token
     * @return bool True if restored successfully
     */
    public static function restoreDraft(string $token): bool
    {
        $draft = static::where('token', $token)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$draft || empty($draft->data)) {
            return false;
        }

        foreach ($draft->data as $key => $value) {
            session()->put($key, $value);
        }

        return true;
    }

    /**
     * Delete a draft by token (called after successful submission).
     */
    public static function deleteByToken(string $token): void
    {
        static::where('token', $token)->delete();
    }

    /**
     * Get drafts for a user (for the "Resume" UI).
     */
    public static function getForUser(?int $userId): array
    {
        if (!$userId) {
            return [];
        }

        return static::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();
    }
}
