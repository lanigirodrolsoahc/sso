<?php

include_once ( $dirSsoCommons = dirname(__FILE__) ).'/../../CommonServer.class.php';
include_once $dirSsoCommons.'/SsoCalls.class.php';

class SsoCommons extends CommonServer
{
    private
    const       CODE_GONE   = 410;

    public
    function __setDirname ()
    {
        $this->dirname = dirname(__FILE__);
    }

    /**
     * checks session's validity, answering only by
     * - a `200` status code and remaining time for session validity limit, in milliseconds
     * - or a `410` status code without any answer
     *
     * @url GET /services/sessionCheck
     * @url GET /corporateApp1/vendor/corporate/shared/components/sso/services/sessionCheck
     * @url GET /equipments/vendor/corporate/shared/components/sso/services/sessionCheck
     * @url GET /IndicateurISO/vendor/corporate/shared/components/sso/services/sessionCheck
     * @url GET /InterfaceMoteur/vendor/corporate/shared/components/sso/services/sessionCheck
     * @url GET /Intranet/shared/components/sso/services/sessionCheck
     * @url GET /IntraProd/vendor/corporate/shared/components/sso/services/sessionCheck
     * @url GET /IntraStock/vendor/corporate/shared/components/sso/services/sessionCheck
     * @url GET /RevueDeContrat/vendor/corporate/shared/components/sso/services/sessionCheck
     * @url GET /corporateApp2/vendor/corporate/shared/components/sso/services/sessionCheck
     */
    public
    function sessionCheck () : void
    {
        do
        {
            $this->server->setStatus(self::CODE_GONE);

            SessionUtils::start();
            DateUtils::zoned();

            if ( ! ( $stored = SsoCalls::getSessionParam( SsoCalls::APP_EXPIRES_AT) ) ) break;
            if ( ! DateUtils::validDate($stored) ) break;
            if ( strtotime('now') > strtotime($stored) ) break;

            $this->server->setStatus(self::CODE_OK);

            $data   = Std::__new()->token( Std::__new()->expires($stored) );

            $this->render(
                Std::__new()->{ SsoCalls::APP_EXPIRES_IN }( SsoCalls::calcTokenLifetime($data) )
            );
        }
        while ( 0 );
    }
}
