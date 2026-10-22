<?php

namespace App\Modules\Loans\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use App\Modules\Loans\Services\LoanCalculator;

class LoanCalculatorTest extends TestCase
{
    protected LoanCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new LoanCalculator();
    }

    // =========================================================================
    // EMI Calculation Tests
    // =========================================================================

    #[Test]
    public function it_calculates_emi_for_interest_free_loan(): void
    {
        // $10,000 over 12 months at 0% = $833.33/month
        $emi = $this->calculator->calculateEMI(10000, 0, 12);
        $this->assertEquals(833.33, $emi);
    }

    #[Test]
    public function it_calculates_emi_with_interest(): void
    {
        // $10,000 over 12 months at 12% p.a.
        $emi = $this->calculator->calculateEMI(10000, 12, 12);
        // Expected: ~888.49
        $this->assertEquals(888.49, $emi);
    }

    #[Test]
    public function it_calculates_emi_for_single_month(): void
    {
        // $5,000 over 1 month at 5% = $5,000 + interest for 1 month
        $emi = $this->calculator->calculateEMI(5000, 5, 1);
        // Monthly rate = 5/12/100 = 0.004167
        // EMI = 5000 * 0.004167 * (1.004167)^1 / ((1.004167)^1 - 1)
        // = 5000 * 0.004167 * 1.004167 / 0.004167 = 5000 * 1.004167 = 5020.83
        $this->assertEquals(5020.83, $emi);
    }

    #[Test]
    public function it_handles_zero_months(): void
    {
        $emi = $this->calculator->calculateEMI(1000, 5, 0);
        $this->assertEquals(1000.0, $emi);
    }

    #[Test]
    public function it_handles_zero_principal(): void
    {
        $emi = $this->calculator->calculateEMI(0, 5, 12);
        $this->assertEquals(0.0, $emi);
    }

    #[Test]
    public function it_calculates_emi_for_long_term_loan(): void
    {
        // $50,000 over 60 months at 8% p.a.
        $emi = $this->calculator->calculateEMI(50000, 8, 60);
        // Expected: ~1013.82
        $this->assertEquals(1013.82, $emi);
    }

    // =========================================================================
    // Total Repayable Tests
    // =========================================================================

    #[Test]
    public function it_calculates_total_repayable(): void
    {
        $total = $this->calculator->calculateTotalRepayable(888.49, 12);
        $this->assertEquals(10661.88, $total);
    }

    #[Test]
    public function it_calculates_total_repayable_for_single_month(): void
    {
        $total = $this->calculator->calculateTotalRepayable(5000, 1);
        $this->assertEquals(5000.0, $total);
    }

    // =========================================================================
    // Total Interest Tests
    // =========================================================================

    #[Test]
    public function it_calculates_total_interest(): void
    {
        $interest = $this->calculator->calculateTotalInterest(10661.88, 10000);
        $this->assertEquals(661.88, $interest);
    }

    #[Test]
    public function it_calculates_zero_interest_for_interest_free_loan(): void
    {
        $interest = $this->calculator->calculateTotalInterest(10000, 10000);
        $this->assertEquals(0.0, $interest);
    }

    // =========================================================================
    // Repayment Schedule Tests
    // =========================================================================

    #[Test]
    public function it_generates_schedule_with_correct_number_of_installments(): void
    {
        $schedule = $this->calculator->generateSchedule(10000, 0, 12, '2026-02-01');

        $this->assertCount(12, $schedule);
    }

    #[Test]
    public function it_generates_schedule_with_correct_dates(): void
    {
        $schedule = $this->calculator->generateSchedule(10000, 0, 6, '2026-01-15');

        $this->assertEquals('2026-01-15', $schedule[0]['due_date']);
        $this->assertEquals('2026-02-15', $schedule[1]['due_date']);
        $this->assertEquals('2026-06-15', $schedule[5]['due_date']);
    }

    #[Test]
    public function it_generates_schedule_with_zero_balance_at_end(): void
    {
        $schedule = $this->calculator->generateSchedule(10000, 0, 12, '2026-01-01');

        $lastInstallment = end($schedule);
        $this->assertEquals(0.0, $lastInstallment['balance_after']);
    }

    #[Test]
    public function it_generates_schedule_with_interest_components(): void
    {
        $schedule = $this->calculator->generateSchedule(10000, 12, 12, '2026-01-01');

        // First installment should have interest > 0
        $this->assertGreaterThan(0, $schedule[0]['interest_component']);
        $this->assertGreaterThan(0, $schedule[0]['principal_component']);
    }

    #[Test]
    public function it_numbers_installments_correctly(): void
    {
        $schedule = $this->calculator->generateSchedule(5000, 0, 3, '2026-01-01');

        $this->assertEquals(1, $schedule[0]['installment_number']);
        $this->assertEquals(2, $schedule[1]['installment_number']);
        $this->assertEquals(3, $schedule[2]['installment_number']);
    }

    // =========================================================================
    // Amount Validation Tests
    // =========================================================================

    #[Test]
    public function it_validates_amount_within_limits(): void
    {
        $result = $this->calculator->validateAmount(5000, 1000, 10000);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['message']);
    }

    #[Test]
    public function it_rejects_amount_below_minimum(): void
    {
        $result = $this->calculator->validateAmount(500, 1000, 10000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('at least', $result['message']);
    }

    #[Test]
    public function it_rejects_amount_above_maximum(): void
    {
        $result = $this->calculator->validateAmount(15000, 1000, 10000);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('cannot exceed', $result['message']);
    }

    #[Test]
    public function it_allows_amount_when_min_is_zero(): void
    {
        $result = $this->calculator->validateAmount(500, 0, 10000);
        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function it_allows_amount_when_max_is_zero(): void
    {
        // max=0 means no upper limit
        $result = $this->calculator->validateAmount(50000, 1000, 0);
        $this->assertTrue($result['valid']);
    }
}
