<?php 

namespace App\Http\Requests;

use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UserPasswordRequest extends FormRequest
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
        $email = auth()->user()->email;

        return [
            'current_password' => 'required|min:8|max:100|current_password:api',
            'password' => ['required', 'min:8', 'max:100', 'letters', 'numbers', 'case_diff', 'confirmed',
                            new ContextSpecificWords($email), new DerivativesOfContextSpecificWords($email)],
        ];
    }

}