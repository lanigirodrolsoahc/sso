<?php

include_once dirname(__FILE__).'/SsoCalls.class.php';

final
class corporateApp1 extends SsoCalls
{
    protected
    const       APP     = CorporateConfig::corporateApp1;

    protected
    function buildResponseForUserLog () : Std
    {
        return $this->payload = $this->buildCommonSession($this->application);
    }
}
