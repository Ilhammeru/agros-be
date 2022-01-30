<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();
        $rules = [
            'name'  => 'required',
            'role'  => 'required|string',
            'city'  => 'required|string',
            'email' => 'required|email:rfc'
        ];

        if (request()->method() == "POST") {
            $rules['password'] = 'required';
        } else if (request()->method() == "PATCH") {
            if ($user->email != $this->email) {
                $rules['email'] = 'required|unique:users,email';
            }
        }

        return $rules;
    }

    public function messages()
{
    return [
        'email.required' => 'Email harus di isi',
        'email.rfc' => 'Email tidak sesuai',
        'name.required' => 'Nama harus di isi',
        'role.required' => 'Role harus di isi',
        'city.required' => 'Kota Harus di isi',
        'password.required' => 'Password harus di isi'
    ];
}
}
