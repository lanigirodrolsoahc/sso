<?php

include_once dirname(__FILE__).'/SsoCalls.class.php';

final
class SsoBookkeeperEngine extends SsoCalls
{
    public
    const   P_TOKEN     = 'token',
            P_SOFTWARE  = 'logiciel';

    protected
    const   APP         = CorporateConfig::INTERFACEMOTEUR;

    private $target,
            $token;

    protected
    function buildResponseForUserLog () : Std
    {
        $this->payload = $this->buildEngineSession();

        $this->buildSession();
        $this->keep();

        return $this->payload ?: Std::__new();
    }

    /**
     * builds a bookkeeper engine session
     *
     * @return  Std
     */
    private
    function buildEngineSession () : Std
    {
        return Std::__new()->{ self::APP_TOKEN }( $this->token );
    }

    /**
     * sets target application, based on given software
     *
     * @param   ?string     $application
     *
     * @return  bool
     */
    private
    function setTarget ( string $application = null ) : bool
    {
        return ! is_null( $this->target = self::isSso($application ?? '') ? $application : null );
    }

    /**
     * sets a token for identification keep
     *
     * @param   ?string     $token
     *
     * @return  bool
     */
    private
    function setToken ( string $token = null ) : bool
    {
        return ! is_null( $this->token = $token );
    }

    /**
     * tries to keep connection from `$_GETed` data
     *
     * @return  SsoBookkeeperEngine
     */
    public static
    function start () : SsoBookkeeperEngine
    {
        $sso = self::Instance()->setApplication( static::APP );

        if (
            $sso->setTarget( strtolower($_GET[ self::P_SOFTWARE ] ?? '') )
            && $sso->setToken( $_GET[ self::P_TOKEN ] ?? null )
            && ! empty( (array) $sso->buildResponseForUserLog() )
        )
            $sso->buildSession();
        elseif ( ! $sso->keep() )
            $sso->logOut(false);

        return $sso;
    }
}
