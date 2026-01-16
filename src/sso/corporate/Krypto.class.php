<?php

/**
 * - use `$this->keysGen` to generate new encryption keys
 * - beware of data loss if you change keys for already encrypted data!
 */
abstract
class Krypto
{
    private
    const   KRYPT_AES   = 'aes-256-cbc',
            KRYPT_SHA   = 'sha3-512';

    protected
    const   KEY_32  = self::KEY_32,
            KEY_64  = self::KEY_64;

    private $key32,
            $key64;

    private
    function __construct ()
    {
        $this->key32    = base64_decode( static::KEY_32 );
        $this->key64    = base64_decode( static::KEY_64 );
    }

    /**
     * returns the only instance of this class
     *
     * @return  static
     */
    public static
    function & Instance () // : static
    {
        static $instance = null;

        if ( $instance == null )
        {
            $class = get_called_class();
            $instance = new $class();
        }

        return $instance;
    }

    /**
     * protects data by encryption
     *
     * @param   mixed   $data   arrays and objects will be `json_encod`ed
     *
     * @return  string
     */
    public
    function crypt ( $data ) : string
    {
        return base64_encode(
            sprintf(
                '%1$s%3$s%2$s',
                $iv     = openssl_random_pseudo_bytes( $this->key32length() ),
                $key1   = openssl_encrypt(
                    is_array($data) || is_object($data)
                        ? json_encode($data)
                        : $data,
                    self::KRYPT_AES,
                    $this->key32,
                    OPENSSL_RAW_DATA,
                    $iv
                ),
                $this->key64hmac($key1)
            )
        );
    }

    /**
     * gives a length for `$this->key32`
     *
     * @return  int|false
     */
    private
    function key32length ()
    {
        return openssl_cipher_iv_length(self::KRYPT_AES);
    }

    /**
     * hash based on `$this->key64`
     *
     * @param   string  $data
     *
     * @return  string
     */
    private
    function key64hmac ( $data ) : string
    {
        return hash_hmac(self::KRYPT_SHA, $data, $this->key64, true);
    }

    /**
     * generates new 32 & 64 keys
     *
     * @return  array<int,string>
     */
    public
    function keysGen () : array
    {
        return [
            ( $l32 = 32 ) => ( $fn = fn ( int $length ) : string => base64_encode( openssl_random_pseudo_bytes($length) ) )($l32),
            ( $l64 = 64 ) => $fn($l64)
        ];
    }

    /**
     * uncovers data by decryption
     *
     * @param   string  $data
     *
     * @return  mixed|false
     */
    public
    function uncrypt ( string $data )
    {
        return
            hash_equals(
                substr( $decoded = base64_decode($data), $ivLength = $this->key32length(), $n64 = 64 ),
                $this->key64hmac( $key1 = substr( $decoded, $ivLength + $n64 ) )
            )
                ? openssl_decrypt( $key1, self::KRYPT_AES, $this->key32, OPENSSL_RAW_DATA, substr($decoded, 0, $ivLength) )
                : false;
    }
}

class KryptoSso extends Krypto
{
    protected
    const   KEY_32      = 'KbH4GP4vyoLMS6cKDdivQmYuOfumhm/CBog9WPyVRNc=',
            KEY_64      = 'wqbk8xtoAPURGH6cIABeM3Wi5adrLilnCWigvIdeEHXOZtNFb+5X1Y1wWMsLLk1uBI4HPpZV32Dqs306pcUavw==';
}
