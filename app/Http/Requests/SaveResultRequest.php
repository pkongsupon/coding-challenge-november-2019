<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveResultRequest extends FormRequest
{
    public function rules()
    {
        return [
            '*.log_id' => 'required|integer',
            '*.order' => 'required|integer',
            '*.result' => 'required',
            '*.attampt' => 'required|integer'
        ];
    }
}
