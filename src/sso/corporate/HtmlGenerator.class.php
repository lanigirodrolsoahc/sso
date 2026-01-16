<?php

class HtmlGenerator
{
    public
    const       ATT_TYPE        = 'type',
                EMO_CADDY       = '🛒',
                EMO_ENVELOPE    = '✉️',
                EMO_EYE         = '👁️‍🗨️',
                EMO_FAMILY      = '👨‍👨‍👦‍👦',
                EMO_FILES       = '🗃️',
                EMO_GLASSES     = '😎',
                EMO_IDEA        = '💡',
                EMO_JOKE        = '🤪',
                EMO_LIST        = '📋',
                EMO_LUSTFUL     = '😏',
                EMO_NO_WAY      = '⛔',
                EMO_OOPS        = '🙄',
                EMO_PRODUCT     = '🅿️',
                EMO_SIM         = '📶',
                EMO_SMILE       = '😁',
                EMO_SWEAR       = '🤬',
                EMO_TAG         = '🏷️',
                TYPE_CLASS      = 'class';

    protected
    const       COMPANY_LOGO    = 'sso.png',
                CSS_COMPANY     = 'company.css',
                COMPONENT_PATH  = 'corporate/',
                SIXTY_FOR_ICON  = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACt0lEQVQ4T01TTUhUURg9971RMdGRJGIGnRZD0CKpJMiNmyAw2yTpqJiWVC4KhaJFKiEEtZBKAyHIbFCp0FpYVhuhiDAIG4xaKP5AoJhUKGmo8/du57tvDB887p153/m55/uu6uj5VPJt8kd4cy0a9DhJeBwNj07C5mo5DvfgqqG0vODjwOZ+R1bGnM+3M6TqrgzPri6vB9Ok2ABYQIDNvSEhSDkkSYEVvyvSyOvNzZxTlRcGtRONG6CQiKoLpJMkVamoHAVLQKyRR/YgoSfNhqo590TrWMJYd1XFIlBcsgf7DwYM4PP7aUxGFgzIgFME6TYJ6hoeaydGB9tsi9L1O+XwF+TBIeHN5kH8XlxzrZujcKW7dI+CajjbzyMkTFBbgfkLctB2+xSUsjA3tYB7ra+ozGI3QzcPBp7u4b/n64Ugvi0wjbLQAZRVHjZKL/vHMP5uhk40NlZjxoGSrCQzOULj6bDW0eT/tKVdbZ0n4QvkIUrit8MRlIaOYGN9E+1nBuBEJVSXIMOmg4u1YZ0kgbRKLPrys9FytwKWpTDxcQajQxO42hkCf+JB+wimI/OuW5MBMZdqHpFAeusOS2llIU5UF8OhwsNbb0z6N8J1yPZmYez1F7zo/kACySHJI9BBU3UvCdx+QydwrasC/sAuxNmZga5R6LjGsVARAnt9WPn1Bx31fVSXeaEDyaC5qlcnYjwCGXcXeNHSVUW7FjTdmMa72bMjlODZ7zc9xdLUT0NgHDRX9bgZsLjk+D4cLT+UgmxNjNCk7gFJx55FMP78q5uBEFwO9eg4HciEGVXTJvciyTibS8RiW+4IV1G2kuCebZQ5aGvom11d2QhunzCZRGmTENkslsBcoPsKkZDm5mV9V92tI0VLi8tD639jQUtayQkTVQNIXS5DJN+ouuUix5s5n1/or/0HkCqR6CwO40QAAAAASUVORK5CYII=';

    protected   $dirname,
                $jsMain,
                $mainContent,
                $title,
                $tmpContent,
                $tmpType,
                $toRoot;

    public      $doc;

    public
    function __construct ()
    {
        $this->doc = ( new DOMImplementation )->createDocument(
            null,
            '',
            ( new DOMImplementation )->createDocumentType(
                'html',
                '',
                ''
            )
        );

        $this->doc->formatOutput    = true;
        $this->dirname              = dirname(__FILE__);
        $this->mainContent          = $this->doc->createElement('div');
        $this->toRoot               = sprintf('%1$s%2$s', $this->locateRoot(), self::COMPONENT_PATH);

        $this->mainContent->setAttribute('class', 'mainContent');
    }

    /**
     * creates a link
     *
     * @param   ?string     $content
     *
     * @return  DOMElement
     */
    public
    function __a ( string $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'a';

        return $this->__new();
    }

    /**
     * adds an element
     *
     * @param   DOMElement|array            $elements
     * @param   ?DOMElement|DOMDocument     $to
     *
     * @return  static
     */
    public
    function __add ( $elements, $to = null ) : HtmlGenerator
    {
        $to = is_null($to) ? $this->mainContent : $to;

        if ( ! is_array($elements) ) $elements = [$elements];

        foreach ( $elements as $element ) $to->appendChild($element);

        return $this;
    }

    /**
     * sets an attribute to given element
     *
     * @param   DOMElement  $element
     * @param   string      $attribute
     * @param   string      $value
     *
     * @return  static
     */
    public
    function __att ( DOMElement $element, string $attribute, string $value ) : HtmlGenerator
    {
        $element->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * creates a b element
     *
     * @param   string  $content
     *
     * @return  DOMElement
     */
    public
    function __b ( string $content ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'b';

        return $this->__new();
    }

    /**
     * creates a button element
     *
     * @return  DOMElement
     */
    public
    function __button ( string $content ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'button';

        return $this->__new();
    }

    /**
     * creates a body
     *
     * @return  DOMElement
     */
    public
    function __body () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'body';

        return $this->__new();
    }

    /**
     * creates a break
     *
     * @return  DOMElement
     */
    public
    function __br () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'br';

        return $this->__new();
    }

    /**
     * creates a canvas
     *
     * @return  DOMElement
     */
    public
    function __canvas () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'canvas';

        return $this->__new();
    }

    /**
     * sets a class to given element
     *
     * @param   DOMElement  $element
     * @param   string      $value
     *
     * @return  static
     */
    public
    function __class ( DOMElement $element, $value ) : HtmlGenerator
    {
        return $this->__att( $element, self::TYPE_CLASS, $value);
    }

    /**
     * creates a div
     *
     * @param   ?string|DOMElement  $content
     *
     * @return  DOMElement
     */
    public
    function __div ( $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'div';

        return $this->__new();
    }

    /**
     * creates a fieldset
     *
     * @param   string  $legend
     *
     * @return  DOMElement
     */
    public
    function __fieldset ( $legend = null ) : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'fieldset';

        $fieldset = $this->__new();

        if ( ! is_null($legend) ) $fieldset->appendChild( $this->__legend($legend) );

        return $fieldset;
    }

    /**
     * creates a form
     *
     * @return  DOMElement
     */
    public
    function __form () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'form';

        return $this->__new();
    }

    /**
     * creates an iframe
     *
     * @return  DOMElement
     */
    public
    function __frame () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'iframe';

        return $this->__new();
    }

    /**
     * creates a title
     *
     * @param   int     $level
     * @param   ?string  $content
     *
     * @return  DOMElement
     */
    public
    function __h ( int $level, string $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = sprintf('h%1$s', abs($level));

        return $this->__new();
    }

    /**
     * creates a HEAD element
     *
     * @return  DOMElement
     */
    public
    function __head () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'head';

        return $this->__new();
    }

    /**
     * creates an HTML tag, charset UTF-8
     *
     * @param   ?bool       $utf8
     *
     * @return  DOMElement
     */
    public
    function __html ( bool $utf8 = true ) : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'html';
        $new                = $this->__new();

        if ( $utf8 ) $this->__att($new, 'charset', 'utf-8');

        return $new;
    }

    /**
     * creates an image
     *
     * @return  DOMElement
     */
    public
    function __img () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'img';

        return $this->__new();
    }

    /**
     * creates an input
     *
     * @param   string  $type
     *
     * @return  DOMElement
     */
    public
    function __input ( string $type ) : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'input';

        $this->__att( $new = $this->__new(), self::ATT_TYPE, $type );

        return $new;
    }

    /**
     * creates a label
     *
     * @param   ?string|DOMElement  $content
     *
     * @return  DOMElement
     */
    public
    function __label ( $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'label';

        return $this->__new();
    }

    /**
     * creates a legend
     *
     * @param   string  $text
     *
     * @return  DOMElement
     */
    private
    function __legend ( string $text ) : DOMElement
    {
        $this->tmpContent   = $text;
        $this->tmpType      = 'legend';

        return $this->__new();
    }

    /**
     * creates a list element (`ul`)
     *
     * @param   ?array<DOMElement>  $elements
     *
     * @return  DOMElement
     */
    public
    function __list ( array $elements = [] ) : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'ul';

        $this->__add( $elements, $list = $this->__new() );

        return $list;
    }

    /**
     * creates a list part (`li`)
     *
     * @param   string|DOMElement  $content
     *
     * @return  DOMElement
     */
    public
    function __listPart ( $content ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'li';

        return $this->__new();
    }

    /**
     * creates a META tag
     *
     * @return  DOMElement
     */
    public
    function __meta () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'meta';

        return $this->__new();
    }

    /**
     * - creates a new element, based on :
     * - `$this->tmpContent`
     * - `$this->tmpType`
     *
     * @return  DOMElement
     */
    private
    function __new () : DOMElement
    {
        if ( is_object($this->tmpContent) && get_class($this->tmpContent) == 'DOMElement' )
        {
            $div = $this->doc->createElement($this->tmpType);
            $div->appendChild($this->tmpContent);
        }
        // else $div = $this->doc->createElement($this->tmpType, $this->tmpContent);
        else $div = $this->doc->createElement($this->tmpType, is_null($this->tmpContent) ? '' : $this->tmpContent); # thanks PHP8

        return $div;
    }

    /**
     * creates a paragraph
     *
     * @param   ?string|DOMElement  $content
     *
     * @return  DOMElement
     */
    public
    function __p ( $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'p';

        return $this->__new();
    }

    /**
     * adds a script from file
     *
     * @param   string          $path
     * @param   ?DOMElement     $attachTo   or `$this`
     * @param   ?bool           $isPathContent
     *
     * @return  static
     */
    public
    function __script ( string $path, DOMElement $attachTo = null, $isPathContent = false ) : HtmlGenerator
    {
        $js         = $this->doc->createTextNode( $isPathContent ? $path : file_get_contents($path) );
        $script     = $this->doc->createElement('script');
        $script->appendChild($js);

        return $this->__add($script, $attachTo);
    }

    /**
     * creates a select element
     *
     * @param   array<Std>  $stds
     *
     * @return  DOMElement
     */
    public
    function __select ( array $stds ) : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'select';
        $select             = $this->__new();
        $this->tmpType      = 'option';

        foreach ( $stds as $text => $attributes )
        {
            $this->tmpContent   = $text;
            $option             = $this->__new();

            foreach ( $attributes as $attribute => $value )
                $this->__att($option, $attribute, $value);

            $this->__add($option, $select);
        }

        return $select;
    }

    /**
     * creates a span
     *
     * @param   ?string|DOMElement  $content
     *
     * @return  DOMElement
     */
    public
    function __span ( $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'span';

        return $this->__new();
    }

    /**
     * adds a style from file
     *
     * @param   string          $path
     * @param   ?DOMElement     $attachTo   or this
     *
     * @return  static
     */
    public
    function __style ( string $path, DOMElement $attachTo = null ) : HtmlGenerator
    {
        $style      = $this->doc->createTextNode(file_get_contents($path));
        $css        = $this->doc->createElement('style');
        $css->appendChild($style);

        return $this->__add($css, $attachTo);
    }

    /**
     * creates a table
     *
     * @return  DOMElement
     */
    public
    function __table () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'table';

        return $this->__new();
    }

    /**
     * creates a table cell
     *
     * @param   ?string     $content
     *
     * @return  DOMElement
     */
    public
    function __tableCell ( string $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'td';

        return $this->__new();
    }

    /**
     * creates a table head row
     *
     * @return  DOMElement
     */
    public
    function __tableHead () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'thead';

        return $this->__new();
    }

    /**
     * creates a table row
     *
     * @return  DOMElement
     */
    public
    function __tableRow () : DOMElement
    {
        $this->tmpContent   = null;
        $this->tmpType      = 'tr';

        return $this->__new();
    }

    /**
     * creates a label
     *
     * @param   ?string|DOMElement  $content
     *
     * @return  DOMElement
     */
    public
    function __textarea ( $content = null ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'textarea';

        return $this->__new();
    }

    /**
     * creates a HEAD>Title element
     *
     * @param   string  $content
     *
     * @return  DOMElement
     */
    public
    function __title ( string $content ) : DOMElement
    {
        $this->tmpContent   = $content;
        $this->tmpType      = 'title';

        return  $this->__new();
    }

    /**
     * sets page content
     *
     * @param   ?DOMElement     $content
     */
    public
    function addContent ( DOMElement $content = null ) : void
    {
        true && ! is_null($content) && $this->mainContent->appendChild($content);
    }

    /**
     * adds JavaScript module if `$this->jsMain` is to be found
     *
     * @param   DOMElement  &$parent
     *
     * @return  static
     */
    protected
    function addModuleJS ( DOMElement &$parent ) // : static
    {
        if ( ! is_null($this->jsMain) )
        {
            $js = $this->doc->createElement('script');

            $js->setAttribute('type', 'module');
            $js->setAttribute('src', $this->jsMain);

            $parent->appendChild($js);
        }

        return $this;
    }

    /**
     * appends HTML content to parent element
     *
     * @param   DOMElement  &$parent
     * @param   string      $html
     *
     * @return  static
     */
    public
    function appendHTML ( DOMElement &$parent, string $html ) : HtmlGenerator
    {
        $tmpDoc = new self;
        $tmpDoc->doc->loadHTML($html);

        foreach ( $tmpDoc->doc->getElementsByTagName('body')->item(0)->childNodes as $node )
        {
            $node = $parent->ownerDocument->importNode($node, true);

            $parent->appendChild($node);
        }

        return $this;
    }

    /**
     * manages errors' hiding
     *
     * @param   ?bool       $on     set to false to retrieve normal behaviour
     *
     * @return  static
     */
    public
    function errors ( $on = true ) : HtmlGenerator
    {
        if ( $on )  libxml_clear_errors();
        else        libxml_use_internal_errors(true);

        return $this;
    }

    /**
     * creates company page
     *
     * @return  static
     */
    public
    function createPage () : HtmlGenerator
    {
        $html   = $this->doc->createElement('html');
        $head   = $this->doc->createElement('head');
        $meta   = $this->doc->createElement('meta');
        $title  = $this->doc->createElement('title', $this->title);
        $body   = $this->doc->createElement('body');
        $css    = $this->doc->createElement('link');

        $meta->setAttribute('charset', 'utf-8');
        $css->setAttribute('href', sprintf('%2$s%1$s', self::CSS_COMPANY, $this->toRoot));
        $css->setAttribute('type', 'text/css');
        $css->setAttribute('rel', 'stylesheet');

        $html->appendChild($head);
        $head->appendChild($meta);
        $head->appendChild($title);
        $head->appendChild( $this->getFavicon() );
        $html->appendChild($body);
        $body->appendChild( $this->getLogo() );
        $head->appendChild($css);

        $this->addModuleJS($head);

        true && ! is_null($this->mainContent) && $body->appendChild($this->mainContent);

        $this->doc->appendChild($html);

        return $this;
    }

    /**
     * get base64 favicon for `<head>`'s use
     *
     * @return  DOMElement
     */
    protected
    function getFavicon () : DOMElement
    {
        $link = $this->doc->createElement('link');

        $link->setAttribute('rel', 'icon');
        $link->setAttribute('type', 'image/png');
        $link->setAttribute('sizes', '16x16');
        $link->setAttribute('href', sprintf('data:image/png;base64, %1$s', static::SIXTY_FOR_ICON));

        return $link;
    }

    /**
     * gets `$this->doc`
     *
     * @return  DOMDocument
     */
    public
    function getDomDocument () : DOMDocument
    {
        return $this->doc;
    }

    /**
     * get simple file name without extension
     *
     * @param   string  $path   to file
     *
     * @return  string
     */
    public static
    function getFileName ( string $path ) : string
    {
        return pathinfo(basename($path), PATHINFO_FILENAME);
    }

    /**
     * get company logo
     *
     * @return  DOMElement
     */
    private
    function getLogo () : DOMElement
    {
        $div = $this->doc->createElement('div');
        $img = $this->doc->createElement('img');

        $div->appendChild($img);

        $div->setAttribute('class', 'logoContainer');
        $img->setAttribute('src', sprintf('%2$s%1$s', self::COMPANY_LOGO, $this->toRoot));
        $img->setAttribute('alt', 'logo company');

        return $div;
    }

    /**
     * locates root for include pathes
     *
     * @return  string|null
     */
    private
    function locateRoot ()
    {
        do
        {
            $uri = $_SERVER['REQUEST_URI'] ?? false;

            if ( ! $uri ) break;

            $separators = substr_count($uri, '/');
            $rewind     = [];

            for ( $i = 0; $i < $separators - 2; $i++ ) array_push($rewind, '..');

            $out = sprintf('%1$s/', implode('/', $rewind));
        }
        while ( 0 );

        return $out ?? null;
    }

    /**
     * renders HTML view
     *
     * @param   ?DOMElement|DOMDocument     $element
     *
     * @return  static
     */
    public
    function render ( $element = null ) : HtmlGenerator
    {
        echo html_entity_decode( $this->doc->saveHTML($element) );

        return $this;
    }

    /**
     * sets page title
     *
     * @param   string  $title
     *
     * @return  static
     */
    public
    function setTitle ( string $title )  : HtmlGenerator
    {
        $this->title = $title;

        return $this;
    }
}
