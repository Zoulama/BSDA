<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentilePstnNumberStore extends FormRequest
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
        return [
            'PSTNNumbers' => 'required|array',
            'PSTNNumbers.*' => 'required|e164_number|fixed_number_fr|unique_pstn',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
