<?php

namespace SSO;

class Session
{
    /**
     * destroys a session parameter
     *
     * @param   string  $name
     */
    public static
    function remove ( string $name ) : void
    {
        if ( self::retrieve($name) ) unset($_SESSION[ Database::SSO ][ $name ]);
    }

    /**
     * retrieves a stored parameter
     *
     * @param   string  $name
     *
     * @return  mixed|false
     */
    public static
    function retrieve ( string $name )
    {
        return $_SESSION[ Database::SSO ][ $name ] ?? false;
    }

    /**
     * starts session and sets cookie
     */
    public static
    function start () : void
    {
        if ( session_status() === PHP_SESSION_NONE )
        {
            session_set_cookie_params([
                'lifetime' => 1800,
                'path' => '/sso',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }
    }

    /**
     * stores parameter
     *
     * @param   string  $name
     * @param   mixed   $value
     */
    public static
    function store ( string $name, $value ) : void
    {
        $_SESSION[ Database::SSO ][ $name ] = $value;
    }
}
