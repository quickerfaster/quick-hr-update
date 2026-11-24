<?php

namespace App\Modules\Payroll\Components\FieldTypes;

use QuickerFaster\UILibrary\Contracts\FieldTypes\FieldType;
use QuickerFaster\UILibrary\Traits\FieldTypes\HasBladeRendering;

/**
 * Salary components field — embeds the SalaryComponentsEditor repeater.
 */
class SalaryComponentsField implements FieldType
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
        return $this->renderBlade('payroll::components.fields.salary-components', [
            'field' => $this,
            'value' => $value,
            'name'  => $this->name,
            'label' => $this->definition['label'] ?? 'Salary Components',
        ]);
    }

    public function renderTable($value, $record): string
    {
        $count = \App\Modules\Payroll\Models\EmployeeSalaryComponent::withoutCompanyScope()
            ->where('employee_id', $record->employee_id ?? null)
            ->count();

        return $count ? "{$count} component(s)" : '—';
    }

    public function renderDetail($value, $record): string
    {
        return $this->renderTable($value, $record);
    }

    public function renderInlineEditor($value, $record, array $extra = []): string
    {
        return $this->renderComplexFallback($record, $extra, 'Edit Salary Components');
    }

    public function getValidationRules(): array
    {
        return [$this->name => 'nullable'];
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
        return $this->definition['label'] ?? ucfirst($this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
