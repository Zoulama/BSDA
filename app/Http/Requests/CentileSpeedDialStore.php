<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileSpeedDialStore extends FormRequest
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
            'pstn' => 'required|e164_number',
            'label' => 'max:100',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors]);
    }
}
