<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use CentileENT;

class CentileDeviceUpdate extends FormRequest
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
            'physicalID' => 'filled|mac_address|unique_mac_address:' . $this->mac,
            'label' => 'label|max:40',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
