<?php

namespace SSO;

class Database
{
    private
    const   TYPE_IN_MARKER      = 'formInParams';

    public
    const   PER_PAGE            = 50,
            PER_PAGE_SMALL      = 5,
            SSO                 = 'sso';

    private $dbSso              = self::SSO,
            $isReading          = false,
            $pwd,
            $tries              = 0;

    public  $allowPagination    = true,
            $current            = false,
            $database,
            $lastId             = false,
            $lastSql,
            $next               = false,
            $ok                 = false,
            $orderBy            = [],
            $pages              = 0,
            $perPage            = self::PER_PAGE,
            $previous           = false,
            $queryParameters    = [],
            $results            = [],
            $rows,
            $sqlError,
            $statement,
            $total              = 0;

    /**
     *  returns the one and only instance of this class
     *
     * @return  Database
    */
    public static
    function & Instance () : Database
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
     * Use `Database::Instance()`
     */
    private
    function __construct ()
    {
        $this->connect();
    }

    /**
     * connects to database
     *
     * @param   string  $pwd    allows params to stay put between home and work
     *
     * @throws  DatabaseConnectionException
     */
    protected
    function connect ( string $pwd = null ) : void
    {
        try
        {
            $host       = getenv('DB_HOST') ?: 'localhost';
            $port       = getenv('DB_PORT') ?: 3306;

            $database   = new \PDO(
                "mysql:host={$host};port={$port};dbname={$this->dbSso};charset=utf8mb4",
                ( $system = SsoSystem::Instance() )->{ SsoSystem::SQL_USER },
                is_null($pwd)
                    ? $system->{ SsoSystem::SQL_PWD_WORK }
                    : $pwd,
                []
            );

            $database->setAttribute( \PDO::ATTR_EMULATE_PREPARES, false );
            $database->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
            $database->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ );
            $database->setAttribute( \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false );

            $this->database = $database;
            $this->pwd      = $pwd;
        }
        catch ( \Throwable $t )
        {
            $this->database = null;

            error_log( $t->getMessage() );
        }

        $this->tries++;

        if ( is_null($this->database) && ( $this->tries % 2 ) !== 0 )
            $this->connect( SsoSystem::Instance()->{ SsoSystem::SQL_PWD_HOME } );
    }

    /**
     * did your last request find anything?
     *
     * @return  bool
     */
    public
    function found () : bool
    {
        return $this->ok && $this->rows > 0;
    }

    /**
     * sets reading mode
     *
     * @param   ?bool   $unset
     *
     * @return  Database
     */
    public
    function isReading ( bool $unset = false ) : Database
    {
        $this->isReading = ! $unset;

        return $this;
    }

    /**
     * erazes query parameters
     *
     * @return  Database
     */
    public
    function noQueryParameters () : Database
    {
        $this->queryParameters = [];

        return $this;
    }

    /**
     * gets now from Paris with love
     *
     * @return  string
     */
    public static
    function now () : string
    {
        \DateUtils::zoned();

        return \DateUtils::nowISOTime();
    }

    /**
     * paginates SQL
     *
     * @param   string  &$sql
     */
    private
    function paginate ( string &$sql ) : Database
    {
        do
        {
            $total = 'total';

            if ( ! $this->current ) break;

            $this->allowPagination  = false;

            $this
                ->isReading(true)
                ->query("SELECT COUNT(*) AS `{$total}` FROM ({$sql}) x");

            if ( ! ( $res = $this->results[0] ?? false ) ) break;

            $this->allowPagination  = true;
            $this->total            = $res->$total;
            $this->pages            = ceil($this->total / $perPage = $this->perPage);
            $this->current          = $this->current > $this->pages ? $this->pages : $this->current;
            $this->previous         = ( $previous = $this->current - 1 ) < 1 ? false : $previous;
            $this->next             = ( $next = $this->current + 1 ) > $this->pages ? false : $next;

            $offset         = ( $offset = $previous * $this->perPage ) < 0 ? 0 : $offset;
            $sql            .= " LIMIT {$offset}, {$perPage}";

            $this->perPage = self::PER_PAGE;

            $this->setPage();
        }
        while ( 0 );

        return $this;
    }

    /**
     * uses PDO to execute a prepared query
     *
     * @param   string|array    $sql
     *
     * @return  Database
     */
    public
    function query ( $sql ) : Database
    {
        // $this->ensureConnection();

        $this->statement    = null;
        $this->results      = [];
        $this->ok           = false;
        $this->sqlError     = null;
        $this->lastId       = false;

        if ( is_string($sql) )
        {
            try {
                if ( ! empty($this->orderBy) )
                {
                    $imploded       = implode(', ', $this->orderBy);
                    $sql            .= " ORDER BY {$imploded}";
                    $this->orderBy  = [];
                }

                if ( $this->allowPagination )   $this->paginate($sql);
                if ( $this->isReading )         $this->reduceInTypes();

                $this->statement        = $this->database->prepare($sql);

                $this->statement->execute($this->queryParameters ?? null);

                $this->results          = $this->statement->fetchAll();
                $this->rows             = count($this->results);
                $this->lastSql          = $sql;
                $this->ok               = true;
                $this->lastId           = ($last = $this->database->lastInsertId()) == 0 ? false : $last;
            }
            catch ( \Throwable $t ) {
                $this->ok           = false;
                $this->sqlError     = $t->getMessage();

                error_log(json_encode(
                    \Std::__new()
                        ->sql($sql)
                        ->msg($this->sqlError)
                        ->from(sprintf('%1$s::%2$s', get_called_class(), __FUNCTION__))
                ));
            }
        }
        elseif ( is_array($sql) )
            foreach ( $sql as $request )
                if ( $this->ok )
                    $this->query($request);

        return $this;
    }

    /**
     * reduces `IN` types to simple arguments
     */
    private
    function reduceInTypes () : void
    {
        foreach ( $this->queryParameters ?? [] as $field => $value )
            if ( ! is_array($value) )
                continue;
            elseif ( strpos( key($value) ?? '', self::TYPE_IN_MARKER ) === false )
                continue;
            else
            {
                $this->queryParameters = array_merge(
                    $this->queryParameters,
                    User::Instance()->formInParams($value)
                );

                unset( $this->queryParameters[$field] );
            }
    }

    /**
     * - sets orderBy for next SQL request
     * - calling `null` empties any orderBy previously set
     *
     * @param   ?string|?array      $fieldName
     * @param   ?bool               $ascending
     *
     * @return  Database
     *
     * @todo multisort
     */
    public
    function setOrderBy ( $fieldName = null, bool $ascending = true ) : Database
    {
        if ( is_null($fieldName) ) $this->orderBy = [];
        else
            $this->orderBy[] = implode(
                ', ',
                array_map(
                    fn ( string $field ) : string => sprintf(
                        '`%1$s` %2$s',
                        $field,
                        $ascending ? SsoView::SORT_ASC : SsoView::SORT_DESC
                    ),
                    $fieldName = is_array($fieldName) ? $fieldName : [$fieldName]
                )
            );

        return $this;
    }

    /**
     * sets the page to target or cancels it
     *
     * @param   ?int    $page
     *
     * @return  Database
     */
    public
    function setPage ( int $page = null ) : Database
    {
        $this->current = is_null($page) ? false : abs($page);

        return $this;
    }
}
