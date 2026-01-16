<?php

namespace SSO;

class Form extends SsoServer
{
    use Errors;

    public
    const   ERR_AVATAR_UNKNOWN      = 'Avatar inconnu',
            ERR_BAD_PWD_ID          = 'Utilisateur mal identifié...',
            ERR_BAD_REQUEST         = 'Erreur de requête !',
            ERR_OLD_PWD_MEMORY      = 'Erreur de mémorisation de l\'ancien mot de passe !',
            ERR_INACTIVE_USER       = 'Cet utilisateur ne semble pas actif',
            ERR_INVALID_TOKEN       = 'Token invalide',
            ERR_PREVIOUSLY          = 'Ce mot de passe a déjà été utilisé !',
            ERR_PWD_COMPLEXITY      = 'Le mot de passe diverge du format canonique.',
            ERR_TOKEN_UNSAVED       = 'Erreur de renouvellement du token',
            ERR_TOO_MANY_RESETS     = 'Merci de patienter avant l\'émission d\'une nouvelle demande',
            ERR_UKNOWN_TOKEN        = 'Impossible d\'authentifier le token.',
            ERR_UKNOWN_USER         = 'Impossible d\'authentifier l\'utilisateur.',
            ERR_UNSAVED_USER        = 'Utilisateur non mis à jour !',
            ERR_UNSENT_MAIL         = 'Erreur lors de la tentative d\'envoi du message électronique !',
            MSG                     = 'msg',
            MSG_OK                  = 'ok',
            URL_CONNECT             = 'services/connect';

    protected $code;

    /**
     * checks user & password and save them
     *
     * @return  int|false   int as return code, false in error cases
     */
    private
    function checkAndSavePassword ()
    {
        if ( ! Password::complexity( $this->vars->{ User::PWD_NEW } ) )
            $this->error(self::ERR_PWD_COMPLEXITY);
        elseif (
            ( $used = UsedPasswords::Instance() )
                ->fill(
                    $fill = \Std::__new()
                        ->{ $used->f_userId }( ( $vo = ( $user = User::Instance() )->getVirtual() )->{ $user->f_id } )
                        ->{ $used->f_content }( $hashed = Password::hash($this->vars->{ User::PWD_NEW }) )
                )
                ->read()
        )
            $this->error(self::ERR_PREVIOUSLY);
        elseif (
            ! $used
                ->fill(
                    $fill->{ $used->f_archived }( $now = $used->now()->format( $used->fmt_timestamp ) )
                )
                ->save()
        )
            $this->error(self::ERR_OLD_PWD_MEMORY);
        elseif (
            ! $user
                ->fill(
                    $vo
                        ->{ $user->f_password }($hashed)
                        ->{ $user->f_lastPwdChange }($now)
                )
                ->save()
        )
            $this->error(self::ERR_UNSAVED_USER);
        else
            $code = self::CODE_OK;

        return $code ?? false;
    }

    /**
     * connects user
     *
     * @url POST /connect
     * @url POST /services/connect
     */
    public
    function connect () : void
    {
        if ( ! $this->controlRequest(User::LOGIN, User::PWD) )
            $this->error(self::ERR_BAD_REQUEST);
        elseif (
            ! ( $user = ( User::Instance() )->deactivateExpired() )
                ->fill(
                    \Std::__new()
                        ->{ $user->f_login }( $this->vars->{ User::LOGIN } )
                        ->{ $user->f_password }( Password::hash($this->vars->{ User::PWD }) )
                )
                ->read()
        )
            $this->error(self::ERR_UKNOWN_USER);
        elseif ( ! $user->isActive() )
            $this->error(self::ERR_INACTIVE_USER);
        elseif ( $user->mustRenew() )
        {
            $renew = ( $token = Token::Instance() )->refresh($userId = $user->getVirtual()->{ $user->f_id }, Token::TYPE_PWD_RENEWAL) ?? true;

            $this->server->setStatus(self::CODE_FORBIDDEN);

            $this
                ->render( \Std::__new()
                    ->{ self::MSG }($url = sprintf(
                        '%1$s%2$s?%3$s',
                        $this->urlRoot,
                        Router::RENEW,
                        http_build_query(
                            \Std::__new()
                                ->{ Token::PARAM_NAME }( $token->getGenerated() )
                                ->{ $user->f_id }($userId)
                        )
                    )));
        }
        elseif (
            ! $user
                ->fill(
                    ( $vo = $user->getVirtual() )
                        ->{ $user->f_lastConnection }( $now = $this->database::now() )
                        ->{ $user->f_lastSessionCheck }($now)
                )
                ->save()
        )
            $this->error(self::ERR_UNSAVED_USER);
        else
        {
            Session::store( User::SESS_MARK, $vo->{ $user->f_id } );

            $code = self::CODE_OK;
        }

        if ( empty($renew) ) $this->out($code ?? false);
    }

    /**
     * controls request
     *
     * @param   string  $controls   needed for current task
     *
     * @return  bool
     */
    protected
    function controlRequest ( string ...$controls ) : bool
    {
        $query = $_REQUEST;

        return $this
            ->errorsReset()
            ->setControls(...$controls)
            ->setAuthFromSession($query)
            ->setQuery($query)
            ->start();
    }

    /**
     * logs dev point
     *
     * @param   string  $method
     */
    protected
    function logDevPoint ( string $method ) : void
    {
        error_log( sprintf('dev point for Form::%1$s', $method) );
    }

    /**
     * redefines password
     *
     * @url POST /redefinePassword
     * @url POST /services/redefinePassword
     */
    public
    function redefinePassword () : void
    {
        if ( ! $this->controlRequest(
            User::PWD_NEW,
            Token::PARAM_NAME,
            $userId = ( $user = User::Instance() )->f_id
        ) )
            $this->error(self::ERR_BAD_REQUEST);
        elseif ( ! $user->is( (int) $this->vars->{ $userId } ) )
            $this->error(self::ERR_UKNOWN_USER);
        else
            $code = $this->checkAndSavePassword();

        $this->out($code ?? false);
    }

    /**
     * sends an email for password reset to given login
     *
     * @url POST /reset
     * @url POST /services/reset
     */
    public
    function reset () : void
    {
        if (
            ! $this->controlRequest( $login = User::LOGIN )
        )
            $this->error(self::ERR_BAD_REQUEST);
        elseif (
            ! ( $user = User::Instance() )
                ->fill(
                    \Std::__new()->{ $user->f_login }( $this->vars->$login )
                )
                ->read()
        )
            $this->error(self::ERR_UKNOWN_USER);
        elseif (
            ( $token = Token::Instance() )
                ->fill(
                    \Std::__new()
                        ->{ $token->f_userId }( ( ( $voUser = $user->getVirtual() )->{ $user->f_id } ) )
                        ->{ $token->f_type }( Token::TYPE_PWD_RENEWAL )
                )
                ->read()
            && $token->isValid()
        )
            $this->error(self::ERR_TOO_MANY_RESETS);
        elseif (
            ! $token->refresh($voUser->{ $user->f_id }, Token::TYPE_PWD_RENEWAL)
        )
            $this->error(self::ERR_TOKEN_UNSAVED);
        elseif (
            ! ( new Mailer() )->send(Mailer::MODEL_LOST_PASSWORD, $user->getVirtual()->{ $user->f_email })
        )
            $this->error(self::ERR_UNSENT_MAIL);
        else
            $code = self::CODE_OK;

        $this->out($code ?? false);
    }

    /**
     * sets new avatar
     *
     * @url POST /setNewAvatar
     * @url POST /services/setNewAvatar
     */
    public
    function setNewAvatar ()
    {
        if ( ! $this->controlRequest(Avatar::CHOICE) )
            $this->error(self::ERR_BAD_REQUEST);
        elseif ( ! Avatar::Instance()->is( $this->vars->{ Avatar::CHOICE } ) )
            $this->error(self::ERR_AVATAR_UNKNOWN);
        elseif (
            ! ( $user = User::Instance() )->getUserFromSession()
        )
            $this->error(self::ERR_UKNOWN_USER);
        elseif (
            ! $user
                ->fill(
                    ( $user->getVirtual() )->{ $user->f_avatarId }( $this->vars->{ Avatar::CHOICE } )
                )
                ->save()
        )
            $this->error(self::ERR_UNSAVED_USER);
        else
            $code = self::CODE_OK;

        $this->out($code ?? false);
    }

    /**
     * sets new password
     *
     * @url POST /setNewPassword
     * @url POST /services/setNewPassword
     */
    public
    function setNewPassword () : void
    {
        if ( ! $this->controlRequest(User::PWD, User::PWD_NEW) )
            $this->error(self::ERR_BAD_REQUEST);
        elseif (
            ! ( $user = User::Instance() )
                ->fill(
                    \Std::__new()
                        ->{ $user->f_id }( Session::retrieve(User::SESS_MARK) )
                        ->{ $user->f_password }( Password::hash($this->vars->{ User::PWD }) )
                )
                ->read()
        )
            $this->error(self::ERR_BAD_PWD_ID);
        else
            $code = $this->checkAndSavePassword();

        $this->out($code ?? false);
    }

    /**
     * sets a new password from outer realm
     *
     * @url POST /setOuterPassword
     * @url POST /services/setOuterPassword
     */
    public
    function setOuterPassword () : void
    {
        if ( ! $this->controlRequest(Token::PARAM_NAME, User::PWD_NEW) )
            $this->error(self::ERR_BAD_REQUEST);
        elseif (
            ! ( $token = Token::Instance() )
                ->fill(
                    \Std::__new()->{ $token->f_content }( $this->vars->{ Token::PARAM_NAME } )
                )
                ->read()
        )
            $this->error(self::ERR_UKNOWN_TOKEN);
        elseif ( ! $token->isValid() )
            $this->error(self::ERR_INVALID_TOKEN);
        elseif ( ! ( $user = User::Instance() )->is( (int) $token->getVirtual()->{ $token->f_userId } ) )
            $this->error(self::ERR_UKNOWN_USER);
        elseif ( ( $code = $this->checkAndSavePassword() ) !== false )
        {
            Session::store( User::SESS_MARK, $userId = (int) $user->getVirtual()->{ $user->f_id } );

            if ( ! $token->refresh($userId, Token::TYPE_PWD_RENEWAL ))
                $this->error(self::ERR_TOKEN_UNSAVED) and $code = false;
        }

        $this->out($code ?? false);
    }

    /**
     * outs an answer
     *
     * @param   int|false   $code
     */
    protected
    function out ( $code ) : void
    {
        $this->server->setStatus( $code !== false ? $code : self::CODE_BAD_REQUEST );

        $this->render( \Std::__new()->{ self::MSG }( $code ? self::MSG_OK : $this->failures() ) );
    }
}
