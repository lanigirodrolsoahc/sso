<?php

if(!defined('__STRINGUTILS_LIB__')) :
    define('__STRINGUTILS_LIB__',1);
class StringUtils
{
    static $win1252Euro;

    /**
     * returns a 's' if array has more than one element, or int is superior to 1
     *
     * @param   array|int   $what   to examine
     *
     * @return  string|null
     */
    public static
    function esse ( $what )
    {
        if ( is_array($what) ) $what = count($what);

        return $what > 1 ? 's' : null;
    }

    /**
     * removes accentuated chars
     *
     * @param   string  $str
     *
     * @return  string
     */
    public static
    function clearAccentuation ( string $str ) : string
    {
        return \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($str);
    }

    static
    function initialize ()
    {
        // https://www.php.net/manual/fr/mbstring.supported-encodings.php
        self::$win1252Euro = mb_convert_encoding('€','Windows-1252','utf-8');
    }

    /**
     * recursively trims any string contained in given data
     *
     * @param   mixed   &$datas
     *
     * @return  void
     */
    public static
    function TrimWalk ( &...$datas ) : void
    {
        foreach ( $datas as &$data )
        {
            if ( is_string($data) )
                $data = trim($data);

            elseif ( is_array($data) || is_object($data) )
                foreach ( $data as &$value )
                    self::TrimWalk($value);
        }
    }
}

StringUtils::initialize();

endif;