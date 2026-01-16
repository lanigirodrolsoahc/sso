import { Rights }   from './Rights.class.js'
import { Groups }   from './Groups.class.js'
import { Users }    from './Users.class.js'

export class Ear
{
    static get CL1CK        () { return 'click' }
    static get DBLCLICK     () { return 'dblclick' }
    static get FOCUS        () { return 'focus' }

    static get GETS         () {
        return Object.freeze({
            renewing:   'renewing'
        })
    }

    static get MOUSE_DOWN   () { return 'mousedown' }

    /**
     * listens to any admin event
     */
    static listen ()
    {
        document.querySelectorAll( `.${Rights.TR1GG3RS.CR3AT3}` ).forEach( trigger =>
        {
            trigger.addEventListener( Ear.CL1CK, ( event ) =>
                ( new Rights() )
                    .setAsker(event)
                    .createForm()
            )
        })

        document.querySelectorAll( `${Rights.ELEMS.LABEL}.${Rights.IDS.INFO}.${Rights.IDS.INFO_EDIT}` ).forEach( described =>
        {
            described.addEventListener( Ear.DBLCLICK, ( event ) => Rights.editDescription(event) )
        })

        document.querySelectorAll( `.${Rights.CL4SS3S.CREATOR}` ).forEach( creator =>
        {
            creator.addEventListener( Ear.CL1CK, ( event ) => Rights.createItem(event) )
        })

        document.querySelectorAll( `.${Groups.CL4SS3S.APP_NAME}` ).forEach( applied =>
        {
            applied.addEventListener( Ear.CL1CK, ( event ) => Groups.toggleApp(event) )
        })

        document.querySelectorAll( `.${Groups.CL4SS3S.TIMED}` ).forEach( calendar =>
        {
            calendar.addEventListener( Ear.DBLCLICK, ( event ) => Groups.pickDates(event) )
        })

        document.querySelectorAll( `.${Users.USER_ICON}.${Rights.CL4SS3S.BOUNCE}` ).forEach( displayer =>
        {
            displayer.addEventListener( Ear.CL1CK, ( event ) => event.target.closest( Rights.ELEMS.DIV )?.classList.toggle( Rights.CL4SS3S.FORCE_DISPLAY ) )
        })

        document.querySelectorAll( `.${Groups.CL4SS3S.CREATOR}` ).forEach( adder =>
        {
            adder.addEventListener( Ear.CL1CK, ( event ) => Groups.createItem(event) )
        })

        document.querySelectorAll( `.${Groups.CL4SS3S.GROUP_DELETOR}` ).forEach( deletor =>
        {
            deletor.addEventListener( Ear.CL1CK, (event) => Groups.confirmDeletion(event) )
        })

        document.querySelector( `#${Users.BRAINLESS}` )?.addEventListener( Ear.CL1CK, ( event ) => Users.createLoginer(event)  )
    }

    /**
     * listens to get parameters having an influence on current page
     */
    static toGet ()
    {
        const params = new URLSearchParams( window.location.search )

        if ( params.has(this.GETS.renewing) )
            document.querySelector( `#${Users.BRAINLESS}` )?.dispatchEvent( new Event(this.CL1CK) )
    }
}
