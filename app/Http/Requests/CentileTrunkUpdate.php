<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileTrunkUpdate extends FormRequest
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
        return $rules = [
            'label' => 'max:30',
            'maxChannels' => 'filled|in:2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,40,50,60',
            'areaCode' => 'filled|insee_code',
            'defaultPstn' => 'e164_number|phone:AUTO,FR|unique_trunk_pstn',
            'unreachable' => 'e164_number|phone:AUTO,FR',
            'callBarrings' => 'filled|array',
            'callBarrings.*' => 'filled|call_barring',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
