<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            'username' => 'required|string|unique:users',
            'profilepict' => 'required|image|mimes:jpg,png,jpeg,webp|max:5120',
            'name' => 'required|string',
            'email' => 'required|string|email:filter|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah ada',
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password harus terdiri dari minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'profilepict.required' => 'Foto profil wajib diunggah',
            'profilepict.image' => 'Foto profil harus berupa gambar',
            'profilepict.mimes' => 'Foto profil harus memiliki format jpg, png, jpeg, atau webp',
            'profilepict.max' => 'Foto profil tidak boleh lebih dari 5 MB',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new ValidationException($validator, response()->json([
            'status' => 'failed',
            'error' => $errors,
        ]));
    }
}
