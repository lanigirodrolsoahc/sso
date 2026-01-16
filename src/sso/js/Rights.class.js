import { Tooltip }  from './Tooltip.class.js'
import { Commons }  from './Commons.class.js'

export class Rights extends Commons
{
    static get CL4SS3S () {
        return Object.freeze({
            APP:            'applicationBlock',
            APP_CREATOR:    'applicationCreator',
            BOUNCE:         'bouncing',
            CREATOR:        'adminCreatorButton',
            ERAZOR:         'rightErazor',
            FORCE_DISPLAY:  'show',
            HOLDER:         'rightsEditor',
            MODALED:        'rightsModal',
            NO_POINT:       'noPointer',
            TIPPED:         'jsTipped'
        })
    }

    static get IDS () {
        return Object.freeze({
            ADDED:      'added',
            APP:        'application',
            GROUP:      'group',
            INFO:       'info',
            INFO_EDIT:  'infoEdit',
            LIST:       'rightsList',
            MARKED:     'rightAsker',
            MODAL:      'rightAddModal',
            USER:       'user'
        })
    }

    static get L4B3LS () {
        return Object.freeze({
            [Rights.IDS.APP]: {
                name: Rights.IDS.APP,
                title: `d'une `,
                hold: `de l'`
            },
            [Rights.IDS.GROUP]: {
                name: 'groupe',
                title: `d'un `,
                hold: `du `
            },
            [Rights.IDS.USER]: {
                name: 'utilisateur',
                title: `d'un `,
                hold: `de l'`
            }
        })
    }

    static get MSGS () {
        return Object.freeze({
            CREATE_RIGHT:   'Créer un droit dans',
            HOLD_NEW:       'nom',
            HOLD_DESC:      'description du droit',
            HOLD_NAME:      'nom du droit',
            TITLE_NEW:      'Création'
        })
    }

    static get REQ ()
    {
        return Object.freeze({
            MAX_ID:             9999,
            MIN_ID:             3,
            NAME_MIN_LENGTH:    3,
        })
    }

    static get TR1GG3RS () {
        return Object.freeze({
            CR3AT3: 'rightCreator'
        })
    }

    constructor ()
    {
        super()

        this.asker
    }

    /**
     * gets closest fieldset
     *
     * @param   {Event}     event
     *
     * @returns {Element|null}
     */
    static closestFieldset ( event )
    {
        return event.target.closest( Rights.ELEMS.FIELDSET )
    }

    /**
     * gets closest form
     *
     * @param   {Element}   elem
     *
     * @returns {Element|null}
     */
    static #closestForm ( elem )
    {
        return elem.closest( Rights.ELEMS.FORM )
    }

    /**
     * creates a form for admin to create a right
     *
     * @returns {Rights}
     */
    createForm ()
    {
        Rights.delete()

        let modal       = this.#createThisModal()
        let title       = this.__div()
        let form        = this.__form()
        let holder      = this.__div()
        let input       = this.__input()
        let area        = this.__area()
        let buttoned    = this.__div()
        let sub         = this.__input()

        this
            .__append(
                this.__text( `${Rights.MSGS.CREATE_RIGHT} ${this.#getApplicationName(this.asker)}` ),
                title
            )
            .__append([title, form], modal)
            .__append(holder, form)
            .__append([input, area, buttoned], holder)
            .__append([sub, this.cancelButton()], buttoned)
            .__att(input, Rights.ATTR.TYPE, Rights.TYPES.TEXT)
            .__att(input, Rights.ATTR.HOLD, Rights.MSGS.HOLD_NAME)
            .__att(input, Rights.ATTR.AUTO, Rights.ATTR.OFF)
            .__att(area, Rights.ATTR.HOLD, Rights.MSGS.HOLD_DESC)
            .__att(sub, Rights.ATTR.TYPE, Rights.TYPES.SUB)
            .__att(sub, Rights.ATTR.VALUE, Rights.EMO.SUBMIT)
            .__att(holder, Rights.ATTR.CLASS, Rights.CL4SS3S.HOLDER)

        sub.addEventListener( Rights.EVENTS.CLICK, (e) => Rights.createRight(e) )

        modal.showModal()

        return this
    }

    /**
     * creates a creator item
     *
     * @param   {Event}     event
     */
    static createItem ( event )
    {
        do
        {
            Rights.delete()

            const   obj     = new Rights
            let     params  = obj.getAskerParams(event, [Commons.D4T4S3TS.NAMED])

            if ( ! params ) break

            let input = obj.__input()

            obj
                .__att(input, Rights.ATTR.REQ, true)
                .__att(input, Rights.ATTR.TYPE, Rights.TYPES.TEXT)
                .__att(input, Rights.ATTR.HOLD, `${Rights.MSGS.HOLD_NEW} ${Rights.L4B3LS[ params.typed ].hold}${Rights.L4B3LS[ params.typed ].name}`)
                .__att(input, Rights.ATTR.NAME, params.named)
                .__att(input, Rights.ATTR.AUTO, Rights.ATTR.OFF)
                .creatorModal(
                    obj.#createThisModal(),
                    obj.__text( `${Rights.MSGS.TITLE_NEW} ${Rights.L4B3LS[ params.typed ].title}${Rights.L4B3LS[ params.typed ].name}` ),
                    [input],
                    Rights.CL4SS3S.HOLDER,
                    Rights.CL4SS3S.APP_CREATOR,
                    params.urled,
                    params.method
                )
        }
        while ( 0 )
    }

    /**
     * creates a `Rights` modal
     *
     * @returns {Element}
     */
    #createThisModal ()
    {
        return this.createModal( Rights.IDS.MODAL, Rights.CL4SS3S.MODALED )
    }

    /**
     * appends a new right before current asker
     *
     * @param   {Event} e
     *
     * @returns {Rights}
     */
    static createRight (e)
    {
        do
        {
            e.preventDefault()

            const help      = new Rights
            let asker       = Rights.#getAsker()

            if ( ! asker ) break

            let form        = Rights.#closestForm(e.target)
            let input       = form.querySelector( `${Rights.ELEMS.INPUT}[type="${Rights.TYPES.TEXT}"]` )
            let name        = input?.value.replace(/[^_a-z]/ig, '')
            let area        = form.querySelector( Rights.ELEMS.AREA )?.value
            let fieldset    = help.__field()
            let legend      = help.__legend()
            let target      = Rights.#closestForm(asker)
            let erazor      = help.__div()
            let newPut      = help.__input()
            let label       = help.__label()
            let index       = asker.querySelector( Rights.ELEMS.DIV )?.dataset[ Rights.ATTR.INDEX ]
            let id          = Rights.generateUnikId( Rights.IDS.ADDED )

            input.value     = name

            if ( name.length < Rights.REQ.NAME_MIN_LENGTH ) break
            if ( ! name.match(/^[a-z]+/ig) ) break

            let putName     = `${Rights.IDS.LIST}[${index}][${name}]`

            if ( target.querySelector( `${Rights.ELEMS.INPUT}[${Rights.ATTR.NAME}="${putName}"]` ) )
            {
                Rights.delete()
                break
            }

            help.__append(legend, fieldset)

            if ( area.length > 0 )
            {
                let info        = help.__input()
                let bulbed      = help.__label()
                let bulbid      = Rights.generateUnikId( Rights.IDS.INFO )
                let sendInfo    = help.__input()
                let areaVal     = Rights.escapeQuotes(area)

                help
                    .__append([info, bulbed, sendInfo], fieldset)
                    .__append( help.__text(Rights.EMO.BULB), bulbed )
                    .__att(info, Rights.ATTR.ID, bulbid )
                    .__att(info, Rights.ATTR.DISABLED, true )
                    .__att(info, Rights.ATTR.TYPE, Rights.TYPES.RADIO )
                    .__att(bulbed, Rights.ATTR.CLASS, `${Rights.CL4SS3S.TIPPED} ${Rights.IDS.INFO} ${Rights.CL4SS3S.NO_POINT}` )
                    .__att(bulbed, Rights.ATTR.FOR, id )
                    .__att(bulbed, Rights.ATTR.TIP, areaVal )
                    .__att(sendInfo, Rights.ATTR.TYPE, Rights.TYPES.HIDE )
                    .__att(sendInfo, Rights.ATTR.NAME, `${Rights.IDS.LIST}[${Rights.IDS.INFO}][${name}]`)
                    .__att(sendInfo, Rights.ATTR.VALUE, areaVal )

                Tooltip.listenFor( fieldset.querySelectorAll( `.${Tooltip.CLASS.TIPPED}` ) )
            }

            help
                .__append([newPut, label], fieldset)
                .__append( help.__text(name), legend )
                .__append( help.__text(Rights.EMO.PIRATE), label )
                .__append( help.__text(Rights.EMO.DELETE), erazor )
                .__append(erazor, fieldset)
                .__att(erazor, Rights.ATTR.CLASS, Rights.CL4SS3S.ERAZOR )
                .__att(newPut, Rights.ATTR.NAME, putName )
                .__att(newPut, Rights.ATTR.ID, id )
                .__att(newPut, Rights.ATTR.TYPE, Rights.TYPES.RADIO )
                .__att(newPut, Rights.ATTR.CHECK, true )
                .__att(label, Rights.ATTR.FOR, id )
                .__att(label, Rights.ATTR.CLASS, Rights.CL4SS3S.NO_POINT )

            target.insertBefore(fieldset, asker)

            erazor.addEventListener( Rights.EVENTS.CLICK, (e) => e.target.closest( Rights.ELEMS.FIELDSET )?.remove() )

            Rights.delete()
        }
        while ( 0 )

        return this
    }

    /**
     * frees modal of existence
     *
     * @param   {String}    [modalId = Rights.IDS.MODAL]
     */
    static delete ( modalId = Rights.IDS.MODAL )
    {
        document.querySelector( `#${modalId}` )?.remove()
    }

    /**
     * creates a form to edit a right's description
     *
     * @param   {Event}     event
     *
     * @returns {Rights}
     */
    static editDescription ( event )
    {
        Rights.delete()

        const   obj     = new Rights()
        let     modal   = obj.#createThisModal()
        let     title   = obj.__div()
        let     form    = obj.__form()
        let     holder  = obj.__div()
        let     area    = obj.__area()
        let     btnd    = obj.__div()
        let     sub     = obj.__input()
        let     asker   = event.target
        let     set     = Rights.closestFieldset(event)

        obj
            .setAsker(event)
            .__append([title, form], modal)
            .__append(holder, form)
            .__append( obj.__text(
                `Description du droit : ${ set.querySelector( Rights.ELEMS.LEGEND ).textContent } (${ obj.#getApplicationName(set) })`
                ),
                title
            )
            .__append([area, btnd], holder)
            .__append([sub, obj.cancelButton()], btnd)
            .__att(holder, Rights.ATTR.CLASS, Rights.CL4SS3S.HOLDER)
            .__att(sub, Rights.ATTR.TYPE, Rights.TYPES.SUB)
            .__att(sub, Rights.ATTR.VALUE, Rights.EMO.SUBMIT)
            .__inner(area, asker.getAttribute( Rights.ATTR.TIP )?.replace('/\\/', '') ?? '')

        sub.addEventListener( Rights.EVENTS.CLICK, (e) => Rights.modifyDescription(e) )

        modal.showModal()

        return obj
    }

    /**
     * escapes double quotes
     *
     * @param   {String}    str
     *
     * @returns {String}
     */
    static escapeQuotes ( str )
    {
        return str.replace(/"/g, '\\"')
    }

    /**
     * frees current asker from temp mark
     *
     * @returns {Rights}
     */
    static free ()
    {
        this.#getAsker()?.removeAttribute( Rights.ATTR.ID )

        return this
    }

    /**
     * generates non being identifier
     *
     * @param   {String}    root
     *
     * @returns {String}
     */
    static generateUnikId ( root )
    {
        let min = Math.ceil( Rights.REQ.MIN_ID )
        let max = Math.floor( Rights.REQ.MAX_ID )
        let id  = `${root}${ Math.floor( Math.random() * (max - min + 1) ) + min }`

        return document.getElementById(id) ? Rights.generateUnikId(root) : id
    }

    /**
     * gets application name based on current asker
     *
     * @param   {Element}   asker
     *
     * @returns {String}
     */
    #getApplicationName ( asker )
    {
        return asker.closest( `.${Rights.CL4SS3S.APP}` )?.querySelector( `div:nth-child(1)` ).innerText
    }

    /**
     * gets marked asker
     *
     * @returns {Element|null}
     */
    static #getAsker ()
    {
        return document.getElementById( Rights.IDS.MARKED )
    }

    /**
     * modifies a right description
     *
     * @param   {Event}     event
     *
     * @returns {Rights}
     */
    static modifyDescription ( event )
    {
        const obj = new Rights()

        do
        {
            event.preventDefault()

            let asker = Rights.#getAsker()

            if ( ! asker ) break

            let input   = asker.querySelector( `${Rights.ELEMS.INPUT}[type="${Rights.TYPES.RADIO}"].${Rights.IDS.INFO}` )
            let marker  = `${Rights.ELEMS.LABEL}.${Rights.IDS.INFO}`
            let target  = asker.querySelector(marker)
            let area    = event.target.closest( Rights.ELEMS.FORM )?.querySelector( Rights.ELEMS.AREA )

            if ( ! input )  break
            if ( ! target ) break
            if ( ! area )   break

            let str = Rights.escapeQuotes(area.value)

            obj
                .__att(target, Rights.ATTR.TIP, str)
                .__att(input, Rights.ATTR.VALUE, str)
                .__class(target, Rights.CL4SS3S.TIPPED)

            Tooltip.listenFor( asker.querySelectorAll(marker) )
        }
        while ( 0 )

        Rights.delete()

        return obj
    }

    /**
     * sets asker for later use
     *
     * @param   {Event}     event
     *
     * @returns {Rights}
     */
    setAsker ( event )
    {
        Rights.free()

        this.asker = Rights.closestFieldset(event)

        this.asker.setAttribute( Rights.ATTR.ID, Rights.IDS.MARKED )

        return this
    }
}
