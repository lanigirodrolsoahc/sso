<?php

namespace SSO;

class Password
{
    use Errors;

    public
    const   LOGIN_MAX_LENGTH    = 45,
            LOGIN_MIN_LENGTH    = 10,
            PWD_DESCRIBE        = 'Minimum %1$s caractères, maximum %2$s, dont 1 minuscule, 1 majuscule, 1 caractère spécial : &@+\?_#=',
            PWD_MAX_LENGTH      = 100,
            PWD_MIN_LENGTH      = 10,
            REG_PWD             = '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[&@+\?_#=])/',
            SALT                = '4n3P198A';

    private
    const   CAPITALS    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            DIGITS      = '0123456789',
            LETTERS     = 'abcdefghijklmnopqrstuvwxyz',
            SPECIALS    = '!@#$%^&*()_+-={}[]|:;"<>,.?/';

    /**
     * determines if password matches expected complexity
     *
     * @param   string  $pwd
     *
     * @return  bool
     */
    public static
    function complexity ( string $pwd ) : bool
    {
        return ( $length = mb_strlen($pwd) ) >= self::PWD_MIN_LENGTH
            && $length <= self::PWD_MAX_LENGTH
            && preg_match(self::REG_PWD, $pwd);
    }

    /**
     * generates a random password
     */
    public static
    function generate () : string
    {
        $pwd    = '';
        $chars  = implode('', [ self::CAPITALS, self::DIGITS, self::LETTERS, self::SPECIALS ]);

        for ( $i = 0; $i < random_int(self::PWD_MIN_LENGTH, self::PWD_MAX_LENGTH); $i++ )
            $pwd .= $chars[ random_int(0, mb_strlen($chars) - 1) ];

        try {
            if ( ! self::complexity($pwd) ) throw new \Exception;

            if ( ( $user = User::Instance() )
                ->init()
                ->fill( \Std::__new()->{ $user->f_password }( self::hash($pwd) ) )
                ->read() ) throw new \Exception;
        }
        catch ( \Throwable $t ) {
            $pwd = false;
        }

        return empty($pwd) ? self::generate() : $pwd;
    }

    /**
     * hashes password
     *
     * @param   string  $pwd
     *
     * @return  string
     */
    public static
    function hash ( string $pwd ) : string
    {
        return crypt($pwd, self::SALT);
    }
}
