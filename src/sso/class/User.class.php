<?php

namespace SSO;

class UnreadUserException extends \Exception {}

class User
{
    use Dates;
    use VirtualObject;

    private
    const   PWD_VALIDITY        = '+6 month';

    public
    const   ACTIVE              = 1,
            ALL                 = 3,
            CREATED_ID          = 'createdUserId',
            FIRST_NAME          = 'firstName',
            LOGIN               = 'login',
            PWD                 = 'password',
            PWD_NEW             = 'newPassword',
            SESS_MARK           = 'user',
            SLEEPING            = 0;

    protected   $defaultOrderBy = self::FIRST_NAME,
                $tableName      = self::SESS_MARK;

    public  $f_id               = 'id',
            $f_firstName        = self::FIRST_NAME,
            $f_lastName         = 'lastName',
            $f_email            = 'email',
            $f_login            = 'login',
            $f_password         = 'password',
            $f_creation         = 'creation',
            $f_update           = 'update',
            $f_lastConnection   = 'lastConnection',
            $f_lastPwdChange    = 'lastPwdChange',
            $f_status           = 'status',
            $f_validityLimit    = 'validityLimit',
            $f_lastSessionCheck = 'lastSessionCheck',
            $f_phone            = 'phone',
            $f_mobile           = 'mobile',
            $f_initials         = 'initials',
            $f_short            = 'short',
            $f_avatarId         = 'avatarId',
            $f_hired            = 'hired',
            $f_fired            = 'fired';

    /**
     * lists any user
     *
     * @param   ?bool   $actives    only?
     *
     * @return  array<\StdClass>
     */
    public
    function any ( bool $actives = false ) : array
    {
        return $this->listing(
            $this->p_order == $this->f_firstName
                ? [ $this->f_firstName, $this->f_lastName ]
                : [ $this->f_lastName, $this->f_firstName ],
            $actives
        );
    }

    /**
     * deactivates expired users
     *
     * @return  User
     */
    public
    function deactivateExpired () : User
    {
        foreach (
            $this
                ->fill(
                    \Std::__new()->{ $this->f_validityLimit }( $this->now()->format( $this->fmt_timestamp ) ),
                    $this->type_underEqual
                )
                ->readAll()
            as $expired
        )
            $this
                ->fill(
                    \Std::__new()
                        ->__setAll(
                            (array) $expired
                        )
                        ->{ $this->f_status }( User::SLEEPING )
                )
                ->save();

        return $this;
    }

    /**
     * extracts `id` property from given data
     *
     * @param   array<\stdClass>    $data
     * @param   string              $propertyName
     *
     * @return  array<int>
     */
    private
    function extractProperties ( array $data, string $propertyName ) : array
    {
        return array_map(
            fn ( \stdClass $item ) : int => (int) $item->$propertyName,
            $data
        );
    }

    /**
     * lists users having at least a right in targeted application
     *
     * @param   string  $applicationName
     *
     * @return  array<stdClass>
     */
    public
    function fromApplication ( string $applicationName ) : array
    {
        do
        {
            if (
                ! ( $application = Application::Instance() )
                    ->fill(
                        \Std::__new()->{ $application->f_name }($applicationName)
                    )
                    ->read()
            ) break;

            if (
                empty(
                    $rightsIds = $this->extractProperties(
                        ( $right = Right::Instance() )
                            ->fill(
                                \Std::__new()->{ $right->f_applicationId }( (int) $application->getVirtual()->{ $application->f_id } )
                            )
                            ->readAll(),
                        $right->f_id
                    )
                )
            ) break;

            if (
                empty(
                    $groupsIds = $this->extractProperties(
                        ( $belonging = Belonging::Instance() )
                            ->fill(
                                \Std::__new()
                                    ->{ $belonging->f_rightId }( $rightsIds ),
                                $belonging->type_in
                            )
                            ->setGroupBy( $belonging->f_groupId )
                            ->readAll(),
                        $belonging->f_groupId
                    )
                )
            ) break;

            if ( empty( $groups = ( $membership = Membership::Instance() )->getGroupsProgeny($groupsIds) ) ) break;

            if (
                empty(
                    $users = $membership
                        ->fill(
                            \Std::__new()
                                ->{ $membership->f_subjectType }( Membership::TYPE_USER )
                                ->{ $membership->f_targetType }( Membership::TYPE_GROUP )
                        )
                        ->multiFill(
                            \Std::__new()
                                ->{ $membership->f_target }(
                                    array_map(
                                        fn ( \stdClass $item ) : int => (int) $item->{ Group::Instance()->f_id },
                                        $groups
                                    )
                                ),
                            $membership->type_in
                        )
                        ->setGroupBy( $membership->f_subject )
                        ->readAll()
                )
            ) break;

            $out = ( $user = User::Instance() )
                ->fill(
                    \Std::__new()
                        ->{ $user->f_id }(
                            array_map(
                                fn ( \stdClass $item ) : int => (int) $item->{ $membership->f_subject },
                                $users
                            )
                        ),
                    $user->type_in
                )
                ->readAll();
        }
        while ( 0 );

        return $out ?? [];
    }

    /**
     * lists members of targeted group
     *
     * @param   string   $groupName     as is
     *
     * @return  array<StdClass>
     */
    public
    function fromGroup ( string $groupName ) : array
    {
        $list = [];

        if (
            ( $group = Group::Instance() )
            ->fill(
                \Std::__new()
                    ->{ $group->f_name }($groupName)
            )
            ->read()
        )
            foreach (
                ( $membership = $membership ?? Membership::Instance() )
                    ->getGroupsProgeny(
                        [(int) $group->getVirtual()->{ $group->f_id }]
                    )
                as $groop
            )
                foreach (
                    $membership
                        ->getUsersForGroup( (int) $groop->{ $group->f_id } )
                    as $candidate
                )
                    if ( ! ( $list[ $userId = $candidate->{ ( $user = $user ?? User::Instance() )->f_id } ] ?? false ) )
                        $list[$userId] = $user->is( (int) $candidate->{ $membership->f_subject } );

        return array_values($list);
    }

    /**
     * gets current user from session identifier
     *
     * @return \Std|false
     */
    public
    function getUserFromSession ()
    {
        return ( $user = self::Instance() )->is( Session::retrieve(User::SESS_MARK) )
            ? $user->getVirtual()
            : false;
    }

    /**
     * determines if (read only) user has targeted right for given application
     *
     * @param   string      $app
     * @param   string      $right
     * @param   ?int        $type
     *
     * @return  bool
     */
    public
    function hasRight ( string $app, string $right, int $type ) : bool
    {
        return
            $this->isRead()
            && ! empty(
                $valued = ( ( $instance = Right::Instance() )
                    ->readForCurrent()
                    ->getRights($instance::TYPE_FLAT) )->$app->$right ?? false
            )
            && ( Right::Instance()->reverseReading[$valued] ?? 0 ) >= $type;
    }

    /**
     * determines if current user is active or not
     *
     * @return  bool
     */
    public
    function isActive () : bool
    {
        return
            $this->isRead()
            && $this->getVirtual()->{ $this->f_status } == self::ACTIVE;
    }

    /**
     * determines if current user is admin with writing right
     *
     * @param   int     $typeOfRight
     *
     * @return  bool
     */
    public
    function isAdmin ( int $typeOfRight ) : bool
    {
        return ( $user = self::Instance() )->getUserFromSession() && $user->hasRight(Application::SSO, Right::ROLE_ADMIN, $typeOfRight);
    }

    /**
     * determines if given user is of appropriate type
     *
     * @param   stdClass|Std    $user
     */
    public static
    function isStd ( $user ) : bool
    {
        return is_object($user) && ( $user instanceof \Std || $user instanceof \stdClass );
    }

    /**
     * lists all active users
     *
     * @param   ?bool   $public
     *
     * @return  array<\stdClass>
     */
    public
    function listAllActive ( bool $public = false ) : array
    {
        $yousers = $this
            ->deactivateExpired()
            ->listing([ $this->f_firstName, $this->f_lastName ]);

        return $public
            ? $this->publicFetchAll($yousers)
            : $yousers;
    }

    /**
     * common ground for listing people
     *
     * @param   array<string>   $ordering
     * @param   ?bool           $activeOnly
     *
     * @return  array<\stdClass>
     */
    private
    function listing ( array $ordering, bool $activeOnly = true )
    {
        $this->database->setOrderBy($ordering, $this->p_orderType == SsoView::SORT_ASC);

        return $this
            ->fill(
                ...(
                    $activeOnly
                        ? [ \Std::__new()->{ $this->f_status }( self::ACTIVE ) ]
                        : [
                            \Std::__new()->{ $this->f_id }(0),
                            $this->type_over
                        ]
                )
            )
            ->readAll();
    }

    /**
     * determines if current user has to renew his password
     *
     * @return  bool
     *
     * @throws  UnreadUserException
     */
    public
    function mustRenew () : bool
    {
        if ( ! $this->isRead() ) throw new UnreadUserException;

        return ( ( new \DateTime($this->getVirtual()->{ $this->f_lastPwdChange }, $this->parisTimeZone()) )->modify(self::PWD_VALIDITY) )->format($this->fmt_timestamp)
        < $this->now()->format($this->fmt_timestamp);
    }

    /**
     * fetches user for public display
     *
     * @param   ?bool   $rights
     *
     * @return  \stdClass
     */
    public
    function publicFetch ( bool $rights = true ) : \stdClass
    {
        return
            ! $this->isRead()
                ? new \stdClass
                : (object) array_merge(
                    array_filter(
                        (array) ( $vo = $this->getVirtual() ),
                        fn ( string $property ) : bool => in_array(
                            $property,
                            [
                                $this->f_id,
                                $this->f_firstName,
                                $this->f_lastName,
                                $this->f_email,
                                $this->f_status,
                                $this->f_phone,
                                $this->f_mobile,
                                $this->f_initials,
                                $this->f_short
                            ]
                        ),
                        ARRAY_FILTER_USE_KEY
                    ),
                    array_filter(
                        (array) \Std::__new()
                        ->avatar(
                            ( ( $avatar = Avatar::Instance() )->is( (int) $vo->{ $this->f_avatarId } ) )->{ $avatar->f_content }
                        )
                        ->rights(
                            $rights
                                ? (array) Right::Instance()
                                    ->readForCurrent()
                                    ->getRights( Right::TYPE_FLAT )
                                : null
                        )
                    )
                );
    }

    /**
     * fetches users for public display, retaining non essential informations
     *
     * @param   array<\stdClass>
     *
     * @return  array<\stdClass>
     *
     * @todo    relocate
     */
    public
    function publicFetchAll ( array $yousers ) : array
    {
        foreach ( $yousers as &$item )
            if ( ( $user = User::Instance() )->is( $item->{ $user->f_id } ) )
                $item = $user->publicFetch(false);

        return $yousers;
    }
}
