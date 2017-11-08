<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use CentileENT;

class CentileConferenceBridgeUpdate extends FormRequest
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
        $conference = CentileENT::getConferenceBridge($context);
        $extension = $conference ? $conference->extension : null;

        return [
            'extension' => 'present|unassigned_extension:' . $context . ',' . $extension,
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
