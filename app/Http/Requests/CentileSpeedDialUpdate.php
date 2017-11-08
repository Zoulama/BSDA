<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileSpeedDialUpdate extends FormRequest
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
        $speedDial = $this->speedDial;

        return [
            'extension' => 'filled|unassigned_extension:' . $context . ',' . $speedDial->extension,
            'pstn' => 'filled|e164_number',
            'label' => 'max:100',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors]);
    }
}
