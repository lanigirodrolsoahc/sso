export class Helper
{
    static get ATTR () {
        return Object.freeze({
            ACTION:     'action',
            AUTO:       'autocomplete',
            CHECK:      'checked',
            CLASS:      'class',
            DISABLED:   'disabled',
            FOR:        'for',
            ID:         'id',
            HOLD:       'placeholder',
            INDEX:      'index',
            METHOD:     'method',
            NAME:       'name',
            OFF:        'off',
            REQ:        'required',
            TIP:        'data-title',
            TYPE:       'type',
            VALUE:      'value'
        })
    }

    static get ELEMS () {
        return Object.freeze({
            AREA:       'textarea',
            DIAL:       'dialog',
            DIV:        'div',
            FIELDSET:   'fieldset',
            FORM:       'form',
            INPUT:      'input',
            LABEL:      'label',
            LEGEND:     'legend'
        })
    }

    static get EMO () {
        return Object.freeze({
            BULB:       '💡',
            CANCEl:     '⛔',
            DELETE:     '❌',
            MUNCH:      '😱',
            PIRATE:     '🏴‍☠️',
            PRETEND:    '🙄',
            RECYCLE:    '♻️',
            SUBMIT:     '💾',
            THUMB:      '👍',
            TRASH:      '🗑️'
        })
    }

    static get EVENTS () {
        return Object.freeze({
            CLICK:  'click',
            KEYUP:  'keyup',
            SUBMIT: 'submit'
        })
    }

    static get TYPES () {
        return Object.freeze({
            BUTTON: 'button',
            DATE:   'date',
            HIDE:   'hidden',
            RADIO:  'radio',
            SUB:    'submit',
            TEXT:   'text'
        })
    }

    /**
     * appends children to parent element
     *
     * @param   {Array<Element>|Element}    children
     * @param   {Element}                   parent
     *
     * @returns {Helper}
     */
    __append (children, parent)
    {
        if ( ! Array.isArray(children) ) children = [children]

        children.forEach( child =>
        {
            parent.appendChild(child)
        })

        return this
    }

    /**
     * creates a textarea element
     *
     * @returns {Element}
     */
    __area ()
    {
        return document.createElement(Helper.ELEMS.AREA)
    }

    /**
     * defines and sets an attribute
     *
     * @param   {Element}   target
     * @param   {String}    named
     * @param   {*}         valued
     *
     * @returns {Helper}
     */
    __att ( target, named, valued )
    {
        target.setAttribute(named, valued)

        return this
    }

    /**
     * adds a class to given element
     *
     * @param   {Element}   target
     * @param   {String}    className
     */
    __class ( target, className )
    {
        target.classList.add(className)

        return this
    }

    /**
     * creates a dialog element
     *
     * @returns {Element}
     */
    __dial ()
    {
        return document.createElement(Helper.ELEMS.DIAL)
    }

    /**
     * creates a div element
     *
     * @returns {Element}
     */
    __div ()
    {
        return document.createElement(Helper.ELEMS.DIV)
    }

    /**
     * creates a fieldset element
     *
     * @returns {Element}
     */
    __field ()
    {
        return document.createElement( Helper.ELEMS.FIELDSET )
    }

    /**
     * creates a form element
     *
     * @returns {Element}
     */
    __form ()
    {
        return document.createElement(Helper.ELEMS.FORM)
    }

    /**
     * sets inner HTML for given element
     *
     * @param   {Element}   element
     * @param   {String}    content
     *
     * @returns {Helper}
     */
    __inner ( element, content )
    {
        element.innerHTML = content

        return this
    }

    /**
     * creates an input element
     *
     * @returns {Element}
     */
    __input ()
    {
        return document.createElement(Helper.ELEMS.INPUT)
    }

    /**
     * creates a label element
     *
     * @returns {Element}
     */
    __label ()
    {
        return document.createElement( Helper.ELEMS.LABEL )
    }

    /**
     * cerates a legend element
     *
     * @returns {Element}
     */
    __legend ()
    {
        return document.createElement( Helper.ELEMS.LEGEND )
    }

    /**
     * creates a text node
     *
     * @param   {String}    content
     *
     * @returns {Element}
     */
    __text ( content )
    {
        return document.createTextNode(content)
    }
}
