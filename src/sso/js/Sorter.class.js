import { Ear }      from './Ear.class.js'
import { Helper }   from './Helper.class.js'
import { Rights }   from './Rights.class.js'
import { Pager }    from './Pager.class.js'

export class Sorter extends Helper
{
    static get CLASS_SORT_MODAL () { return 'sortModal' }
    static get CLASS_SORT       () { return 'dataSorter' }
    static get DATA_SORTING     () { return 'sorter' }
    static get EMO_SORT         () { return '🆎' }
    static get MODAL_ID         () { return 'sorterModal' }
    static get SORT_PARAM       () { return 'sortmanner' }
    static get SORT_ROOT        () { return 'sortItem' }
    static get SORT_TITLE       () { return 'Trier par :' }

    constructor ()
    {
        super()
    }

    /**
     * displays available options
     *
     * @param   {Event}     clicked
     *
     * @returns {Sorter}
     */
    #displayOptions ( clicked )
    {
        Loader.show()

        do
        {
            clicked = clicked.target.closest( Sorter.ELEMS.DIV )

            if ( ! clicked ) break

            let options = clicked.dataset[ Sorter.DATA_SORTING ]

            if ( ! options ) break

            try {
                var json = JSON.parse(options)
            }
            catch ( error ) {
                break
            }

            Rights.delete( Sorter.MODAL_ID )

            let modal   = this.__dial()
            let title   = this.__div()
            let form    = this.__form()
            let buts    = this.__div()
            let cancel  = ( new Rights ).cancelButton( Sorter.MODAL_ID )
            let choices = this.__div()

            this
                .__att(modal, Sorter.ATTR.ID, Sorter.MODAL_ID)
                .__class(modal, Sorter.CLASS_SORT_MODAL)
                .__append([title, choices, buts], form)
                .__append( this.__text(Sorter.SORT_TITLE), title )
                .__append(form, modal)
                .__append(cancel, buts)
                .__append(modal, document.body)

            for ( const key in json )
            {
                let row     = this.__div()
                let input   = this.__input()
                let label   = this.__label()
                let ided    = Rights.generateUnikId( Sorter.SORT_ROOT )

                this
                    .__att(input, Sorter.ATTR.TYPE, Sorter.TYPES.BUTTON)
                    .__att(input, Sorter.ATTR.VALUE, Sorter.EMO_SORT)
                    .__att(input, `data-${Sorter.SORT_PARAM}`, btoa(json[key]))
                    .__att(input, Sorter.ATTR.ID, ided)
                    .__att(label, Sorter.ATTR.FOR, ided)
                    .__append( this.__text(key), label )
                    .__append([input, label], row)
                    .__append(row, choices)

                input.addEventListener( Sorter.EVENTS.CLICK, (event) => Sorter.sortResult(event) )
            }

            modal.showModal()
            Sorter.unFocus()
        }
        while ( 0 )

        Loader.hide()

        return this
    }

    /**
     * listens to page sorting events
     */
    static listen ()
    {
        document.querySelectorAll( `.${Sorter.CLASS_SORT}` ).forEach( button =>
        {
            button.addEventListener( Ear.CL1CK, (event) => ( new Sorter ).#displayOptions(event) )
        })
    }

    /**
     * sorts result with event's button's parameter
     *
     * @param   {Event}     event
     */
    static sortResult ( event )
    {
        Loader.show()

        do
        {
            let button = event.target.closest( `${Sorter.ELEMS.INPUT}[${Sorter.ATTR.TYPE}="${Sorter.TYPES.BUTTON}"]` )

            if ( ! button ) break

            let target = button.dataset[ Sorter.SORT_PARAM ]

            if ( ! target ) break

            Pager.reloadWith( { [Sorter.SORT_PARAM]: target } )
        }
        while ( 0 )

        Loader.hide()
    }

    /**
     * removes current focus, if any
     */
    static unFocus ()
    {
        document.querySelector(':focus')?.blur()
    }
}
