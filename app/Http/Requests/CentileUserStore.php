<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileUserStore extends FormRequest
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

        return [
            'extension' => 'required|unassigned_extension:' . $context,
            'email' => 'email|unique_email|max:120',
            'login' => 'required|login|unique_login',
            'password' => 'required|numeric|digits_between:4,12',
            'firstName' => 'required_without:lastName|max:40',
            'lastName' => 'required_without:firstName|max:40',
            'mobileNumber' => 'mobile_number_fr|e164_number',
            'callBarrings.*' => 'filled|call_barring',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
