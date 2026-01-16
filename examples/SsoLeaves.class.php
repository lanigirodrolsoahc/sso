<?php

include_once dirname(__FILE__).'/SsoCalls.class.php';

final
class SsoLeaves extends SsoCalls
{
    protected
    const       APP     = CorporateConfig::LEAVES;

    protected
    function buildResponseForUserLog () : Std
    {
        return $this->payload = $this->buildCommonSession($this->application);
    }
}
