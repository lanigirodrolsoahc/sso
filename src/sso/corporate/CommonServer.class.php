<?php

abstract
class CommonServer
{
    public
    const   CODE_ACCEPTED       = 202,
            CODE_CREATED        = 201,
            CODE_BAD_REQUEST    = 400,
            CODE_OK             = 200,
            CODE_RESET_CONTENT  = 205,
            CODE_UNAUTHORIZED   = 401,
            CODE_UNIVAILABLE    = 503,
            ERROR               = 'error',
            FAILED              = 'failed',
            SUCCESS             = 'success';

    protected
    const   AUTH                = 'auth',
            DOCS                = 'documents',
            KEY                 = '';

    protected   $dirname,
                $handle,
                $response,
                $success,
                $vars;

    public      $server;

    public
    function __construct ()
    {
        $this->__setDirname();
    }

    /**
     * controls that query has the expected variables
     *
     * @param   ?string    $properties
     */
    protected
    function __controlQuery ( ...$properties ) : bool
    {
        $ok = true;

        foreach ( $properties as $prop ) $ok &= $this->__has($prop);

        return $ok;
    }

    /**
     * extracts query variables
     *
     * @param   array   $query
     */
    protected
    function __extract ( array $query ) : void
    {
        $this->vars = empty($query) ? new stdClass : json_decode(json_encode($query));
    }

    /**
     * checks a variable can be found in `$this->vars`
     *
     * @return  bool
     */
    protected
    function __has ( string $propertyName ) : bool
    {
        return property_exists($this->vars, $propertyName);
    }

    /**
     * initializes `$this->vars`
     */
    protected
    function __init () : void
    {
        $this->vars         = new stdClass;
        $this->success      = false;
        $this->handle       = curl_init();
        $this->response     = null;
    }

    /**
     * sets `$this->dirname`
     */
    abstract protected
    function __setDirname ();

    /**
     * checks if given authorization is correct
     *
     * @return  bool
     */
    protected
    function checkAuth () : bool
    {
        return isset($this->vars->{ self::AUTH }) && $this->vars->{ self::AUTH } == static::KEY;
    }

    /**
     * checks CURL result and closes handle
     *
     * @param   int     $expectedHttpCode
     *
     * @return  bool
     */
    protected
    function checkAndCloseCurl ( int $expectedHttpCode ) : bool
    {
        return
            (
                is_resource($this->handle)
                || ( is_object($this->handle) && ( $this->handle instanceof CurlHandle) )
            )
            && ( $this->response = curl_exec($this->handle) )
            && ! curl_errno($this->handle)
            && ( curl_getinfo($this->handle, CURLINFO_HTTP_CODE) == $expectedHttpCode )
            && ( is_null(curl_close($this->handle)) );
    }

    /**
     * sets an option for curl
     *
     * @return  CommonServer
     */
    protected
    function curlOption ( ...$params ) // : static
    {
        curl_setopt($this->handle, ...$params);

        return $this;
    }

    /**
     * gets `php://input` content
     *
     * @return  string
     */
    protected
    function getPhpInput () : string
    {
        try {
            $str = file_get_contents('php://input');
        }
        catch ( \Throwable $t ) {
            $str = $t->getMessage();
        }

        return $str;
    }

    /**
     * says hello to the universe
     *
     * @noAuth
     * @url GET /RevueDeContrat/services
     * @url GET /hello
     * @url GET /IntraStock/services/hello
     */
    public
    function hello () : void
    {
        $this->render('Hello, universe!');
    }

    /**
     * renders data as JSON
     *
     * @param   mixed   $data
     *
     * @return  CommonServer
     */
    protected
    function render ( $data ) : CommonServer
    {
        header('content-type:application/json');

        echo json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

        return $this;
    }

    /**
     * renders error on failure, checking for `$this->success`
     */
    protected
    function renderError () : void
    {
        true
        && ! $this->success
        && $this->render( [self::ERROR => self::FAILED] )->server->setStatus( self::CODE_BAD_REQUEST );
    }

    /**
     * to support multiple origins
     *
     * @return static
     */
    protected
    function corsHeaders () //: static
    {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
		header('Access-Control-Allow-Credential: true');
		header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers, Authorization');

        return $this;
    }
}
