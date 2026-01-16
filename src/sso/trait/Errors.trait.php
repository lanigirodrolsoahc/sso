<?php

namespace SSO;

trait Errors
{
    protected  $errors     = [];

    /**
     * resets errrors' list
     *
     * @return  static
     */
    protected
    function errorsReset () // : static
    {
        $this->errors = [];

        return $this;
    }

    /**
     * registers failure
     *
     * @param   string  $msg
     *
     * @return  static
     */
    protected
    function error ( string $msg ) // : static
    {
        $this->errors[] = $msg;

        return $this;
    }

    /**
     * determines if any errror has occurred
     *
     * @return  bool
     */
    protected
    function errored () : bool
    {
        return is_array($this->errors) && ! empty($this->errors);
    }

    /**
     * gets a list of failures
     *
     * @return  array<string>
     */
    protected
    function failures () : array
    {
        return ! is_array($this->errors) || empty($this->errors) ? ['triggered failures with none registered!'] : $this->errors;
    }
}
