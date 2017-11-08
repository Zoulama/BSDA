<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use CentileENT;

class CentileDeviceStore extends FormRequest
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

        $rules = [
            'physicalID' => 'required|mac_address|unique_mac_address',
            'model' => 'required|device_model',
            'label' => 'label|max:40',
            'extension' => 'user_extension:' . $context,
        ];

        $deviceModel = CentileENT::getDeviceModel($this->model);
        if ($deviceModel && $deviceModel->manufacturer == 'Gigaset')
            $rules['secret'] = 'required|alpha_num|size:4';

        return $rules;
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
