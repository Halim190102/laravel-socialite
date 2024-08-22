<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ImageUpdateRequest extends FormRequest
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
            'profilepict' => 'required|image|mimes:jpg,png,jpeg,webp|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'profilepict.required' => 'Foto profil wajib diunggah',
            'profilepict.image' => 'Foto profil harus berupa gambar',
            'profilepict.mimes' => 'Foto profil harus memiliki format jpg, png, jpeg, atau webp',
            'profilepict.max' => 'Foto profil tidak boleh lebih dari 5 MB',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new ValidationException($validator, response()->json($errors, 0));
    }
}
