import { Helper } from './Helper.class.js'
import { Listen } from './Listen.class.js'
import { Rights } from './Rights.class.js'

export class Commons extends Helper
{
    static get D4T4S3TS () {
        return Object.freeze({
            DESC:   'desc',
            ID:     'id',
            METHOD: 'method',
            NAMED:  'named',
            TYPED:  'typed',
            URLED:  'urled'
        })
    }

    static get INNER_FORM () { return 'formInnerService' }

    constructor ()
    {
        super()
    }

    /**
     * creates a button to close current modal
     *
     * @param   {String}    [modalId = Rights.IDS.MODAL]
     *
     * @returns {Element}
     */
    cancelButton ( modalId = Rights.IDS.MODAL )
    {
        let butt = this.__input()

        this
            .__att(butt, Rights.ATTR.TYPE, Rights.TYPES.BUTTON)
            .__att(butt, Rights.ATTR.VALUE, Rights.EMO.CANCEl)

        butt.addEventListener( Rights.EVENTS.CLICK, () => Rights.delete(modalId) )

        return butt
    }

    /**
     * creates a creator item
     *
     * @param   {Element}           modal
     * @param   {String}            title
     * @param   {Array<Element>}    elements
     * @param   {String}            holderClass
     * @param   {String}            formClass
     * @param   {String}            url
     * @param   {String}            method
     * @param   {String}            [modalId = Rights.IDS.MODAL]
     */
    creatorModal ( modal, description, elements, holderClass, formClass, url, method, modalId = Rights.IDS.MODAL )
    {
        let title       = this.__div()
        let form        = this.__form()
        let holder      = this.__div()
        let buttoned    = this.__div()
        let sub         = this.__input()

        this
            .__append(description, title)
            .__append([title, form], modal)
            .__append(holder, form)
            .__append( elements.concat(buttoned), holder )
            .__append([sub, this.cancelButton(modalId)], buttoned)
            .__att(form, Commons.ATTR.ACTION, url)
            .__att(form, Commons.D4T4S3TS.METHOD, method)
            .__att(sub, Commons.ATTR.TYPE, Commons.TYPES.SUB)
            .__att(sub, Commons.ATTR.VALUE, Commons.EMO.SUBMIT)
            .__att(holder, Commons.ATTR.CLASS, holderClass)
            .__class(form, Commons.INNER_FORM)
            .__class(form, formClass)

        Listen.listenForForms( document.querySelectorAll(`.${formClass}`) )

        modal.showModal()
    }

    /**
     * creates a modal
     *
     * @param   {String}    modalId
     * @param   {String}    modalClass
     *
     * @returns {Element}
     */
    createModal ( modalId, modalClass )
    {
        let modal = this.__dial()

        this
            .__att(modal, Commons.ATTR.ID, modalId)
            .__att(modal, Commons.ATTR.CLASS, modalClass)
            .__append(modal, document.body)

        return modal
    }

    /**
     * escapes double quotes in string
     *
     * @param   {String}    str
     */
    static escapeDoubleQuotes ( str )
    {
        return str.replace(/"/g, '\\"')
    }

    /**
     * removes empty properties from given object
     *
     * @param   {Object}    candidate
     *
     * @return  {Object}
     */
    #filterEmptyProperties ( candidate )
    {
        return Object
            .entries( candidate )
            .filter( ([key, value]) => value !== undefined )
            .reduce( (obj, [key, value]) => {
                obj[key] = value
                return obj
            }, {} )
    }

    /**
     * gets parameters from asker
     *
     * @param   {Event}     event
     * @param   {Array}     [requirements]
     *
     * @returns {Object|false}
     */
    getAskerParams ( event, requirements = [] )
    {
        do
        {
            let asker   = event.target.closest( Commons.ELEMS.DIV )
            let typed   = asker.dataset[ Commons.D4T4S3TS.TYPED ]
            let urled   = asker.dataset[ Commons.D4T4S3TS.URLED ]
            let method  = asker.dataset[ Commons.D4T4S3TS.METHOD ]

            if ( ! typed )  break
            if ( ! urled )  break
            if ( ! method ) break

            var out = {
                typed:  typed,
                urled:  urled,
                method: method
            }

            requirements.forEach( (property) => out[ property ] = asker.dataset[ property ] )

            this.#filterEmptyProperties(out)

            let check = requirements.every( (property) => out.hasOwnProperty(property) )

            if ( ! check ) out = false
        }
        while ( 0 )

        return out ?? false
    }
}
