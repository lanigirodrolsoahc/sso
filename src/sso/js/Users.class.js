import { Commons }  from './Commons.class.js'
import { Ear }      from './Ear.class.js'
import { Rights }   from './Rights.class.js'
import { Forms }    from './Forms.class.js'
import { Listen }   from './Listen.class.js'

export class Users extends Commons
{
    static get CL4SS3S      () {
        return Object.freeze({
            CREATE:         'adminUserCreatorButton',
            PWD_DISPLAYER:  'pwdDisplayer',
            PWD_GENERATOR:  'pwdGenerator',
            PWD_RENEWER:    'pwdRenewer',
            USR_STATUS:     'userStatusSwitcher'
        }) }

    static get MODAL_MARK   () { return 'usersModal' }

    static get MSGS         () {
        return Object.freeze({
            FAIL:   'Erreur de chargement',
            LOGIN:  'identifiant',
            PWD:    'Attribuer ce nouveau mot de passe ?',
            RENEW:  'Renseignez votre identifiant'
        })
    }

    static get BRAINLESS    () { return 'forgotten' }
    static get USER_ICON    () { return 'userIcon' }
    static get RENEWAL      () { return 'login' }

    constructor ()
    {
        super()
    }

    /**
     * creates a form to ask for password's reset
     *
     * @param   {Event}     event
     */
    static createLoginer ( event )
    {
        do
        {
            Users.#deleteModal()

            const obj   = new Users
            let params  = obj.getAskerParams(event)

            if ( ! params ) break

            let modal   = obj.#createThisModal()
            let title   = obj.__div()
            let holder  = obj.__div()
            let form    = obj.__form()
            let buttons = obj.__div()
            let login   = obj.__input()
            let send    = obj.__input()

            obj
                .__append([title, form], modal)
                .__append( obj.__text( Users.MSGS.RENEW ), title )
                .__append(holder, form)
                .__append([login, buttons], holder)
                .__append([send, obj.cancelButton( Users.MODAL_MARK )], buttons)
                .__att(form, Commons.ATTR.ACTION, params.urled)
                .__att(form, Commons.ATTR.METHOD, params.method)
                .__att(login, Commons.ATTR.TYPE, Commons.TYPES.TEXT)
                .__att(login, Commons.ATTR.NAME, Users.RENEWAL)
                .__att(login, Commons.ATTR.HOLD, Users.MSGS.LOGIN)
                .__att( login, Commons.ATTR.VALUE, event.target.closest( Commons.ELEMS.FORM )?.querySelector( Commons.ELEMS.INPUT )?.value )
                .__att(login, Commons.ATTR.REQ, true)
                .__att(login, Commons.ATTR.AUTO, Commons.ATTR.OFF)
                .__att(send, Commons.ATTR.TYPE, Commons.TYPES.SUB)
                .__att(send, Commons.ATTR.VALUE, Commons.EMO.RECYCLE)
                .__class(send, Users.CL4SS3S.PWD_RENEWER)

            modal.showModal()

            Listen.listenForForms( modal.querySelectorAll( Commons.ELEMS.FORM ) )
        }
        while ( 0 )
    }

    /**
     * creates a `Users` modal
     *
     * @returns {Element}
     */
    #createThisModal ()
    {
        return this.createModal( Users.MODAL_MARK, Users.MODAL_MARK )
    }

    /**
     * deletes groups modal
     */
    static #deleteModal ()
    {
        Rights.delete( Users.MODAL_MARK )
    }

    /**
     * generates a new password if confirmed
     *
     * @param   {Event}     event
     */
    static async generatePassword ( event )
    {
        event.target.blur()

        Users.#deleteModal()

        const obj   = new Users
        let modal   = obj.#createThisModal()
        let title   = obj.__div()
        let holder  = obj.__div()
        let buttons = obj.__div()
        let goFor   = obj.__input()
        let pwd     = obj.__div()
        let pwdTxt  = await obj.#getNewPassword(event)

        obj
            .__append([title, holder, buttons], modal)
            .__append( obj.__text( Users.MSGS.PWD ), title )
            .__append(pwd, holder)
            .__append( obj.__text(pwdTxt), pwd )
            .__append([goFor, obj.cancelButton( Users.MODAL_MARK )], buttons)
            .__att(goFor, Commons.ATTR.TYPE, Commons.TYPES.BUTTON)
            .__att(goFor, Commons.ATTR.VALUE, Commons.EMO.THUMB)
            .__class(pwd, Users.CL4SS3S.PWD_DISPLAYER)

        modal.showModal()

        goFor.addEventListener( Ear.CL1CK, () => Users.#retrievePassword() )
    }

    /**
     * gets a new password
     *
     * @param   {Event}     event
     *
     * @returns {String}
     */
    async #getNewPassword ( event )
    {
        let target      = event.target
        let form        = this.__form()
        const formobj   = new Forms

        this
            .__att( form, Users.ATTR.METHOD, target.dataset[ Commons.D4T4S3TS.METHOD ] )
            .__att( form, Commons.ATTR.ACTION, target.dataset[ Commons.D4T4S3TS.URLED ] )

        await formobj.sends(form)

        return formobj.succeeded() ? formobj.messaged : Users.MSGS.FAIL
    }

    /**
     * goes to specified url in event parameters
     *
     * @param   {Event}     event
     */
    static goTo ( event )
    {
        const obj   = new Users
        var params  = obj.getAskerParams(event)

        if ( params ) window.location.href = params.urled
    }

    /**
     * listens to Users events
     */
    static listen ()
    {
        document.querySelector( `.${Users.CL4SS3S.CREATE} > ${Users.ELEMS.DIV}` )?.addEventListener( Ear.CL1CK, (event) => Users.goTo(event) )

        document.querySelector( `.${Users.CL4SS3S.PWD_GENERATOR}` )?.addEventListener( Ear.FOCUS, (event) => Users.generatePassword(event) )

        document.querySelector( `.${Users.CL4SS3S.USR_STATUS}` )?.addEventListener( Ear.CL1CK, (event) => Users.goTo(event) )
    }

    /**
     * retrieves password, sets affiliated input
     */
    static #retrievePassword ()
    {
        do
        {
            let pwd     = document.querySelector( `.${Users.CL4SS3S.PWD_DISPLAYER}` )?.textContent
            let input   = document.querySelector( `.${Users.CL4SS3S.PWD_GENERATOR}` )

            if ( ! pwd ) break
            if ( ! input ) break

            input.value = pwd
        }
        while ( 0 )

        Rights.delete( Users.MODAL_MARK )
    }
}
