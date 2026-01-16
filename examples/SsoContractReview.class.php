<?php

include_once dirname(__FILE__).'/SsoCalls.class.php';

class SsoContractReview extends SsoCalls
{
    private
    const       G_MOD       = 'mod';

    protected
    const       APP         = CorporateConfig::REVUECONTRAT,
                P_LOGIN     = 'username',
                P_PWD       = 'pwd',
                V_OUT       = 'logout';

    public
    const       CORPORATE2_USERS    = 'corporateApp2Users';

    protected
    function buildResponseForUserLog () : Std
    {
        return $this->payload = $this->buildContractSession();
    }

    /**
     * builds a specific session for current application
     *
     * @return  Std
     */
    private
    function buildContractSession () : Std
    {
        $data = $this->decodeJson();

        return Std::__new()
            ->{ $this->name }( $this->buildContractUser($data) )
            ->{ self::APP_USERS_LIST }( ( $data = $this->listUsers($data->token->content) ? : $data )->list ?? [] )
            ->{ self::CORPORATE2_USERS }(
                array_map(
                    fn ( stdClass $user ) => (array) $user,
                    ( $data = $this->listUsers($data->token->content, CorporateConfig::CorporateApp2) )->list ?? []
                )
            )
            ->{ self::APP_TOKEN }( $data->token->content )
            ->{ self::APP_EXPIRES_IN }( self::calcTokenLifetime($data) )
            ->{ self::APP_EXPIRES_AT }($data->token->expires);
    }

    /**
     * builds user in current application specific format
     *
     * @param   stdClass &$data
     *
     * @return  array
     */
    protected
    function buildContractUser ( stdClass &$data ) : array
    {
        return (array) Std::__new()
            ->__setAll(
                array_merge(
                    [
                        self::APP_RIGHT_R   => ( $piped = $this->pipeRights($data) )->{ self::APP_RIGHT_R },
                        self::APP_RIGHT_W   => $piped->{ self::APP_RIGHT_W },
                        self::APP_USER_ID   => (int) ( $user = $data->user )->id
                    ],
                    (array) $this->buildUser($user)
                )
            );
    }

    /**
     * overwriting from SsoCalls::buildUser
     *
     * @param   stdClass    $user
     *
     * @return  Std
     */
    protected
    function buildUser ( stdClass $user ) : Std
    {
        return Std::__new()
            ->userId( $user->id )
            ->nomUser( $user->lastName )
            ->prenomUser( $user->firstName )
            ->avatar( $user->avatar )
            ->userActive( (bool) $user->status );
    }

    protected
    function getUser ()
    {
        return ( $data = self::getSessionParam( $this->name ) )
            ? Std::__new()
                ->id( ( $data = (object) $data )->userId )
                ->userLastName( $data->prenomUser )
                ->userFirstName( $data->nomUser )
                ->{ $avatar = 'avatar' }( $data->$avatar )
                ->status( $data->userActive )
            : false;
    }

    /**
     * starts sso, building session if adequate `POST` params are to be found
     *
     * @return  SsoContractReview
     */
    public static
    function start () // : static
    {
        $sso = self::Instance()->setApplication( static::APP );

        if (
            ( $log = $_POST[self::P_LOGIN] ?? false )
            && ( $pwd = $_POST[self::P_PWD] ?? false )
            && $sso->logUser($log, $pwd)
        )
            $sso->buildSession();
        elseif ( ( $_GET[self::G_MOD] ?? false ) == static::V_OUT )
            $sso->logOut();
        elseif ( ! $sso->keep() )
            $sso->logOut(false);

        return $sso;
    }

    /**
     * updates user's rights in session
     *
     * @return  bool
     */
    protected
    function updateUserRightsAndToken () : bool
    {
        $data = $this->decodeJson();

        $this->payload = Std::__new()
            ->{ $this->name }( $this->buildContractUser($data) )
            ->{ self::APP_TOKEN }( $data->token->content )
            ->{ self::APP_EXPIRES_IN }( self::calcTokenLifetime($data) )
            ->{ self::APP_EXPIRES_AT }($data->token->expires);

        return $this->buildSession();
    }
}
