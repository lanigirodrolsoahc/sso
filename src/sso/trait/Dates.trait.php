<?php

namespace SSO;

trait Dates
{
    public  $fmt_pretty     = 'j F Y',
            $fmt_timestamp  = 'Y-m-d H:i:s',
            $now            = 'now';

    /**
     * gets Paris' TimeZone
     *
     * @return  \DateTimeZone
     */
    public
    function parisTimeZone () : \DateTimeZone
    {
        return new \DateTimeZone( \DateUtils::DEFAULT_TIMEZONE );
    }

    /**
     * gets a nowed DateTime
     *
     * @return  \DateTime
     */
    public
    function now () : \DateTime
    {
        return ( new \DateTime($this->now, $this->parisTimeZone() ) );
    }

    /**
     * gives a pretty representation of given date
     *
     * @param   string      $date
     * @param   ?string     $fmt
     *
     * @return  string
     */
    public
    function strToPretty ( string $date, string $fmt = 'fr_FR' ) : string
    {
        return ( new \IntlDateFormatter($fmt, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE) )->format( strtotime(explode(' ', $date, 2)[0] ));
    }
}
