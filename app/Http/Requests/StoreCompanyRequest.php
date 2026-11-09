<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:64', 'unique:companies,serial_number'],
            'airwork_enabled' => ['sometimes', 'boolean'],
            'indeed_email' => ['nullable', 'email'],
        ];
    }
}
