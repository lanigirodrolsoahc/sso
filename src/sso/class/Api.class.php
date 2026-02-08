<?php

namespace SSO;

class Api extends SsoServer
{
    use Errors;

    private
    const   ERR_BAD_PARAMS      = 'Bad parameters!',
            ERR_INVALID_TOKEN   = 'Invalid token!',
            ERR_PWD_RENEWAL     = 'User has to renew his password!',
            ERR_UNKNOWN_APP     = 'Unable to identify application...',
            ERR_UKNOWN_USER     = 'Unable to identify user...',
            ERR_UNSAVED_TOKEN   = 'Unable to save token';

    public
    const   KEY_APP_KEY         = 'applicationKey',
            KEY_APP_NAME        = 'applicationName',
            KEY_USER_LOGIN      = 'userLogin',
            KEY_USER_PWD        = 'userPassword';

    protected $staticAuth = false;

    /**
     * authenticates user
     *
     * @param   ?array  $query
     *
     * @url PUT /auth
     * @url PUT /api/auth
     */
    public
    function auth ( $query = [] ) : void
    {
        $this->server->setStatus(self::CODE_BAD_REQUEST);

        if (
            ! $this
                ->setControls(self::KEY_APP_NAME, self::KEY_APP_KEY, self::KEY_USER_LOGIN, self::KEY_USER_PWD)
                ->setPhpInputOrQuery($query)
                ->start()
        )
            $this->error(self::ERR_BAD_PARAMS);
        elseif (
            ! ( $app = Application::Instance() )
                ->fill(
                    \Std::__new()->{ $app->f_name }( $this->vars->{ self::KEY_APP_NAME } )
                )
                ->read()
            || \KryptoSso::Instance()->uncrypt( $app->getVirtual()->{ $app->f_key } ) !== $this->vars->{ self::KEY_APP_KEY }
        )
            $this->error(self::ERR_UNKNOWN_APP);
        elseif (
            ! ( $user = User::Instance() )
                ->fill(
                    \Std::__new()
                        ->{ $user->f_login }( $this->vars->{ self::KEY_USER_LOGIN } )
                )
                ->read()
            || ! $user->isActive()
        )
            $this->error(self::ERR_UKNOWN_USER);
        elseif ( ! Password::verify( $this->vars->{ self::KEY_USER_PWD }, $user->getVirtual()->{ $user->f_password } ) )
            $this->error(Form::ERR_INVALID_PWD);
        elseif (
            $user->mustRenew()
        )
            $this
                ->error(self::ERR_PWD_RENEWAL)
                ->server->setStatus(self::CODE_UNAUTHORIZED);
        elseif (
            ! (
                (
                    ( $token = Token::Instance() )
                        ->fill(
                            \Std::__new()
                                ->{ $token->f_userId }( $userId = $user->getVirtual()->{ $user->f_id } )
                                ->{ $token->f_type }( Token::TYPE_API )
                        )
                        ->read()
                    && $token->keep()
                )
                || $token->refresh($userId, Token::TYPE_API)
            )
        )
            $this->error(self::ERR_UNSAVED_TOKEN);
        else
            $this->server->setStatus(self::CODE_OK);

        $this->renderTokenizedUser();
    }

    /**
     * checks that:
     * - token is in request
     * - any additional requirement is in request
     * - token is valid
     * - 👉 then renews token
     *
     * @param   array       $query
     * @param   ?string     $required
     *
     * @return  bool
     */
    private
    function checkTokenAndCo ( array $query, ...$required ) : bool
    {
        $this->server->setStatus(self::CODE_BAD_REQUEST);

        if (
            ! $this
                ->setControls( ...array_merge([Token::PARAM_NAME], $required) )
                ->setPhpInputOrQuery($query)
                ->start()
        )
            $this->error(self::ERR_BAD_PARAMS);
        elseif (
            ! ( $token = Token::Instance() )
                ->fill(
                    \Std::__new()->{ $token->f_content }( $this->vars->{ Token::PARAM_NAME } )
                )
                ->read()
            || ! $token->isValid()
        )
            $this->error(self::ERR_INVALID_TOKEN);
        elseif ( ! $token->keep() )
            $this->error(self::ERR_UNSAVED_TOKEN);

        return ! $this->errored();
    }

    /**
     * gets available error, considering you only generated one
     *
     * @return  \Std
     */
    private
    function getError () : \Std
    {
        return \Std::__new()->error( $this->failures()[0] );
    }

    /**
     * keeps token alive
     *
     * @param   ?array  $query
     *
     * @url POST /keepAlive
     * @url POST /api/keepAlive
     */
    public
    function keepAlive ( $query = [] ) : void
    {
        if (
            $this->checkTokenAndCo($query)
            && User::Instance()->is( ( $token = Token::Instance() )->getVirtual()->{ $token->f_userId } )
        )
            $this->server->setStatus(self::CODE_OK);

        $this->renderTokenizedUser();
    }

    /**
     * gets last token and its expiration timestamp
     *
     * @return  \Std
     */
    private
    function lastTokenValue () : \Std
    {
        return \Std::__new()
            ->content( ( $token = Token::Instance() )->getGenerated() )
            ->expires( $token->getVirtual()->{ $token->f_expiration } );
    }

    /**
     * lists all active users for SSO
     *
     * @param   ?array      $query
     * @param   ?string     $scope  application name
     * @param   ?string     $name   see `$this->listGroupMembers()`
     *
     * @url GET /listUsers
     * @url GET /api/listUsers
     * @url GET /listUsers/$scope
     * @url GET /api/listUsers/$scope
     */
    public
    function listUsers ( $query = [], ?string $scope = null, ?string $name = null ) : void
    {
        if ( $this->checkTokenAndCo($query) )
            $this->server->setStatus(self::CODE_OK);

        $this->render(
            $this->errored()
                ? $this->getError()
                : \Std::__new()
                    ->token( $this->lastTokenValue() )
                    ->list(
                        ( $user = User::Instance() )
                            ->deactivateExpired()
                            ->publicFetchAll(
                                ( $noScope = is_null($scope) ) && is_null($name)
                                    ? $user->any()
                                    : (
                                        $noScope
                                            ? $user->fromGroup( urldecode($name) )
                                            : $user->fromApplication($scope)
                                    )
                            )
                    )
        );
    }

    /**
     * lists all members of a group
     *
     * @param   string  $name
     *
     * @url GET /api/listUsers/group/$name
     * @url GET /listUsers/group/$name
     */
    public
    function listGroupMembers ( string $name ) : void
    {
        $this->listUsers([], null, $name);
    }

    /**
     * renders encountered errors or token and public user
     */
    private
    function renderTokenizedUser () : void
    {
        $this->render(
            $this->errored()
                ? $this->getError()
                : \Std::__new()
                    ->token( $this->lastTokenValue() )
                    ->user( User::Instance()->publicFetch() )
        );
    }
}
