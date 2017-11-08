<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileForwardingUpdate extends FormRequest
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
        $forwarding = $this->forwarding;

        return [
            'activated' => 'filled|in:true,false',
            'type' => 'filled|in:AL,UR,NA,OB',
            'destination' => 'filled|forwarding_destination:' . $context,
            'label' => 'label',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors]);
    }
}
