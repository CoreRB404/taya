<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLegalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $detainee = $this->route('detainee');

        return ($this->user()->canManageOperations() || $this->user()->isLawyer())
            && $this->user()->can('view', $detainee);
    }

    public function rules(): array
    {
        return [
            'action_type' => 'required|in:motion_for_release,habeas_corpus,pao_referral,ngo_referral,case_review,other',
            'alert_id' => [
                'required',
                'integer',
                Rule::exists('alerts', 'id')->where('detainee_id', $this->route('detainee')->id),
            ],
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
