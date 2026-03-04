<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:schools,id',
            'amount' => 'required|integer|min:100', // Minimum $1
            'donor_first_name' => 'required|string|max:255',
            'donor_last_name' => 'required|string|max:255',
            'donor_email' => 'required|email|max:255',
            'donor_title' => 'nullable|string|max:50',
            'donor_title2' => 'nullable|string|max:50',
            'donor_first_name2' => 'nullable|string|max:255',
            'donor_last_name2' => 'nullable|string|max:255',
            'donor_phone' => 'nullable|string|max:50',
            'billing_street' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_state' => 'required|string|max:50',
            'billing_postal_code' => 'required|string|max:20',
            'billing_country' => 'required|string|max:2',
            'mailing_street' => 'nullable|string|max:255',
            'mailing_city' => 'nullable|string|max:255',
            'mailing_state' => 'nullable|string|max:50',
            'mailing_postal_code' => 'nullable|string|max:20',
            'mailing_country' => 'nullable|string|max:2',
            'filing_year' => 'required|integer|min:2020|max:'.(date('Y') + 1),
            'filing_status' => 'required|integer|in:1,2,3',
            'qco' => 'nullable|string|max:50',
            'tax_professional_name' => 'nullable|string|max:255',
            'tax_professional_phone' => 'nullable|string|max:50',
            'tax_professional_email' => 'nullable|email|max:255',
            'payment_method_id' => 'required|string',
        ];
    }
}
