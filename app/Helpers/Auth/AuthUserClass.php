<?php

namespace Provisioning\Helpers\Auth;

use Saml;

class AuthUserClass
{

    public function getUserID()
    {
        return $this->getUserAttribute('uid');
    }


    public function getUserName()
    {
        return $this->getUserAttribute('lastname') . ' ' . $this->getUserAttribute('firstname');
    }

    public function getUserEmail()
    {
        return $this->getUserAttribute('email');
    }

    public function getAttribute($attribute)
    {
        $attributes = Saml::getSamlAttributes();
        if (array_key_exists($attribute, $attributes)) {
            return $attributes[$attribute];
        }
    }

    public function getUserAttribute($attribute)
    {
        $attr = $this->getAttribute($attribute);
        return $attr[0];
    }

    public function getApplications()
    {
        $applications = [];

        $apps = $this->getAttribute('applications') ?: [];
        foreach ($apps as $app)
            $applications[] = unserialize($app);

        return $applications;
    }

    public function hasApplication($apptoken)
    {
        $applications = $this->getApplications();
        foreach ($applications as $app) {
            if ($app['apptoken'] == $apptoken) return true;
        }
        return false;
    }

    /**
     * Check if user has a permission by its name
     *
     * @param string $permission Permission string.
     *
     * @access public
     *
     * @return boolean
     */
    public function can($permission)
    {
        $permissions = $this->getPermissions();
        if (!empty($permissions)) {
            if (is_array($permission)) {
                foreach ($permission as $value) {
                    if (!in_array($value, $permissions)) {
                        return false;
                    }
                }
                return true;
            } else {
                if (in_array($permission, $permissions)) {
                    return true;
                }
            }
        }
        return false;
    }


    public function getUrlProfile()
    {
        return $this->getUserAttribute('url_profile');
    }

    public function getUserGroups()
    {
        return $this->getAttribute('groups');
    }

    public function getCapacities()
    {
        $vcapacities = $this->getAttribute('capacities');
        $capacities = [];
        foreach ($vcapacities as $value) {
            $capacities[] = unserialize($value);
        }
        return $capacities;
    }

    public function getGroupsInfo()
    {
        $groupInfo = [];
        foreach ($this->getAttribute('groupInfo') as $group) {
            $subArray = unserialize($group);
            $groupInfo[] = ['groupID' => $subArray['group_id'], 'groupName' => $subArray['group_name']];
        }
        return $groupInfo;
    }

    public function getGroupName($idGroup)
    {
        foreach ($this->getGroupsInfo() as $group) {
            if ($group['groupID'] == $idGroup) {
                return $group['groupName'];
            }
        }
        return "";
    }

    /**
     * @return array
     */
    public function getGroupInfo()
    {
        $groups = $this->getAttribute('groupInfo');
        $groupInfo = [];
        foreach ($groups as $group) {
            $groupInfo[] = unserialize($group);
        }
        return $groupInfo;
    }

    public function getPermissions()
    {
        $app = $this->getApplication(env('APPTOKEN'));
        if ($app && array_key_exists('permissions', $app)) {
            return json_decode($app['permissions']);
        }
        return false;
    }

    public function getApplication($token)
    {
        $apps = $this->getApplications();
        foreach ($apps as $app) {
            if ($app['apptoken'] == $token) return $app;
        }
        return false;
    }
}