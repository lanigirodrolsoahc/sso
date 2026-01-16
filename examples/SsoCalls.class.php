<?php

include_once ( $ssoCallsDir = dirname(__FILE__) ).'/../../system/system.lib.php';
include_once $ssoCallsDir.'/../../utils/Std.class.php';
include_once $ssoCallsDir.'/../../utils/debug-utils.lib.php';
include_once $ssoCallsDir.'/../../utils/date-utils.lib.php';
include_once $ssoCallsDir.'/../../utils/session-utils.lib.php';
include_once $ssoCallsDir.'/../../CommonServer.class.php';
include_once $ssoCallsDir.'/../view/HtmlGenerator.class.php';

class SsoCallsDefinitionException       extends \Exception {}
class SsoCallsJsonConsistencyException  extends \Exception {}

abstract
class SsoCalls extends CommonServer
{
    private
    const   H_JSON          = [ 'Content-Type: application/json' ],
            K_KEY           = 'key',
            K_NAME          = 'name',
            K_RIGHTS        = 'rights',
            K_TOKEN         = 'token',
            KEYS            = [
                CorporateConfig::corporateApp1            => [
                    self::K_NAME    => 'corporateApp1',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::INDICATEURISO   => [
                    self::K_NAME    => 'Indicateurs',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::INTRANET        => [
                    self::K_NAME    => 'corporateApp3',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::PRODUCTION      => [
                    self::K_NAME    => 'IntraProd',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::INTRASTOCK      => [
                    self::K_NAME    => 'IntraStock',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::REVUECONTRAT    => [
                    self::K_NAME    => self::SESS_CONTRACT,
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::corporateApp2          => [
                    self::K_NAME    => 'corporateApp2',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::INTERFACEMOTEUR => [
                    self::K_NAME    => 'InterfaceMoteur',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::LEAVES  => [
                    self::K_NAME    => 'leaves',
                    self::K_KEY     => 'redacted'
                ],
                CorporateConfig::INTRAPARC  => [
                    self::K_NAME    => 'IntraParc',
                    self::K_KEY     => 'redacted'
                ]
            ],
            LOCAL           = 'localhost',
            LOCALS          = [ self::LOCAL, '127.0.0.1' ],
            M_PUT           = 'PUT',
            MILLISECONDS    = 1000,
            MSG_FORGOT      = 'Mot de passe oublié ?',
            PIPE            = '|',
            PROD            = '192.168.1.21',
            SSO_APPS        = [
                                CorporateConfig::corporateApp1,
                                CorporateConfig::corporateApp2,
                                CorporateConfig::REVUECONTRAT,
                                CorporateConfig::INTERFACEMOTEUR,
                                CorporateConfig::INTRAPARC
                            ],
            TOKEN_LIFE      = 'SsoControlTokenLifeTime.class.js',
            V_READ          = 'read',
            V_WRITE         = 'write';

    protected
    const   APP         = false,
            CSS         = 'style',
            G_ACT       = 'act',
            G_FORGOT    = 'renewing',
            HREF        = 'href',
            P_LOGIN     = 'login',
            P_PWD       = 'pass',
            TARGET      = 'target',
            V_OUT       = 'dec';

    public
    const   APP_SOFTWARE    = 'logiciel',
            APP_RIGHT_R     = 'droitL',
            APP_RIGHT_W     = 'droitE',
            APP_USER        = 'user',
            APP_USER_ID     = 'idUser',
            APP_USERS_LIST  = 'users',
            APP_TOKEN       = self::K_TOKEN,
            APP_EXPIRES_AT  = 'expiresAt',
            APP_EXPIRES_IN  = 'expiresIn',
            SESS_CONTRACT   = 'revueDeContrat';

    protected   $application,
                $name,
                $payload,
                $view;

    private $key,
            $ssoRoot,
            $target;

    /**
     *  returns the one and only instance of this class
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

        $this->view     = new HtmlGenerator;
        $this->target   = sprintf(
            '%1$s/%2$s/',
            $this->ssoRoot = sprintf(
                'http://%1$s/sso',
                in_array( $_SERVER['SERVER_NAME'] ?? false, self::LOCALS )
                    ? self::LOCAL
                    : self::PROD
            ),
            'api'
        );
    }

    /**
     * defines
     * - application's key
     * - application's name for auth use
     *
     * @return  SsoCalls
     *
     * @throws  SsoCallsDefinitionException
     */
    private
    function __define () : SsoCalls
    {
        if ( ! array_key_exists($this->application, self::KEYS) ) throw new SsoCallsDefinitionException;

        $this->key  = self::KEYS[ $this->application ][ self::K_KEY ];
        $this->name = self::KEYS[ $this->application ][ self::K_NAME ];

        return $this;
    }

    protected
    function __init (): void
    {
        parent::__init();

        $this->payload = false;
    }

    protected
    function __setDirname () : void
    {
        $this->dirname = dirname(__FILE__);
    }

    /**
     * builds a common user while log is processed
     *
     * @param   string  $software
     *
     * @return  Std
     */
    protected
    function buildCommonSession ( string $software ) : Std
    {
        $data = $this->decodeJson();

        return Std::__new()
            ->{ self::APP_SOFTWARE }($software)
            ->{ self::APP_RIGHT_R }( ( $piped = $this->pipeRights($data) )->{ self::APP_RIGHT_R } )
            ->{ self::APP_RIGHT_W }( $piped->{ self::APP_RIGHT_W } )
            ->{ self::APP_USER_ID }( ( $user = $data->user )->id )
            ->{ self::APP_USER }( (array) $this->buildUser($user) )
            ->{ self::APP_USERS_LIST }( ( $data = $this->listUsers($data->token->content) ? : $data )->list ?? [] )
            ->{ self::APP_TOKEN }( $data->token->content )
            ->{ self::APP_EXPIRES_IN }( self::calcTokenLifetime($data) )
            ->{ self::APP_EXPIRES_AT }($data->token->expires);
    }

    /**
     * builds a response depending on application expectations
     *
     * @return  Std
     */
    abstract protected
    function buildResponseForUserLog () : Std;

    /**
     * builds session with given parameters
     *
     * @return  bool
     */
    public
    function buildSession () : bool
    {
        SessionUtils::start();

        if ( $this->payload )
            array_map(
                fn ( $value, $key ) => $_SESSION[$key] = $value,
                $dta = (array) $this->payload,
                array_keys($dta)
            );

        return !! $this->payload;
    }

    /**
     * builds user from SSO v2 result
     *
     * @param   stdClass    $user
     *
     * @return  Std
     */
    protected
    function buildUser ( stdClass $user ) : Std
    {
        return Std::__new()
            ->userId($user->id)
            ->userLastName($user->lastName)
            ->userFirstName($user->firstName)
            ->userEmail($user->email)
            ->userPhone($user->phone)
            ->userPortable($user->mobile)
            ->userInitial($user->initials)
            ->userPoste($user->short)
            ->userActive( (bool) $user->status )
            ->avatar($user->avatar);
    }

    /**
     * calculates token's lifetime
     *
     * @param   stdClass    &$data
     *
     * @return  int
     */
    public static
    function calcTokenLifetime ( stdClass &$data ) : int
    {
        DateUtils::zoned();

        return ( strtotime($data->token->expires) - strtotime( DateUtils::nowISOTime() ) ) * self::MILLISECONDS;
    }

    /**
     * creates a link with button effect
     *
     * @param   string  $described
     * @param   string  $to
     * @param   ?bool   $blank
     *
     * @return  DOMElement
     */
    protected
    function createLink ( string $decribed, string $to, bool $blank = false ) : DOMElement
    {
        $this->view
            ->__add( $butt = $this->view->__div() )
            ->__add( $link = $this->view->__a($decribed), $butt )
            ->__att($link, self::HREF, $to)
            ->__att($butt, self::CSS, 'margin: .7em .3em')
            ->__att($link, self::CSS, 'margin-left: 2.8em; text-decoration: none; color: #943571; font-weight: bold; border: 1px solid #943571; text-shadow: 1px 1px 1px white; border-radius: .3em; padding: .4em; background-color: rgba(148, 53, 113, 0.2)');

        if ( $blank )
            $this->view->__att($link, self::TARGET, '_blank');

        return $butt;
    }

    /**
     * tries to decode answered JSON
     *
     * @return  stdClass
     *
     * @throws  SsoCallsJsonConsistencyException
     */
    protected
    function decodeJson () : stdClass
    {
        $data = json_decode($this->response);

        if ( json_last_error() !== JSON_ERROR_NONE )
            throw new SsoCallsJsonConsistencyException;

        return $data;
    }

    /**
     * discriminates users on status
     *
     * @param   array<Std>  &$list
     *
     * @return  array<Std>
     */
    public static
    function filterActiveUsers ( array &$list ) : array
    {
        return array_filter(
            $list,
            fn ( Std $user ) : bool => $user->userActive
        );
    }

    /**
     * gets a string representation of `DOMElement`
     *
     * @param   DOMElement  $element
     *
     * @return  string
     */
    protected
    function getHtml ( DOMElement $element ) : string
    {
        return html_entity_decode( $this->view->doc->saveHTML($element) );
    }

    /**
     * get a token's lifetime listener
     *
     * @return  string
     */
    public
    function getScriptForTokenLifetime () : string
    {
        if ( $this->getSessionParam(self::APP_EXPIRES_IN) )
            $this->view
                ->__script(
                    sprintf(
                            '%1$s/%2$s',
                            $this->dirname,
                            self::TOKEN_LIFE
                        ),
                    $script = $this->view->__div()
                )
                ->__add(
                    $trigger = $this->view->doc->createElement('script'),
                    $script
                )
                ->__add(
                    $this->view->doc->createTextNode('new SsoControlTokenLifeTime()'),
                    $trigger
                );

        return isset($script) ? $this->getHtml($script) : '';
    }

    /**
     * gets a stored parameter iff to be found
     *
     * @param   string  $index
     *
     * @return  mixed|false
     */
    public static
    function getSessionParam ( string $index )
    {
        return $_SESSION[ $index ] ?? false;
    }

    /**
     * gets sessioned token iff to be found
     *
     * @return  string  can be empty one
     */
    public static
    function getToken () : string
    {
        return $_SESSION[ self::APP_TOKEN ] ?? '';
    }

    /**
     * gets user from session
     *
     * @return  Std|false
     */
    protected
    function getUser ()
    {
        return empty( $user = self::getSessionParam(self::APP_USER) )
            ? false
            : Std::__new()
                ->__setAll($user);
    }

    /**
     * finds any user in current sessioned list
     *
     * @param   int     $userId
     * @param   ?bool   $inactives  included?
     *
     * @return  Std|false
     */
    public static
    function getUserFromSessionList ( int $userId, bool $inactives = false )
    {
        do
        {
            if ( empty( $users = self::getSessionParam(self::APP_USERS_LIST) ) ) break;
            if ( empty( $res = array_filter($users, fn ( Std $user ) => $user->userId == $userId)) ) break;

            $user = $res[ key($res) ];

            if ( ! $inactives && ! $user->userActive )
                $user = false;
        }
        while ( 0 );

        return $user ?? false;
    }

    /**
     * determines if given (or current) application has already been migrated to Sso
     *
     * @param   ?string     $application
     *
     * @return  bool
     */
    public static
    function isSso ( string $application = null ) : bool
    {
        return in_array(is_null($application) ? CorporateConfig::whereAmI() : $application, self::SSO_APPS);
    }

    /**
     * - keeps a connexion alive
     * - sets user's rights again
     *
     * @return  bool
     */
    public
    function keep () : bool
    {
        $this->__init();

        $this
            ->curlOption( CURLOPT_URL, $this->url('keepAlive') )
            ->curlOption(CURLOPT_HTTPHEADER, self::H_JSON)
            ->curlOption(CURLOPT_RETURNTRANSFER, true)
            ->curlOption(CURLOPT_POSTFIELDS, json_encode( Std::__new()->token( self::getToken() ) ));

        return
            $this->checkAndCloseCurl(self::CODE_OK)
            && $this->updateUserRightsAndToken();
    }

    /**
     * creates a link to Sso's homepage
     *
     * @param   ?bool   $isRenewal
     *
     * @return  string
     */
    public
    function linkToSsoHome ( bool $isRenewal = true ) : string
    {
        return $this->getHtml(
            $this->createLink(
                sprintf('%1$s %2$s', HtmlGenerator::EMO_OOPS, self::MSG_FORGOT),
                sprintf('%1$s%2$s', $this->ssoRoot, $isRenewal ? sprintf('?%1$s', http_build_query([self::G_FORGOT => true])) : ''),
                true
            )
        );
    }

    /**
     * lists SSO users
     *
     * @param   string  $token
     * @param   ?string $appName
     *
     * @return  stdClass|false
     */
    public
    function listUsers ( string $token, string $appName = null )
    {
        $this->__init();

        $this
            ->curlOption( CURLOPT_URL, sprintf(
                '%1$s?%2$s',
                $this->url(
                    implode(
                        '/',
                        array_filter(
                            [ __FUNCTION__, $appName ],
                            fn ( $item ) : bool => $item !== null
                    ) )
                ),
                http_build_query(
                    (array) Std::__new()->{ self::K_TOKEN }($token)
                )
            ) )
            ->curlOption(CURLOPT_HTTPHEADER, self::H_JSON)
            ->curlOption(CURLOPT_RETURNTRANSFER, true);

        if ( $this->checkAndCloseCurl(self::CODE_OK) )
        {
            $data = $this->decodeJson();

            $data->list = array_map(
                fn ( stdClass $user ) => $this->buildUser($user),
                $data->list
            );
        }

        return $data ?? false;
    }

    /**
     * logs user in SSO
     *
     * @param   string  $login
     * @param   string  $password
     *
     * @return  Std|false
     */
    public
    function logUser ( string $login, string $password )
    {
        $this->__init();

        $this
            ->curlOption( CURLOPT_URL, $this->url('auth') )
            ->curlOption(CURLOPT_CUSTOMREQUEST, self::M_PUT)
            ->curlOption(CURLOPT_HTTPHEADER, self::H_JSON)
            ->curlOption(CURLOPT_RETURNTRANSFER, true)
            ->curlOption(CURLOPT_POSTFIELDS, json_encode(
                Std::__new()
                    ->applicationName($this->name)
                    ->applicationKey($this->key)
                    ->userLogin($login)
                    ->userPassword($password)
            ));

        return $this->checkAndCloseCurl(self::CODE_OK)
            ? $this->buildResponseForUserLog()
            : false;
    }

    /**
     * logs user out, destroying session iff found
     *
     * @param   ?bool   $redirect
     */
    public
    function logOut ( bool $redirect = true ) : void
    {
        if ( session_status() === PHP_SESSION_ACTIVE ) $_SESSION = [];

        if ( $redirect )
            header( sprintf(
                'Location: http://%1$s/%2$s',
                $_SERVER['HTTP_HOST'] === ( $defined = ( $cfg = CorporateConfig::instance() )->getIp( $this->application ) )
                    ? $defined
                    : self::LOCAL,
                $cfg->getPath( $this->application )
            ) );
    }

    /**
     * gets pipe separated rights for current application
     *
     * @param   stdClass     &$data
     *
     * @return  Std
     */
    protected
    function pipeRights ( stdClass &$data ) : Std
    {
        $out = Std::__new()
            ->{ self::APP_RIGHT_R }( [] )
            ->{ self::APP_RIGHT_W }( [] );

        foreach ( $data->user->{ self::K_RIGHTS }->{ self::KEYS[ $this->application ][ self::K_NAME ] } ?? [] as $name => $value )
        {
            $out->{ self::APP_RIGHT_R }[] = $name;

            if ( $value == self::V_WRITE )
                $out->{ self::APP_RIGHT_W }[] = $name;
        }

        foreach ( $out as $type => &$list )
            $list = empty($list)
                ? ''
                : sprintf('%1$s%2$s%1$s', self::PIPE, implode(self::PIPE, $list));

        return $out;
    }

    /**
     * sets current application
     *
     * @param   string  $name
     *
     * @return  static
     */
    public
    function setApplication ( string $name ) // : static
    {
        $this->application = $name;

        return $this->__define();
    }

    /**
     * starts Sso by
     * - connecting user
     * - keeping connection alive
     * - loging out
     *
     * @return  static
     *
     * @see     `static::APP`
     *
     * @todo    overwrite if needed
     */
    public static
    function start () // : static
    {
        $sso = self::Instance()->setApplication( static::APP );

        if (
            ( $log = $_POST[self::P_LOGIN] ?? false )
            && ( $pwd = $_POST[self::P_PWD] ?? false )
            && $sso->logUser($log, $pwd)
        )
            $sso->buildSession();
        elseif ( ( $_GET[self::G_ACT] ?? false ) == self::V_OUT )
            $sso->logOut();
        elseif ( ! $sso->keep() )
            $sso->logOut(false);

        return $sso;
    }

    /**
     * defines a successful start
     *
     * @return  bool
     */
    public
    function started () : bool
    {
        return ! empty( self::getToken() );
    }

    /**
     * updates user's rights in session
     *
     * @return  bool
     *
     * @todo    overload iff specific
     */
    protected
    function updateUserRightsAndToken () : bool
    {
        $data = $this->decodeJson();

        $this->payload = Std::__new()
            ->{ self::APP_TOKEN }( $data->token->content )
            ->{ self::APP_EXPIRES_IN }( self::calcTokenLifetime($data) )
            ->{ self::APP_RIGHT_R }( ( $piped = $this->pipeRights($data) )->{ self::APP_RIGHT_R } )
            ->{ self::APP_RIGHT_W }( $piped->{ self::APP_RIGHT_W } )
            ->{ self::APP_USER }( (array) $this->buildUser($data->user) )
            ->{ self::APP_EXPIRES_AT }($data->token->expires);

        return $this->buildSession();
    }

    /**
     * defines an url for targeted service
     *
     * @param   string  $service
     *
     * @return  string
     */
    private
    function url ( string $service ) : string
    {
        return sprintf('%1$s%2$s', $this->target, $service);
    }

    /**
     * gets a welcoming block with disconnection option
     *
     * @param   ?string     $logoutHref
     *
     * @return  string
     *
     * @todo    overload iff specific
     */
    public
    function welcome ( string $logoutHref = '?act=dec' ) : string
    {
        SessionUtils::start();

        if ( $user = $this->getUser() )
            $this->view
                ->__add(
                    [
                        $text   = $this->view->__div(),
                        $this->createLink('Déconnexion', $logoutHref)
                    ],
                    $dom = $this->view->__div()
                )
                ->__add(
                    [
                        $emo    = $this->view->__div( ( $user = (object) $user )->avatar ),
                        $names  = $this->view->__div( sprintf('%1$s %2$s', $user->userFirstName, $user->userLastName) )
                    ],
                    $text
                )
                ->__att($emo, self::CSS, 'font-size: 1.4em' )
                ->__att($names, self::CSS, 'font-style: oblique; align-self: end; margin-left: .7em' )
                ->__att($text, self::CSS, 'margin: .3em; display: flex; flex-wrap: nowrap; justify-content: flex-start; align-items: center')
                ->__att($dom, self::CSS, 'font-family: "Bradley Hand", cursive; color: #943571');

        return ( $dom ?? false ) ? $this->getHtml($dom) : '';
    }
}
