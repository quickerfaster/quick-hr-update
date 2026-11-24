<?php

namespace App\Modules\Payroll\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Modules\Payroll\Services\Payroll\Countries\NigeriaPayrollCalculator;

class NigeriaPayrollCalculatorTest extends BaseTestCase
{
    protected NigeriaPayrollCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'quick_hr_payroll.component_groups.NG.pensionable' => ['basic', 'housing', 'transport'],
            'quick_hr_payroll.country_tax_rules.NG.minimum_wage_exemption' => 70000,
            'quick_hr_payroll.country_tax_rules.NG.rent_relief_rate' => 0.20,
            'quick_hr_payroll.country_tax_rules.NG.rent_relief_cap' => 500000,
        ]);

        $this->calculator = new NigeriaPayrollCalculator();
    }

    #[Test]
    public function it_resolves_pensionable_base_as_bht_sum()
    {
        $base = $this->calculator->resolvePensionableBase([
            'basic'          => 500000,
            'housing'        => 200000,
            'transport'      => 100000,
            'other_allowance' => 50000,
        ], 0);

        $this->assertEquals(800000, $base);
    }

    #[Test]
    public function it_falls_back_to_base_salary_when_no_components()
    {
        $base = $this->calculator->resolvePensionableBase([], 750000);

        $this->assertEquals(750000, $base);
    }

    #[Test]
    public function it_exempts_minimum_wage_earners_from_tax()
    {
        $this->assertTrue($this->calculator->isIncomeTaxExempt(840000));   // 70k × 12
        $this->assertFalse($this->calculator->isIncomeTaxExempt(840001));
    }

    #[Test]
    public function it_applies_rent_relief_capped_at_500k()
    {
        // 20% of ₦3,000,000 rent = ₦600,000 → capped at ₦500,000
        $taxable = $this->calculator->applyTaxReliefs(2000000, ['annual_rent' => 3000000]);

        $this->assertEquals(1500000, $taxable);
    }

    #[Test]
    public function it_applies_rent_relief_without_cap()
    {
        // 20% of ₦1,000,000 rent = ₦200,000 (below cap)
        $taxable = $this->calculator->applyTaxReliefs(1000000, ['annual_rent' => 1000000]);

        $this->assertEquals(800000, $taxable);
    }

    #[Test]
    public function it_returns_unchanged_taxable_when_no_rent()
    {
        $taxable = $this->calculator->applyTaxReliefs(1000000, []);

        $this->assertEquals(1000000, $taxable);
    }
}
