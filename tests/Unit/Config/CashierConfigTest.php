<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class CashierConfigTest extends TestCase
{
    public function test_currency_is_eur(): void
    {
        $this->assertSame('eur', config('cashier.currency'));
    }

    public function test_city_monthly_plan_is_defined(): void
    {
        $plan = config('cashier.plans.city_monthly');

        $this->assertNotNull($plan, 'The city_monthly plan must be configured for Stripe checkout.');
        $this->assertIsString($plan);
        $this->assertNotSame('', $plan);
    }

    public function test_subscription_amount_is_80_eur(): void
    {
        $this->assertSame(8000, config('cashier.subscription.amount'));
        $this->assertSame('eur', config('cashier.subscription.currency'));
        $this->assertSame('month', config('cashier.subscription.interval'));
    }
}
