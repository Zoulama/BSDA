<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentilePstnNumberUpdate extends FormRequest
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
        $prestation = $this->prestation;
        $context = $prestation->getCentileContext();

        return [
            'extension' => 'extension:' . $context,
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
