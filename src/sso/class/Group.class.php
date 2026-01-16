<?php

namespace SSO;

class Group
{
    use VirtualObject;

    public
    const   NAME_LENGTH     = 4;

    protected   $tableName  = 'group';

    public  $f_id           = 'id',
            $f_name         = 'name',
            $f_description  = 'description';

    /**
     * determines if given name has required length
     *
     * @param   string  $name
     *
     * @return  bool
     */
    public static
    function isLengthed ( string $name ) : bool
    {
        return mb_strlen($name) >= self::NAME_LENGTH;
    }
}
