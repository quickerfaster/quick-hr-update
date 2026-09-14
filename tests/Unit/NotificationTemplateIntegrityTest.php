<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use QuickerFaster\UILibrary\Models\NotificationTemplate;

class NotificationTemplateIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('channel');
                $table->string('subject')->nullable();
                $table->text('body_template');
                $table->string('locale')->default('en');
                $table->timestamps();
            });
        }
    }

    #[Test]
    public function all_workflow_notification_types_have_templates()
    {
        $definitions = config('ui-library.workflows.definitions', []);
        $missing = [];

        foreach ($definitions as $key => $def) {
            $notifications = $def['notifications'] ?? [];
            if (!($notifications['enabled'] ?? false)) continue;

            $types = $notifications['types'] ?? [];
            foreach ($types as $event => $typeName) {
                foreach (['mail', 'database'] as $channel) {
                    $template = NotificationTemplate::where('type', $typeName)
                        ->where('channel', $channel)
                        ->first();

                    if (!$template) {
                        $missing[] = "Workflow '{$key}': type '{$typeName}' ({$channel}) has no template";
                    } elseif (empty($template->subject) || empty($template->body_template)) {
                        $missing[] = "Workflow '{$key}': type '{$typeName}' ({$channel}) has empty subject or body";
                    }
                }
            }
        }

        $this->assertEmpty($missing, "Missing/invalid notification templates:\n" . implode("\n", $missing));
    }
}
