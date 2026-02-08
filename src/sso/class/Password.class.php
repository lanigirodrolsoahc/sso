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
            REG_PWD             = '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[&@+\?_#=])/';

    private
    const   CAPITALS    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            DIGITS      = '0123456789',
            HASH_OLD    = 'sha256',
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

            if (
                ( $used = UsedPasswords::Instance() )
                    ->fill(
                        \Std::__new()
                            ->{ $used->f_userId }( ( ( $user = User::Instance() )->getVirtual() )->{ $user->f_id } )
                            ->{ $used->f_content }( self::hashUsed($pwd) )
                    )
                    ->read()
            )
                throw new \Exception;
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
        return password_hash($pwd, PASSWORD_DEFAULT);
    }

    /**
     * hashes a password for used ones storage only
     *
     * @param   string  $pwd
     *
     * @return  string
     */
    public static
    function hashUsed ( string $pwd ) : string
    {
        return hash(self::HASH_OLD, $pwd);
    }

    /**
     * determines if current password can be accepted
     *
     * @param   string  $pwd
     * @param   string  $hashed
     *
     * @return  bool
     */
    public static
    function verify ( string $pwd, string $hashed ) : bool
    {
        return password_verify($pwd, $hashed);
    }
}
