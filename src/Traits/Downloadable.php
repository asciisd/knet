<?php

namespace Asciisd\Knet\Traits;

use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

trait Downloadable
{
    /**
     * Create an invoice download response.
     */
    public function download(array $data): Response
    {
        $filename = $data['product'].'_'.$this->date()->month.'_'.$this->date()->year;

        return $this->downloadAs($filename, $data);
    }

    /**
     * Create an invoice download response with a specific filename.
     */
    public function downloadAs($filename, array $data): Response
    {
        return new Response($this->pdf($data), 200, [
            'Content-Description'       => 'File Transfer',
            'Content-Disposition'       => 'attachment; filename="'.$filename.'.pdf"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type'              => 'application/pdf',
            'X-Vapor-Base64-Encode'     => 'True',
        ]);
    }

    /**
     * Capture the invoice as a PDF and return the raw bytes.
     */
    public function pdf(array $data): string
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
     * Get the View instance for the invoice.
     */
    public function view(array $data)
    {
        return View::make('knet::pdf_receipt', array_merge($data, [
            'invoice' => $this,
            'owner'   => $this->owner()
        ]));
    }
}
