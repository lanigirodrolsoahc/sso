<?php

class StdNotAnAssociativeArrayException extends \Exception {}
class StdNotAValidVariableNameException extends \Exception {}

class Std extends stdClass
{
    /**
     * use `self::Instance` instead
     */
    private
    function __construct () {}

    /**
     * using magic method to assign a property to stdClass
     *
     * @param   string  $name
     * @param   array   $argument
     *
     * @return  Std
     */
    public
    function __call ( string $name, array $argument ) : Std
    {
        $this->$name = $argument[0];

        return $this;
    }

    /**
     * gets a new stdClass
     *
     * @return  static
     */
    public static
    function __new () // : static
    {
        return new static;
    }

    /**
     * sets a bunch of properties for current object
     *
     * @param   array   $assoc
     *
     * @return  Std
     *
     * @throws  StdNotAnAssociativeArrayException
     * @throws  StdNotAValidVariableNameException
     */
    public
    function __setAll ( array $assoc ) : Std
    {
        if ( ! $this->isAssoc($assoc) ) throw new StdNotAnAssociativeArrayException;

        foreach ( $assoc as $name => $param )
        {
            if ( ! $this->startsWithLetter($name) ) throw new StdNotAValidVariableNameException;

            $this->$name( $param );
        }

        return $this;
    }

    /**
     * determines if an array is associative or not
     *
     * @param   array   $array
     *
     * @return  bool
     */
    private
    function isAssoc ( array $array ) : bool
    {
        return ( $keys = array_keys($array) ) !== array_keys($keys);
    }

    /**
     * checks if str starts with a letter
     *
     * @param   string  $str
     *
     * @return  bool
     */
    private
    function startsWithLetter ( string $str ) : bool
    {
        return ctype_alpha( substr($str, 0, 1) );
    }
}