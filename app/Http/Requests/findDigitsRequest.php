<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class findDigitsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'left' => 'required|array',
            'left.*' => 'string|nullable',
            'right' => 'required|array',
            'right.*' => 'required|string|nullable',
        ];
    }
}
