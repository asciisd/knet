<?php

namespace Asciisd\Knet\Exceptions;

use Illuminate\Http\Request;
use Throwable;

class Handler
{
    public function handle(Request $request, Throwable $e)
    {
        logger()->error('Knet Error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        if ($e instanceof KnetException) {
            return redirect()->route('knet.error', [
                'error' => 'payment_failed',
                'error_text' => $e->getMessage(),
            ]);
        }

        throw $e;
    }
} 