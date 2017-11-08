<?php

function toE164($number) {
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

    $numberProto = $phoneUtil->parse($number, "FR");

    if (!$phoneUtil->isValidNumber($numberProto))
        throw new Provisioning\Exceptions\InvalidPhoneNumberException('Invalid Phone Number ' . $number);

    return $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
}

function isE164format($number) {
    if (preg_match('/^\+\d+$/', $number))
        return true;

    return false;
}