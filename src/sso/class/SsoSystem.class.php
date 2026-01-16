<?php

namespace SSO;

final
class SsoSystem
{
    public
    const   COMPANY_COLOR           = 'companyColor',
            COMPANY_GOOGLE_URL      = 'companyGoogleUrl',
            COMPANY_LOCATION        = 'companyLocation',
            COMPANY_LOGO_B64_PNG    = 'companyLogoPng',
            COMPANY_NAME            = 'companyName',
            COMPANY_PHONE           = 'companyPhone',
            COMPANY_WEBSITE         = 'companyWebsite',
            ROOT_API                = 'rootApi',
            ROOT_INDEX              = 'rootIndex',
            ROOT_SERVICES           = 'rootServices',
            SMTP_NAME               = 'smtpName',
            SMTP_PORT               = 'smtpPort',
            SMTP_PWD                = 'smtpPwd',
            SMTP_USER               = 'smtpUser',
            SMTP_SERVER             = 'smtpServer',
            SQL_PWD_HOME            = 'sqlPwdHome',
            SQL_PWD_WORK            = 'sqlPwdWork',
            SQL_USER                = 'sqlUser';

    private $companyColor,
            $companyGoogleUrl,
            $companyLocation,
            $companyLogoPng,
            $companyName,
            $companyPhone,
            $companyWebsite,
            $rootApi,
            $rootIndex,
            $rootServices,
            $smtpName,
            $smtpPort,
            $smtpPwd,
            $smtpUser,
            $smtpServer,
            $sqlPwdHome,
            $sqlPwdWork,
            $sqlUser;

    /**
     *  returns the one and only instance of this class
     *
     * @return  SsoSystem
    */
    public static
    function & Instance () : SsoSystem
    {
        static $instance = null;

        if ( is_null($instance) )
        {
            $class      = get_called_class();
            $instance   = new $class();
        }

        return $instance;
    }

    /**
     * Use `SsoSystem::Instance()`
     */
    private
    function __construct () {}

    /**
     * gets a property while to be found
     *
     * @param   string  $property
     *
     * @return  mixed|false
     */
    public
    function __get ( string $property )
    {
        return property_exists($this, $property) ? $this->$property : false;
    }

    /**
     * registers all parameters
     *
     * @param   \Std    $data
     *
     * @return  SsoSystem
     */
    public
    function register ( \Std $data ) : SsoSystem
    {
        foreach ( $data as $property => $value )
            if ( property_exists($this, $property) )
                $this->$property = $value;

        return $this;
    }
}
