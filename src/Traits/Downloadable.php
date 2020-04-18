<?php

namespace Asciisd\Knet\Traits;

use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

trait Downloadable
{
    /**
     * Get the View instance for the invoice.
     *
     * @param array $data
     * @return \Illuminate\Contracts\View\View
     */
    public function view(array $data)
    {
        return View::make('knet::pdf_receipt', array_merge($data, [
            'invoice' => $this,
            'owner' => $this->owner()
        ]));
    }

    /**
     * Capture the invoice as a PDF and return the raw bytes.
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
     * Create an invoice download response.
     *
     * @param array $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(array $data)
    {
        $filename = $data['product'] . '_' . $this->date()->month . '_' . $this->date()->year;

        return $this->downloadAs($filename, $data);
    }

    /**
     * Create an invoice download response with a specific filename.
     *
     * @param string $filename
     * @param array $data
     * @return \Symfony\Component\HttpFoundation\Response
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
}
