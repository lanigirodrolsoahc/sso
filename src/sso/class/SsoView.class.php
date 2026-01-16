<?php

namespace SSO;

class SsoView extends \HtmlGenerator
{
    private
    const       MSG_PWD_CHANGE          = 'Changer mon mot de passe',
                REG_SORTER              = '/^([a-z]+):([a-z]+)$/i';

    protected
    const       CLASS_FLEX_RADIO        = 'flexRadio',
                CLASS_FORM_INFO         = 'formInformation',
                CLASS_FORM_WS           = 'formInnerService',
                CLASS_FREE_LINK         = 'undecoratedLink',
                CLASS_PAGER             = 'pageMobility',
                CLASS_PWD_EYED          = 'pwdEyed',
                CLASS_RIGHT_SUBMIT      = 'formRightSubmit',
                CLASS_WHITED            = 'whited',
                EMO_ARROWS_LEFT         = '⏪',
                EMO_ARROWS_RIGHT        = '⏩',
                EMO_BLOOD_TYPE          = '🆎',
                EMO_BIG_QUESTION        = '⁉️',
                EMO_EIGHT_BALL          = '🎱',
                EMO_EYES                = '👀',
                EMO_MAGNIFYING_GLASS    = '🔎',
                EMO_PAWN                = '♟️',
                EMO_RED_FLAG            = '🚩',
                EMO_RED_RING            = '⭕',
                EMO_TOP                 = '🔝',
                ENC_TYPE_DATA           = 'multipart/form-data',
                HEX_BALD                = '&#x1F9B2;',
                HEX_BULB                = '&#x1F4A1;',
                HEX_KEY                 = '&#x1F511;',
                HEX_HOME                = '&#x1F3E0;',
                HEX_OUT                 = '&#x1F534;',
                LENGTH_MAX              = 'max-length',
                LENGTH_MIN              = 'min-length',
                METHOD_ADMIN_SCRIPT     = 'addAdminScript',
                PARAM_AUTOCOMPOFF       = ['autocomplete', 'off'],
                PARAM_NEW_PWD           = ['nouveau mot de passe', User::PWD_NEW],
                PARAM_REQUIRED          = ['required', true],
                PARAM_VALUE_SAVE        = ['value', 'Enregistrer'],
                SORT_HOW                = 'how',
                SORT_WHAT               = 'what';

    public
    const       DATA_REDIRECTED     = 'data-redirected',
                EMO_FINGER_LEFT     = '👈',
                EMO_RECYCLE         = '♻️',
                PAGE_ID             = 'pageid',
                ROAD_NEW_PWD        = 'index/change',
                ROAD_USERS          = 'index/manageUsers',
                SEARCH_STR          = 'search',
                SORT_ASC            = 'ASC',
                SORT_ASC_MSG        = 'ascendant',
                SORT_DESC           = 'DESC',
                SORT_DESC_MSG       = 'descendant',
                SORT_MANNER         = 'sortmanner';

    protected   $dired,
                $isDev,
                $pathCorporateStyle,
                $pathStyle,
                $root,
                $urlRoot;

    /**
     * returns the one and only instance of this class
     *
     * @return  static
     */
    public static
    function & Instance () // : static
    {
        static $instance = null;

        if ( is_null($instance) )
        {
            $class      = get_called_class();
            $instance   = new $class();
        }

        return $instance;
    }

    private
    function __construct ()
    {
        parent::__construct();

        $this->__setRoot('sso');

        $this->dired                = dirname(__FILE__);
        $this->toRoot               = sprintf('%1$s://%2$s/sso/%3$s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'], self::COMPONENT_PATH);
        $this->isDev                = $_SERVER['HTTP_HOST'] == 'localhost';
        $this->pathStyle            = sprintf('%1$s/../sso.css', $this->dired);
        $this->pathCorporateStyle   = sprintf('%1$s/../%2$s', $this->dired, self::CSS_COMPANY);
        $this->jsMain               = sprintf('%1$sjs/main.js', SsoServer::__getRoot());
        $this->urlRoot              = SsoServer::__getRoot();
    }

    /**
     * sets `$this->root`
     *
     * @return  SsoView
     */
    private
    function __setRoot ( string $marker ) : SsoView
    {
        $this->root = implode(
            $slash = '/',
            array_filter(
                array_slice(
                    $root = explode($slash, $_SERVER['REQUEST_URI']),
                    0,
                    array_search($marker, $root) + 1
                )
            )
        );

        return $this;
    }

    /**
     * creates a form to change avatar
     */
    public
    function changeAvatar () : void
    {
        $current = ( $user = User::Instance() )->is( Session::retrieve(User::SESS_MARK) );

        $this
            ->setTitle('Changer mon avatar')
            ->__add( $main = $this->__div() )
            ->__add( $form = $this->__form(), $main )
            ->__add(
                [
                    $title      = $this->__h(1, 'SSO : changement d\'avatar'),
                    $buttons    = $this->__div(),
                    $validate   = $this->__div()
                ],
                $form
            )
            ->__add( $change = $this->__input('submit'), $validate )
            ->createAvatarsOptions( $buttons, $current->{ $user->f_avatarId } )
            ->setFormAttributes( $form, \Std::__new()->action('setNewAvatar') )
            ->__class($main, 'avatarForm')
            ->__class($title, self::CLASS_WHITED)
            ->__class($buttons, self::CLASS_FLEX_RADIO)
            ->__att($change, 'value', 'Changer')
            ->__class($change, self::CLASS_WHITED)
            ->__class($validate, self::CLASS_RIGHT_SUBMIT)
            ->renderView();
    }

    /**
     * creates a form to change password
     *
     * @param   ?string     $token  renewed, while user calls from outer realm
     */
    public
    function changePassword ( string $token = null ) : void
    {
        $this
            ->setTitle(self::MSG_PWD_CHANGE)
            ->__add( $main = $this->__div() )
            ->__add( $form = $this->__form(), $main )
            ->__add(
                [
                    $dtitle     = $this->__div(),
                    $this->describePassword(),
                    ( $tokened = ! is_null($token) )
                        ? $tokenified = $this->__input('hidden')
                        : $this->createPwdinput('ancien mot de passe', User::PWD),
                    $this->createPwdinput(...self::PARAM_NEW_PWD),
                    $buttons    = $this->__div()
                ],
                $form
            )
            ->__add( $dconnect  = $this->__div(), $buttons )
            ->__add( $title     = $this->__h(1, 'SSO : changement de mot de passe'), $dtitle )
            ->__add( $connect   = $this->__input('submit'), $dconnect )
            ->__att($connect, ...self::PARAM_VALUE_SAVE)
            ->__att($form, self::DATA_REDIRECTED, $this->createLink(''))
            ->__class($main, 'logger')
            ->__class($buttons, 'inlineButtons')
            ->__class($title, self::CLASS_WHITED)
            ->__class($connect, self::CLASS_WHITED)
            ->setFormAttributes( $form, \Std::__new()->action( $tokened ? 'setOuterPassword' : 'setNewPassword' ) );

        if ( $tokened )
            $this
                ->__att($tokenified, 'name', Token::PARAM_NAME)
                ->__att($tokenified, 'value', $token);

        $this->renderView();
    }

    /**
     * creates a list of available avatars
     *
     * @param   \DOMElement     &$parent
     * @param   int             $currentId
     *
     * @return  SsoView
     */
    private
    function createAvatarsOptions ( \DOMElement &$parent, int $currentId ) : SsoView
    {
        foreach (
            ( $av4t4r = Avatar::Instance() )
                ->setRequestParameters()
                ->listAll()
            as $id => $avatar
        )
        {
            $this
                ->__add( $choice = $this->__div(), $parent )
                ->__add(
                    [
                        $opt = $this->__input('radio'),
                        $lab = $this->__label( $avatar->{ $av4t4r->f_content } )
                    ],
                    $choice
                )
                ->__att($opt, 'name', Avatar::CHOICE)
                ->__att($opt, 'id', $ided = sprintf('avatarId%1$s', $id))
                ->__att($lab, 'for', $ided)
                ->__att($opt, 'value', $id)
                ->__att($opt, 'required', true);

            if ( $currentId == $id ) $this->__att($opt, 'checked', true);
        }

        return $this;
    }

    /**
     * creates a helper button
     *
     * @param   string              $emo
     * @param   string              $described
     * @param   ?array<string>      $datasets
     * @param   ?array<string>      $classes
     *
     * @return  \DOMElement
     */
    protected
    function createButton ( string $emo, string $described, array $datasets = [], array $classes = [] ) : \DOMElement
    {
        $this
            ->__add( $button = $this->__div($emo), $cont = $this->__div() )
            ->__att($button, AdminView::DATASET_TIP, $described)
            ->__class($button, trim(sprintf('%1$s pageHelper %2$s', AdminView::CLASS_JS_TIPPED, implode(' ', $classes))));

        foreach ( $datasets as $key => $data ) $this->__att($button, sprintf('data-%1$s', $key), is_string($data) ? $data : htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8'));

        return $cont;
    }

    /**
     * creates a holder for additionnal buttons
     *
     * @return  \DOMElement
     */
    protected
    function createButtonsHolder () : \DOMElement
    {
        $this
            ->__add( $handler = $this->__div() )
            ->__class($handler, 'mobilityManager');

        return $handler;
    }

    /**
     * creates a path for hyperlink
     *
     * @param   string  $href
     *
     * @return  string
     */
    public static
    function createLink ( string $href ) : string
    {
        return sprintf('%1$s%2$s', SsoServer::__getRoot(), $href);
    }

    /**
     * creates a menu
     *
     * @return  SsoView
     */
    protected
    function createMenu () : SsoView
    {
        do
        {
            if ( ! ( ( $user = User::Instance() )->getUserFromSession() ) ) break;

            $list = [
                $this->createMenuRow(self::HEX_HOME, 'Accueil', ''),
                $this->createMenuRow(self::HEX_KEY, ucfirst(UserView::MSG_PASSWORD), self::ROAD_NEW_PWD),
                $this->createMenuRow(self::HEX_BALD, 'Avatar', 'index/avatar')
            ];

            if ( $user->isAdmin(Belonging::READ) )
            {
                $list[] = $this->createMenuRow(UserView::EMO_MASKS, 'Applications', 'index/manageApps');
                $list[] = $this->createMenuRow(UserView::EMO_GROUP, 'Groupes', 'index/manageGroups');
                $list[] = $this->createMenuRow(UserView::EMO_USERS, 'Utilisateurs', self::ROAD_USERS);
            }

            $list[] = $this->createMenuRow(self::HEX_OUT, 'Déconnexion', 'index/logout');

            $this
                ->__add( $menu = $this->__div() )
                ->__add( [...$list], $menu )
                ->__class($menu, 'menu');
        }
        while ( 0 );

        return $this;
    }

    /**
     * creates a menu item
     *
     * @param   string  $symbol
     * @param   string  $text
     * @param   string  $href
     *
     * @return  DOMElement
     */
    protected
    function createMenuRow ( string $symbol, string $text, string $href ) : \DOMElement
    {
        $this
            ->__add(
                [
                    $picto  = $this->__div(),
                    $txt    = $this->__div($text)
                ],
                $row = $this->__div()
            )
            ->__add(
                $link = $this->__a($symbol),
                $picto
            )
            ->__att($link, 'href', self::createLink($href))
            ->__class($row, 'menuItem')
            ->__class($picto, 'menuPicto')
            ->__class($txt, 'menuTxt')
            ->__class($link, self::CLASS_FREE_LINK);

        return $row;
    }

    /**
     * creates a pager for user's use
     *
     * @param   object  &$virtualObject
     *
     * @return  array<\DOMElement>
     */
    protected
    function createPager ( &$virtualObject ) : array
    {
        /**
         * creates a tip for previous or next page
         *
         * @param   string  $label
         * @param   int     $target
         * @param   int     $total
         *
         * @return  string
         */
        $pageTip = fn ( string $label, int $target, int $total ) : string => sprintf('%1$s (%2$s sur %3$s)', $label, $target, $total);

        if ( ( $virtualObject->pageCount ?? 0 ) > 1 )
            $out = array_filter(
                [
                    ! ( $counted = $virtualObject->pagePrevious )
                        ? null
                        : $this->createButton(
                            self::EMO_ARROWS_LEFT,
                            $pageTip('Page précédente', $counted, $virtualObject->pageCount),
                            (array) \Std::__new()->{ self::PAGE_ID }($counted),
                            [self::CLASS_PAGER]
                        ),
                    ! ( $counted = $virtualObject->pageNext )
                        ? null
                        : $this->createButton(
                            self::EMO_ARROWS_RIGHT,
                            $pageTip('Page suivante', $counted, $virtualObject->pageCount),
                            (array) \Std::__new()->{ self::PAGE_ID }($counted),
                            [self::CLASS_PAGER]
                        )
                ]
            );

        return $out ?? [];
    }

    /**
     * creates a view/data searcher
     *
     * @param   ?bool   isCancellation
     *
     * @return  \DOMElement
     */
    protected
    function createSearcher ( bool $isCancellation = false ) : \DOMElement
    {
        return $this->createButton(
            ...(
                ! $isCancellation
                    ? [self::EMO_MAGNIFYING_GLASS, 'Chercher', [], ['searcher']]
                    : [self::EMO_RED_RING, 'Annuler', [], ['searchCancel']]
            )
        );
    }

    /**
     * creates a view/data sorter
     *
     * @param   \Std    $options
     *
     * @return  \DOMElement
     */
    protected
    function createSorter ( \Std $options ) : \DOMElement
    {
        return $this->createButton(self::EMO_BLOOD_TYPE, 'Trier', (array) $options, ['dataSorter']);
    }

    /**
     * creates a password inputing div
     *
     * @param   string  $placeholder
     * @param   string  $name
     *
     * @return  DOMElement
     */
    protected
    function createPwdinput ( string $placeholder, string $name ) : \DOMElement
    {
        $this
            ->__add(
                [
                    $pwd = $this->__input('password'),
                    $eye = $this->__div(self::EMO_EYES)
                ],
                $dpwd   = $this->__div()
            )
            ->__att($pwd, 'placeholder', $placeholder)
            ->__att($pwd, ...self::PARAM_AUTOCOMPOFF)
            ->__att($pwd, 'name', $name)
            ->__att($pwd, ...self::PARAM_REQUIRED)
            ->__att($pwd, self::LENGTH_MIN, Password::PWD_MIN_LENGTH)
            ->__att($pwd, self::LENGTH_MAX, Password::PWD_MAX_LENGTH)
            ->__class($dpwd, self::CLASS_PWD_EYED);

        return $dpwd;
    }

    /**
     * creates a password complexity description
     *
     * @return  \DOMElement
     */
    private
    function describePassword () : \DOMElement
    {
        $this
            ->__add(
                [
                    $this->__div(self::HEX_BULB),
                    $this->__div(
                        htmlentities( sprintf(
                            Password::PWD_DESCRIBE,
                            Password::PWD_MIN_LENGTH,
                            Password::PWD_MAX_LENGTH
                        ) )
                    )
                ],
                $describe = $this->__div()
            )
            ->__class($describe, self::CLASS_FORM_INFO);

        return $describe;
    }

    /**
     * escapes double quotes
     *
     * @param   string  $str
     *
     * @return  string
     */
    protected
    function escape2Quotes ( string $str ) : string
    {
        return addcslashes($str, '"');
    }

    /**
     * gets current page
     *
     * @return  int
     */
    protected
    function getPage () : int
    {
        return (int) ( $_GET[ self::PAGE_ID ] ?? 1 );
    }

    /**
     * gets search string iff any
     *
     * @return  string|false
     */
    protected
    function getSearch ()
    {
        return ( $get = $_GET[ self::SEARCH_STR ] ?? false ) ? urldecode($get) : $get;
    }

    /**
     * gets current URL sorter, if any
     *
     * @return  \Std|false
     */
    protected
    function getSort ()
    {
        do
        {
            if ( ! ( $get = $_GET[ self::SORT_MANNER ] ?? false ) ) break;
            if ( ! preg_match(self::REG_SORTER, base64_decode( urldecode($get) ), $matches) ) break;

            $out = \Std::__new()
                ->{ self::SORT_WHAT }( $matches[1] )
                ->{ self::SORT_HOW }( $matches[2] );
        }
        while ( 0 );

        return $out ?? false;
    }

    /**
     * log form
     */
    public
    function login () : void
    {
        $this
            ->setTitle('Connexion')
            ->__add( $main = $this->__div() )
            ->__add( $form = $this->__form(), $main )
            ->__add(
                [
                    $dtitle     = $this->__div(),
                    $dlogin     = $this->__div(),
                    $dpwd       = $this->createPwdinput(UserView::MSG_PASSWORD, User::PWD),
                    $buttons    = $this->__div()
                ],
                $form
            )
            ->__add(
                [
                    $dconnect   = $this->__div(),
                    $dforgot    = $this->__div()
                ],
                $buttons
            )
            ->__add( $title     = $this->__h(1, 'SSO : connexion'), $dtitle )
            ->__add( $login     = $this->__input('login'), $dlogin )
            ->__add( $connect   = $this->__input('submit'), $dconnect )
            ->__add( $forgot    = $this->__input('button'), $dforgot )
            ->__att($login, 'placeholder', UserView::MSG_LOGIN)
            ->__att($login, ...self::PARAM_AUTOCOMPOFF)
            ->__att($login, 'name', User::LOGIN)
            ->__att($login, ...self::PARAM_REQUIRED)
            ->__att($login, self::LENGTH_MIN, Password::LOGIN_MIN_LENGTH)
            ->__att($login, self::LENGTH_MAX, Password::LOGIN_MAX_LENGTH)
            ->__att($connect, $value = 'value', 'Je me connecte')
            ->__att($forgot, $value, 'Mot de passe oublié')
            ->__att($dforgot, AdminView::DATASET_URLED, self::createLink('services/reset'))
            ->__att($dforgot, AdminView::DATASET_METHOD, AdminView::METHOD_POST)
            ->__att($dforgot, AdminView::DATASET_TYPED, AdminView::ENC_TYPE_DATA)
            ->__att($forgot, 'id', 'forgotten')
            ->__att($login, 'autofocus', true)
            ->__class($main, 'logger')
            ->__class($buttons, 'inlineButtons')
            ->__class($title, self::CLASS_WHITED)
            ->__class($connect, self::CLASS_WHITED)
            ->__class($forgot, self::CLASS_WHITED)
            ->setFormAttributes( $form, \Std::__new()->action('connect') )
            ->renderView();
    }

    /**
     * renders a full page
     */
    protected
    function renderView () : void
    {
        $this
            ->createMenu()
            ->createPage()
            ->__style($this->pathStyle)
            ->__script( sprintf('%1$s/Loader.class.js', $this->dirname) );

        if ( method_exists($this, $meth = self::METHOD_ADMIN_SCRIPT) ) $this->{ $meth }();

        $this->render();
    }

    /**
     * creates a form to renew password, token dependant
     */
    public
    function renewPassword () : void
    {
        $this
            ->setTitle('Renouvellement de mot de passe')
            ->__add( $main = $this->__div() )
            ->__add( $form = $this->__form(), $main )
            ->__add(
                [
                    $dtitle     = $this->__div(),
                    $this->describePassword(),
                    $this->createPwdinput(...self::PARAM_NEW_PWD),
                    $buttons    = $this->__div(),
                    $tokened    = $this->__input($hidden = 'hidden'),
                    $usered     = $this->__input($hidden)
                ],
                $form
            )
            ->__add( $dconnect  = $this->__div(), $buttons )
            ->__add( $title     = $this->__h(1, 'SSO : renouvellement de mot de passe'), $dtitle )
            ->__add( $connect   = $this->__input('submit'), $dconnect )
            ->__att($connect, ...self::PARAM_VALUE_SAVE)
            ->__class($main, 'logger')
            ->__class($buttons, 'inlineButtons')
            ->__class($title, self::CLASS_WHITED)
            ->__class($connect, self::CLASS_WHITED)
            ->setFormAttributes( $form, \Std::__new()->action('redefinePassword') )
            ->__att(
                $tokened,
                $value = 'value',
                Token::Instance()->refresh(
                    $userId = ( $user = User::Instance() )->getVirtual()->{ $user->f_id },
                    Token::TYPE_PWD_RENEWAL
                )
            )
            ->__att($tokened, $name = 'name', Token::PARAM_NAME)
            ->__att($usered, $value, $userId)
            ->__att($usered, $name, $user->f_id)
            ->renderView();
    }

    /**
     * sets form attributes
     *
     * @param   \DOMElement  &$form
     * @param   \Std         $params at least `action` property
     */
    protected
    function setFormAttributes ( \DOMElement &$form, \Std $params ) : SsoView
    {
        return $this
            ->__class($form, self::CLASS_FORM_WS)
            ->__att($form, 'method', $params->method ?? 'post')
            ->__att($form, 'action', $this->url($params->action))
            ->__att($form, 'enctype', $params->enctype ?? self::ENC_TYPE_DATA);
    }

    /**
     * creates a full URL
     *
     * @param   string      $endPoint
     * @param   ?string     $type
     *
     * @return  string
     */
    protected
    function url ( string $endPoint, string $type = 'services' ) : string
    {
        return sprintf('%1$s%3$s/%2$s', $this->urlRoot, $endPoint, $type);
    }
}
