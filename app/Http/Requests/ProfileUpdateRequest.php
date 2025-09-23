<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'nik' => [
                'nullable',
                'string',
                'digits:16',
                Rule::unique(User::class, 'nik')->ignore($this->user()->id)
            ],
            'nip' => ['nullable', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tgl_lahir' => ['nullable', 'date_format:Y-m-d'],
            'alamat' => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:255'],
            'eselon' => ['nullable', 'string', 'max:255'],
            'tmt_eselon' => ['nullable', 'date_format:Y-m-d'],
            'jenis_jabatan' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'pendidikan_jurusan' => ['nullable', 'string', 'max:255'],
            'pendidikan_universitas' => ['nullable', 'string', 'max:255'],
            'tmt_cpns' => ['nullable', 'date_format:Y-m-d'],
            'tmt_pns' => ['nullable', 'date_format:Y-m-d'],
            'unit_id' => ['required', 'exists:units,id'],
            'jabatan_name' => ['required', 'string', 'max:255'],
            'atasan_id' => ['nullable', 'exists:users,id', 'not_in:'.$this->user()->id],
        ];
    }
}
