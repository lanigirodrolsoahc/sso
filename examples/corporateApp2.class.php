<?php

include_once dirname(__FILE__).'/SsoCalls.class.php';

class corporateApp2 extends SsoCalls
{
    protected
    const       APP     = CorporateConfig::corporateApp2;

    protected
    function buildResponseForUserLog () : Std
    {
        return $this->payload = $this->buildCommonSession( strtoupper($this->application) );
    }
}
