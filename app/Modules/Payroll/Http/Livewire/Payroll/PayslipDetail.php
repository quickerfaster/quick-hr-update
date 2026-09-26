<?php

namespace App\Modules\Payroll\Http\Livewire\Payroll;

use Livewire\Component;
use App\Modules\Payroll\Models\PayrollPayslip;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;
use QuickerFaster\UILibrary\Services\Config\ConfigResolver;

class PayslipDetail extends Component
{
    use HasCurrencySymbol;

    public int $recordId;
    public string $configKey;
    public array $returnParams = [];
    public bool $inline = false;
    public $payslip;
    public string $currencySymbol = '$';
    public array $fieldGroups = [];
    public array $fieldDefinitions = [];
    public array $hiddenFields = [];

    protected array $monetaryFields = [
        'base_salary', 'gross_pay', 'total_deductions', 'total_taxes',
        'total_benefit_deductions', 'net_pay', 'employer_contribution_total',
        'taxable_earnings', 'income_tax', 'social_security_tax', 'medicare_tax',
        'pension_employee', 'health_insurance_employee', 'other_earnings',
        'other_deductions', 'pension_employer', 'health_insurance_employer',
    ];

    public function mount(string $configKey, int $recordId, $inline = false, array $returnParams = [], ?string $crudType = null): void
    {
        $this->configKey = $configKey;
        $this->recordId = $recordId;
        $this->inline = $inline;
        $this->returnParams = $returnParams;

        $resolver = app(ConfigResolver::class, ['configKey' => $this->configKey]);
        $config = $resolver->getConfig();
        $this->fieldGroups = $config['fieldGroups'] ?? [];
        $this->fieldDefinitions = $config['fieldDefinitions'] ?? [];
        $this->hiddenFields = $config['hiddenFields'] ?? ['onDetail' => []];

        $this->payslip = PayrollPayslip::with(['employee', 'payrollRun'])->find($recordId);
        if (!$this->payslip) { abort(404); }

        $emp = $this->payslip->employee;
        if ($emp) {
            $emp->loadMissing(['company', 'employeePosition']);
            $code = $emp->company->currency_code
                ?? $emp->employeePosition->salary_currency
                ?? $this->payslip->currency_code
                ?? 'USD';
        } else {
            $code = $this->payslip->currency_code ?: 'USD';
        }
        $this->currencySymbol = $this->getCurrencySymbol($code);
    }

    public function getFieldLabel(string $field): string
    {
        return $this->fieldDefinitions[$field]['label'] ?? ucwords(str_replace('_', ' ', $field));
    }

    public function isMonetaryField(string $field): bool
    {
        return in_array($field, $this->monetaryFields, true);
    }

    public function renderFieldValue(string $fieldName, $value): string
    {
        if ($value === null || $value === '') {
            return '<span class="text-muted fst-italic">-</span>';
        }

        $def = $this->fieldDefinitions[$fieldName] ?? [];
        $type = $def['field_type'] ?? 'string';

        // Relationship: show related model's display field
        if (in_array($type, ['select', 'livewire-searchable-select']) && !empty($def['relationship'])) {
            $relName = $def['relationship']['dynamic_property'] ?? $fieldName;
            $displayField = $def['relationship']['display_field'] ?? 'name';
            $related = $this->payslip->{$relName} ?? null;

            if ($related) {
                // Build display: primary field + optional hint fields
                $primary = $related->{$displayField} ?? '';
                if ($primary instanceof \DateTimeInterface) {
                    $primary = $primary->format('M d, Y');
                }
                $primary = (string) $primary;

                $hintField = $def['options']['hintField'] ?? '';
                if (!empty($hintField)) {
                    $hintParts = array_map('trim', explode(',', $hintField));
                    $hintVals = [];
                    foreach ($hintParts as $hp) {
                        $hv = $related->{$hp} ?? '';
                        if ($hv instanceof \DateTimeInterface) {
                            $hv = $hv->format('M d, Y');
                        }
                        $hintVals[] = (string) $hv;
                    }
                    $hint = implode(' ', array_filter($hintVals));
                    if (!empty($hint)) {
                        return e($primary . ' (' . $hint . ')');
                    }
                }

                return e($primary ?: (string) $value);
            }
            return e((string) $value);
        }

        // Monetary
        if ($this->isMonetaryField($fieldName) && is_numeric($value)) {
            return $this->currencySymbol . number_format((float) $value, 2);
        }

        // Date
        if (in_array($type, ['date', 'datepicker', 'datetimepicker']) && $value instanceof \DateTimeInterface) {
            return e($value->format('M d, Y'));
        }

        return e((string) $value);
    }

    public function render()
    {
        return view('payroll::livewire.payroll.payslip-detail');
    }
}
