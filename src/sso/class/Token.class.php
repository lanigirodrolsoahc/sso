<?php

namespace SSO;

class Token
{
    use VirtualObject;
    use Dates;

    public
    const   PARAM_NAME          = 'token',
            TYPE_ACCESS         = 'access',
            TYPE_API            = 'api',
            TYPE_PWD_RENEWAL    = 'pwdRenewal';

    private     $lastGenerated  = '';

    protected   $defaultOrderBy = Belonging::ID,
                $tableName      = 'token';

    public  $f_id           = 'id',
            $f_userId       = 'userId',
            $f_type         = 'type',
            $f_creation     = 'creation',
            $f_expiration   = 'expiration',
            $f_content      = 'content',
            $f_updated      = 'updated';

    /**
     * - generates a token not being already written in database
     * - see `$this->getGenerated()` to retrieve last generated token
     *
     * @param   int     $length
     *
     * @return  string
     */
    private
    function generate ( int $length = 100 )
    {
        return $this
            ->fill(
                \Std::__new()
                    ->{ $this->f_content }( $this->lastGenerated = bin2hex(random_bytes($length)) )
            )
            ->read()
                ? (__FUNCTION__)($length)
                : $this->lastGenerated;
    }

    /**
     * gets last generated and validated token
     *
     * @return  string
     */
    public
    function getGenerated () : string
    {
        return $this->lastGenerated;
    }

    /**
     * determines if current token remains valid
     *
     * @return  bool
     */
    public
    function isValid () : bool
    {
        return $this->isRead() && ( $this->getVirtual()->{ $this->f_expiration } > $this->now()->format( $this->fmt_timestamp ) );
    }

    /**
     * keeps current token alive
     *
     * @return  bool
     */
    public
    function keep () : bool
    {
        return
            $this->isValid()
            && $this
                ->fill(
                    \Std::__new()
                        ->{ $this->f_id }( $voId = ( $vo = $this->getVirtual() )->{ $this->f_id } )
                        ->{ $this->f_content }( $this->lastGenerated =  $vo->{ $this->f_content } )
                        ->{ $this->f_updated }( $this->now()->format( $this->fmt_timestamp ) )
                )
                ->save() !== false
            && $this->is($voId) !== false;
    }

    /**
     * refreshes a token for given user and of given type, creating one if missing
     *
     * @param   int     $userId
     * @param   string  $type
     *
     * @return  bool
     */
    public
    function refresh ( int $userId, string $type ) : bool
    {
        return $this
            ->fill(
                \Std::__new()
                    ->{ $this->f_userId }($userId)
                    ->{ $this->f_type }($type)
                    ->{ $this->f_content }( $this->generate() )
            )
            ->save() !== false
            && $this->is( $this->getVirtual()->{ $this->f_id } ) !== false;
    }
}
