<?php

namespace SSO;

class UsedPasswords
{
    use VirtualObject;

    protected   $defaultOrderBy = Belonging::ID,
                $tableName      = 'passolds';

    public  $f_id           = 'id',
            $f_userId       = 'userId',
            $f_content      = 'content',
            $f_archived     = 'archived';
}
