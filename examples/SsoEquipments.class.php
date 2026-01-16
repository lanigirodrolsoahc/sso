<?php

include_once dirname(__FILE__).'/SsoCalls.class.php';

final
class SsoEquipments extends SsoCalls
{
    protected
    const       APP     = CorporateConfig::INTRAPARC;

    protected
    function buildResponseForUserLog () : Std
    {
        return $this->payload = $this->buildCommonSession($this->application);
    }

    /**
     * sets current application and logs out, redirecting
     */
    public static
    function logMeOut () : void
    {
        self::Instance()
            ->setApplication( static::APP )
            ->logOut();
    }
}
