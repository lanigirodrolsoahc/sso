<?php

namespace SSO;

class FormForAdmin extends Form
{
    private
    const   ERR_APP_HAS_RIGHTS      = 'Cette application possède des droits !',
            ERR_APP_NAME_EXISTS     = 'Ce nom applicatif est réservé !',
            ERR_APP_NAME_FMT        = 'Format de nom non reconnu.',
            ERR_APP_NO_CREATION     = 'Application non créée...',
            ERR_APP_NO_DELETION     = 'Application non supprimée !',
            ERR_APP_NO_READING      = 'Application non reconnue.',
            ERR_APP_NO_SAVING       = 'Application non modifiée...',
            ERR_FAIL_ERAZING        = 'Failed to eraze %1$s with identifier %2$s',
            ERR_FIELD_NOT_UNIK      = '« %1$s » désobéit à la nécessité d\'unicité',
            ERR_FORMAT              = 'Le format de la valeur « %1$s » est invalide',
            ERR_GROUP_NAME_EXISTS   = 'Ce nom de groupe existe déjà !',
            ERR_GROUP_NAME_FMT      = 'Format de nom de groupe non reconnu...',
            ERR_GROUP_NAME_LENGTH   = 'Le nom du groupe doit atteindre %1$s caractères !',
            ERR_GROUP_NOT_SAVED     = 'Erreur de sauvegarde du groupe !',
            ERR_GROUP_SAVE          = 'Erreur de sauvegarde du nom et de la description du groupe n° %1$s',
            ERR_GROUP_UNATTACHED    = 'Erreur de rattachement du groupe n° %1$s',
            ERR_GROUP_UNFREED       = 'Groupe n° %1$s non libéré !',
            ERR_GROUP_UNKNOWN       = 'Groupe non reconnu...',
            ERR_NOT_AN_ADMIN        = 'Vous ne semblez pas disposer des droits d\'administration en écriture !',
            ERR_MISSING_PWD         = 'Merci de fournir un mot de passe !',
            ERR_RIGHT_NOT_SAVED     = 'Droit non sauvegardé : %1$s',
            ERR_RIGHT_UNDELETED     = 'Droit non supprimé : %1$s',
            ERR_RIGHT_UNKNOWN       = 'Droit non identifié : %1$s',
            ERR_USER_UNATTACHED     = 'Utilisateur n° %1$s non libéré !',
            ERR_USER_UNFREED        = 'Erreur de rattachement de l\'utilisateur n° %1$s',
            ERR_USER_UNKNOWNED      = 'Utilisateur non reconnu',
            ERR_USER_UNSAVED        = 'Utilisateur non sauvegardé : %1$s',
            REG_FMT_APP_NAME        = '/^[a-z]{4,50}$/i',
            REG_FMT_GROUP_NAME      = '/^[a-z0-9-]{4,255}$/i';

    public
    const   INIT_LENGTH_NEW         = 3,
            INIT_LENGTH_OLD         = 2;

    /**
     * @var \Std
     */
    private $group;

    /**
     * breaks an if
     *
     * @return false
     *
     * @deprecated
     */
    private
    function breaking () : bool
    {
        return false;
    }

    /**
     * changes application's description
     *
     * @url POST /changeAppDesc
     * @url POST /services/changeAppDesc
     */
    public
    function changeAppDesc () : void
    {
        $this->out(
            $this->controlAdmin(
                ( $application = Application::Instance() )->f_id,
                $described = $application->f_description
            )
            && $this->readApp()
            && $this->saveApp($described)
                ? self::CODE_OK
                : false
        );
    }

    /**
     * changes application's key
     *
     * @url POST /changeAppKey
     * @url POST /services/changeAppKey
     */
    public
    function changeAppKey () : void
    {
        $this->out(
            $this->controlAdmin(
                ( $application = Application::Instance() )->f_id,
                $key = $application->f_key
            )
            && $this->readApp()
            && $this->kryptKey()
            && $this->saveApp($key)
                ? self::CODE_OK
                : false
        );
    }

    /**
     * changes application's list of rights
     *
     * @url POST /changeAppRights
     * @url POST /services/changeAppRights
     */
    public
    function changeAppRights () : void
    {
        $this->out(
            $this->controlAdmin(
                Application::Instance()->f_id,
                AdminView::INPUT_RIGHTS
            )
            && $this->readApp()
            && $this->changeRights()
                ? self::CODE_OK
                : false
        );
    }

    /**
     * manages application's rights between add, update informations and remove
     *
     * @return  bool
     */
    private
    function changeRights () : bool
    {
        $add    = array_keys( (array) $this->extractRightsList(AdminView::RIGHTS_ADD) );
        $info   = $this->extractRightsList(AdminView::RIGHTS_INFO);
        $del    = array_keys( (array) $this->extractRightsList(AdminView::RIGHTS_DEL) );

        foreach ( $add as $new )
            if ( ! property_exists($info, $new) )
                $info->$new = '';

        foreach ( $info as $prop => $value )
        {
            if ( in_array($prop, $del) ) continue;

            if ( !
                ( $right = Right::Instance() )
                    ->fill(
                        \Std::__new()
                            ->{ $right->f_applicationId }( $this->vars->{ Application::Instance()->f_id } )
                            ->{ $right->f_name }($prop)
                            ->{ $right->f_described }( stripslashes($value) )
                    )
                    ->save()
            )
                $this->error( sprintf(self::ERR_RIGHT_NOT_SAVED, $prop) );
        }

        foreach ( $del as $named )
        {
            if (
                ! (
                    ( $right = Right::Instance() )
                        ->fill(
                            $fill = \Std::__new()
                                ->{ $right->f_applicationId }( $this->vars->{ Application::Instance()->f_id } )
                                ->{ $right->f_name }($named)
                        )
                        ->read()
                    && ( $belongs = Belonging::Instance() )
                        ->fill(
                            \Std::__new()
                                ->{ $belongs->f_rightId }( $right->getVirtual()->{ $right->f_id } )
                        )
                        ->deleteAll()
                    && $right
                        ->fill($fill)
                        ->eraze()
                )
            )
                $this->error( sprintf(self::ERR_RIGHT_UNDELETED, $named) );
        }

        return ! $this->errored();
    }

    /**
     * - controls request like parent
     * - verifies that current user is admin
     *
     * @param   string  $controls   needed for current task
     *
     * @return  bool
     */
    protected
    function controlAdmin ( string ...$controls ) : bool
    {
        if ( ! User::Instance()->isAdmin(Belonging::WRITE) )
            $this->error(self::ERR_NOT_AN_ADMIN);
        elseif ( ! parent::controlRequest(...$controls) )
            $this->error(self::ERR_BAD_REQUEST);

        return ! $this->errored();
    }

    /**
     * checks if application name is not in use and matches requirements
     *
     * @return  bool
     */
    private
    function checkAppName () : bool
    {
        if ( ( $application = Application::Instance() )
                ->fill(
                    \Std::__new()->{ $application->f_name }( $named = $this->vars->{ AdminView::APP_NAME } )
                )
                ->read()
        )
            $this->error(self::ERR_APP_NAME_EXISTS);
        elseif ( ! preg_match(self::REG_FMT_APP_NAME, $named) )
            $this->error(self::ERR_APP_NAME_FMT);

        return ! $this->errored();
    }

    /**
     * checks if group name matches requirements
     *
     * @return  bool
     */
    private
    function checkGroupFormat () : bool
    {
        if ( ! preg_match(self::REG_FMT_GROUP_NAME, $this->vars->{ AdminView::GROUP_NAME }) )
            $this->error(self::ERR_GROUP_NAME_FMT);

        return ! $this->errored();
    }

    /**
     * checks if group name is not in use
     *
     * @return  bool
     */
    private
    function checkGroupName () : bool
    {
        if ( ( $group = Group::Instance() )
            ->fill(
                \Std::__new()->{ $group->f_name }( $this->vars->{ AdminView::GROUP_NAME } )
            )
            ->read()
        )
            $this->error(self::ERR_GROUP_NAME_EXISTS);

        return ! $this->errored();
    }

    /**
     * creates a new application
     *
     * @url POST /createApp
     * @url POST /services/createApp
     */
    public
    function createApp () : void
    {
        $this->out(
            $this->controlAdmin(AdminView::APP_NAME)
            && $this->checkAppName()
            && $this->createApplication()
                ? self::CODE_OK
                : false
        );
    }

    /**
     * do creates an app
     *
     * @return  bool
     */
    private
    function createApplication () : bool
    {
        if ( ! ( $app = Application::Instance() )
                    ->fill(
                        \Std::__new()
                            ->{ $app->f_name }( $this->vars->{ AdminView::APP_NAME } )
                            ->{ $app->f_key }( $app->generateKey() )
                    )
                    ->save()
        )
            $this->error(self::ERR_APP_NO_CREATION);

        return ! $this->errored();
    }

    /**
     * creates a group
     *
     * @url POST /createGroup
     * @url POST /services/createGroup
     */
    public
    function createGroup () : void
    {
        $this->out(
            $this->controlAdmin(AdminView::GROUP_NAME, AdminView::GROUP_DESCRIBED)
            && $this->checkGroupName()
            && $this->checkGroupFormat()
            && $this->createGroupInDatabase()
                ? self::CODE_OK
                : false
        );
    }

    /**
     * saves a group in database
     *
     * @return  bool
     */
    private
    function createGroupInDatabase () : bool
    {
        if ( ! ( $group = Group::Instance() )
            ->fill(
                \Std::__new()
                    ->{ $group->f_name }( $this->vars->{ AdminView::GROUP_NAME } )
                    ->{ $group->f_description }( $this->vars->{ AdminView::GROUP_DESCRIBED } )
            )
            ->save()
        )
            $this->error(self::ERR_GROUP_NOT_SAVED);

        return ! $this->errored();
    }

    /**
     * deletes an application iff no rights attached
     *
     * @url POST /deleteApp
     * @url POST /services/deleteApp
     */
    public
    function deleteApp () : void
    {
        $this->out(
            $this->controlAdmin( Application::Instance()->f_id )
            && $this->deleteApplication()
                ? self::CODE_OK
                : false
        );
    }

    /**
     * deletes an application
     *
     * @return  bool
     */
    private
    function deleteApplication () : bool
    {
        if ( ! empty( ( $app = Application::Instance() )->getRights( $id = (int) $this->vars->{ $ider = $app->f_id } ) ) )
            $this->error(self::ERR_APP_HAS_RIGHTS);
        elseif (
            ! $app
                ->fill(
                    \Std::__new()->{ $ider }($id)
                )
                ->eraze()
        )
            $this->error(self::ERR_APP_NO_DELETION);

        return ! $this->errored();
    }

    /**
     * deletes by lot
     *
     * @param   Belonging|Membership    $vo
     * @param   \Std                    $fill
     *
     * @return  static
     */
    private
    function deleteByLot ( $virtual, \Std $fill ) : FormForAdmin
    {
        foreach ( $virtual->fill($fill)->readAll() as $item )
            if (
                ! $virtual
                    ->fill( \Std::__new()->{ $virtual->f_id }( $item->{ $virtual->f_id } ) )
                    ->eraze()
            )
                $this->error( sprintf( self::ERR_FAIL_ERAZING, get_class($virtual), $item->{ $virtual->f_id } ) );

        return $this;
    }

    /**
     * deletes a group and all his associations
     *
     * @url POST /deleteGroup
     * @url POST /services/deleteGroup
     */
    public
    function deleteGroup () : void
    {
        $this->out(
            $this->controlAdmin( AdminView::GROUP_ID )
            && $this->doGroupDeletion()
                ? self::CODE_OK
                : false
        );
    }

    /**
     * do deletes a group if any to be found
     *
     * @return  bool
     */
    private
    function doGroupDeletion () : bool
    {
        if (
            ! ( $group = Group::Instance() )
                ->fill(
                    \Std::__new()->{ $group->f_id }( $groupId = (int) $this->vars->{ AdminView::GROUP_ID } )
                )
                ->read()
        )
            $this->error(self::ERR_GROUP_UNKNOWN);
        elseif (
            ! $this
                ->deleteByLot(
                    ( $vo = Belonging::Instance() ),
                    \Std::__new()->{ $vo->f_groupId }( $groupId ),
                )
                ->deleteByLot(
                    ( $vo = Membership::Instance() ),
                    \Std::__new()
                        ->{ $vo->f_targetType }( Membership::TYPE_GROUP )
                        ->{ $vo->f_target }( $groupId ),
                )
                ->errored()
        )
            if ( ! $group->eraze() )
                $this->error( sprintf(self::ERR_FAIL_ERAZING, Group::class, $groupId) );

        return ! $this->errored();
    }

    /**
     * extracts a list of rights from current vars
     *
     * @param   string   $index
     *
     * @return  \stdClass
     */
    private
    function extractRightsList ( string $index ) : \stdClass
    {
        return ( $this->vars->{ AdminView::INPUT_RIGHTS } )->$index ?? new \stdClass;
    }

    /**
     * gets a fresh password
     *
     * @url GET /getPassword
     * @url GET /services/getPassword
     */
    public
    function getPassword () : void
    {
        $this->server->setStatus( ( $isAdmin = $this->controlAdmin() ) ? self::CODE_OK : self::CODE_BAD_REQUEST );

        $this->render( \Std::__new()->{ self::MSG }( $isAdmin ? Password::generate() : $this->failures() ) );
    }

    /**
     * key encryption
     *
     * @return  bool
     */
    private
    function kryptKey () : bool
    {
        $this->vars->{ Application::Instance()->f_key } = \KryptoSso::Instance()->crypt( $this->vars->{ Application::Instance()->f_key } );

        return $this->vars->{ Application::Instance()->f_key } !== false;
    }

    /**
     * modifies all group informations
     *
     * @url POST /modifyGroup
     * @url POST /services/modifyGroup
     */
    public
    function modifyGroup () : void
    {
        $this->out(
            $this->controlAdmin(AdminView::GROUP_ID, AdminView::GROUP_NAME)
            && $this->requiredData()
            && $this->redefineVars()
            && $this->updateGroup()
                ? self::CODE_OK
                : false
        );
    }

    /**
     * reads application from current vars
     *
     * @return  bool
     */
    private
    function readApp () : bool
    {
        if ( ! ( $application = Application::Instance() )->is( $this->vars->{ $application->f_id } ) )
            $this->error(self::ERR_APP_NO_READING);

        return ! $this->errored();
    }

    /**
     * controls required data:
     * - reads group from requested data
     * - controls group name's length
     *
     * @return  bool
     */
    private
    function requiredData () : bool
    {
        if ( ! ( $group = Group::Instance() )->is( $this->vars->{ AdminView::GROUP_ID } )
        )
            $this->error(self::ERR_GROUP_UNKNOWN);
        else
            $this->group = $group->getVirtual();

        if ( ! Group::isLengthed( $this->vars->{ AdminView::GROUP_NAME } ) )
            $this->error( sprintf(self::ERR_GROUP_NAME_LENGTH, Group::NAME_LENGTH) );

        return ! $this->errored() && $this->checkGroupFormat();
    }

    /**
     * simplifies vars complexified due to view's completeness
     *
     * @return  bool
     */
    private
    function redefineVars () : bool
    {
        \StringUtils::TrimWalk($this->vars);

        ( $validated = \Std::__new() )
            ->{ AdminView::GROUP_ID }           ( (int) ( $groupId = $this->vars->{ AdminView::GROUP_ID } ) )
            ->{ AdminView::GROUP_NAME }         ( $this->vars->{ AdminView::GROUP_NAME } )
            ->{ AdminView::GROUP_DESCRIBED }    ( $this->vars->{ AdminView::GROUP_DESCRIBED }->$groupId ?? '' )
            ->{ AdminView::INPUT_RIGHTS }       ( array_replace_recursive(
                                                    ...array_map(
                                                        fn ( $sub ) => $sub = (array) $sub->{ AdminView::RIGHTS_AUTH },
                                                        (array) $this->vars->{ AdminView::INPUT_RIGHTS } ?? []
                                                    )
            ) )
            ->{ AdminView::INPUT_USERS }        (
                                                    ( $toInt = fn ( array $data ) : array => array_map( fn ( string $val ) : int => intval($val), $data ) )
                                                    ( $users = array_keys((array) ($this->vars->{ AdminView::INPUT_USERS }->$groupId ?? [])) )
            )
            ->{ AdminView::GROUPS_DATES_LIST }  ( array_filter(
                                                    (array) $this->vars->{ AdminView::GROUPS_DATES_LIST }->$groupId ?? [],
                                                    fn ( $key ) => in_array( (int) $key, $users ),
                                                    ARRAY_FILTER_USE_KEY
            ) )
            ->{ AdminView::GROUPS_LIST }        ( $toInt( (array) ($this->vars->{ AdminView::GROUPS_LIST }->{ AdminView::GROUPS_GROUP } ?? []) ) );

        $this->vars = $validated;

        return ! $this->errored();
    }

    /**
     * saves application
     *
     * @param   string  $modifiedProperty
     *
     * @return  bool
     */
    private
    function saveApp ( string $modifiedProperty ) : bool
    {
        if ( ! ( $application = Application::Instance() )
                ->fill(
                    $application
                        ->getVirtual()
                        ->$modifiedProperty( $this->vars->{ $modifiedProperty } )
                )
                ->save()
        )
            $this->error(self::ERR_APP_NO_SAVING);

        return ! $this->errored();
    }

    /**
     * do saves user based on current `$this->vars`
     *
     * @return  bool
     */
    private
    function savedUser () : bool
    {
        if (
            ! ( $user = User::Instance() )
                ->fill(
                    \Std::__new()->__setAll(
                        array_map( fn ($value) => in_array($value, ['', null], true) ? null : $value, (array) $this->vars )
                    )
                )
                ->save()
        )
            $this->error( sprintf( self::ERR_USER_UNSAVED, $this->vars->{ $user->f_id } ) );
        else
            $user->deactivateExpired();

        return ! $this->errored();
    }

    /**
     * saves user identifier to session if user is new
     *
     * @return  bool
     */
    private
    function savedUserRememberNewId () : bool
    {
        if ( $this->userIsNew() ) Session::store( User::CREATED_ID, Database::Instance()->lastId );

        return ! $this->errored();
    }

    /**
     * saves an existing or welcomed user
     *
     * @url POST /saveUser
     * @url POST /services/saveUser
     */
    public
    function saveUser () : void
    {
        $this->out(
            $this->controlAdmin(
                ( $user = User::Instance() )->f_id,
                $user->f_firstName,
                $user->f_lastName,
                $user->f_email,
                $user->f_login,
                $user->f_password,
                $user->f_status,
                $user->f_validityLimit,
                $user->f_phone,
                $user->f_mobile,
                $user->f_initials,
                $user->f_short,
                $user->f_hired,
                $user->f_fired,
            )
            && $this->saveUserControl()
            && $this->saveUserUnicity()
            && $this->saveUserPassword()
            && $this->savedUser()
            && $this->savedUserRememberNewId()
                ? self::CODE_OK
                : false
        );
    }

    /**
     * determines if
     * - all required vars are to be found
     * - all found vars match the expected format
     *
     * @return  bool
     */
    private
    function saveUserControl () : bool
    {
        foreach ( array_filter(
            [
                \Std::__new()
                    ->test( preg_match(
                        $hyphenLetters  = '/^[A-Za-zÀ-ÖØ-öø-ÿ]{1}[A-zÀ-ú ]{0,149}$/',
                        $firstName      = $this->vars->{ ( $user = User::Instance() )->f_firstName }
                    ) )
                    ->named(UserView::MSG_FIRST_NAME),
                \Std::__new()
                    ->test( preg_match($hyphenLetters, $lastName = $this->vars->{ $user->f_lastName }) )
                    ->named(UserView::MSG_LAST_NAME),
                \Std::__new()
                    ->test(
                        ( mb_strlen( $email = $this->vars->{ $user->f_email } ) <= 150 )
                        && Mailer::isValid($email)
                    )
                    ->named(UserView::MSG_EMAIL),
                \Std::__new()
                    ->test(
                        preg_match(
                            ( $isNew = $this->userIsNew() )
                                ? mb_strtolower(
                                    \StringUtils::clearAccentuation(
                                    sprintf(
                                        '/^%1$s%2$s$/',
                                        (
                                            $ploder = function ( string $toClear, bool $words = false ) : string
                                            {
                                                preg_match_all($words ? '/\b\w+/' : '/\b\w/', $toClear, $matches);

                                                return implode('', $matches[0]);
                                            }
                                        )
                                            ( $firstName ),
                                        $ploder( $lastName, true )
                                    ))
                                )
                                : '/^[a-z]+$/',
                            $this->vars->{ $user->f_login }
                        )
                        && ( $length = mb_strlen($this->vars->{ $user->f_login }) ) <= 50
                        && $length >= 3
                    )
                    ->named(UserView::MSG_LOGIN),
                \Std::__new()
                    ->test( in_array( (int) $this->vars->{ $user->f_status }, [User::ACTIVE, User::SLEEPING], true ) )
                    ->named(UserView::MSG_STATUS),
                \Std::__new()
                    ->test( preg_match(
                        $init = sprintf('/^[A-Z]{%1$s,7}$/', $isNew ? self::INIT_LENGTH_NEW : self::INIT_LENGTH_OLD),
                        $this->vars->{ $user->f_initials }
                    ) )
                    ->named(UserView::MSG_INITIALS),
                \Std::__new()
                    ->test( preg_match(
                        $dateFmt = '/^([0-9]{4})[-\/]{1}([0-9]{2})[-\/]{1}([0-9]{2})$/',
                        $this->vars->{ $user->f_hired }
                    ) )
                    ->named(UserView::MSG_HIRED),
                (
                    $notEmptyDate = fn ( string $date, string $msg ) : ?\Std =>
                        empty($date)
                        ? null
                        : \Std::__new()
                            ->test( preg_match($dateFmt, $date) )
                            ->named($msg)
                )(
                    $this->vars->{ $user->f_validityLimit }, UserView::MSG_LIMIT
                ),
                $notEmptyDate( $this->vars->{ $user->f_fired }, UserView::MSG_FIRED ),
                empty( $pwd = $this->vars->{ $user->f_password } )
                    ? null
                    : \Std::__new()
                        ->test( Password::complexity($pwd) )
                        ->named( UserView::MSG_PASSWORD ),
                (
                    $notEmptyFrenchPhone = fn ( string &$line, string $msg ) : ?\Std =>
                        empty( $line = preg_replace('/[^0-9]/', '', $line) )
                            ? null
                            : \Std::__new()
                                ->test( preg_match('/^[0-9]{10}$/', $line) )
                                ->named($msg)
                )(
                    $this->vars->{ $user->f_phone }, UserView::MSG_PHONE
                ),
                $notEmptyFrenchPhone( $this->vars->{ $user->f_mobile }, UserView::MSG_MOBILE ),
                empty( $short = $this->vars->{ $user->f_short } )
                    ? null
                    : \Std::__new()
                        ->test( preg_match('/^[0-9]{3,4}$/', $short) )
                        ->named( UserView::MSG_SHORT )
            ]
                ) as $checks )
                    if ( ! $checks->test )
                        $this->error( sprintf(self::ERR_FORMAT, $checks->named) );

        return ! $this->errored();
    }

    /**
     * manages password depending on user's novelty
     *
     * @return  bool
     */
    private
    function saveUserPassword () : bool
    {
        $isEmpty    = empty( $pwd = &$this->vars->{ ( $user = User::Instance() )->f_password } );
        $isNew      = $this->userIsNew();

        if ( $isEmpty && $isNew )
            $this->error(self::ERR_MISSING_PWD);
        elseif ( ! $isNew && ! $user->is($this->vars->{ $user->f_id }) )
            $this->error(self::ERR_USER_UNKNOWNED);
        else
            $pwd = $isEmpty ? $user->getVirtual()->{ $user->f_password } : Password::hash($pwd);;

        return ! $this->errored();
    }

    /**
     * determines if all uniqueness can be checked
     *
     * @return  bool
     */
    private
    function saveUserUnicity () : bool
    {
        foreach (
            [
                \Std::__new()->field( ( $user = User::Instance() )->f_email )->msg( UserView::MSG_EMAIL ),
                \Std::__new()->field( $user->f_login )->msg( UserView::MSG_LOGIN ),
                \Std::__new()->field( $user->f_initials )->msg( UserView::MSG_INITIALS )
            ]
            as $unik
        )
        {
            if (
                $user
                    ->fill( \Std::__new()->{ $unik->field }( $this->vars->{ $unik->field } ) )
                    ->multiFill( \Std::__new()->{ $user->f_id }( $this->vars->{ $user->f_id } ), $user->type_different )
                    ->read()
            )
                $this->error( sprintf(self::ERR_FIELD_NOT_UNIK, $unik->msg) );
        }

        return ! $this->errored();
    }

    /**
     * updates group with requested values
     *
     * @return  bool
     */
    private
    function updateGroup () : bool
    {
        do
        {
            if ( ! ( $group = Group::Instance() )
                ->fill(
                    $this->group
                        ->{ $group->f_name }( $this->vars->{ AdminView::GROUP_NAME } )
                        ->{ $group->f_description }( $this->vars->{ AdminView::GROUP_DESCRIBED } )
                )
                ->save()
            )
                $this->error( sprintf(self::ERR_GROUP_SAVE, $this->group->{ AdminView::GROUP_ID }) );

            if ( $this->errored() ) break;

            foreach ( $this->vars->{ AdminView::INPUT_RIGHTS } as $rightId => $value )
                if ( ! Right::Instance()->is( (int) $rightId ) ) $this->error( sprintf(self::ERR_RIGHT_UNKNOWN, $rightId) );
                elseif (
                    ( ! ( $exists = ( $belonging = Belonging::Instance() )
                        ->fill(
                            ( $fill = \Std::__new() )
                                ->{ $belonging->f_rightId }( (int) $rightId )
                                ->{ $belonging->f_groupId }( (int) $this->vars->{ AdminView::GROUP_ID } )
                        )
                        ->read()
                    ) )
                    && ( $value == Belonging::CANCELLED )
                )
                    continue;
                elseif ( ! $belonging
                        ->fill(
                            ( $exists ? $belonging->getVirtual() : $fill )->{ $belonging->f_value }( $value )
                        )
                        ->save()
                )
                    $this->error( sprintf(self::ERR_RIGHT_NOT_SAVED, $rightId) );

            if ( $this->errored() ) break;

            /**
             * date format validator
             *
             * @param   string      $date   to examine
             * @param   ?string     $fmt    to observe
             *
             * @return  bool
             */
            $dateValidator = fn ( string $date, string $fmt = \DateUtils::FMT_SQL_DATE ) : bool
                => ( $create = \DateTime::createFromFormat($fmt, $date) ) && $create->format($fmt) == $date;

            /**
             * finds a well formatted date
             *
             * @param   int     $userId
             * @param   string  $dateType
             *
             * @return  string|false
             */
            $datePicker = fn ( int $userId, string $dateType )
                => $dateValidator( $found = $this->vars->{ AdminView::GROUPS_DATES_LIST }[ (string) $userId ]->$dateType ?? '' )
                    ? $found
                    : false;

            /**
             * gets membership identifier if member is to be found
             *
             * @param   int     $userId
             * @param   array   &$members
             *
             * @return  false|int
             */
            $getMembershipId = fn ( int $userId, array & $members )
                => empty( $find = array_filter($members, fn ( $member ) => $member->{ Membership::Instance()->f_subject } == $userId) )
                    ? false
                    : $find[ key($find) ]->{ Membership::Instance()->f_id };

            foreach (
                \Std::__new()
                    ->groups(
                        \Std::__new()
                            ->members( ( $membership = Membership::Instance() )->getGroupsForGroup( $groupId = & $this->vars->{ AdminView::GROUP_ID }, true ) )
                            ->posted( $this->vars->{ AdminView::GROUPS_LIST } )
                            ->subjectType( $membership::TYPE_GROUP )
                            ->unfreed(self::ERR_GROUP_UNFREED)
                            ->unattached(self::ERR_GROUP_UNATTACHED)
                    )
                    ->users(
                        \Std::__new()
                            ->members( $membership->getUsersForGroup($groupId) )
                            ->posted( $this->vars->{ AdminView::INPUT_USERS } )
                            ->subjectType( $membership::TYPE_USER )
                            ->unfreed(self::ERR_USER_UNFREED)
                            ->unattached(self::ERR_USER_UNATTACHED)
                    )
                as $target
            )
            {
                foreach ( $noMoreMember = array_filter(
                    $target->members,
                    fn ( $candidate ) => ! in_array($candidate->{ Membership::Instance()->f_subject }, $target->posted)
                ) as $std )
                    if ( $this->errored() )
                        break (2);
                    elseif ( ! ( $membership = Membership::Instance() )->is( $targetId = $std->{ $membership->f_id } ) )
                        continue;
                    elseif ( ! $membership->eraze() )
                        $this->error( sprintf($target->unfreed, $targetId) );

                $isUser = $target->subjectType == $membership::TYPE_USER;

                foreach (
                    $isUser
                        ? $target->posted
                        : $newbies = array_filter(
                                $target->posted,
                                fn ( int $ided ) => empty(
                                    array_filter(
                                        $target->members,
                                        fn ( \stdClass $std ) => $std->{ Membership::Instance()->f_subject } == $ided
                                    )
                                )
                            )
                as $targetId )
                    if ( $this->errored() )
                        break (2);
                    elseif ( ! ( $membership = Membership::Instance() )
                        ->fill(
                            ( $isUser && ( $ided = $getMembershipId($targetId, $target->members) )
                                ? \Std::__new()->__setAll( (array) $membership->is($ided) )
                                : \Std::__new()
                                    ->{ $membership->f_subjectType }( $target->subjectType )
                                    ->{ $membership->f_subject }( $targetId )
                                    ->{ $membership->f_targetType }( $membership::TYPE_GROUP )
                                    ->{ $membership->f_target }( $this->group->{ Group::Instance()->f_id } )
                            )
                                ->{ $membership->f_start }(
                                    $isUser && ( $started = $datePicker($targetId, AdminView::GROUPS_TIMED_START) )
                                        ? $started
                                        : null
                                )
                                ->{ $membership->f_stop }(
                                    $isUser &&  ( $stopped = $datePicker($targetId, AdminView::GROUPS_TIMED_STOP) )
                                        ? $stopped
                                        : null
                                )
                        )
                        ->save()
                    )
                        $this->error( sprintf($target->unattached, $targetId) );
            }
        }
        while ( 0 );

        return ! $this->errored();
    }

    /**
     * determines if current user is new and sets its type as integer
     *
     * @return  bool
     */
    private
    function userIsNew () : bool
    {
        return ( function ( string &$var ) : int { $var = is_int($var) ? $var : intval($var); return $var; } )( $this->vars->{ ( User::Instance() )->f_id } ) < 1;
    }
}
