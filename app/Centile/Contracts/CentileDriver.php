<?php

namespace Provisioning\Centile\Contracts;

use Provisioning\Centile\PBXTrunking;
use Provisioning\Centile\PSTNRange;

interface CentileDriver
{
    public function __construct($username, $password, $wsdl_type = self::ROUTING_COMMUNITY_TYPE);

    public function login();
    public function setCommunityContext($params);
    public function getAdministrator($parameters);
    public function getAdministrativeDomains();
    public function getRoutingCommunities();

    public function getTrunk($routingCommunity, $parameters);
    public function getTrunks($routingCommunity);
    public function createTrunk($routingCommunity, PBXTrunking $trunk);
    public function updateTrunk($routingCommunity, $trunkName, $params);
    public function deleteTrunk($routingCommunity, $name);

    public function getPstnRange($context, $parameters);
    // public function getPstnRanges($context, $parameters = null, $withSubContexts = false);
    public function getPstnRanges($context, $parameters = null);
    public function createPstnRange(PSTNRange $pstnRange);
    public function deletePstnRange($label);

    public function getPstnNumber($parameters);
    public function getPstnNumbers($context, $parameters = null);
    public function assignPstnToAdmtiveDomain($params);

    public function assignPstnToTrunk($routingCommunity, $params);
    public function unassignPstn($params);
}
