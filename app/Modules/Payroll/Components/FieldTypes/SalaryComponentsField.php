<?php

namespace App\Modules\Payroll\Components\FieldTypes;

use QuickerFaster\UILibrary\Contracts\FieldTypes\FieldType;
use QuickerFaster\UILibrary\Traits\FieldTypes\HasBladeRendering;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;

/**
 * Salary components field — embeds the SalaryComponentsEditor repeater.
 */
class SalaryComponentsField implements FieldType
{
    use HasBladeRendering;
    use HasCurrencySymbol;

    protected string $name;
    protected array $definition;

    public function __construct(string $name, array $definition)
    {
        $this->name = $name;
        $this->definition = $definition;
    }

    public function renderForm($value = null): string
    {
        // Embedded <livewire:> in standalone View::make() causes memory
        // exhaustion. Salary components are configured on a dedicated
        // page: /payroll/salary-structure/{employee}
        return '<div class="mb-3">'
            . '<label class="form-label">Salary Components</label>'
            . '<p class="text-muted small mb-2">Basic, Housing, Transport & Other Allowance are configured on the dedicated <strong>Salary Structure</strong> page.</p>'
            . '<small class="text-info"><i class="fas fa-info-circle me-1"></i>Save this profile first, then use the Salary Structure page to configure components.</small>'
            . '</div>';
    }

    public function renderTable($value, $record): string
    {
        $employeeId = $record->employee_id ?? null;
        $count = \App\Modules\Payroll\Models\EmployeeSalaryComponent::withoutCompanyScope()
            ->where('employee_id', $employeeId)
            ->count();

        if (!$count) {
            return '—';
        }

        $url = url("/payroll/salary-structure/{$record->id}");

        return "<a href=\"{$url}\" class=\"text-decoration-none\">"
            . "<i class=\"fas fa-money-bill-wave me-1\"></i>"
            . "{$count} component(s)"
            . "</a>";
    }

    public function renderDetail($value, $record): string
    {
        $employeeId = $record->employee_id ?? null;
        $components = \App\Modules\Payroll\Models\EmployeeSalaryComponent::withoutCompanyScope()
            ->where('employee_id', $employeeId)
            ->orderBy('component_key')
            ->get();

        $labels = config('quick_hr_payroll.salary_component_types.NG', [
            'basic' => 'Basic Salary',
            'housing' => 'Housing Allowance',
            'transport' => 'Transport Allowance',
            'other_allowance' => 'Other Allowance',
        ]);

        $pensionableKeys = config('quick_hr_payroll.component_groups.NG.pensionable', ['basic', 'housing', 'transport']);

        // Resolve currency symbol from the record's currency_code
        $currencyCode = $record->currency_code ?? 'NGN';
        $symbol = $this->getCurrencySymbol($currencyCode);

        if ($components->isEmpty()) {
            $url = url("/payroll/salary-structure/{$record->id}");
            return "<a href=\"{$url}\" class=\"btn btn-sm btn-outline-primary\">"
                . "<i class=\"fas fa-plus me-1\"></i> Configure Salary Components"
                . "</a>";
        }

        $rows = '';
        $totalBht = 0;
        $totalGross = 0;

        foreach ($components as $c) {
            $label = $labels[$c->component_key] ?? ucfirst($c->component_key);
            $isBht = in_array($c->component_key, $pensionableKeys);
            $badge = $isBht
                ? '<span class="badge bg-success ms-1" title="Part of pensionable BHT">BHT</span>'
                : '';

            $rows .= "<tr>"
                . "<td>{$label}{$badge}</td>"
                . "<td class=\"text-end\">{$symbol}" . number_format((float) $c->amount, 2) . "</td>"
                . "</tr>";

            $totalGross += (float) $c->amount;
            if ($isBht) {
                $totalBht += (float) $c->amount;
            }
        }

        $url = url("/payroll/salary-structure/{$record->id}");

        return "<div class=\"table-responsive\">"
            . "<table class=\"table table-sm table-borderless mb-2\">"
            . "<tbody>{$rows}</tbody>"
            . "<tfoot class=\"border-top\">"
            . "<tr><td class=\"fw-bold\">Total Gross</td><td class=\"text-end fw-bold\">{$symbol}" . number_format($totalGross, 2) . "</td></tr>"
            . "<tr><td class=\"text-muted\">Pensionable (BHT)</td><td class=\"text-end text-muted\">{$symbol}" . number_format($totalBht, 2) . "</td></tr>"
            . "</tfoot>"
            . "</table>"
            . "<a href=\"{$url}\" class=\"btn btn-sm btn-outline-primary\">"
            . "<i class=\"fas fa-edit me-1\"></i> Configure"
            . "</a>"
            . "</div>";
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
