<?php

namespace Asciisd\Knet\Http\Controllers;

use Asciisd\Knet\Events\KnetReceiptSeen;
use Asciisd\Knet\Http\Requests\ReceiptRequest;
use Asciisd\Knet\Knet;
use Asciisd\Knet\KnetTransaction;
use Asciisd\Knet\Payment;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\View\View;

class ReceiptController
{
    /**
     * Display receipt.
     *
     * @param ReceiptRequest $request
     * @return View
     */
    public function show(ReceiptRequest $request)
    {
        $payment = new Payment(
            KnetTransaction::findByPaymentId($request->paymentid)
        );

        if ($request->user()->id !== $payment->owner()->id) {
            throw new UnauthorizedException('Sorry! But this invoice did\'t belongs to you.');
        }

        KnetReceiptSeen::dispatch($payment);

        return view('knet::receipt', ['payment' => $payment] + Knet::invoiceDataFor($payment->owner()));
    }
}
