<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'alert_level' => 'required|in:critical,at_risk,flagged,monitored,resolved',
            'override_note' => 'required_if:admin_override,true|nullable|string|max:2000',
        ];
    }
}
