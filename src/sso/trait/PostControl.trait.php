<?php

namespace SSO;

class PleaseIncreaseYourMaxInputVarsLimit extends \Exception {};

trait PostControl
{
    private $counted,
            $inputLimit;

    /**
     * initializes trait's properties
     *
     * @return  static
     */
    private
    function initPostControl () // : static
    {
        if ( is_null($this->inputLimit) ) $this->inputLimit = (int) ( ini_get('max_input_vars') ?? 0 );

        $this->counted = 0;

        return $this;
    }

    /**
     * increases count of post items
     *
     * @param   int     $counted
     *
     * @return  static
     */
    private
    function pilePostCount ( int $counted ) // : static
    {
        $this->counted += $counted;

        return $this;
    }

    /**
     * determines if counted elements exceeds post limitation, throwing an exception
     *
     * @return  static
     */
    private
    function ruling () // : static
    {
        if ( $this->counted > $this->inputLimit ) throw new PleaseIncreaseYourMaxInputVarsLimit;

        return $this;
    }
}
