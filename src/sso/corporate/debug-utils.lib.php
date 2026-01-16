<?php

if ( ! defined('__DEBUGUTILS_LIB__') ) :
    define('__DEBUGUTILS_LIB__',1);

    class Debug
    {
        /**
         * allows a complete var_dump
         */
        public static
        function allLines ()
        {
            ini_set('xdebug.var_display_max_depth', -1);
            ini_set('xdebug.var_display_max_children', -1);
            ini_set('xdebug.var_display_max_data', -1);
        }

        /**
         * dumps and dies
         *
         * @param   mixed   $any
         */
        public static
        function dies ( ...$any ) : void
        {
            self::dumps($any);
            die;
        }

        /**
         * dumps data with pre tags
         *
         * @param   $anything   you want to var_dump
         */
        public static
        function dumps ( ...$anything ) : void
        {
            self::allLines();

            foreach ( $anything as $value )
            {
                echo '<pre style="text-align: left">';
                var_dump($value);
                echo '</pre>';
            }
        }

        /**
     * sprintf a message and the value to debug
     *
     * @param   string      $msg
     * @param   mixed       $debug (bools as string)
     * @param   ?string     $file
     * @param   ?int        $line
     *
     * @return  string
     */
    public static
    function msg ( string $msg, $debug, string $file = 'undefined', int $line = 0  ) : string
    {
        $main   = ( $dom = new DOMDocument )->createElement($div = 'div');
        $infos  = $dom->createElement($div);
        $file   = $dom->createElement($div, basename($file));
        $lined  = $dom->createElement($div, sprintf('line %1$s', $line));
        $desc   = $dom->createElement($div, self::sprintEscape($msg));
        $val    = $dom->createElement($div, is_string($debug) ? $debug : htmlentities(json_encode($debug, JSON_PRETTY_PRINT)) );

        $main->setAttribute('style',
            sprintf(
                'background-color: #ffc0cb; display: flex; justify-content: flex-start; align-items: flex-start; %1$s',
                $common = implode( $space = ' ', [
                    'border: 1px solid #ccc;',
                    $padMarg = implode($space, [
                        sprintf('padding: %1$s;', $emp4 = '.4em'),
                        sprintf('margin: %1$s;', $emp4)
                    ]),
                    sprintf('border-radius: %1$s;', $emp4)
                ])
            ));
        $desc->setAttribute('style',
            sprintf(
                'background-color: white; %1$s',
                $blocks = implode($space, [
                    $common,
                    'user-select: none; font-family: cursive; font-size: .8em; text-align: center;',
                ])
            ));
        $file->setAttribute('style',
            sprintf(
                'background-color: #7ea8ed; %1$s',
                $blocks
            ));
        $lined->setAttribute('style',
            sprintf(
                'background-color: %2$s; %1$s',
                $blocks,
                $greened = '#95cc14'
            ));
        $val->setAttribute('style', sprintf('%1$s overflow-x: scroll; scrollbar-width: thin; scrollbar-color: %2$s white; width: 100%%', $padMarg, $greened));
        $infos->setAttribute('style', 'display: flex; flex-direction: column; justify-content: flex-start');

        $main->appendChild($infos);
        $infos->appendChild($file);
        $infos->appendChild($desc);
        $main->appendChild($val);

        if ( $line > 0 ) $infos->appendChild($lined);

        return $dom->saveHTML($main);
    }

        /**
         * uses ini_set to allow a script having no time limit
         *
         * @param   ?int    $memory     to also set `memory_limit`, expressed like `1024M`
         *
         */
        public static
        function noTimeLimit ( int $memory = null ) : void
        {
            ini_set('max_execution_time', 0);

            true && is_int($memory) && ini_set('memory_limit', sprintf('%1$sM', strval($memory)));
        }

        /**
         * throws an error with all given content, using buffer (non text values)
         *
         * @param   $anything   you want to throw
         *
         * @throws  Error (via self::throws)
         */
        public static
        function obThrows ( ...$anything ) : void
        {
            ob_start();

            foreach ( $anything as $else ) self::dumps($else);

            self::throws( ob_get_clean() );
        }

        /**
         * replaces all spaces or line breaks in given string
         *
         * @param   string  $str
         *
         * @return  string
         */
        public static
        function simpleRow ( string $str ) : string
        {
            return preg_replace('/\s+/s', ' ', $str);
        }

        /**
         * escapes string for `sprintf`'s use
         *
         * @param   string  $str
         */
        public static
        function sprintEscape ( string $str ) : string
        {
            return str_replace('%', '%%', $str);
        }

        /**
         * throws an error with all given content dump
         *
         * @param   $anything   you want to var_dump
         *
         * @throws  Error
         */
        public static
        function throws ( ...$anything ) : void
        {
            self::allLines();

            foreach ( $anything as $else )
            {
                echo nl2br(PHP_EOL.'DEBUG:');
                var_export($else);
                echo nl2br(PHP_EOL);
            }

            throw new Error( ob_get_clean() );
        }

        /**
     * - caclulates elapsed time since last call
     * - has to be initialized by a first call
     *
     * @param   ?string     $msg
     * @param   ?bool       $html
     * @param   ?bool       $getTotal
     *
     * @return  string|null|int
     */
    public static
    function timeElapsed ( string $msg = '', bool $html = true, bool $getTotal = false )
    {
        static $last = null;
        static $total = null;

        $now = microtime(true);

        if ( ! is_null($last) )
        {
            $diff   = number_format($now - $last, 4);
            $out    = sprintf('%1$s :: %2$s%3$s', $msg, $diff, PHP_EOL);
            $total += $diff;
        }

        $last = $now;

        if ( $getTotal ) return $total;

        if ( empty($out) ) $out = '';

        return $html ? nl2br($out) : $out;
    }

        /**
         * var_exports data with pre tags
         *
         * @param   $anything   you want to var_export
         */
        public static
        function varexport ( ...$anything ) : void
        {
            self::allLines();

            foreach ( $anything as $value )
            {
                echo '<pre style="text-align: left">';
                var_export($value);
                echo '</pre>';
            }
        }
    }

endif;