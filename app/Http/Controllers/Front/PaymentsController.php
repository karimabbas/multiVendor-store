<?php

namespace App\Http\Controllers\Front;

use App\Helpers\Currency;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class PaymentsController extends Controller
{
    public function create(Order $order)
    {
        return view('front.payments.create', [
            'order' => $order
        ]);
    }

    public function createStripePaymentIntent(Order $order)
    {

        // $amount = $order->sum($order->products->pivot->quantity * $order->products->price);
        $amount = $order->getSum();
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret_key'));
        $paymentIntent =  $stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);
        try {
            $payment = new Payment();
            $payment->forceFill([
                'order_id' => $order->id,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => 'pending',
                'method' => 'stripe',
                'transaction_id' => $paymentIntent->id,

            ])->save();
        } catch (QueryException $e) {
            echo $e->getMessage();
            return;
        }
        return [
            'clientSecret' => $paymentIntent->client_secret,

        ];
    }

    public function confirm(Request $request, Order $order)
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret_key'));

        $paymentIntent = $stripe->paymentIntents->retrieve(
            $request->query('payment_intent'),
            []
        );
        if ($paymentIntent->status == 'succeeded') {
            try {
                $payment = Payment::where('order_id', $order->id)->first();
                $payment->forceFill([
                    'status' => 'completed',
                    'transaction_data' => json_encode($paymentIntent),

                ])->save();
            } catch (QueryException $e) {
                echo $e->getMessage();
                return;
            }
            event('payment.created', $payment->id);

            return redirect()->route('home')->with('success', 'Payment Proccess Done Successflly');
        }

        return redirect()->route('orders.payments.create')->with('danger', 'Payment Proccess faild,try again');
    }
}
