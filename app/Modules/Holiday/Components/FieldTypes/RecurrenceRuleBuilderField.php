<?php

namespace App\Modules\Holiday\Components\FieldTypes;

use QuickerFaster\UILibrary\Contracts\FieldTypes\FieldType;
use QuickerFaster\UILibrary\Traits\FieldTypes\HasBladeRendering;

/**
 * Custom field type that renders the RecurrenceRuleBuilder Livewire
 * component inside a DataTable form.
 *
 * Registered via FieldFactory::registerType() in HolidayServiceProvider.
 */
class RecurrenceRuleBuilderField implements FieldType
{
    use HasBladeRendering;

    protected string $name;
    protected array $definition;

    public function __construct(string $name, array $definition)
    {
        $this->name = $name;
        $this->definition = $definition;
    }

    public function renderForm($value = null): string
    {
        return $this->renderBlade('holiday::components.fields.recurrence-rule-builder', [
            'field'  => $this,
            'value'  => $value,
            'name'   => $this->name,
            'label'  => $this->definition['label'] ?? 'Recurrence Rule',
        ]);
    }

    public function renderTable($value, $record): string
    {
        if (empty($value)) {
            return '—';
        }

        $data = json_decode($value, true);
        if (!is_array($data)) {
            return '—';
        }

        $frequency = $data['frequency'] ?? 'yearly';
        $label = match ($frequency) {
            'yearly'  => isset($data['month'], $data['day'])
                ? 'Every ' . date('F', mktime(0, 0, 0, $data['month'], 1)) . ' ' . $data['day']
                : 'Yearly',
            'monthly' => isset($data['day_of_month'])
                ? 'Monthly on day ' . $data['day_of_month']
                : 'Monthly',
            'weekly'  => 'Weekly',
            'daily'   => 'Daily',
            default   => ucfirst($frequency),
        };

        return '<span class="badge bg-info">' . e($label) . '</span>';
    }

    public function renderInlineEditor($value, $record, array $extra = []): string
    {
        return $this->renderComplexFallback($record, $extra, 'Edit Recurrence Rule');
    }

    public function renderDetail($value, $record): string
    {
        return $this->renderTable($value, $record);
    }

    public function getValidationRules(): array
    {
        return [$this->name => 'nullable|json'];
    }

    public function getOptions(): array
    {
        return [];
    }

    public function isRelationship(): bool
    {
        return false;
    }

    public function getRelationshipConfig(): ?array
    {
        return null;
    }

    public function getLabel(): string
    {
        return $this->definition['label'] ?? 'Recurrence Rule';
    }

    public function getName(): string
    {
        return $this->name;
    }
}
