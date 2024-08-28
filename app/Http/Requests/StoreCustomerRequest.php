<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
{
    return [
        'name' => ['required', 'max:50'],
        'kana' => ['required', 'regex:/\A[ァ-ヴー]+\z/u', 'max:50'],
        'tel' => ['required', 'max:20', 'unique:customers,tel'], 
        'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
        'postcode' => ['required', 'max:7'],
        'birthday' => ['required', 'max:100'], 
        'gender' => ['required'],
        'memo' => ['max:1000']
    ];
}

}