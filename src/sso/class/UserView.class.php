<?php

namespace SSO;

class UserView extends SsoView
{
    use Dates;

    protected
    const   CLASS_APPLICATIONED     = 'applicationed',
            CLASS_EDITABLE          = 'editable',
            CLASS_EDITING           = 'editing',
            CLASS_HIDABLE_CHILD     = 'hidableChild',
            CLASS_HIDABLE_PARENT    = 'hidableParent',
            CLASS_ICONED_ROW        = 'iconedRow',
            CLASS_JS_TIPPED         = 'jsTipped',
            CLASS_RECURSIVE_MARGED  = 'cumulatedMarges',
            CLASS_SINGLE            = 'single',
            CLASS_USER_HEAD         = 'userHead',
            CLASS_USER_ICON         = 'userIcon',
            CLASS_USER_ITEM         = 'userItem',
            DATASET_TIP             = 'data-title',
            USER_EDITION            = 'editUser';

    public
    const   EMO_ALIEN               = '👽',
            EMO_BOOK_BLUE           = '📘',
            EMO_BOOK_GRAY           = '📓',
            EMO_BOOK_OPEN           = '📖',
            EMO_BOOK_ORANGE         = '📙',
            EMO_CALENDAR            = '📅',
            EMO_COUNTER_BELL        = '🛎️',
            EMO_DOOR                = '🚪',
            EMO_FREE                = '🆓',
            EMO_GEAR                = '⚙️',
            EMO_GROUP               = '🧑🏿‍🤝‍🧑🏻',
            EMO_LOCK                = '🔓',
            EMO_MAIL                = '📫',
            EMO_MASKS               = '🎭',
            EMO_MOBILE              = '📱',
            EMO_NOTES               = '📝',
            EMO_PEN                 = '✏️',
            EMO_PHONE               = '☎️',
            EMO_ROLLING             = '🙄',
            EMO_SAVE                = '💾',
            EMO_SEARCH              = '🔎',
            EMO_SLEEP               = '💤',
            EMO_TARGET              = '🎯',
            EMO_THUMB               = '👍🏻',
            EMO_USERS               = '👨‍👦‍👦',
            MSG_ACTIVE              = 'profil actif',
            MSG_DISABLED            = 'profil désactivé',
            MSG_EMAIL               = 'e-mail',
            MSG_FIRST_NAME          = 'prénom',
            MSG_FIRED               = 'départ',
            MSG_HIRED               = 'embauche',
            MSG_INITIALS            = 'initiales',
            MSG_LAST_NAME           = 'nom de famille',
            MSG_LIMIT               = 'limite de validité',
            MSG_LOGIN               = 'identifiant',
            MSG_MOBILE              = 'portable',
            MSG_PASSWORD            = 'mot de passe',
            MSG_PHONE               = 'téléphone',
            MSG_SHORT               = 'raccourci',
            MSG_STATUS              = 'statut',
            TXT_READ                = 'lecture',
            TXT_WRITE               = 'écriture';

    private     $hasAnyRight        = false;

    protected   $isEditable         = false,
                $user;

    /**
     * lists apps from `Right::INNER_RIGHTS`'s property
     *
     * @param   \Std    &$apps
     *
     * @return  array<\DOMElement>
     */
    private
    function apps ( \Std &$apps ) : array
    {
        $out = [];

        foreach ( $apps as $app => $rights )
        {
            $this
                ->__add(
                    [
                        $this->iconedRow(self::EMO_MASKS, $app),
                        $listed = $this->__div()
                    ],
                    $apped = $this->__div()
                )
                ->__add( $this->rightsBlocks($rights), $listed )
                ->__class($apped, sprintf(
                    '%1$s %2$s',
                    self::CLASS_RECURSIVE_MARGED,
                    self::CLASS_HIDABLE_PARENT
                ))
                ->__class($listed, sprintf(
                    '%1$s %2$s',
                    self::CLASS_APPLICATIONED,
                    self::CLASS_HIDABLE_CHILD
                ));

            $out[] = $apped;
        }

        return $out;
    }

    /**
     * counts rights and stocks judgment
     *
     * @param   array   &$pile
     *
     * @return  UserView
     */
    private
    function countRights ( array &$pile ) : UserView
    {
        $this->hasAnyRight = ! empty($pile);

        return $this;
    }

    /**
     * fetches current `$this->user` for display
     *
     * @param   ?bool   $single and takes all width
     * @param   ?bool   $named  only
     *
     * @return  DOMElement
     */
    protected
    function fetch ( bool $single = false, bool $named = false ) : \DOMElement
    {
        $ui = User::Instance();

        if ( ! $single ) $ui->is( $this->user->{ $ui->f_id } );

        $rights = $this->listRights();

        $this
            ->__add(
                array_filter(
                    [
                        $this->isEditable ? $gear = $this->__div() : null,
                        $head = $this->iconedRow(
                            (
                                ( $avatar = Avatar::Instance() )->is( $this->user->{ $ui->f_avatarId } )
                            )->{ $avatar->f_content },
                            sprintf(
                                '%1$s %2$s',
                                $this->user->{ $ui->f_firstName },
                                $this->user->{ $ui->f_lastName }
                            ),

                        ),
                        ...($named
                            ? []
                            : [
                                $this->iconedRow(self::EMO_MAIL, $this->user->{ $ui->f_email }, self::MSG_EMAIL),
                                $this->iconedRow(self::EMO_LOCK, $this->user->{ $ui->f_login }, self::MSG_LOGIN),
                                $this->iconedRow(self::EMO_PEN, $this->user->{ $ui->f_initials }, self::MSG_INITIALS),
                                $this->iconedRow(self::EMO_CALENDAR, $this->strToPretty($this->user->{ $ui->f_creation }), 'création'),
                                $this->user->{ $ui->f_lastConnection }
                                    ? $this->iconedRow(self::EMO_CALENDAR, $this->strToPretty($this->user->{ $ui->f_lastConnection }), 'dernière connexion')
                                    : null,
                                $this->user->{ $ui->f_lastPwdChange }
                                    ? $this->iconedRow(self::EMO_CALENDAR, $this->strToPretty($this->user->{ $ui->f_lastPwdChange }), 'changement mot de passe')
                                    : null,
                                ( $hired = $this->user->{ $ui->f_hired } )
                                    ? $this->iconedRow(self::EMO_COUNTER_BELL, $this->strToPretty($hired), self::MSG_HIRED)
                                    : null,
                                ( $fired = $this->user->{ $ui->f_fired } )
                                    ? $this->iconedRow(self::EMO_ALIEN, $this->strToPretty($fired), self::MSG_FIRED)
                                    : null,
                                $this->user->{ $ui->f_phone }
                                    ? $this->iconedRow(self::EMO_PHONE, $this->user->{ $ui->f_phone }, self::MSG_PHONE)
                                    : null,
                                $this->user->{ $ui->f_mobile }
                                    ? $this->iconedRow(self::EMO_MOBILE, $this->user->{ $ui->f_mobile }, self::MSG_MOBILE)
                                    : null,
                                isset($this->user->{ $ui->f_status })
                                    ? $this->iconedRow(
                                        ( $active = (bool) $this->user->{ $ui->f_status } )
                                            ? self::EMO_THUMB
                                            : self::EMO_SLEEP,
                                        $active
                                            ? self::MSG_ACTIVE
                                            : self::MSG_DISABLED
                                    )
                                    : null,
                                $this->user->{ $ui->f_short }
                                    ? $this->iconedRow(self::EMO_TARGET, $this->user->{ $ui->f_short }, self::MSG_SHORT)
                                    : null,
                                $this->hasAnyRight
                                    ? $this->iconedRow(
                                        self::EMO_DOOR,
                                        $this->listRights(),
                                        'droits')
                                    : null
                            ])
                    ]
                ),
                $block = $this->__div()
            )
            ->__class($block, sprintf(
                '%1$s %2$s%3$s',
                self::CLASS_USER_ITEM,
                self::CLASS_EDITING,
                $single ? sprintf(' %1$s', self::CLASS_SINGLE) : null
            ))
            ->__class($head, self::CLASS_USER_HEAD);

            if ( $this->isEditable )
                $this
                    ->__add($geared = $this->__a(self::EMO_GEAR), $gear)
                    ->__att(
                        $geared,
                        'href',
                        self::createLink( sprintf('index/%3$s?%1$s=%2$s', $ui->f_id, $this->user->{ $ui->f_id }, self::USER_EDITION) )
                    )
                    ->__class($gear, self::CLASS_EDITABLE)
                    ->__class($geared, self::CLASS_FREE_LINK);

        return $block;
    }

    /**
     * view a list of groups
     *
     * @param   array   &$groups
     *
     * @return  array<\DOMElement>
     */
    private
    function groups ( array &$groups ) : array
    {
        $out = [];

        foreach ( $groups as &$std )
        {
            $this
                ->__add(
                    $this->iconedRow(self::EMO_GROUP, $std->name, $std->description),
                    $group = $this->__div()
                )
                ->__class($group, self::CLASS_RECURSIVE_MARGED);

            if ( property_exists($std, Right::INNER_RIGHTS) )
                $this->__add(
                    $this->apps($std->{ Right::INNER_RIGHTS } ),
                    $group
                );

            if ( property_exists($std, Right::PARENTS) )
                $this->__add(
                    $this->{ __FUNCTION__ }( $std->{ Right::PARENTS } ),
                    $group
                );

            $out[] = $group;
        }

        return $out;
    }

    /**
     * creates an icon/text element
     *
     * @param   string              $icon
     * @param   string|\DOMElement  $text
     * @param   ?string             $tip
     *
     * @return  \DOMElement
     */
    private
    function iconedRow ( string $icon, $content, string $tip = null ) : \DOMElement
    {
        $this
            ->__add(
                [
                    $iconed = $this->__div($icon),
                    is_string($content) ? $texted = $this->__div($content) : $content
                ],
                $block = $this->__div()
            )
            ->__class($block, self::CLASS_ICONED_ROW)
            ->__class($iconed, sprintf(
                '%1$s%2$s%3$s',
                self::CLASS_USER_ICON,
                ( $nullTip = is_null($tip) )
                    ? ''
                    : sprintf(' %1$s', self::CLASS_JS_TIPPED),
                $icon != self::EMO_DOOR
                    ? ''
                    : ' bouncing'
            ));

        if ( ! $nullTip ) $this->__att($iconed, self::DATASET_TIP, $tip);

        return $block;
    }

    /**
     * displays user's rights
     *
     * @return  DOMElement
     */
    private
    function listRights () : \DOMElement
    {
        $rights = ( $right = Right::Instance() )
            ->readForCurrent()
            ->getRights($right::TYPE_GROUPED);

        $this
            ->countRights($rights)
            ->__add(
                $this->groups($rights),
                $item = $this->__div()
            );

        return $item;
    }

    /**
     * gets a message for user to know he misses data
     *
     * @return  \DOMElement
     */
    protected
    function noData () : \DOMElement
    {
        $this
            ->__add(
                [
                    $this->__div(self::EMO_ROLLING),
                    $this->__div('Aucune donnée !')
                ],
                $nop = $this->__div()
            )
            ->__class($nop, __FUNCTION__);

        return $nop;
    }

    /**
     * creates rights block
     *
     * @param   \Std  &$rights
     *
     * @return  array<\DOMElement>
     */
    private
    function rightsBlocks ( \Std &$rights ) : array
    {
        $out = [];

        foreach ( $rights as $name => $type )
        {
            $this
                ->__add(
                    [
                        $infos = $this->__div( ($isRead = ($type == Right::READING[Belonging::READ])) ? self::EMO_BOOK_BLUE : self::EMO_BOOK_ORANGE),
                        $this->__div($name)
                    ], $righted = $this->__div()
                )
                ->__att($infos, self::DATASET_TIP, $isRead ? self::TXT_READ : self::TXT_WRITE)
                ->__class($infos, self::CLASS_JS_TIPPED);

            $out[] = $righted;
        }

        return $out;
    }

    /**
     * sets edit mode
     *
     * @param   bool    $editable
     *
     * @return  static
     */
    protected
    function setEditMode ( bool $editable ) // : static
    {
        $this->isEditable = $editable;

        return $this;
    }

    /**
     * sets current user
     *
     * @param   stdClass|Std    $user
     *
     * @return  UserView
     */
    protected
    function setUser ( $user ) : UserView
    {
        $this->user = User::isStd($user) ? $user : false;

        return $this;
    }

    /**
     * greets user welcome
     */
    public
    function welcome () : void
    {
        $this
            ->setTitle('Sso demo')
            ->__add(
                [
                    $title = $this->__h(1, 'Bienvenue !'),
                    $cont = $this->__div()
                ]
            )
            ->__add(
                $this
                    ->setUser( User::Instance()->getUserFromSession() )
                    ->fetch(true),
                $cont
            )
            ->__class($title, self::CLASS_WHITED)
            ->renderView();
    }
}
