<?php

namespace SSO;

class Application
{
    use VirtualObject;

    private
    const   KEY_LENGTH      = 25;

    public
    const   PARAM_CURRENT   = 'currentKey',
            SSO             = 'SSO';

    protected   $tableName  = 'application';

    public  $f_id           = 'id',
            $f_name         = 'name',
            $f_key          = 'key',
            $f_description  = 'description';

    /**
     * generates an application key
     *
     * @return  string
     */
    public
    function generateKey () : string
    {
        return $this
            ->fill(
                \Std::__new()
                    ->{ $this->f_key }( $key = bin2hex(random_bytes(self::KEY_LENGTH)) )
            )
            ->read()
                ? $this->{__FUNCTION__}()
                : $key;
    }

    /**
     * gets all rights attached to current application
     *
     * @param   int     $applicationId
     *
     * @return  array<\stdClass>
     */
    public
    function getRights ( int $applicationId ) : array
    {
        return ! $this->is($applicationId)
            ?  []
            : ( $right = Right::Instance() )
                ->fill(
                    \Std::__new()->{ $right->f_applicationId }( $this->getVirtual()->{ $this->f_id } )
                )
                ->readAll();
    }
}
