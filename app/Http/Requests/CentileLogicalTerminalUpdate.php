<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileLogicalTerminalUpdate extends FormRequest
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
            'logicalIDs' => 'in:USER_MOBILE',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
