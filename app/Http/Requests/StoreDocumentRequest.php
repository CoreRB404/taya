<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Document;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Document::class, $this->route('detainee')]);
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimetypes:application/pdf,image/jpeg,image/png|extensions:pdf,jpg,jpeg,png|max:10240',
            'doc_type' => 'required|in:commitment_order,charge_sheet,court_record,other',
            'phase_number' => 'nullable|integer|between:1,4',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'The file must not exceed 10MB.',
            'file.mimes' => 'Only PDF, JPG, and PNG files are allowed.',
        ];
    }

}
