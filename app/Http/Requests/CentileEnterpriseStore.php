<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CentileEnterpriseStore extends FormRequest
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
        if (is_array($this->input('PSTNNumbers')))
            $pstns = implode(',', $this->input('PSTNNumbers'));

        return [
            'PSTNNumbers' => 'required|array',
            'PSTNNumbers.*' => 'filled|e164_number|fixed_number_fr|unique_pstn',
            'defaultPSTNNumber' => 'e164_number|fixed_number_fr|in:' . $pstns,
            'label' => 'required|max:128|label|unique:istra.COMMUNITY,FULLNAME',
            'maxChannels' => 'required|in:2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,40,50,60',
            'dialplan' => 'required|dialplan|dialplan_not_restricted',
            'areaCode' => 'required|insee_code',
            'outsideLinePrefixDigit' => 'present|digits:1',
            'allowedPublicIPAddress' => 'present|public_ipv4',
            'restriction' => 'in:0,1,2',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
