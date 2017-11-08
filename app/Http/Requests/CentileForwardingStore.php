<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileForwardingStore extends FormRequest
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
            'assignedTo' => 'required|user_extension:' . $context,
            'type' => 'required|in:AL,UR,NA,OB',
            'destination' => 'required|forwarding_destination:' . $context . '|not_in:' . $this->assignedTo,
            'label' => 'label',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
