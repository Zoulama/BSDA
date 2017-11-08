<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Provisioning\Centile\Line;

class CentileLineStore extends FormRequest
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
        $device = $this->device->withDeviceModel();
        $maxLines = $device->deviceModel->getMaxLinesSupported();

        $rules = [
            'line' => 'required|numeric|max:' . $maxLines,
            'type' => 'required|in:0,1,2',
            'label' => 'max:50',
        ];

        if ($this->type == Line::TYPE_MONITORING)
            $rules['linkedTo'] = 'required|user_extension_or_ext_group:' . $context;
        elseif ($this->type == Line::TYPE_SPEED_DIAL)
            $rules['linkedTo'] = 'required|extension_or_e164:' . $context;

        return $rules;
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
