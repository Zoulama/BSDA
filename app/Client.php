<?php

namespace Provisioning;
use Provisioning\Helpers\LibClient;
use JsonSerializable;

class Client implements JsonSerializable
{
    protected $id;
    protected $name;
    protected $groupId;
    protected $resellerId;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function getResellerId()
    {
        return $this->resellerId;
    }

    public static function find($id)
    {
        if (!$clientApi = LibClient::getClientById($id))
            return null;

        $client = new self;
        $client->id = $clientApi['clientID'];
        $client->name = $clientApi['clientNom'];
        $client->groupId = $clientApi['groupID'];
        $client->resellerId = $clientApi['parentID'];

        return $client;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $attributes = [];
        foreach (get_object_vars($this) as $name => $value)
            $attributes[$name] = $value;

        return $attributes;
    }
}
