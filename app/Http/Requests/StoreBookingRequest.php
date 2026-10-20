<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'persons_standard' => ['required', 'integer', 'min:0'],
            'persons_fai' => ['required', 'integer', 'min:0'],
            'slot_id' => ['nullable', 'integer', 'exists:slots,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'string', 'in:in_loco,stripe,online'],
            'fai_cards' => ['nullable', 'array'],
            'fai_cards.*' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'persons_standard' => 'partecipanti interi',
            'persons_fai' => 'partecipanti soci FAI',
            'slot_id' => 'fascia oraria',
            'first_name' => 'nome',
            'last_name' => 'cognome',
            'email' => 'indirizzo e-mail',
            'phone' => 'recapito telefonico',
            'payment_method' => 'metodo di pagamento',
            'fai_cards.*' => 'numero tessera FAI',
        ];
    }
}
