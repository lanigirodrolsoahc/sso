<?php

namespace SSO;

class Right
{
    use VirtualObject;
    use VirtualObject { __construct as private traitConstruct; }

    public
    const   READING = [
        Belonging::CANCELLED    => self::TXT_NOPE,
        Belonging::READ         => self::TXT_READ,
        Belonging::WRITE        => self::TXT_WRITE
    ];

    public
    const   INNER_RIGHTS    = 'innerRights',
            PARENTS         = 'parenthood',
            ROLE_ADMIN      = 'admin',
            ROLE_GIVER      = 'giver',
            TYPE_FLAT       = 'listOfRights',
            TYPE_GROUPED    = 'listOfGroups',
            TXT_DEL         = 'delete',
            TXT_NOPE        = 'cancelled',
            TXT_READ        = 'read',
            TXT_WRITE       = 'write';

    private $applications,
            $membered,
            $listOfGroups,
            $listOfRights;

    protected   $tableName  = 'right';

    public  $f_id               = 'id',
            $f_name             = 'name',
            $f_applicationId    = 'applicationId',
            $f_described        = 'described',
            $reverseReading;

    private
    function __construct ()
    {
        $this
            ->initInner()
            ->traitConstruct();

        $this->reverseReading   = array_flip(self::READING);
        $this->applications     = Application::Instance()
            ->setRequestParameters()
            ->listAll();
    }

    /**
     * - attaches rights R/W to current groups
     * - creates a complex list of rights per group where value is raw
     * - creates a flat list of rights per application where value is synthetized
     * - the more elevated privilege takes all in conflicted cases (same right, different app)
     *
     * @param   array
     */
    private
    function collectRights ( array &$listOfGroups ) : Right
    {
        foreach ( $listOfGroups as &$group  )
        {
            foreach ( ( $belonging = Belonging::Instance() )->forGroup( $group->{ Group::Instance()->f_id } ) as $belongs )
            {
                if ( ! $read = $this->is( $belongs->{ $belonging->f_rightId } ) ) continue;

                if ( ! property_exists($group, self::INNER_RIGHTS) )
                    $group->{ self::INNER_RIGHTS } = \Std::__new();

                if ( ! property_exists(
                    $this->listOfRights,
                    $appName = $this->applications[ $read->{ $this->f_applicationId } ]->{ Application::Instance()->f_name }
                ))
                    $this->listOfRights->$appName = \Std::__new();

                if ( ! property_exists($group->{ self::INNER_RIGHTS }, $appName) )
                    $group->{ self::INNER_RIGHTS }->$appName = \Std::__new();

                if ( ! property_exists(
                    $this->listOfRights->$appName,
                    $rightName = $read->{ $this->f_name }
                ))
                    $this->listOfRights->$appName->$rightName( $newly = $this->readable( $belongs->{ $belonging->f_value } ) );
                else
                    $this->listOfRights->$appName->$rightName(
                        $this->reverseReading[ $this->listOfRights->$appName->$rightName ] > ( $new = $belongs->{ $belonging->f_value } )
                            ? $this->listOfRights->$appName->$rightName
                            : $newly = $this->readable($new)
                    );

                $group->{ self::INNER_RIGHTS }->$appName->$rightName( $newly );
            }

            if ( property_exists($group, self::PARENTS) )
                $this->{ __FUNCTION__ }( $group->{ self::PARENTS } );
        }

        return $this;
    }

    /**
     * gets groups from membership
     *
     * @param   array   &$members
     * @param   array   &$list
     *
     *@return   Right
     */
    private
    function fetchGroups ( array &$members, array &$list ) : Right
    {
        foreach ( $members as $dbData )
        {
            if ( ! $read = ( Group::Instance() )->is(
                $groupId = $dbData->{ ( $membership = Membership::Instance() )->f_target }
            ) )
                continue;

            $list[] = $read;

            if ( empty(${ self::PARENTS } = $membership->getGroupsForGroup( $groupId )) ) continue;

            $read->{ self::PARENTS } = [];

            $this->{ __FUNCTION__ }( ${ self::PARENTS }, $read->{ self::PARENTS } );
        }

        return $this;
    }

    /**
     * gets membership for current user
     *
     * @return  Right
     */
    private
    function getDirectMembership () : Right
    {
        $this->membered = Membership::Instance()->getGroupsForUser(true);

        return $this;
    }

    /**
     * gets a flat or grouped list of rights
     *
     * @see `$this->collectRights()`
     *
     * @param   string  $type
     *
     * @return  array<\Std>|\Std
     */
    public
    function getRights ( string $type )
    {
        static $auth = [self::TYPE_FLAT, self::TYPE_GROUPED];

        return in_array($type, $auth) ? $this->$type : false;
    }

    /**
     * initializes inner properties:
     * - current list of rights
     * - current membership
     *
     * @return  Right
     */
    private
    function initInner () : Right
    {
        $this->listOfGroups = [];
        $this->listOfRights = \Std::__new();
        $this->membered     = [];

        return $this;
    }

    /**
     * have a readable right value
     *
     * @param   int     $rightValue
     *
     * @return  string
     */
    public
    function readable ( int $rightValue ) : string
    {
        return self::READING[$rightValue];
    }

    /**
     * - reads rights for current user
     * - call `this->getRights()` next
     *
     * @return  Right
     */
    public
    function readForCurrent () : Right
    {
        $this
            ->initInner()
            ->database
                ->setPage()
                ->setOrderBy();

        $this
            ->getDirectMembership()
            ->fetchGroups($this->membered, $this->listOfGroups)
            ->collectRights($this->listOfGroups)
            ->sortGroups($this->listOfGroups)
            ->sortRights($this->listOfRights);

        return $this;
    }

    /**
     * sorts an array of objects by property, in alphabetical order
     *
     * @param   array   &$ofObjects
     * @param   string  $propertyName
     *
     * @return  Right
     */
    public
    function sortByProperty ( array &$ofObjects, string $propertyName )
    {
        usort($ofObjects, fn ($a, $b) => strcmp(mb_strtolower($a->$propertyName), mb_strtolower($b->$propertyName)) );

        return $this;
    }

    /**
     * sorts group's list
     *
     * @param   array   &$listOfGroups
     *
     * @return  Right
     */
    private
    function sortGroups ( array &$listOfGroups ) : Right
    {
        $this->sortByProperty($listOfGroups, Group::Instance()->f_name);

        foreach ( $listOfGroups as &$group )
        {
            if ( property_exists($group, self::INNER_RIGHTS) )  $this->sortRights($group->{ self::INNER_RIGHTS } );
            if ( property_exists($group, self::PARENTS) )       $this->{ __FUNCTION__ }( $group->{ self::PARENTS } );
        }

        return $this;
    }

    /**
     * sorts flat rights
     *
     * @param   \Std    &$list
     *
     * @return  Right
     */
    private
    function sortRights ( \Std &$list ) : Right
    {
        $this->sortStd($list);

        foreach ( $list as $app => &$rights ) $this->sortStd($rights);

        return $this;
    }

    /**
     * sorts object's properties by name
     *
     * @param   \Std    &$toSort
     *
     * @return  Right
     */
    public
    function sortStd ( \Std &$toSort ) : Right
    {
        do
        {
            $copied = (array) $toSort;

            if ( empty($copied) ) break;

            ksort($copied, SORT_STRING|SORT_FLAG_CASE);

            $toSort = \Std::__new()->__setAll($copied);
        }
        while ( 0 );

        return $this;
    }
}
