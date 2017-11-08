<?php

namespace Provisioning\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Provisioning\Centile\DialPlan;
use Provisioning\Centile\Extension;
use Provisioning\Centile\UserExtension;
use Provisioning\Centile\SpeedDial;
use Provisioning\Centile\ExtensionsGroup;
use Provisioning\Centile\Device;
use CentileENT;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('mac_address', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^([0-9A-Fa-f]{2}[:]){5}([0-9A-Fa-f]{2})$/', $value);
        });

        Validator::extend('unique_mac_address', function ($attribute, $value, $parameters, $validator) {
            $except = isset($parameters[0]) ? $parameters[0] : null;
            $value = str_replace(':', '', $value);

            if ($value && strtolower($value) != strtolower($except)) {
                foreach (Device::existingMacAddresses() as $mac) {
                    if (strtolower($value) == strtolower($mac))
                        return false;
                }
            }

            return true;
        });

        Validator::extend('insee_code', function ($attribute, $value, $parameters, $validator) {
            $inseeCodes = config('insee_codes.codes');
            if (in_array($value, $inseeCodes))
                return true;

            return false;
        });

        Validator::extend('extension', function ($attribute, $value, $parameters, $validator) {
            if (!Extension::checkFormat($value))
                return false;

            list($context) = $parameters;
            $enterprise = CentileENT::getEnterprise($context);
            $dialPlan = new DialPlan($enterprise->internalDialplan);
            if (!$dialPlan->includesExtension($value))
                return false;

            return true;
        });

        // $parameters[0] is the context
        // $parameters[1] can contain a value to ignore for uniqueness
        Validator::extend('unassigned_extension', function ($attribute, $value, $parameters, $validator) {
            if (!Extension::checkFormat($value))
                return false;

            if (!isset($parameters[1]))
                $parameters[1] = null;
            list($context, $except) = $parameters;

            if ($value && $value != $except) {
                $enterprise = CentileENT::getEnterprise($context);
                $dialPlan = new DialPlan($enterprise->internalDialplan);
                if (!$dialPlan->includesExtension($value))
                    return false;

                if (Extension::exists($context, $value))
                    return false;
            }

            return true;
        });

        Validator::extend('user_extension', function ($attribute, $value, $parameters, $validator) {
            if (!Extension::checkFormat($value))
                return false;

            if (!isset($parameters[1]))
                $parameters[1] = null;
            list($context, $except) = $parameters;

            if ($value && $value != $except) {
                if (!UserExtension::exists($context, $value))
                    return false;
            }

            return true;
        });

        Validator::extend('user_extension_or_ext_group', function ($attribute, $value, $parameters, $validator) {
            if (!Extension::checkFormat($value))
                return false;

            $context = head($parameters);

            if (!UserExtension::exists($context, $value) && !ExtensionsGroup::exists($context, $value))
                return false;

            return true;
        });

        Validator::extend('extension_or_e164', function ($attribute, $value, $parameters, $validator) {
            $context = head($parameters);

            if (isE164format($value))
                return true;
            elseif (Extension::checkFormat($value) && Extension::exists($context, $value))
                return true;

            return false;
        });

        Validator::extend('user_extension_or_e164', function ($attribute, $value, $parameters, $validator) {
            $context = head($parameters);

            if (isE164format($value))
                return true;
            elseif (Extension::checkFormat($value) && UserExtension::exists($context, $value))
                return true;

            return false;
        });

        Validator::extend('forwarding_destination', function ($attribute, $value, $parameters, $validator) {
            $context = head($parameters);

            if (isE164format($value))
                return true;
            elseif (in_array($value, ['USER_MOBILE', 'ENT_VM', 'REJECTION']))
                return true;
            elseif (Extension::checkFormat($value) && UserExtension::exists($context, $value))
                return true;
            elseif (Extension::checkFormat($value) && ExtensionsGroup::exists($context, $value))
                return true;
            elseif (Extension::checkFormat($value) && SpeedDial::exists($context, $value))
                return true;
            return false;
        });

        Validator::extend('user_extensions_list', function ($attribute, $value, $parameters, $validator) {
            $context = head($parameters);
            $exts = explode(',', $value);

            foreach ($exts as $ext) {
                if (Extension::checkFormat($ext) && !UserExtension::exists($context, $ext))
                    return false;
            }

            return true;
        });

        // $parameters[0] can contain a value to ignore for uniqueness
        Validator::extend('unique_email', function ($attribute, $value, $parameters, $validator) {
            $except = null;
            if (count($parameters))
                list($except) = $parameters;

            if ($value && $value != $except) {
                if (CentileENT::getUserByEmail($value))
                    return false;
            }

            return true;
        });

        Validator::extend('login', function ($attribute, $value, $parameters, $validator) {
            if (!$value)
                return false;

            if (!preg_match('/^(([[:alnum:]])+([\-_.]?[[:alnum:]]+)*){6,81}$/', $value))
                return false;

            return true;
        });

        // $parameters[0] can contain a value to ignore for uniqueness
        Validator::extend('unique_login', function ($attribute, $value, $parameters, $validator) {
            $except = null;
            if (count($parameters))
                list($except) = $parameters;

            if ($value && $value != $except) {
                if (CentileENT::getUser($value))
                    return false;
            }

            return true;
        });

        // phone number in international format E164
        Validator::extend('e164_number', function ($attribute, $value, $parameters, $validator) {
            if (!isE164format($value))
                return false;

            return true;
        });

        // only French mobile phone numbers
        Validator::extend('mobile_number_fr', function ($attribute, $value, $parameters, $validator) {
            if (!preg_match('/^(\+33|0)(6|7)\d{8}$/', $value))
                return false;

            return true;
        });

        // only French landline phone numbers
        Validator::extend('fixed_number_fr', function ($attribute, $value, $parameters, $validator) {
            if (!preg_match('/^(\+33|0)(1|2|3|4|5|9)\d{8}$/', $value))
                return false;

            return true;
        });

        // only French landline and mobile phones numbers (without surtaxed numbers)
        Validator::extend('fixed_mobile_number_fr', function ($attribute, $value, $parameters, $validator) {
            if (!preg_match('/^(\+33|0)(1|2|3|4|5|6|7|9)\d{8}$/', $value))
                return false;

            return true;
        });

        // all French phone numbers
        Validator::extend('phone_number_fr', function ($attribute, $value, $parameters, $validator) {
            if (!preg_match('/^(\+33|0)\d{9}$/', $value))
                return false;

            return true;
        });

        Validator::extend('call_barring', function ($attribute, $value, $parameters, $validator) {
            $callBarrings = collect(CentileENT::getCallBarrings("@NULL"))->pluck('name')->toArray();
            if (!in_array($value, $callBarrings))
                return false;

            return true;
        });

        Validator::extend('unique_pstn', function ($attribute, $value, $parameters, $validator) {
            if ($pstn = CentileENT::getPstnNumber($value)) {
                if ($pstn->admtiveDomain != 'Top-Level')
                    return false;
            }

            return true;
        });

        Validator::extend('unique_trunk_pstn', function ($attribute, $value, $parameters, $validator) {
            return true;
        });

        Validator::extend('existing_pstn', function ($attribute, $value, $parameters, $validator) {
            list($context) = $parameters;
            $pstns = CentileENT::getPstnNumbers($context);
            foreach ($pstns as $pstn)
                if ($pstn->number == $value)
                    return true;

            return false;
        });

        Validator::extend('dialplan', function ($attribute, $value, $parameters, $validator) {
            if (!DialPlan::isValid($value, true))
                return false;

            return true;
        });

        Validator::extend('dialplan_not_restricted', function ($attribute, $value, $parameters, $validator) {
            if (DialPlan::hasRestrictedMask($value))
                return false;

            return true;
        });

        Validator::extend('public_ipv4', function ($attribute, $value, $parameters, $validator) {
            if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4|FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))
                return false;

            return true;
        });

        Validator::extend('label', function ($attribute, $value, $parameters, $validator) {
            $pattern = '/^[\pL\d\s~`!@#$%ˆ&*()_\-+={}\[\]|\\:;"\'<>,.?\/]+$/u';
            if (!preg_match($pattern, $value))
                return false;

            return true;
        });

        Validator::extend('device_model', function ($attribute, $value, $parameters, $validator) {
            foreach (CentileENT::getDeviceModels() as $model) {
                if ($value == $model->name)
                    return true;
            }

            return false;
        });

        Validator::extend('no_logical_terminal', function ($attribute, $value, $parameters, $validator) {
            list($context) = $parameters;

            if (!Extension::checkFormat($value))
                return false;

            if (CentileENT::getLogicalTerminalsByExtension($context, $value))
                return false;

            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
