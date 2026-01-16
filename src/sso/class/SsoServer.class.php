<?php

namespace SSO;

class SsoServer extends \CommonServer
{
    protected
    const       KEY         = 'mZgna56wyaPcPbRLFZwrqzdUBoxxnQDy5uSZlujkK6';

    public
    const       APP_KEY         = 'ssoKey',
                CODE_FORBIDDEN  = 403;

    protected   $controls   = [],
                $database,
                $errors     = [],
                $query,
                $staticAuth = true,
                $urlRoot;

    public
    function __construct ()
    {
        parent::__construct();
        Session::start();
        self::storeKey();

        $this->database = Database::Instance();
        $this->urlRoot  = self::__getRoot();
    }

    protected
    function __setDirname ()
    {
        $this->dirname = dirname(__FILE__);
    }

    /**
     * sets root path for URLs
     *
     * @return  string
     */
    public static
    function __getRoot () : string
    {
        return sprintf(
            'http://%1$s/%2$s/',
            $_SERVER['HTTP_HOST'],
            implode(
                $sep = '/',
                array_slice(
                    $filtered = array_filter(
                        explode($sep, $_SERVER['REQUEST_URI'])
                    ),
                    0,
                    array_search( Database::SSO, $filtered )
                )
            )
        );
    }

    /**
     * registers an error
     *
     * @param   string  $msg
     *
     * @return  SsoServer
     */
    protected
    function fail ( string $msg ) : SsoServer
    {
        $this->errors[] = $msg;

        return $this;
    }

    /**
     * tells if process failed
     *
     * @return  bool
     */
    protected
    function failed () : bool
    {
        return ! empty($this->errors);
    }

    /**
     * reports errors
     *
     * @return  array
     */
    protected
    function report () : array
    {
        $out = $this->errors;

        $this->errors = [];

        return $out;
    }

    /**
     * sets auth from session for inner service calls
     *
     * @param   array       &$query
     *
     * @return  SsoServer
     */
    protected
    function setAuthFromSession ( array &$query ) : SsoServer
    {
        $query[ self::AUTH ] = Session::retrieve(self::APP_KEY);

        return $this;
    }

    /**
     * sets required parameters for query
     *
     * @param   string[]    $needs
     *
     * @return  SsoServer
     */
    protected
    function setControls ( ...$needs ) : SsoServer
    {
        $this->controls = array_merge( array_filter([ $this->staticAuth ? self::AUTH : null ]), $needs );

        return $this;
    }

    /**
     * sets data from php input as query, or given one
     *
     * @param   ?array  $query
     *
     * @return  SsoServer
     */
    protected
    function setPhpInputOrQuery ( array $query = [] ) : SsoServer
    {
        try
        {
            $q = json_decode($this->getPhpInput(), true);

            if ( json_last_error() !== JSON_ERROR_NONE )
                throw new \Exception;
            if ( ! is_array($q) )
                throw new \Exception;

            if ( array_key_exists(self::AUTH, $query) )
                $q[ self::AUTH ] = $query[ self::AUTH ];
        }
        catch ( \Throwable $t )
        {
            $q = $query;
        }

        $this->setQuery($q);

        return $this;
    }

    /**
     * sets query
     *
     * @param   mixed   $query
     *
     * @return  SsoServer
     */
    protected
    function setQuery ( $query ) : SsoServer
    {
        $this->query = $query;

        return $this;
    }

    /**
     * starts
     * - init
     * - extract
     * - query requirements control
     *
     * @return  bool
     */
    protected
    function start ( bool $staticAuth = false ) : bool
    {
        do
        {
            if ( is_null($this->query) ) break;

            $this->__extract($this->query);

            foreach ( $this->controls as $need )
                if ( ! $this->__controlQuery($need) )
                    break 2;

            $out = $this->staticAuth ? $this->checkAuth() : true;
        }
        while ( 0 );

        return $out ?? false;
    }

    /**
     * stores application key in session to control inner calls
     */
    public static
    function storeKey () : void
    {
        Session::store(self::APP_KEY, self::KEY);
    }
}
