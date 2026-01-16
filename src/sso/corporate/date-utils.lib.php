<?php

if(!defined('__DATEUTILS_LIB__')) :
    define('__DATEUTILS_LIB__',1);

class DateUtils
{
    const   DEFAULT_TIMEZONE    = 'Europe/Paris';
    const   FMT_SQL_DATE        = 'Y-m-d';

    static
    function nowISOTime ()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * set the time zone.
     * Usage :
     *  - DateUtils::zoned() ; sets timezone to default 'Europe/Paris'
     *  - DateUtils::zoned('Europe/Paris') ; sets timezone with specified timezone
     *  - DateUtils::zoned('Europe/Paris',$datetime) ; sets timezone with specified timezone to a DateTime object
     *  - DateUtils::zoned($datetime) ; sets timezone with default 'Europe/Paris' to a DateTime object
     *  - DateUtils::zoned($datetime,'Europe/Paris') ; sets timezone with specified timezone to a DateTime object
     *
     * @see date_default_timezone_get(), DateTime::getTimezone()->getName()
     */
    static
    function zoned ( /*$zone = 'Europe/Paris', DateTime $datetime=null*/ )
    {
        $z = DateUtils::DEFAULT_TIMEZONE;
        $d = null;

        for ( $i=0, $l=func_num_args(); $i<$l ; $i++ )
        {
            //$obj = & func_get_arg($i);
            $obj = func_get_arg($i);
            $typ = gettype( $obj );
            if ( $typ === 'string' ) $z = $obj;
            elseif ( $typ === 'object' && is_a($obj,'DateTime') ) $d = $obj;
        }

        $name='';

        if ( $d === null )
        {
            date_default_timezone_set($z);
            $name = date_default_timezone_get();
        }
        else
        {
            $d->setTimezone(new DateTimezone($z));
            $name = $d->getTimezone()->getName();
        }

        return $name;
    }
}

endif;