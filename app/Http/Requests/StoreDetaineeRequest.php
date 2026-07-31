<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Detainee;

class StoreDetaineeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $detainee = $this->route('detainee');

        return $detainee instanceof Detainee
            ? $this->user()->can('update', $detainee)
            : $this->user()->can('create', Detainee::class);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'charge_description' => ['required', 'string', 'min:3', 'max:5000'],
            'charge_rpc_code' => ['required', 'integer', Rule::exists('penalty_references', 'id')],
            'commitment_date' => 'required|date|before_or_equal:today',
            'facility_id' => [
                'required',
                'integer',
                Rule::exists('facilities', 'id'),
                Rule::when($this->user()->isFacilityStaff(), Rule::in([$this->user()->facility_id])),
            ],
            'bail_amount' => 'nullable|required_if:bail_status,posted|integer|min:0|max:1000000000',
            'bail_status' => 'nullable|in:not_posted,posted,unable_to_pay,pending_review',
            'bail_posted_at' => 'nullable|required_if:bail_status,posted|date|before_or_equal:now',
            'bail_notes' => 'nullable|string|max:5000',
            'relative_name' => 'nullable|string|max:255',
            'relative_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9 ()-]{7,20}$/'],
            'relative_email' => 'nullable|email|max:255',
            'tracking_enabled' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'charge_rpc_code.exists' => 'The selected charge code does not exist in the penalty reference database.',
            'commitment_date.before_or_equal' => 'The commitment date cannot be in the future.',
        ];
    }
}
