<?php

namespace Revolution\Ordering\Http\Livewire\Order;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Livewire\Component;
use PayPay\OpenPaymentAPI\Controller\ClientControllerException;
use Revolution\Ordering\Contracts\Actions\Order;
use Revolution\Ordering\Payment\PayPay as PaymentPayPay;

class PayPay extends Component
{
    /**
     * @var string
     */
    public string $payment;

    /**
     * @var string
     */
    public string $status;

    /**
     * @param  Request  $request
     */
    public function mount(Request $request)
    {
        $this->payment = $request->payment ?? '';
    }

    /**
     * @return RedirectResponse|void
     * @throws ClientControllerException
     */
    public function check()
    {
        $response = app(PaymentPayPay::class)->getPaymentDetails($this->payment);

        $status = Arr::get($response, 'status');

        // PayPayではgetPaymentDetailsのステータスがCOMPLETEDを確認して注文送信。
        if ($status === PaymentPayPay::COMPLETED) {
            $options = [
                'payment'     => 'paypay',
                'paypay_data' => $response,
            ];

            app(Order::class)->order($options);

            return redirect()->route(config('ordering.redirect.from_payment'));
        } else {
            $this->status = $status;
        }
    }

    /**
     * @return RedirectResponse
     */
    public function back()
    {
        return redirect()->route('order', ['table' => session('table')]);
    }

    public function render()
    {
        return view('ordering::livewire.order.paypay');
    }
}