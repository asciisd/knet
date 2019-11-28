<?php

namespace Asciisd\Knet;

use Carbon\Carbon;
use DateTimeZone;
use Dompdf\Dompdf;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class Invoice
{
    /**
     * The Stripe invoice instance.
     *
     * @var KnetTransaction
     */
    protected $transaction;

    /**
     * Create a new invoice instance.
     *
     * @param KnetTransaction $transaction
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get a Carbon date for the invoice.
     *
     * @param DateTimeZone|string $timezone
     * @return Carbon
     */
    public function date($timezone = null)
    {
        $carbon = Carbon::createFromTimestampUTC($this->transaction->created ?? $this->transaction->date);
        return $timezone ? $carbon->setTimezone($timezone) : $carbon;
    }

    /**
     * Get the total amount that was paid (or will be paid).
     *
     * @return string
     */
    public function total()
    {
        return $this->formatAmount($this->rawTotal());
    }

    /**
     * Get the raw total amount that was paid (or will be paid).
     *
     * @return int
     */
    public function rawTotal()
    {
        return $this->transaction->total + $this->rawStartingBalance();
    }

    /**
     * Get the total of the transaction (before discounts).
     *
     * @return string
     */
    public function subtotal()
    {
        return $this->formatAmount($this->transaction->subtotal);
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param int $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return $amount . ' KWD';
    }

    /**
     * Get the View instance for the transaction.
     *
     * @param array $data
     * @return View
     */
    public function view(array $data)
    {
        return View::make('knet::receipt', array_merge($data, [
            'transaction' => $this
        ]));
    }

    /**
     * Capture the transaction as a PDF and return the raw bytes.
     *
     * @param array $data
     * @return string
     */
    public function pdf(array $data)
    {
        if (!defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define('DOMPDF_ENABLE_AUTOLOAD', false);
        }
        $dompdf = new Dompdf;
        $dompdf->setPaper(config('knet.paper', 'letter'));
        $dompdf->loadHtml($this->view($data)->render());
        $dompdf->render();
        return $dompdf->output();
    }

    /**
     * Create an transaction download response.
     *
     * @param array $data
     * @return Response
     */
    public function download(array $data)
    {
        $filename = $data['product'] . '_' . $this->date()->month . '_' . $this->date()->year;
        return $this->downloadAs($filename, $data);
    }

    /**
     * Create an transaction download response with a specific filename.
     *
     * @param string $filename
     * @param array $data
     * @return Response
     */
    public function downloadAs($filename, array $data)
    {
        return new Response($this->pdf($data), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
            'X-Vapor-Base64-Encode' => 'True',
        ]);
    }

    /**
     * Dynamically get values from the Stripe transaction.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->transaction->{$key};
    }
}
