<?php

namespace SSO;

use \Std;

trait VirtualObject
{
    private     $dbFieldsMarker     = 'f_',
                $fieldId            = 'id',
                $fx_around          = 'around',
                $fx_operator        = 'operator',
                $groupBy            = [],
                $operators,
                $p_order            = null,
                $p_orderType        = SsoView::SORT_ASC,
                $p_page             = null,
                $updPrefix          = '_upd_';

    protected   $dbFields,
                $fields,
                $map,
                $tableNameStr       = 'tableName';

    public      $totalResults       = 0,
                $type_different     = 'differ',
                $type_equal         = 'equal',
                $type_in            = 'in',
                $type_like          = 'like',
                $type_likeEnd       = 'likeEnd',
                $type_likeLoose     = 'likeLoose',
                $type_notLike       = 'notLike',
                $type_notLikeEnd    = 'notLikeEnd',
                $type_notLikeLoose  = 'notLikeLoose',
                $type_over          = 'over',
                $type_overEqual     = 'overEqual',
                $type_under         = 'under',
                $type_underEqual    = 'underEqual';

    use Dates;
    use Errors;
    use Databased {
        Databased::__construct as private DatabasedContruct;
    }

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

    /**
     * use `self::Instance` instead
     */
    private
    function __construct ()
    {
        $this->DatabasedContruct();
        $this
            ->setOperators()
            ->init()
            ->maps();
    }

    /**
     * adds a `GROUP BY` condition iff any to be found
     *
     * @param   string  &$sql
     *
     * @return  static
     */
    private
    function addGroupByCondition ( string &$sql ) // : static
    {
        if ( ! empty($this->groupBy) )
            $sql = sprintf(
                '%1$s GROUP BY %2$s',
                $sql,
                implode(
                    ', ',
                    array_map(
                        fn ( string $field ) : string => sprintf('`%1$s`', $field),
                        $this->groupBy
                    )
                )
            );

        return $this->setGroupBy();
    }

    /**
     * deletes all targets
     *
     * @return  bool
     */
    public
    function deleteAll () : bool
    {
        $success = true;

        foreach ( $this->readAll() as $item )
            $success &= $this
                ->fill( \Std::__new()->{ $this->fieldId }( $item->{ $this->fieldId } ?? 0 ) )
                ->eraze();

        return $success;
    }

    /**
     * - erazes a row from database
     * - use: fill()->eraze()
     *
     * @return  bool
     */
    public
    function eraze () : bool
    {
        do
        {
            if ( ! $this->read() ) break;

            $sql = "DELETE FROM `{$this->tableName}` WHERE `{$this->fieldId}` = {$this->getVirtual()->{ $this->fieldId }}";

            $this->database
                ->noQueryParameters()
                ->isReading(true)
                ->query($sql);

            $success = $this->database->ok;
        }
        while ( 0 );

        return $success ?? false;
    }

    /**
     * fills field parameters
     *
     * @param   Std         $fields
     * @param   ?string     $type   of SQL operator, `equal` would be default type
     * @param   ?bool       $init
     *
     * @return  static
     */
    public
    function fill ( Std $fields, string $type = null, bool $init = true ) // : static
    {
        if ( $init ) $this->init();

        if ( is_null($type) ) $type = $this->type_equal;

        if ( property_exists($this->fields, $type) )
            foreach ( $fields as $prop => $val )
                if ( property_exists($this->dbFields, $prop) )
                    $this->fields->$type->$prop(
                        $type == $this->type_in
                            ? $this->formInParams($val)
                            : $val
                    );

        return $this;
    }

    /**
     * forms parameters for an `IN`-type request
     *
     * @param   array   $parameters
     * @param   ?bool   $join
     * @param   ?bool   $prefixed
     *
     * @return  string|array    based on `$join` parameter
     */
    public
    function formInParams ( array $parameters, bool $join = false, bool $prefixed = false )
    {
        $fn = __FUNCTION__;

        /**
         * creates a key for prepared statement
         *
         * @param   int     $key
         *
         * @return  string
         */
        $keyCreator = fn ( int $key ) : string
            => sprintf(
                ':%3$s%1$s%2$s',
                $fn,
                $key,
                $prefixed
                    ? $this->updPrefix
                    : null
            );

        return $join
            ? implode(
                ', ',
                array_map(
                    $keyCreator,
                    array_keys( array_values($parameters) )
                )
            )
            : (
                function ( array $params, array $out = [] ) use ( &$keyCreator )
                {
                    foreach ( $params as $key => $value )
                        $out[ substr($keyCreator($key), 1) ] = $value;

                    return $out;
                }
            )(
                array_values($parameters)
            );
    }

    /**
     * creates a request formula (param =|like|< :param) for PDO use, based on current loaded fields
     *
     * @param   string  $imploder
     * @param   bool    skipId      determines if we should skip `id` field and prefix fields, considered duplicate key marker
     *
     * @return  string
     */
    private
    function formula ( string $imploder, bool $skipId = false ) : string
    {
        if ( empty( (array) $this->fields) ) $this->fields->{ $this->type_equal }->id(0);

        $conditions = [];

        foreach ( $this->fields as $type => $typed )
            foreach ( $typed as $prop => $val )
            {
                if ( $skipId && strcmp($prop, $this->fieldId) == 0 ) continue;

                $conditions[] = sprintf(
                    $this->operators[$type]->{ $this->fx_operator },
                    $prop,
                    $type == $this->type_in
                        ? $this->formInParams($val, true, $skipId)
                        : (
                            $skipId
                                ? $this->prefixField($prop)
                                : $prop
                        )
                );
            }

        return implode($imploder, $conditions);
    }

    /**
     * defines a default order by based on current class
     *
     * @return  string
     */
    private
    function getDefaultOrderBy () : string
    {
        return $this->defaultOrderBy ?? $this->f_name;
    }

    /**
     * get virtual object
     *
     * @return  \Std
     */
    public
    function getVirtual () : \Std
    {
        return $this->dbFields;
    }

    /**
     * initializes:
     * - fields
     *
     * @return  static
     */
    public
    function init () // : static
    {
        $this->fields = Std::__new();

        foreach ( array_keys($this->operators) as $type ) $this->fields->$type( Std::__new() );

        return $this;
    }

    /**
     * identifies a virtual object by id
     *
     * @param   int     $id
     *
     * @return  \stdClass|false
     */
    public
    function is ( int $id )
    {
        $this->setRequestParameters();

        return $this
            ->fill(
                \Std::__new()->{ $this->f_id }($id)
            )
            ->read();
    }

    /**
     * determines if VO has been previously read
     *
     * @return  bool
     */
    public
    function isRead () : bool
    {
        return ! empty( (array) $this->fields );
    }

    /**
     * lists all elements of table
     *
     * @return  array<stdClass>
     */
    public
    function listAll ()
    {
        $this->database
            ->setPage($this->p_page)
            ->setOrderBy()
            ->noQueryParameters();

        $list   = [];
        $sql    = "SELECT * FROM `{$this->tableName}` ORDER BY `{$this->p_order}` {$this->p_orderType}";

        $this
            ->addGroupByCondition($sql)
            ->database
                ->isReading()
                ->query($sql);

        if ( ! is_null($this->p_page) ) $this->paged();

        foreach ( $this->database->results as $data )
            $list[ (int) $data->{ $this->f_id } ] = $data;

        $this
            ->saveTotal()
            ->setRequestParameters();

        return $list;
    }

    /**
     * maps database fields based on `$this->dbFieldsMarker` recognition applied to public properties in using class
     *
     * @return  static
     */
    private
    function maps () // : static
    {
        $this->dbFields = Std::__new();
        $publics        = ( new \ReflectionClass($this) )->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ( $publics as $property )
            if ( strpos( $property->getName(), $this->dbFieldsMarker ) === 0 )
                $this->dbFields->{ $property->getValue($this) }(null);

        return $this;
    }

    /**
     * fills field parameters without call to `$this->init()`
     *
     * @param   Std         $fields
     * @param   ?string     $type   of SQL operator, `equal` would be default type
     *
     * @return  static
     */
    public
    function multiFill ( Std $fields, string $type = null ) // : static
    {
        return $this->fill( ...array_merge(func_get_args(), [false]) );
    }

    /**
     * prefixes fields as you can't repeat them in PDO insert / on duplicate
     *
     * @param   string  $fieldName
     *
     * @return  string
     */
    private
    function prefixField ( string $fieldName ) : string
    {
        return sprintf('%1$s%2$s', $this->updPrefix, $fieldName);
    }

    /**
     * - reads a virtual object
     * - stores result in `$this->dbFields`, if singleton
     *
     * @return  \stdClass|false
     */
    public
    function read ()
    {
        do
        {
            if ( ! $this->reads()->database->ok )                   break;
            if ( $this->database->rows !== 1 )                      break;
            if ( ! ($out = $this->database->results[0] ?? false) )  break;

            $this->setDbValues($out);
        }
        while ( 0 );

        return $out ?? false;
    }

    /**
     * reads an expected collection of objects
     *
     * @return  array<\stdClass>
     */
    public
    function readAll () : array
    {
        return $this->reads()->saveTotal()->database->ok ? $this->database->results : [];
    }

    /**
     * reads data
     *
     * @param   ?string     $operator
     *
     * @return  static
     */
    private
    function reads ( string $operator = ' AND ' ) // : static
    {
        $this
            ->setQueryParameters()
            ->setDbValues()
            ->database->setPage($this->p_page);

        $sql = "SELECT * FROM `{$this->{$this->tableNameStr}}` WHERE {$this->formula($operator)}";

        $this
            ->addGroupByCondition($sql)
            ->database
                ->isReading()
                ->query($sql);

        if ( ! is_null($this->p_page) ) $this->paged();

        return $this;
    }

    /**
     * saves a virtual object; all non equal types of fields will be ignored
     *
     * @return  \Std|false (`$this->dbFields`)
     */
    public
    function save ()
    {
        do
        {
            $this->setDbValues();

            foreach ( $this->fields as $type => $content )
                if ( $type != $this->type_equal )
                    unset( $this->fields->$type );

            $this->setQueryParameters();

            foreach ( $this->database->queryParameters as $key => $val )
                if ( $key != $this->fieldId )
                    $this->database->queryParameters[ $this->prefixField($key) ] = $val;

            $imp = fn ( string $colon = '' ) : string
                    => sprintf(
                        '%1$s%2$s%3$s%1$s',
                        $tick = empty($colon) ? '`' : '',
                        $colon,
                        implode(
                            sprintf('%2$s, %1$s%2$s', $colon, $tick),
                            array_keys( (array) $this->fields->{ $this->type_equal } )
                        ));

            $sql = "INSERT INTO `{$this->{$this->tableNameStr}}` ( {$imp()} )
                    VALUES ( {$imp(':')} )
                    ON DUPLICATE KEY UPDATE {$this->formula(', ', true)}";

            $this->database
                ->isReading(true)
                ->query($sql);

            if ( ! $this->database->ok )
            {
                error_log( json_encode(
                    \Std::__new()
                        ->error($this->database->sqlError)
                        ->sql( \Debug::simpleRow($sql) )
                        ->params($this->database->queryParameters),
                    JSON_PRETTY_PRINT
                ));

                break;
            }

            $saved = true;

            $this->setDbValues( (object) (array) $this->fields->{ $this->type_equal } );

            if ( $this->database->lastId ) $this->dbFields->{ $this->fieldId }( $this->database->lastId );
        }
        while ( 0 );

        return empty($saved) ? false : $this->dbFields;
    }

    /**
     * remembers total results
     *
     * @return  static
     */
    protected
    function saveTotal () // : static
    {
        $this->totalResults = $this->database->total;

        return $this;
    }

    /**
     * reads a simple search (all OR operator)
     *
     * @return  array<\stdClass>
     */
    public
    function search () : array
    {
        return $this->reads(' OR ')->saveTotal()->database->ok ? $this->database->results : [];
    }

    /**
     * sets database values
     * - will nullify them if none given
     *
     * @param   ?\stdClass  $data
     *
     * @return  static
     */
    private
    function setDbValues ( \stdClass $data = null ) // :static
    {
        if ( is_null($data) )   foreach ( $this->dbFields as &$value )  $value = null;
        else                    foreach ( $data as $prop => $value )    $this->dbFields->$prop( $value );

        return $this;
    }

    /**
     * sets another `GROUP BY` condition or nullifies them
     *
     * @param   ?string     $field
     *
     * @return  static
     */
    public
    function setGroupBy ( string $field = null ) // : static
    {
        if ( is_null($field) )
            $this->groupBy = [];
        else
            $this->groupBy[] = $field;

        return $this;
    }

    /**
     * sets operators
     *
     * @return  static
     */
    private
    function setOperators () // : static
    {
        /**
         * creates an Std for parameters
         *
         * @param   string      $txt
         * @param   ?string     $aroundParam
         * @param   ?bool       $parenthesis    on prepared variable
         *
         * @return  \Std
         */
        $stddoer = fn ( string $txt, string $aroundParam = null, bool $parenthesis = false ) : \Std
            => \Std::__new()
                ->{ $this->fx_operator }(
                    sprintf(
                        '`%%1$s` %1$s %2$s%4$s%%2$s%3$s',
                        $txt,
                        ...(
                            $parenthesis
                                ? ['(', ')', null]
                                : array_merge(
                                    array_fill(0, 2, null),
                                    [':']
                                )
                        )
                    )
                )
                ->{ $this->fx_around }( is_null($aroundParam) ? false : $aroundParam );

        $this->operators = [
            $this->type_different       => $stddoer('<>'),
            $this->type_equal           => $stddoer('='),
            $this->type_in              => $stddoer('IN', null, true),
            $this->type_like            => $stddoer( $like = 'LIKE', $start = '%%%1$s' ),
            $this->type_likeEnd         => $stddoer($like, $end = '%1$s%%'),
            $this->type_likeLoose       => $stddoer($like, $loose = '%%%1$s%%'),
            $this->type_notLike         => $stddoer( $not = 'NOT LIKE', $start),
            $this->type_notLikeEnd      => $stddoer($not, $end),
            $this->type_notLikeLoose    => $stddoer($not, $loose),
            $this->type_over            => $stddoer('>'),
            $this->type_overEqual       => $stddoer('>='),
            $this->type_under           => $stddoer('<'),
            $this->type_underEqual      => $stddoer('<=')
        ];

        return $this;
    }

    /**
     * sets query parameters based on current fields
     *
     * @return  static
     */
    private
    function setQueryParameters () // : static
    {
        $params = [];

        foreach ( $this->fields as $type => $parms )
            foreach ( $parms as $prop => $value )
                $params[$prop] = $this->surround($type, $value);

        $this->database->queryParameters = $params;

        return $this;
    }

    /**
     * sets usefull request parameters
     *
     * @param   ?int        $page
     * @param   ?string     $orderBy
     * @param   ?string     $orderType
     *
     * @return  static
     */
    public
    function setRequestParameters ( int $page = null, string $orderBy = null, string $orderType = SsoView::SORT_ASC ) // : static
    {
        static $allowed     = [SsoView::SORT_ASC, SsoView::SORT_DESC];

        $this->p_page       = $page;
        $this->p_order      = is_null($orderBy) ? $this->getDefaultOrderBy() : $orderBy;
        $this->p_orderType  = in_array($orderType, $allowed) ? $orderType : SsoView::SORT_ASC;

        return $this;
    }

    /**
     * surrounds given value depending on operator's type
     *
     * @param   string  &$type
     * @param   mixed   $value
     *
     * @return  string
     */
    private
    function surround ( string &$type, $value )
    {
        return ( $operand = $this->operators[ $type ]->{ $this->fx_around } ?? false ) && is_string($value)
            ? sprintf($operand, $value)
            : $value;
    }
}
