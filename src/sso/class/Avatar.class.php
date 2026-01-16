<?php

namespace SSO;

class Avatar
{
    public
    const   CHOICE      = 'avatarChoice';

    use VirtualObject;

    protected   $tableName      = 'avatar';

    public  $f_id       = 'id',
            $f_name     = 'name',
            $f_content  = 'content';
}
