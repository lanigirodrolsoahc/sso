<?php

namespace SSO;

trait Databased
{
    protected   $database;

    public  $pageCount,
            $pageNext,
            $pagePrevious;

    public
    function __construct ()
    {
        $this->database = Database::Instance();
    }

    /**
     * gets pages informations
     *
     * @return  static
     */
    public
    function paged () // : static
    {
        $this->pageCount    = $this->database->pages;
        $this->pageNext     = $this->database->next;
        $this->pagePrevious = $this->database->previous;

        return $this;
    }

    /**
     * - determines results sets' size
     * - setting is a one shot
     *
     * @param   ?int    $size
     *
     * @return  static
     */
    public
    function lot ( ?int $size = 0 ) // : static
    {
        $this->database->perPage = $size ?: Database::PER_PAGE;

        return $this;
    }
}
