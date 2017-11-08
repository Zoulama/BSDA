<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileTrunkStore extends FormRequest
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
        $pstns = '';
        if (is_array($this->input('PstnNumbers')))
            $pstns = implode(',', $this->input('PstnNumbers'));

        return $rules = [
            'label' => 'required|max:30',
            'maxChannels' => 'required|in:2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,40,50,60',
            'areaCode' => 'required|insee_code',
            'PstnNumbers' => 'filled|array',
            'PstnNumbers.*' => 'filled|e164_number|fixed_number_fr|unique_pstn',
            'defaultPstn' => 'e164_number|phone:AUTO,FR|in:' . $pstns,
            'unreachable' => 'e164_number|phone:AUTO,FR',
            'callBarrings' => 'filled|array',
            'callBarrings.*' => 'filled|call_barring',
            'allowedPublicIPAddress' => 'present|public_ipv4',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
