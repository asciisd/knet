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
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'paymentid' => 'required'
        ];
    }
}
