<?php

namespace Asciisd\Knet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ReceiptRequest
 *
 * @property string paymentid
 *
 * @package Asciisd\Knet\Http\Requests
 */
class ReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'paymentid' => 'required'
        ];
    }
}
