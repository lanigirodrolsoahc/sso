<?php

namespace SSO;

class Belonging
{
    use VirtualObject;

    public
    const   CANCELLED           = 0,
            ID                  = 'id',
            READ                = 1,
            WRITE               = 2;

    protected   $defaultOrderBy = self::ID,
                $tableName      = 'belonging';

    public  $f_id       = self::ID,
            $f_rightId  = 'rightId',
            $f_groupId  = 'groupId',
            $f_value    = 'value';

    /**
     * reads belongings for given group identifier
     *
     * @param   int     $id
     * @param   ?bool   $withCancelled
     *
     * @return  array<\stdClass>
     */
    public
    function forGroup ( int $id, bool $withCancelled = false ) : array
    {
        return array_filter(
            $this
                ->fill(
                    \Std::__new()
                        ->{ $this->f_groupId }($id)
                )
                ->readAll(),
            fn ( \stdClass $item ) => $withCancelled
                ? true
                : ($item->value > 0)
        );
    }
}
