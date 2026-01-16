<?php

namespace SSO;

class AdminView extends UserView
{
    use PostControl;

    private
    const   ADMIN_SCRIPTS       = 'js/admin',
            CLASS_EDIT_BLOCK    = 'userEditionBlock',
            CLASS_EDIT_INFO     = 'userEditionInfo',
            CLASS_EDIT_INPUT    = 'userEditionInput',
            CLASS_GROUP_CHILD   = 'groupChildren',
            CLASS_USER_BUTT     = 'groupUserButton',
            FILE_EAR            = 'Ear.class.js';

    public
    const   APP_NAME            = 'applicationName',
            DATASET_DESC        = 'data-desc',
            DATASET_ID          = 'data-id',
            DATASET_METHOD      = 'data-method',
            DATASET_NAMED       = 'data-named',
            DATASET_TYPED       = 'data-typed',
            DATASET_URLED       = 'data-urled',
            EMO_DELETE          = '❌',
            EMO_SIGN_PLUS       = '➕',
            EMO_RENEW           = '🔄',
            EMO_SAVE            = '💾',
            EMO_TRASH           = '🚮',
            GROUP_DESCRIBED     = 'groupDescription',
            GROUP_ID            = 'groupId',
            GROUP_NAME          = 'groupName',
            GROUPS_DATES_LIST   = 'groupsDates',
            GROUPS_GROUP        = self::TYPE_GROUP,
            GROUPS_TIMED_START  = 'groupsTimeStart',
            GROUPS_TIMED_STOP   = 'groupsTimeStop',
            GROUPS_LIST         = 'grouped',
            INPUT_RIGHTS        = 'rightsList',
            INPUT_USERS         = 'usersList',
            METHOD_GET          = 'GET',
            METHOD_POST         = 'POST',
            RIGHTS_ADD          = 'add',
            RIGHTS_AUTH         = 'auth',
            RIGHTS_DEL          = 'del',
            RIGHTS_EDIT         = 'rightsEdit',
            RIGHTS_INFO         = 'info',
            RIGHTS_INFO_EDIT    = 'infoEdit',
            TYPE_APP            = 'application',
            TYPE_GROUP          = 'group',
            TYPE_USER           = 'user';

    private $applicationId,
            $avatars        = [],
            $apps           = [],
            $emptyRights    = false,
            $groupId,
            $groupsList     = [],
            $isGrouped      = false,
            $legend,
            $parentSet,
            $rightId,
            $usersList      = [];

    /**
     * adds admin javascript folder content
     */
    protected
    function addAdminScript () : void
    {
        foreach ( new \DirectoryIterator( sprintf('%1$s/../%2$s/', $this->dired, self::ADMIN_SCRIPTS) ) as $file )
        {
            if ( $file->getFilename() == self::FILE_EAR ) $final = $file->getPathname();
            elseif (
                ( ! $file->isDot() )
                && ( $file->getExtension() == 'js' )
            )
                $this->__script( $file->getPathname() );
        }

        if ( ! empty($final) ) $this->__script($final);
    }

    /**
     * creates an app deletor form
     *
     * @return  \DOMElement
     */
    private
    function appDeletor () : \DOMElement
    {
        $this
            ->__add(
                [
                    $ided   = $this->__input('hidden'),
                    $sub    = $this->__input('submit')
                ],
                $form = $this->__form()
            )
            ->setFormAttributes( $form, \Std::__new()->action('deleteApp') )
            ->__att($ided, 'name', ( $app = Application::Instance() )->f_id )
            ->__att($ided, $attrValue = 'value', $app->getVirtual()->{ $app->f_id })
            ->__att($sub, $attrValue, self::EMO_DELETE)
            ->__att($sub, self::DATASET_TIP, 'supprimer cette application')
            ->__class($sub, sprintf('%1$s %2$s', self::CLASS_JS_TIPPED, __FUNCTION__));

        return $form;
    }

    /**
     * manages applications
     */
    public
    function applications () : void
    {
        $this
            ->setTitle($named = 'Applications SSO')
            ->__add( $main = $this->__div() )
            ->__add(
                [
                    $title = $this->__h(1, $named),
                    ...$this->applicationsList()
                ],
                $main
            )
            ->__add($this->creatorButton(self::TYPE_APP, 'createApp'))
            ->__class($title, self::CLASS_WHITED)
            ->renderView();
    }

    /**
     * gets applications as elements
     *
     * @return array<DOMElement>
     */
    private
    function applicationsList ()
    {
        $list   = [];
        $listed = ( $mother = Application::Instance() )
            ->setRequestParameters()
            ->listAll();

        Right::Instance()->sortByProperty($listed, $mother->f_name);

        foreach ( $listed as $id => $app )
        {
            $this
                ->__add(
                    [
                        $named      = $this->__div(),
                        $descForm   = $this->__form(),
                        $keyForm    = $this->__form(),
                        $rights     = $this->__div(),
                        $deletor    = $this->__div()
                    ],
                    $block = $this->__div()
                )
                ->__add(
                    [
                        $icon = $this->__div(self::EMO_MASKS),
                        $this->__div( $app->{ $mother->f_name } )
                    ],
                    $named
                )
                ->__add($holdDesc = $this->__div(), $descForm)
                ->__add(
                    [
                        $desc       = $this->__textarea( $app->{ $mother->f_description } ),
                        $descSub    = $this->__input($submit = 'submit'),
                        $appId      = $this->__input($hidden = 'hidden')
                    ],
                    $holdDesc
                )
                ->__att( $appId, 'value', $app->{ $mother->f_id } )
                ->__att( $appId, $name = 'name', $mother->f_id )
                ->__add($holdKey = $this->__div(), $keyForm)
                ->__add(
                    [
                        $keyed  = $this->__input('text'),
                        $keySub = $this->__input($submit),
                        $appId->cloneNode(true),
                        $newKey = $this->__input($hidden)
                    ],
                    $holdKey
                )
                ->__add(
                    [
                        $rightsTitle = $this->__div(),
                        $this->rights( (int) $app->{ $mother->f_id } )
                    ],
                    $rights
                )
                ->__add(
                    [
                        $this->__div(UserView::EMO_BOOK_OPEN),
                        $this->__div('Droits applicatifs')
                    ],
                    $rightsTitle
                )
                ->__add(
                    array_filter([
                        $this->emptyRights ? $this->appDeletor() : null
                    ]),
                    $deletor
                )
                ->__att($descSub, $value = 'value', self::EMO_SAVE)
                ->__att($keySub, $value, self::EMO_RENEW)
                ->__att( $keyed, $value, \KryptoSso::Instance()->uncrypt( $app->{ $mother->f_key } ) )
                ->__att($keyed, 'readonly', true)
                ->__att($descSub, self::DATASET_TIP, 'Modifier la description')
                ->__att($keySub, self::DATASET_TIP, 'Renouveler la clef applicative')
                ->__att($desc, $name, $mother->f_description)
                ->__att($keyed, $name, Application::PARAM_CURRENT)
                ->__att($newKey, $name, $mother->f_key)
                ->__att( $newKey, $value, $mother->generateKey() )
                ->setFormAttributes( $descForm, \Std::__new()->action('changeAppDesc') )
                ->setFormAttributes( $keyForm, \Std::__new()->action('changeAppKey') )
                ->__class($block, 'applicationBlock')
                ->__class($desc, 'applicationDescribed')
                ->__class($icon, 'applicationIconed')
                ->__class($keyed, 'applicationKeyed')
                ->__class($holdDesc, $holder = 'applicationHolder')
                ->__class($holdKey, $holder)
                ->__class($descSub, self::CLASS_JS_TIPPED)
                ->__class($keySub, self::CLASS_JS_TIPPED)
                ->__class($rights, 'applicationRights');

            $list[] = $block;
        }

        return $list;
    }

    /**
     * creates a creator button
     *
     * @param   string      $type
     * @param   string      $url
     * @param   ?string     $method
     *
     * @return  \DOMElement
     */
    private
    function creatorButton ( string $type, string  $url, string $method = self::METHOD_POST ) : \DOMElement
    {
        $trans = [
            self::TYPE_APP =>
                \Std::__new()
                    ->tip('une application')
                    ->classed('adminCreatorButton')
                    ->sets( [ self::DATASET_NAMED => self::APP_NAME ] ),
            self::TYPE_GROUP =>
                \Std::__new()
                    ->tip('un groupe')
                    ->classed('adminGroupCreatorButton')
                    ->sets( [
                        self::DATASET_NAMED => self::GROUP_NAME,
                        self::DATASET_DESC  => self::GROUP_DESCRIBED
                    ] ),
            self::TYPE_USER =>
                \Std::__new()
                    ->tip('un utilisateur')
                    ->classed('adminUserCreatorButton')
                    ->sets([])
        ];

        $this
            ->__add(
                $addPic     = $this->__div(self::EMO_SIGN_PLUS),
                $appButton  = $this->__div()
            )
            ->__att($addPic, self::DATASET_TIP, sprintf('Créer %1$s', $trans[$type]->tip))
            ->__att($addPic, self::DATASET_TYPED, $type)
            ->__att($addPic, self::DATASET_URLED, $this->url( $url, ...array_filter( [$type == self::TYPE_USER ? 'index' : null] ) ))
            ->__att($addPic, self::DATASET_METHOD, $method)
            ->__class( $appButton, sprintf('adminAddingButton %1$s', $trans[$type]->classed) )
            ->__class($addPic, self::CLASS_JS_TIPPED);

        foreach ( $trans[$type]->sets as $attr => $data )
            $this->__att($addPic, $attr, $data);

        return $appButton;
    }

    /**
     * creates `ASC` and `DESC` sorters for given field
     *
     * @param   string  $described
     * @param   string  $field
     *
     * @return  array<string>
     */
    private
    function createDualSorters ( string $described, string $field ) : array
    {
        return [
            sprintf($grouping = '%1$s, %2$s', $described, self::SORT_ASC_MSG)
                => sprintf($sprinting = '%1$s:%2$s', $field, self::SORT_ASC),
            sprintf($grouping, $described, self::SORT_DESC_MSG)
                => sprintf($sprinting, $field, self::SORT_DESC)
        ];
    }

    /**
     * creates a button for right's management
     *
     * @param   string      $label
     * @param   string      $id
     * @param   string      $tip
     * @param   ?string     $index  for forms
     * @param   ?bool       $selected
     *
     * @return  AdminView
     */
    private
    function createRightButton ( string $label, string $id, string $tip, string $index = self::RIGHTS_DEL, bool $selected = false ) : AdminView
    {
        do
        {
            $infoed = $index == self::RIGHTS_INFO
                ? sprintf(
                    ' %1$s',
                    $edit = ( $this->isGrouped
                        ? $index
                        : sprintf('%1$s %2$s', $index, self::RIGHTS_INFO_EDIT)) )
                : false;

            if ( $infoed && empty($tip) && $this->isGrouped ) break;

            $this
                ->__add(
                    [
                        $inputed    = $this->__input($label == self::EMO_TRASH ? 'checkbox' : 'radio'),
                        $labeled    = $this->__label($label)
                    ],
                    $this->parentSet
                )
                ->__att($inputed, 'id', $ided = $this->createUniqueId())
                ->__att($labeled, 'for', $ided)
                ->__att($inputed, 'name', sprintf(
                    '%1$s%4$s[%3$s][%2$s]',
                    self::INPUT_RIGHTS,
                    $this->isGrouped ? $this->rightId : $this->legend,
                    $index,
                    $this->isGrouped ? sprintf('[%1$s]', $this->applicationId) : null
                ));

            if ( ! empty($tip) )
                $this
                    ->__att($labeled, self::DATASET_TIP, $tip)
                    ->__class( $labeled, sprintf('%1$s%2$s', self::CLASS_JS_TIPPED, $infoed ?? null) );
            elseif ( $infoed )
                $this->__class($labeled, $edit);

            if ( $infoed )
            {
                if ( $edit == $index ) $this->__att($inputed, 'disabled', true);

                $this->__class($inputed, $index);
            }

            if ( $selected ) $this->__att($inputed, 'checked', true);

            if ( $index == self::RIGHTS_AUTH ) $this->__att($inputed, 'value', $this->labelToAuthValue($label) );
        }
        while ( 0 );

        return $this;
    }

    /**
     * creates an incremental identifier
     *
     * @return  int
     */
    private
    function createUniqueId () : int
    {
        static $ided = 1;

        $ided++;

        return $ided;
    }

    /**
     * gets avatar base on identifier
     *
     * @param   int     $avatarId
     *
     * @return  string
     */
    private
    function getAvatar ( int $avatarId ) : string
    {
        if ( ! array_key_exists($avatarId, $this->avatars) )
            $this->avatars[ $avatarId ] = (
                ( $avatar = Avatar::Instance() )->is($avatarId)
            )->{ $avatar->f_content };

        return $this->avatars[ $avatarId ];
    }

    /**
     * gets a right value from list of objects, or 0 as cancelled
     *
     * @param   int                 $rightId
     * @param   array<\stdClass>    &$source
     *
     * @return   int
     */
    private
    function getGroupedRightValue ( int $rightId, array &$source ) : int
    {
        $filtered = array_values(array_filter($source, fn ( \stdClass $obj ) => $obj->{ Belonging::Instance()->f_rightId } == $rightId ));

        return $filtered[0]->{ Belonging::Instance()->f_value } ?? 0;
    }

    /**
     * creates a group deletor form
     *
     * @param   \stdClass   $theese
     *
     * @return  \DOMElement
     */
    private
    function groupDeletor ( \stdClass $theese ) : \DOMElement
    {
        $this
            ->__add( $button = $this->__input('button'), $block = $this->__div() )
            ->__att($block, self::DATASET_URLED, $this->url('deleteGroup'))
            ->__att($block, self::DATASET_TYPED, self::ENC_TYPE_DATA)
            ->__att($block, self::DATASET_METHOD, self::METHOD_POST)
            ->__att(
                $block,
                self::DATASET_ID,
                $theese->{ ( $group = Group::Instance() )->f_id } )
            ->__att(
                $button,
                'title',
                sprintf('Supprimer le groupe %1$s', $theese->{ $group->f_name })
            )
            ->__att($button, 'value', self::EMO_DELETE)
            ->__class( $block, sprintf('%1$s innerDelete', __FUNCTION__) );

        return $block;
    }

    /**
     * manages groups
     */
    public
    function groups () : void
    {
        $listed = [];
        $group  = Group::Instance()->lot( Database::PER_PAGE_SMALL );
        $params = $this->retrieve();

        foreach (
            ( $search = $this->getSearch() )
                ? $group
                    ->fill(
                        \Std::__new()
                            ->{ $group->f_name }($search)
                            ->{ $group->f_description }($search),
                        $group->type_likeLoose
                    )
                    ->setRequestParameters(...$params)
                    ->search()
                : $group
                    ->setRequestParameters(...$params)
                    ->listAll()
            as $grouped
        )
        {
            $this
                ->initPostControl()
                ->__add( $groupBlock = $this->__div(), $groupForm = $this->__form() )
                ->__add(
                    [
                        $groupId        = $this->__input('hidden'),
                        $groupName      = $this->__div(),
                        $groupDesc      = $this->__div(),
                        $groupSplit     = $this->__div(),
                        $groupSubmit    = $this->__div()
                    ],
                    $groupBlock
                )
                ->pilePostCount(1)
                ->__add(
                    [
                        $groupIcon = $this->__div( self::EMO_GROUP ),
                        $groupText = $this->__div()
                    ],
                    $groupName
                )
                ->__add( $groupNameInput = $this->__input('text'), $groupText )
                ->pilePostCount(1)
                ->__add(
                    $groupDescribed = $this->__textarea( $grouped->{ $group->f_description } ),
                    $groupDesc
                )
                ->pilePostCount(1)
                ->__add(
                    [
                        $listRights    = $this->__div(),
                        $listUsers     = $this->__div(),
                        $listGroups    = $this->__div()
                    ],
                    $groupSplit
                )
                ->__add(
                    [
                        $rightsHead = $this->__div('Juridiction'),
                        ...$this
                            ->setGroupId( $grouped->{ $group->f_id } )
                            ->rightsForGroup()
                    ],
                    $listRights
                )
                ->__add(
                    [
                        $usersHead = $this->__div('Communauté'),
                        $usersList = $this->__div()
                    ],
                    $listUsers
                )
                ->__add( $this->usersForGroup(), $usersList )
                ->__add(
                    [
                        $groupsHead = $this->__div('Descendance directe'),
                        ...$this->groupsForGroup()
                    ],
                    $listGroups
                )
                ->__add(
                    array_filter([
                        $this->groupDeletor($grouped),
                        $saver = $this->__input('submit')
                    ]),
                    $groupSubmit
                )
                ->pilePostCount(1)
                ->ruling()
                ->setFormAttributes(
                    $groupForm,
                    \Std::__new()->action('modifyGroup')
                )
                ->__att($groupNameInput, $value = 'value', $grouped->{ $group->f_name })
                ->__att($groupNameInput, $name = 'name', self::GROUP_NAME)
                ->__att($groupNameInput, $hold = 'placeholder', 'Nom de groupe')
                ->__att($groupNameInput, 'autocomplete', 'off')
                ->__att($groupName, 'minlength', Group::NAME_LENGTH)
                ->__att($groupDescribed, $hold, 'Description du groupe')
                ->__att($groupDescribed, $name, sprintf(
                    '%1$s[%2$s]',
                    self::GROUP_DESCRIBED,
                    $this->groupId
                ))
                ->__att($saver, $value, self::EMO_SAVE)
                ->__att( $saver, self::DATASET_TIP, sprintf('Enregistrer le groupe %1$s', $this->escape2Quotes($grouped->{ $group->f_name })) )
                ->__att($groupNameInput, $required = 'required', $required)
                ->__att($groupId, $name, self::GROUP_ID)
                ->__att($groupId, $value, $grouped->{ $group->f_id })
                ->__class( $saver, sprintf('groupInnerSaver %1$s', self::CLASS_JS_TIPPED) )
                ->__class( $groupForm, sprintf('groupEditionForm %1$s', self::CLASS_FORM_WS) )
                ->__class($groupName, 'groupHead')
                ->__class($groupSplit, 'groupsListsSplitter')
                ->__class($usersList, 'groupUsersList');

            $listed[] = $groupForm;
        }

        $this->renderButtonedPage(
            '%2$s groupe%1$s SSO',
            $listed,
            $this->createDualSorters('Nom de groupe', $group->f_name),
            $search,
            $group,
            $this->creatorButton(self::TYPE_GROUP, 'createGroup')
        );
    }

    /**
     * lists groups for a given group
     *
     * @return  array<\DOMElement>
     */
    private
    function groupsForGroup () : array
    {
        if ( empty($this->groupsList) )
            $this->groupsList = Group::Instance()
                ->setRequestParameters()
                ->listAll();

        $list       = [];
        $exposed    = array_merge(
            $those = ( $membership = Membership::Instance() )->getGroupsForGroup( $this->groupId, true ),
            array_filter(
                $this->groupsList,
                fn ( \stdClass $item ) => ! in_array(
                    $itemId = ( $item->{ $ided = ( $group = Group::Instance() )->f_id }) ,
                    array_column($those, $membership->f_subject)
                )
                && $itemId != $this->groupId
            )
        );

        foreach ( $exposed as $key => $membered )
        {
            $read = ( $group = Group::Instance() )
                ->is( $memberId = $membered->{ $membership->f_subject } ?? $membered->{ $group->f_id } );

            $this
                ->__add(
                    [
                        $check = $this->__input('checkbox'),
                        $label = $this->__label( sprintf('%1$s %2$s', self::EMO_GROUP, $read->{ $group->f_name }) )
                    ],
                    $grouped = $this->__div()
                )
                ->__att(
                    $check,
                    'id',
                    $ided = sprintf(
                        '%1$s%2$s%3$s',
                        self::GROUPS_LIST,
                        $key,
                        $this->groupId
                    )
                )
                ->__att($check, 'value', $memberId)
                ->__att(
                    $check,
                    'name',
                    sprintf(
                        '%1$s[%2$s][%3$s]',
                        self::GROUPS_LIST,
                        self::GROUPS_GROUP,
                        $memberId
                    )
                )
                ->__att($label, 'for', $ided)
                ->__att($label, self::DATASET_TIP, $this->escape2Quotes( $read->{ $group->f_description } ))
                ->__class($grouped, self::CLASS_GROUP_CHILD)
                ->__class($label, self::CLASS_JS_TIPPED);

            if ( ! empty( array_filter($those, fn ( \stdClass $reference ) => $reference->{ $membership->f_subject } == $memberId) ) )
                $this->__att($check, $checked = 'checked', $checked);

            $list[] = $grouped;
        }

        $this->pilePostCount( count($this->groupsList) );

        return $list;
    }

    /**
     * gets a right value based on label's one
     *
     * @param   string  &$label
     *
     * @return  int
     */
    private
    function labelToAuthValue ( string &$label ) : int
    {
        static $trans = [
            self::EMO_EYE       => Belonging::READ,
            self::EMO_PEN       => Belonging::WRITE,
            self::EMO_NO_WAY    => Belonging::CANCELLED
        ];

        return $trans[$label] ?? Belonging::CANCELLED;
    }

    /**
     * creates an admin page with pagination, sort and search options
     *
     * @param   string                  $title
     * @param   array<\DOMElement>      &$listed
     * @param   array<string,string>    $sortOptions
     * @param   bool                    $searchInMotion
     * @param   Group|User              &$virtualObject
     * @param   ?\DOMElement            $creatingButton
     * @param   ?array<\DOMElement>     $additionalButtons
     */
    private
    function renderButtonedPage ( string $title, array &$listed, array $sortOptions, bool $searchInMotion, &$virtualObject, \DOMElement $creatingButton = null, array $additionalButtons = [] ) : void
    {
        $this
            ->setTitle( $title = sprintf($title, \StringUtils::esse($virtualObject->totalResults), $virtualObject->totalResults) )
            ->__add( $main = $this->__div() )
            ->__add(
                [
                    $title  = $this->__h(1, $title),
                    $list   = $this->__div(),
                    $holder = $this->createButtonsHolder()
                ],
                $main
            )
            ->__add(
                array_filter(
                    [
                        ...$this->createPager($virtualObject),
                        ( $noList = empty($listed) )
                            ? null
                            : $this->createSorter(
                                \Std::__new()->sorter($sortOptions)
                            ),
                        $noList ? null : $this->createSearcher(),
                        ! $searchInMotion ? null : $this->createSearcher(true),
                        ...$additionalButtons
                    ]
                ),
                $holder
            )
            ->__add($noList ? $this->noData() : $listed, $list)
            ->__class($title, self::CLASS_WHITED);

        if ( get_class($virtualObject) == User::class ) $this->__class($list, 'anyUserList');
        if ( ! is_null($creatingButton) )               $this->__add($creatingButton);

        $this->renderView();
    }

    /**
     * retrieves get parameters, if any
     *
     * @param   ?string     $defaultSortField
     *
     * @return  array<int|string>
     */
    private
    function retrieve ( string $defaultSortField = null ) : array
    {
        return [
            $this->getPage(),
            ( $sorting = $this->getSort() ?? false )->{ self::SORT_WHAT } ?? $defaultSortField,
            $sorting ? $sorting->{ self::SORT_HOW } : self::SORT_ASC
        ];
    }

    /**
     * gets a list of rights for targeted application
     *
     * @param   int                 $applicationId
     * @param   ?array<\stdClass>   &$currentGroupRights     💡 rights can be removed from applications'list, and parametered in groups' panel
     *
     * @return  \DOMElement
     */
    private
    function rights ( int $applicationId, array &$currentGroupRights = null ) : \DOMElement
    {
        $this->emptyRights = empty( $data = ( $application = Application::Instance() )->getRights($applicationId) );

        Right::Instance()->sortByProperty($data, $application->f_name );

        if ( ! ( $isGrouped = ! is_null($currentGroupRights) ) )
            $this
                ->__add( $parent = $this->__form(), $list = $this->__div() )
                ->__class($list, self::RIGHTS_EDIT)
                ->setFormAttributes($parent, \Std::__new()->action('changeAppRights'));
        else
            $this
                ->__add( $parent = $this->__div(), $list = $this->__div() )
                ->__class($list, self::RIGHTS_EDIT)
                ->pilePostCount( count($data) );

        foreach ( $data as $keyed => $item )
        {
            $this
                ->__add(
                    $set = $this->__fieldset($legend = $item->{ ( $obj = Right::Instance() )->f_name }),
                    $parent
                )
                ->setButtonsData(
                    $set,
                    $legend,
                    $applicationId,
                    $isGrouped,
                    (int) $item->{ $obj->f_id }
                )
                ->createRightButton(
                    self::HEX_BULB,
                    self::RIGHTS_INFO,
                    $item->{ $obj->f_described } ?? '',
                    self::RIGHTS_INFO
                );

            if ( $isGrouped )
                $this
                    ->createRightButton(
                        self::EMO_EYE,
                        Right::TXT_READ,
                        self::TXT_READ,
                        self::RIGHTS_AUTH,
                        ( $valued = $this->getGroupedRightValue($item->{ $obj->f_id }, $currentGroupRights) ) == Belonging::READ
                    )
                    ->createRightButton(
                        self::EMO_PEN,
                        Right::TXT_WRITE,
                        self::TXT_WRITE,
                        self::RIGHTS_AUTH,
                        $valued == Belonging::WRITE
                    )
                    ->createRightButton(
                        self::EMO_NO_WAY,
                        Right::TXT_NOPE,
                        'ne pas autoriser',
                        self::RIGHTS_AUTH,
                        $valued == Belonging::CANCELLED
                    );
            else
                $this->createRightButton(self::EMO_TRASH, Right::TXT_DEL, 'supprimer ce droit');
        }

        if ( ! $isGrouped)
            $this
                ->__add(
                    [
                        $set    = $this->__fieldset('+ 1 droit'),
                        $subDiv = $this->__div()
                    ],
                    $parent
                )
                ->__add( $add = $this->__div(self::EMO_SIGN_PLUS), $set )
                ->__add( $disk = $this->__input('submit'), $subDiv )
                ->__add( $appId = $this->__input('hidden'), $parent )
                ->__att($add, self::DATASET_TIP, 'créer un nouveau droit')
                ->__att($disk, $value = 'value', self::EMO_SAVE)
                ->__att($disk, self::DATASET_TIP, 'Enregistrer la liste de droits')
                ->__att($add, 'data-index', self::RIGHTS_ADD)
                ->__att( $appId, 'name', Right::Instance()->f_id )
                ->__att( $appId, $value, $applicationId )
                ->__class($disk, self::CLASS_JS_TIPPED)
                ->__class($add, sprintf('%1$s rightCreator', self::CLASS_JS_TIPPED))
                ->initPostControl()
                ->pilePostCount( count($data) * 2 + 4 )
                ->ruling();

        return $list;
    }

    /**
     * lists rights for a given group
     *
     * @return  array<\DOMElement>
     */
    private
    function rightsForGroup () : array
    {
        if ( empty($this->apps) )
            $this->apps = Application::Instance()
                ->setRequestParameters()
                ->listAll();

        $list           = [];
        $groupRights    = Belonging::Instance()->forGroup($this->groupId, true);

        foreach ( $this->apps as $app )
        {
            $this
                ->__add(
                    [
                        $head = $this->__div(),
                        $elems = $this->__div()
                    ],
                    $applicated = $this->__div()
                )
                ->__add(
                    [
                        $this->__div(self::EMO_MASKS),
                        $this->__div( $app->{ ( $application = Application::Instance() )->f_name } )
                    ],
                    $head
                )
                ->__add(
                    $this->rights(
                        $app->{ $application->f_id },
                        $groupRights
                    ),
                    $elems
                )
                ->__att( $head, self::DATASET_TIP, $this->escape2Quotes($app->{ $application->f_description }) )
                ->__class($head, sprintf('groupsAppName %1$s', self::CLASS_JS_TIPPED))
                ->__class($applicated, 'groupsApp')
                ->__class($elems, 'groupsAppRights');

            $list[] = $applicated;
        }

        return $list;
    }

    /**
     * sets buttons data for repeated use
     *
     * @param   \DOMElement &$parent
     * @param   int         $key
     * @param   string      $name
     * @param   int         $applicationId
     * @param   ?bool       $isGrouped
     * @param   ?int        $rightId
     *
     * @return  AdminView
     */
    private
    function setButtonsData ( \DOMElement &$parent, string $name, int $applicationId, bool $isGrouped = false, int $rightId = null ) : AdminView
    {
        $this->parentSet        = &$parent;
        $this->legend           = $name;
        $this->isGrouped        = $isGrouped;
        $this->applicationId    = $applicationId;
        $this->rightId          = $rightId;

        return $this;
    }

    /**
     * sets `$this->groupId`
     *
     * @param   int     $groupId
     *
     * @return  AdminView
     */
    private
    function setGroupId ( int $groupId ) : AdminView
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * creates a form for user alteration
     */
    public
    function userEdit () : void
    {
        $current = ( $current = ( $user = User::Instance() )->is( $_GET[ $user->f_id ] ?? 0 ) ) ? $current : \Std::__new();

        /**
         * creates an inputing row
         *
         * @param   string                              $description
         * @param   string                              $emoticon
         * @param   \DOMElement|array<\DOMElement>      $domInput
         * @param   \Std|array<\Std >                   $inputAttributes
         * @param   ?string                             $legend
         *
         * @return  \DOMElement
         */
        $newBlock = function ( string $description, string $emoticon, $domInputs, $inputAttributes, string $legend = null ) : \DOMElement
        {
            /**
             * always gives back an array
             *
             * @param   mixed   $candidate
             *
             * @return  array
             */
            $arrayOrArray = fn ( $candidate )  : array => is_array($candidate) ? $candidate : [ $candidate ];

            $domInputs          = $arrayOrArray($domInputs);
            $inputAttributes    = $arrayOrArray($inputAttributes);

            $this
                ->__add(
                    [
                        $infos  = $this->__div(),
                        $input  = $this->__div()
                    ],
                    $block = $this->__div()
                )
                ->__add(
                    [
                        $emoji = $this->__div($emoticon),
                        $this->__div($description)
                    ],
                    $infos
                )
                ->__class($block, self::CLASS_EDIT_BLOCK)
                ->__class($infos, self::CLASS_EDIT_INFO);

            if ( ! is_null($legend) )
                $this
                    ->__att($emoji, self::DATASET_TIP, $legend)
                    ->__class($emoji, self::CLASS_JS_TIPPED);

            foreach ( $domInputs as $key => $domInput )
            {
                $this
                    ->__add($domInput, $input)
                    ->__class($domInput, self::CLASS_EDIT_INPUT);

                foreach ( $inputAttributes[ $key ] ?? [] as $name => $value )
                    $this->__att($domInput, $name, $value);
            }

            return $block;
        };

        /**
         * gets current property or default value
         *
         * @param   string  $name
         * @param   ?mixed  $default
         *
         * @return  mixed
         */
        $propEmpty = fn ( string $name, $default = '' ) => $current->$name ?? $default;

        /**
         * displays a readable shortened date
         *
         * @param   string  $date
         *
         * @return  string
         */
        $fmtDate = fn ( string $date ) : string => empty($date) ? $date : ( new \DateTime($date) )->format( \DateUtils::FMT_SQL_DATE );

        /**
         * removes false values, as boolean attriubutes won't accept false
         *
         * @param   \Std    $data
         *
         * @return  \Std
         */
        $noFalse = fn ( \Std $data ) => \Std::__new()->__setAll( array_filter( (array) $data, fn ($value) => $value !== false ) );

        $this
            ->setTitle( $titled = ( $isNew = empty( (array) $current ) )
                ? 'Création d\'un utilisateur'
                : sprintf('Edition d%3$s%1$s %2$s', $first = $current->{ $user->f_firstName }, $current->{ $user->f_lastName }, stripos('aeiouy', substr($first, 0, 1)) !== false ? '\'' : 'e ' )
            )
            ->__add(
                [
                    $title  = $this->__h(1, $titled),
                    $form   = $this->__form()
                ]
            )
            ->__add(
                [
                    $holder = $this->__div(),
                    $saver  = $this->__div()
                ],
                $form
            )
            ->__add(
                [
                    $id             = $this->__input('hidden'),
                    $firstName      = $newBlock( self::MSG_FIRST_NAME, self::EMO_PAWN, $this->__input( $text = 'text' ),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_firstName ) )
                                            ->required( $required = 'required')
                                            ->autocomplete( $off = 'off' )
                                            ->name($user->f_firstName),
                                        $namesRule = 'Lettres et traits d\'union [150]'
                    ),
                    $lastName       = $newBlock( self::MSG_LAST_NAME, self::EMO_FAMILY, $this->__input($text),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_lastName ) )
                                            ->required($required)
                                            ->autocomplete($off)
                                            ->name($user->f_lastName),
                                        $namesRule
                    ),
                    $email          = $newBlock( self::MSG_EMAIL, self::EMO_MAIL, $this->__input('email'),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_email ) )
                                            ->required('required')
                                            ->autocomplete($off)
                                            ->name($user->f_email)
                    ),
                    $login          = $newBlock( self::MSG_LOGIN, self::EMO_TAG, $this->__input($text),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_login ) )
                                            ->required('required')
                                            ->autocomplete($off)
                                            ->name($user->f_login),
                                        'Chaque première lettre des prénoms et l\'entièreté du nom de famille, non accentuées, en minuscules [3-50]'
                    ),
                    $password       = $newBlock( self::MSG_PASSWORD, self::EMO_LOCK, $this->__input('password'),
                                        \Std::__new()
                                            ->readonly(true)
                                            ->name($user->f_password)
                                            ->class( sprintf('%1$s %2$s', self::CLASS_EDIT_INPUT, 'pwdGenerator') )
                                            ->{ self::DATASET_URLED }( $this->url('getPassword') )
                                            ->{ self::DATASET_METHOD }( self::METHOD_GET ),
                                        'Cliquez dans le champ désactivé pour une suggestion non altérable'
                    ),
                    $status         = $newBlock(
                                        self::MSG_STATUS,
                                        self::EMO_RED_FLAG,
                                        [
                                            $active         = $this->__input( $radio = 'radio' ),
                                            $labelActive    = $this->__label(self::EMO_THUMB),
                                            $down           = $this->__input($radio),
                                            $labelDown      = $this->__label(self::EMO_SLEEP)
                                        ],
                                        [
                                            $noFalse(
                                                \Std::__new()
                                                    ->value( $chk = User::ACTIVE )
                                                    ->name($user->f_status)
                                                    ->id( $activeId = $this->createUniqueId() )
                                                    ->checked( $isActive = ( (int) ($current->{ $user->f_status } ?? 1) == $chk ) )
                                            ),
                                            \Std::__new()
                                                ->for($activeId)
                                                ->class( $classed = sprintf('%1$s %2$s', self::CLASS_EDIT_INPUT, self::CLASS_JS_TIPPED) )
                                                ->{ self::DATASET_TIP }(self::MSG_ACTIVE),
                                            $noFalse(
                                                \Std::__new()
                                                    ->value(User::SLEEPING)
                                                    ->name($user->f_status)
                                                    ->id( $sleepId = $this->createUniqueId() )
                                                    ->checked( ! $isActive )
                                            ),
                                            \Std::__new()
                                                ->for($sleepId)
                                                ->class($classed)
                                                ->{ self::DATASET_TIP }(self::MSG_DISABLED)
                                        ]
                    ),
                    $validityLimit  = $newBlock( self::MSG_LIMIT, self::EMO_CALENDAR, $this->__input( $date = 'date' ),
                                        \Std::__new()
                                            ->value( $fmtDate( $propEmpty( $user->f_validityLimit ) ) )
                                            ->autocomplete($off)
                                            ->name($user->f_validityLimit)
                    ),
                    $phone          = $newBlock( self::MSG_PHONE, self::EMO_PHONE, $this->__input( $tel = 'tel' ),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_phone ) )
                                            ->autocomplete($off)
                                            ->name($user->f_phone),
                                        $phonesRule = 'Chiffres [10]'
                    ),
                    $mobile         = $newBlock( self::MSG_MOBILE, self::EMO_MOBILE, $this->__input($tel),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_mobile ) )
                                            ->autocomplete($off)
                                            ->name($user->f_mobile),
                                        $phonesRule
                    ),
                    $initials       = $newBlock( self::MSG_INITIALS, self::EMO_PEN, $this->__input($text),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_initials ) )
                                            ->required('required')
                                            ->autocomplete($off)
                                            ->name($user->f_initials),
                                        sprintf('Lettres majuscules [%1$s-7]', $isNew ? FormForAdmin::INIT_LENGTH_NEW : FormForAdmin::INIT_LENGTH_OLD)
                    ),
                    $short          = $newBlock( self::MSG_SHORT, self::EMO_TARGET, $this->__input('number'),
                                        \Std::__new()
                                            ->value( $propEmpty( $user->f_short ) )
                                            ->autocomplete($off)
                                            ->name($user->f_short),
                                        'Chiffres [3-4]'
                    ),
                    $hired          = $newBlock( self::MSG_HIRED, self::EMO_CALENDAR, $this->__input($date),
                                        \Std::__new()
                                            ->value( $fmtDate( $propEmpty( $user->f_hired ) ) )
                                            ->required('required')
                                            ->autocomplete($off)
                                            ->name($user->f_hired)
                    ),
                    $fired          = $newBlock( self::MSG_FIRED, self::EMO_CALENDAR, $this->__input($date),
                                        \Std::__new()
                                            ->value( $fmtDate( $propEmpty( $user->f_fired ) ) )
                                            ->autocomplete($off)
                                            ->name($user->f_fired)
                        )
                ],
                $holder
            )
            ->__add( $save = $this->__input('submit'), $saver )
            ->__att($save, $value = 'value', self::EMO_SAVE)
            ->__att( $id, $value, $current->{ $user->f_id } ?? 0 )
            ->__att($id, 'name', $user->f_id)
            ->__class($title, self::CLASS_WHITED)
            ->__class($holder, 'userEditionHolder')
            ->__class($saver, 'userEditionSaver')
            ->setFormAttributes( $form, \Std::__new()->action('saveUser') )
            ->renderView();
    }

    /**
     * manages users
     *
     * @param   bool    $activesOnly
     */
    public
    function users ( bool $activesOnly ) : void
    {
        $listed     = [];
        $params     = $this->retrieve( ( $user = User::Instance() )->f_firstName );
        $search     = $this->getSearch();
        $userType   = $search
                        ? null
                        : $this->createButton(
                            self::EMO_SLEEP,
                            $activesOnly
                                ? 'Voir aussi les profils inactifs'
                                : 'Ne voir que les profils actifs',
                            (array) \Std::__new()
                                ->{ ( $dataOut = fn ( string $str ) : string => str_replace('data-', '', $str) )(self::DATASET_URLED) }(
                                    sprintf(
                                        '%1$s/%2$s%3$s',
                                        UserView::createLink(self::ROAD_USERS),
                                        $activesOnly
                                            ? User::ALL
                                            : User::ACTIVE,
                                        empty($_GET)
                                            ? null
                                            : sprintf('?%1$s', http_build_query($_GET))
                                    )
                                )
                                ->{ $dataOut(self::DATASET_TYPED) }( $user->f_status )
                                ->{ $dataOut(self::DATASET_METHOD) }( self::METHOD_GET ),
                                ['userStatusSwitcher']
                        );

        foreach (
            $search
                ? $user
                    ->fill(
                        \Std::__new()
                            ->__setAll(
                                array_fill_keys(
                                    [
                                        $user->f_firstName,
                                        $user->f_lastName,
                                        $user->f_email,
                                        $user->f_login,
                                        $user->f_phone,
                                        $user->f_mobile,
                                        $user->f_initials,
                                        $user->f_short
                                    ],
                                    $search
                                )
                            ),
                        $user->type_likeLoose
                    )
                    ->setRequestParameters(...$params)
                    ->search()
                : $user
                    ->setRequestParameters(...$params)
                    ->any($activesOnly)
            as $person
        )
            $listed[] = $this
                ->setUser($person)
                ->setEditMode(true)
                ->fetch();

        $this->renderButtonedPage(
            '%2$s utilisateur%1$s SSO',
            $listed,
            array_merge(
                $this->createDualSorters(UserView::MSG_FIRST_NAME, $user->f_firstName),
                $this->createDualSorters(UserView::MSG_LAST_NAME, $user->f_lastName)
            ),
            $search,
            $user,
            $this->creatorButton(self::TYPE_USER, self::USER_EDITION, self::METHOD_GET),
            array_filter( [ $userType ] )
        );
    }

    /**
     * gets users for targeted group
     *
     * @return  array<\DOMElement>
     */
    private
    function usersForGroup () : array
    {
        if ( empty($this->usersList) ) $this->usersList = ( $ui = User::Instance() )->listAllActive();

        $list = [];
        $those = ( $membership = Membership::Instance() )->getUsersForGroup( $this->groupId );

        /**
         * names an input
         *
         * @param   string  $type
         * @param   int     $userId
         *
         * @return  string
         */
        $naming = fn ( string $type, int $userId ) : string => sprintf('%1$s[%2$s][%3$s][%4$s]', self::GROUPS_DATES_LIST, $this->groupId, $userId, $type);

        /**
         * gets a string representation of given date
         *
         * @param   \DateTime   $date
         *
         * @return  string
         */
        $dateStr = fn ( \DateTime $date ) : string => $date->format( \DateUtils::FMT_SQL_DATE );

        /**
         * formats a date
         *
         * @param    string     $text
         * @param   \DateTime   $date
         *
         * @return  string
         */
        $dating = fn ( string $text, \DateTime $date ) : string => sprintf( '%1$s %2$s', $text, $dateStr($date));

        foreach ( $this->usersList as $user )
        {
            if ( $oneOfThose = empty(
                    $filtered = array_filter(
                        $those,
                        fn ( \stdClass $element ) => $element->{ $membership->f_subject } == $user->{ ( User::Instance() )->f_id }
                    )
                ) ? false : $filtered[ key($filtered) ]
            )
                $membership->is( $oneOfThose->{ $membership->f_id } );

            $starts = ( $vo = $membership )->starts();
            $ends   = $vo->stops();

            $this
                ->__add(
                    [
                        $dated      = $this->__div(),
                        $picted     = $this->__div( $this->getAvatar( $user->{ ( $ui = User::Instance() )->f_avatarId } ) ),
                        $checking   = $this->__div(),
                        $texted     = $this->__div( sprintf(
                            '%1$s %2$s',
                            $user->{ $ui->f_firstName },
                            $user->{ $ui->f_lastName }
                        ) )
                    ],
                    $item = $this->__div()
                )
                ->__add(
                    [
                        $dater  = $this->__div( self::EMO_CALENDAR ),
                        $start  = $this->__input($hidden = 'hidden'),
                        $end    = $this->__input($hidden)
                    ],
                    $dated
                )
                ->__add(
                    [
                        $checkbox   = $this->__input('checkbox'),
                        $labeled    = $this->__label()
                    ],
                    $checking
                )
                ->__att(
                    $dated,
                    self::DATASET_TIP,
                    sprintf(
                        'Limite temporelle%1$s',
                        ! $oneOfThose
                        || ! $starts && ! $ends
                            ? null
                            : trim(implode(' - ', array_filter(
                                [
                                    ' ',
                                    empty($starts) ? null : $dating('début : ', $starts),
                                    empty($ends) ? null : $dating('fin : ', $ends)
                                ]
                            )))
                    )
                )
                ->__att($start, $name = 'name', $naming( self::GROUPS_TIMED_START, $user->{ $ui->f_id } ))
                ->__att($end, $name , $naming( self::GROUPS_TIMED_STOP, $user->{ $ui->f_id } ))
                ->__att($checkbox, 'id', $id = sprintf(
                    '%1$s%2$s',
                    self::CLASS_USER_BUTT,
                    $this->createUniqueId()
                ))
                ->__att($checkbox, $name, sprintf(
                    '%1$s[%2$s][%3$s]',
                    self::INPUT_USERS,
                    $this->groupId,
                    $user->{ $ui->f_id }
                ))
                ->__att($labeled, 'for', $id)
                ->__class($item, 'groupUserBlock')
                ->__class(
                    $dated,
                    sprintf(
                        '%1$s groupUserTimeLimit %2$s',
                        self::CLASS_USER_BUTT,
                        self::CLASS_JS_TIPPED
                    )
                )
                ->__class($picted, self::CLASS_USER_BUTT)
                ->__class($checking, self::CLASS_USER_BUTT)
                ->__class($texted, 'groupUserNamed');

            if ( $oneOfThose )
            {
                $this->__att($checkbox, $checked = 'checked', $checked);

                if ( $starts )  $this->__att($start, 'value', $dateStr($starts));
                if ( $ends )    $this->__att($end, 'value', $dateStr($ends));
            }

            $list[] = $item;
        }

        $this->pilePostCount( count($this->usersList) );

        return $list;
    }
}
