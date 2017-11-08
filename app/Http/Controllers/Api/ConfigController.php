<?php

namespace Provisioning\Http\Controllers\Api;

use Illuminate\Support\Facades\Lang;
use Provisioning\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function getConfig()
    {
        return response()->json([
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.provision'),
                'permissions' => [
                    'adminEditPresta' => Lang::get('api/conf.permissions.edit'),
                    'adminLinkPresta' => Lang::get('api/conf.permissions.link'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.domain_name'),
                'permissions' => [
                    'adminViewPrestaDomain' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaDomain' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaDomain' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.dns'),
                'permissions' => [
                    'adminViewPrestaNS' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaNS' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaNS' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.web_hosting'),
                'permissions' => [
                    'adminViewPrestaHosting' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaHosting' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaHosting' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.web_mail'),
                'permissions' => [
                    'adminViewPrestaMX' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaMX' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaMX' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.radius_rtc'),
                'permissions' => [
                    'adminViewPrestaRadiusRTC' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaRadiusRTC' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaRadiusRTC' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.access_xDSL'),
                'permissions' => [
                    'adminViewPrestaCollecteDSL' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaCollecteDSL' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaCollecteDSL' => Lang::get('api/conf.permissions.create'),
                    'CollecteDSL_onHold' => Lang::get('api/conf.permissions.put_on_hold'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.radius_xDSL_expert'),
                'permissions' => [
                    'adminEditPrestaRadiusDSLx' => Lang::get('api/conf.permissions.expert_edit'),
                    'adminCreatePrestaRadiusDSLx' => Lang::get('api/conf.permissions.expert_create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.radius_xDSL'),
                'permissions' => [
                    'adminViewPrestaRadiusDSL' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaRadiusDSL' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaRadiusDSL' => Lang::get('api/conf.permissions.create'),
                    'adminCreatePrestaRadiusDSLl' => Lang::get('api/conf.permissions.create_login'),
                    'adminViewPrestaRadiusDSLp' => Lang::get('api/conf.permissions.view_password'),
                    'adminEditPrestaRadiusDSLp' => Lang::get('api/conf.permissions.edit_password'),
                    'adminCreatePrestaRadiusDSLp' => Lang::get('api/conf.permissions.create_password'),
                    'RadiusDSL_netPNAT' => Lang::get('api/conf.permissions.network_p_nat'),
                    'RadiusDSL_netPP' => Lang::get('api/conf.permissions.network_p_p'),
                    'RadiusDSL_netWPN' => Lang::get('api/conf.permissions.network_wpn'),
                    'RadiusDSL_netManual' => Lang::get('api/conf.permissions.network_manual'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.modem'),
                'permissions' => [
                    'adminViewPrestaModem' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaModem' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaModem' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.pack_mobile_office'),
                'permissions' => [
                    'adminViewPrestaDomBM' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaDomBM' => Lang::get('api/conf.permissions.edit'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.mobile_office'),
                'permissions' => [
                    'adminViewPrestaBM' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaBM' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaBM' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.database'),
                'permissions' => [
                    'adminViewPrestaDatabase' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaDatabase' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaDatabase' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.switchless'),
                'permissions' => [
                    'adminViewPrestaSwitchless' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaSwitchless' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaSwitchless' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.voice_over_ip'),
                'permissions' => [
                    'adminViewPrestaVoIP' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaVoIP' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaVoIP' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.phones_grid'),
                'permissions' => [
                    'adminViewPrestaGrilleTel' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaGrilleTel' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaGrilleTel' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.vgast_line'),
                'permissions' => [
                    'adminViewPrestaVGAST' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaVGAST' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaVGAST' => Lang::get('api/conf.permissions.create'),
                    'adminCancelPrestaVGAST' => _('Cancel'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.yellow_pages'),
                'permissions' => [
                    'adminViewPrestaYellowP' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaYellowP' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaYellowP' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.fllu'),
                'permissions' => [
                    'adminViewPrestaFLLU' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaFLLU' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaFLLU' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'permissions',
                'section' => Lang::get('api/conf.section.efm'),
                'permissions' => [
                    'adminViewPrestaEFM' => Lang::get('api/conf.permissions.view'),
                    'adminEditPrestaEFM' => Lang::get('api/conf.permissions.edit'),
                    'adminCreatePrestaEFM' => Lang::get('api/conf.permissions.create'),
                ],
            ],
            [
                'type' => 'capacity',
                'capacity' => 'client',
                'icon' => 'icon-shopping-cart',
                'caption' => Lang::get('api/conf.section.provisions'),
                'url' => route('client_show', ['__CLIENTID__']),
            ],
        ]);
    }
}
