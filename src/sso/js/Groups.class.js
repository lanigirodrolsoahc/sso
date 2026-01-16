import { Commons }  from './Commons.class.js'
import { Rights }   from './Rights.class.js'
import { Ear }      from './Ear.class.js'
import { Listen }   from './Listen.class.js'

export class Groups extends Commons
{
    static get CL4SS3S () {
        return Object.freeze({
            APP_RIGHTS:     'groupsAppRights',
            APP_GROUP:      'groupsApp',
            APP_NAME:       'groupsAppName',
            APP_REVEAL:     'groupsAppUnshy',
            ASKER:          'groupDateAsker',
            CREATOR:        'adminGroupCreatorButton',
            CONFIRM_FAINT:  'yesNoFainting',
            DATE_BLOCK:     'dateBlock',
            GROUP_CREATOR:  'groupCreator',
            GROUP_DELETOR:  'groupDeletor',
            GROUP_FORM:     'groupEditionForm',
            GROUP_NAME:     'groupName',
            HOLDER:         'groupEditor',
            MODAL:          'groupsModal',
            TIMED:          'groupUserTimeLimit',
            USER_BLOCK:     'groupUserBlock',
            USER_NAME:      'groupUserNamed'
        })
    }

    static get SYMB () {
        return Object.freeze({
            BELONGING:  '∈'
        })
    }

    static get IDS () {
        return Object.freeze({
            DATE_END:       'dateEnd',
            DATE_START:     'dateStart',
            GROUP_ID:       'groupId',
            MODAL:          'groupsModal'
        })
    }

    static get MSGS () {
        return Object.freeze({
            CONF:   'Vous confirmez cette suppression ?',
            DESC:   'description',
            END:    'fin',
            NO:     'Non...',
            START:  'début',
            YES:    'Oui !'
        })
    }

    constructor ()
    {
        super()
    }

    /**
     * brands asker for later use
     *
     * @param   {Element}   clicked
     *
     * @returns {Groups}
     */
    brand ( clicked )
    {
        Groups.retrieve()?.classList.remove( Groups.CL4SS3S.ASKER )

        clicked.closest( `.${Groups.CL4SS3S.TIMED}` ).classList.add( Groups.CL4SS3S.ASKER )

        return this
    }

    /**
     * creates a modal to confirm group deletion
     *
     * @param   {Event}     event
     */
    static confirmDeletion ( event )
    {
        Groups.#deleteModal()

        const   obj     = new Groups
        let     modal   = obj.#createThisModal()
        let     params  = obj.getAskerParams(event, [ Commons.D4T4S3TS.ID ])

        do
        {
            if ( ! params ) break

            let ask         = obj.__div()
            let form        = obj.__form()
            let holder      = obj.__div()
            let boxy        = obj.__div()
            let yText       = obj.__div()
            let boxn        = obj.__div()
            let nText       = obj.__div()
            let yep         = obj.__input()
            let nop         = obj.__input()
            let groupId     = obj.__input()


            obj
                .__append( obj.__text( `${Groups.EMO.MUNCH} ${Groups.MSGS.CONF}` ), ask )
                .__append([ask, form], modal)
                .__append([groupId, holder], form)
                .__append([boxn, boxy], holder)
                .__append([nText, nop], boxn)
                .__append([yep, yText], boxy)
                .__att(yep, Groups.ATTR.TYPE, Groups.TYPES.SUB)
                .__att(yep, Groups.ATTR.VALUE, Groups.EMO.TRASH)
                .__att(nop, Groups.ATTR.TYPE, Groups.TYPES.BUTTON)
                .__att(nop, Groups.ATTR.VALUE, Groups.EMO.PRETEND)
                .__att(form, Groups.ATTR.ACTION, params.urled)
                .__att(form, Groups.ATTR.METHOD, params.method)
                .__att(form, Groups.ATTR.TYPE, params.typed)
                .__att(groupId, Groups.ATTR.NAME, Groups.IDS.GROUP_ID)
                .__att(groupId, Groups.ATTR.VALUE, params.id)
                .__att(groupId, Groups.ATTR.TYPE, Groups.TYPES.HIDE)
                .__append( obj.__text( Groups.MSGS.NO ), nText )
                .__append( obj.__text( Groups.MSGS.YES ), yText )
                .__class(holder, Groups.CL4SS3S.CONFIRM_FAINT)

            nop.addEventListener( Ear.CL1CK, () => Groups.#deleteModal() )

            modal.showModal()

            Listen.listenForForms( form.closest( Groups.ELEMS.DIAL ).querySelectorAll( Groups.ELEMS.FORM ) )
        }
        while ( 0 )
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
            Groups.#deleteModal()

            const   obj = new Groups
            let     params = obj.getAskerParams(event, [
                Commons.D4T4S3TS.NAMED,
                Commons.D4T4S3TS.DESC
            ])

            if ( ! params ) break

            let name    = obj.__input()
            let desc    = obj.__area()
            let joiner  = ' '

            obj
                .__att(name, Groups.ATTR.REQ, true)
                .__att(name, Groups.ATTR.TYPE, Groups.TYPES.TEXT)
                .__att(name, Groups.ATTR.HOLD, [
                    Rights.MSGS.HOLD_NEW,
                    Rights.L4B3LS[ params.typed ].hold,
                    Rights.L4B3LS[ params.typed ].name
                ].join(joiner))
                .__att(name, Groups.ATTR.NAME, params[ Commons.D4T4S3TS.NAMED ])
                .__att(name, Groups.ATTR.AUTO, Groups.ATTR.OFF)
                .__att(desc, Groups.ATTR.HOLD, [
                    Groups.MSGS.DESC,
                    Rights.L4B3LS[ params.typed ].hold,
                    Rights.L4B3LS[ params.typed ].name
                ].join(joiner))
                .__att(desc, Groups.ATTR.NAME, params[ Commons.D4T4S3TS.DESC ])
                .creatorModal(
                    obj.#createThisModal(),
                    obj.__text( [
                        Rights.MSGS.TITLE_NEW,
                        Rights.L4B3LS[ params.typed ].title,
                        Rights.L4B3LS[ params.typed ].name
                    ].join(joiner) ),
                    [name, desc],
                    Groups.CL4SS3S.HOLDER,
                    Groups.CL4SS3S.GROUP_CREATOR,
                    params.urled,
                    params.method,
                    Groups.IDS.MODAL
                )
        }
        while ( 0 )
    }

    /**
     * creates a `Groups` modal
     *
     * @returns {Element}
     */
    #createThisModal ()
    {
        return this.createModal( Groups.IDS.MODAL, Groups.CL4SS3S.MODAL )
    }

    /**
     * deletes groups modal
     */
    static #deleteModal ()
    {
        Rights.delete( Groups.IDS.MODAL )
    }

    /**
     * creates a dates picker for begining and ending
     *
     * @param   {Event}     event
     */
    static pickDates ( event )
    {
        let     clicked     = event.target
        const   obj         = new Groups()
        let     modal       = obj.__dial()
        let     start       = obj.__input()
        let     startLabel  = obj.__label()
        let     end         = obj.__input()
        let     endLabel    = obj.__label()
        let     title       = obj.__div()
        let     form        = obj.__form()
        let     buts        = obj.__div()
        let     cancel      = ( new Rights ).cancelButton( Groups.IDS.MODAL )
        let     sub         = obj.__input()
        let     startBlock  = obj.__div()
        let     endBlock    = obj.__div()
        let     startNop    = obj.__div()
        let     endNop      = obj.__div()
        let     userName    = clicked.closest( `.${Groups.CL4SS3S.USER_BLOCK}` ).querySelector( `.${Groups.CL4SS3S.USER_NAME}` )?.textContent
        let     groupName   = clicked.closest( `${Groups.ELEMS.FORM}` ).querySelector( `${Groups.ELEMS.INPUT}[${Groups.ATTR.NAME}="${Groups.CL4SS3S.GROUP_NAME}"]` )?.value
        let     userDates   = Groups.readDates(clicked)

        obj
            .brand(clicked)
            .__append(
                obj.__text( `${userName} ${Groups.SYMB.BELONGING} ${groupName}` ),
                title
            )
            .__append([title, form], modal)
            .__append([startBlock, endBlock, buts], form)
            .__append([startLabel, start, startNop], startBlock)
            .__append([endLabel, end, endNop], endBlock)
            .__append([sub, cancel], buts)
            .__append(modal, document.body)
            .__append( obj.__text( Groups.MSGS.START ), startLabel )
            .__append( obj.__text( Groups.MSGS.END ), endLabel )
            .__append( obj.__text( Groups.EMO.DELETE ) , startNop )
            .__append( obj.__text( Groups.EMO.DELETE ) , endNop )
            .__att(start, Groups.ATTR.TYPE, Groups.TYPES.DATE)
            .__att(end, Groups.ATTR.TYPE, Groups.TYPES.DATE)
            .__att(modal, Groups.ATTR.ID, Groups.IDS.MODAL)
            .__att(sub, Groups.ATTR.TYPE, Groups.TYPES.SUB)
            .__att(sub, Groups.ATTR.VALUE, Groups.EMO.SUBMIT)
            .__att(start, Groups.ATTR.ID, Groups.IDS.DATE_START)
            .__att(end, Groups.ATTR.ID, Groups.IDS.DATE_END)
            .__att(startLabel, Groups.ATTR.FOR, Groups.IDS.DATE_START)
            .__att(endLabel, Groups.ATTR.FOR, Groups.IDS.DATE_END)
            .__att(start, Groups.ATTR.VALUE, userDates[0].value)
            .__att(end, Groups.ATTR.VALUE, userDates[1].value)
            .__class(modal, Groups.IDS.MODAL)
            .__class(startBlock, Groups.CL4SS3S.DATE_BLOCK)
            .__class(endBlock, Groups.CL4SS3S.DATE_BLOCK)

        Groups.pukeDate(startNop)
        Groups.pukeDate(endNop)

        form.addEventListener( Groups.EVENTS.SUBMIT, ( event ) => Groups.pokeDate(event) )

        modal.showModal()
    }

    /**
     * registers inputed dates into main form's asker
     *
     * @param   {Event}     submission
     */
    static pokeDate ( submission )
    {
        submission.preventDefault()

        let userDates = Groups.readDates( document.querySelector( `.${Groups.CL4SS3S.ASKER}` ) )

        userDates[0].value = submission.target.querySelector( `#${Groups.IDS.DATE_START}` ).value
        userDates[1].value = submission.target.querySelector( `#${Groups.IDS.DATE_END}` ).value

        Groups.#deleteModal()
    }

    /**
     * creates a listener for date deletion
     *
     * @param   {Element}   element
     */
    static pukeDate ( element )
    {
        element.addEventListener( Groups.EVENTS.CLICK, ( event ) => event.target.closest( `.${Groups.CL4SS3S.DATE_BLOCK}` ).querySelector( `${Groups.ELEMS.INPUT}[${Groups.ATTR.TYPE}="${Groups.TYPES.DATE}"]` ).value = null)
    }

    /**
     * reads targeted hidden input dates
     *
     * @param   {Element}   element
     *
     * @returns {NodeList}
     */
    static readDates ( element )
    {
        return element.closest( `.${Groups.CL4SS3S.TIMED}` ).querySelectorAll( `${Groups.ELEMS.INPUT}[${Groups.ATTR.TYPE}="${Groups.TYPES.HIDE}"]` )
    }

    /**
     * retrieves dates asker
     *
     * @return  {Element|null}
     */
    static retrieve ()
    {
        return document.querySelector( `.${Groups.CL4SS3S.ASKER}` )
    }

    /**
     * toggles app's rights visibility
     *
     * @param   {Event}     event
     */
    static toggleApp ( event )
    {
        try
        {
            let parent  = event.target.closest( `.${Groups.CL4SS3S.APP_GROUP}` )
            let aim     = parent.querySelector( `.${Groups.CL4SS3S.APP_RIGHTS}` )

            aim.classList.toggle( Groups.CL4SS3S.APP_REVEAL )
        }
        catch ( Error ) {}
    }
}
