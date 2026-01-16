<?php

namespace SSO;

class Router extends SsoServer
{
    use Errors;

    public
    const   RENEW   = 'index/renew';

    /**
     * displays a form to change current user's avatar
     *
     * @url GET /index/avatar
     */
    public
    function avatar () : void
    {
        if ( $this->isConnected() ) SsoView::Instance()->changeAvatar();
        else $this->home();
    }

    /**
     * form to change password
     *
     * @url GET /index/change
     * @url GET /index/change/$token
     */
    public
    function change ( string $token = null ) : void
    {
        if ( $this->isConnected() )
            SsoView::Instance()->changePassword();
        elseif (
            $token !== null
            && ( $vo = Token::Instance() )
                ->fill(
                    \Std::__new()->{ $vo->f_content }($token)
                )
                ->read()
            && $vo->isValid()
            && $vo->refresh( ( $virtual = $vo->getVirtual() )->{ $vo->f_userId }, $virtual->{ $vo->f_type } )
        )
            SsoView::Instance()->changePassword( $vo->getGenerated() );
        else
            $this->home();
    }

    /**
     * form to update user
     *
     * @url GET /index/editUser
     */
    public
    function editUser () : void
    {
        if ( $id = Session::retrieve(User::CREATED_ID) )
        {
            Session::remove(User::CREATED_ID);

            self::headTo( sprintf(
                '%1$sindex/%2$s?%3$s',
                $this->urlRoot,
                __FUNCTION__,
                http_build_query( \Std::__new()->{ User::Instance()->f_id }($id) )
            ) );
        }
        elseif ( $this->isAdmin() )
            AdminView::Instance()->userEdit();
        else
            $this->home();
    }

    /**
     * heads to given URL
     *
     * @param   string  $url
     *
     * @return  bool
     */
    public static
    function headTo ( string $url ) : bool
    {
        header( sprintf('Location: %1$s', $url) );

        return true;
    }

    /**
     * heads home
     */
    private
    function home () : void
    {
        self::headTo($this->urlRoot);
    }

    /**
     * manages index
     *
     * @url GET /
     */
    public
    function index () : void
    {
        if ( ! $this->isConnected() ) SsoView::Instance()->login();
        else UserView::Instance()->welcome();
    }

    /**
     * determines if current user is considered an administrator
     *
     * @return  bool
     */
    private
    function isAdmin () : bool
    {
        return $this->isConnected() && User::Instance()->isAdmin(Belonging::READ);
    }

    /**
     * checks if a user can be found in session
     *
     * @return  bool
     */
    private
    function isConnected () : bool
    {
        return Session::retrieve(User::SESS_MARK);
    }

    /**
     * logs out
     *
     * @url GET /index/logout
     */
    public
    function logout () : void
    {
        Session::remove(User::SESS_MARK);

        $this->home();
    }

    /**
     * manages applications
     *
     * @url GET /index/manageApps
     */
    public
    function manageApps () : void
    {
        if ( $this->isAdmin() )
            AdminView::Instance()->applications();
        else
            $this->home();
    }

    /**
     * manages groups and their rights affiliations
     *
     * @url GET /index/manageGroups
     */
    public
    function manageGroups () : void
    {
        if ( $this->isAdmin() )
            AdminView::Instance()->groups();
        else
            $this->home();
    }

    /**
     * manages users
     *
     * @url GET /index/manageUsers
     * @url GET /index/manageUsers/$status
     */
    public
    function manageUsers ( $status = null ) : void
    {
        if ( $this->isAdmin() )
            AdminView::Instance()->users( is_numeric($status) && ( (int) $status ) === User::ACTIVE );
        else
            $this->home();
    }

    /**
     * renews password
     *
     * @url GET /index/renew
     */
    public
    function renew ( array $query ) : void
    {
        if (
            ! $this
                ->errorsReset()
                ->setControls(Token::PARAM_NAME, ( $user = User::Instance() )->f_id)
                ->setAuthFromSession($query)
                ->setQuery($query)
                ->start()
            ||
            ! ( $token = Token::Instance() )
            ->fill(
                \Std::__new()
                    ->{ $token->f_content }( $this->vars->{ Token::PARAM_NAME } )
                    ->{ $token->f_userId }( $userId = $this->vars->{ $user->f_id } )
            )
            ->read()
            || ! $user->is($userId)
        )
            $this->headTo($this->urlRoot);
        else
            SsoView::Instance()->renewPassword();
    }
}
