<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileUserUpdate extends FormRequest
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
        $context = $this->prestation->getCentileContext();
        $user = $this->user;
        $extension = $user->extension ? $user->extension : null;

        return [
            'extension' => 'filled|unassigned_extension:' . $context . ',' . $extension,
            'email' => 'email|unique_email:' . $user->emails,
            'login' => 'filled|login|unique_login:' . $user->login,
            'password' => 'filled|numeric|digits_between:4,12',
            'firstName' => 'sometimes|required_without:lastName|max:40',
            'lastName' => 'sometimes|required_without:firstName|max:40',
            'mobileNumber' => 'mobile_number_fr|e164_number',
            'callBarrings.*' => 'filled|call_barring',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
