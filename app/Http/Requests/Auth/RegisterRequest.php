<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'unit_id' => ['required', 'exists:units,id'],
            'jabatan_id' => [
                'required',
                'exists:jabatans,id',
                // Custom rule to ensure the selected Jabatan is not already taken.
                function ($attribute, $value, $fail) {
                    $jabatan = Jabatan::find($value);
                    if ($jabatan && $jabatan->user_id) {
                        $fail(__('Jabatan yang dipilih sudah terisi. Silakan pilih yang lain.'));
                    }
                },
            ],
        ];
    }
}
