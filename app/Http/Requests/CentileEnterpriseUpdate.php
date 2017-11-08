<?php

namespace Provisioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Provisioning\ComptaPrestation as Prestation;
use CentileENT;

class CentileEnterpriseUpdate extends FormRequest
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
        $prestation = $this->prestation;

        if (!$enterprise = CentileENT::getEnterprise($prestation->getCentileContext()))
            abort(404);

        return [
            'defaultPSTNNumber' => 'e164_number|fixed_number_fr|existing_pstn:' . $prestation->getCentileContext(),
            'label' => 'filled|max:128|label|unique:istra.COMMUNITY,FULLNAME,' . $enterprise->fullName . ',FULLNAME',
            'maxChannels' => 'filled|in:2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,40,50,60',
            'dialplan' => 'filled|dialplan|dialplan_not_restricted',
            'areaCode' => 'filled|insee_code',
            'restriction' => 'filled|in:0,1,2',
        ];
    }

    public function response(array $errors)
    {
        return response()->json(['errors' => $errors], 400);
    }
}
