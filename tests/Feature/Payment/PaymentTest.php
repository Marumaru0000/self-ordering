<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use Illuminate\Http\RedirectResponse;
use Revolution\Ordering\Contracts\Actions\Order;
use Revolution\Ordering\Contracts\Payment\PaymentMethodFactory;
use Revolution\Ordering\Facades\Payment;
use Revolution\Ordering\Payment\CashDriver;
use Revolution\Ordering\Payment\PaymentManager;
use Revolution\Ordering\Payment\PaymentMethod;
use Revolution\Ordering\Payment\PayPay\PayPay;
use Revolution\Ordering\Payment\PaypayDriver;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function testPaymentManager()
    {
        $menu = new PaymentManager(app());

        $this->assertSame('cash', $menu->getDefaultDriver());
    }

    public function testPaymentMethod()
    {
        $pay = app(PaymentMethodFactory::class);

        $this->assertInstanceOf(PaymentMethod::class, $pay);
        $this->assertSame(['cash', 'paypay'], $pay->keys()->toArray());
        $this->assertSame('PayPay', $pay->name('paypay'));
    }

    public function testCashDriver()
    {
        $this->mock(Order::class)
             ->shouldReceive('order')
             ->once();

        $driver = Payment::driver('cash');
        $redirect = $driver->redirect();

        $this->assertInstanceOf(CashDriver::class, $driver);
        $this->assertInstanceOf(RedirectResponse::class, $redirect);
    }

    public function testPayPayDriver()
    {
        $this->mock(PayPay::class)
             ->shouldReceive('redirect')
             ->once()
             ->andReturn(redirect('test'));

        $driver = Payment::driver('paypay');
        $redirect = $driver->redirect();

        $this->assertInstanceOf(PaypayDriver::class, $driver);
        $this->assertInstanceOf(RedirectResponse::class, $redirect);
    }
}