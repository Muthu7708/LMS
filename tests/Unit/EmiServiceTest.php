<?php

namespace Tests\Unit;

use App\Services\EmiService;
use PHPUnit\Framework\TestCase;

class EmiServiceTest extends TestCase
{
    protected EmiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmiService();
    }

    public function test_calculate_reducing_balance_emi(): void
    {
        // Loan 1,00,000 at 12% p.a. for 12 months (reducing)
        $emi = $this->service->calculateEmi(100000, 12, 12, 'reducing');
        // Monthly rate = 1%, factor = 1.01^12 = 1.126825...
        // EMI should be approx 8884.88
        $this->assertEquals(8884.88, $emi);
    }

    public function test_calculate_flat_rate_emi(): void
    {
        // Loan 1,00,000 at 12% p.a. for 12 months (flat)
        // Total interest = 100000 * 0.12 * 1 = 12000
        // Total payable = 112000
        // EMI = 112000 / 12 = 9333.33
        $emi = $this->service->calculateEmi(100000, 12, 12, 'flat');
        $this->assertEquals(9333.33, $emi);
    }

    public function test_simulate_schedule_structure(): void
    {
        $simulation = $this->service->simulateSchedule(100000, 12, 12, 'reducing');

        $this->assertArrayHasKey('emi_amount', $simulation);
        $this->assertArrayHasKey('total_interest', $simulation);
        $this->assertArrayHasKey('total_payable', $simulation);
        $this->assertArrayHasKey('schedule', $simulation);
        $this->assertCount(12, $simulation['schedule']);
    }
}
