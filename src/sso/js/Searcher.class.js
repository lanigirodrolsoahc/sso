import { Ear }      from './Ear.class.js'
import { Helper }   from './Helper.class.js'
import { Rights }   from './Rights.class.js'
import { Pager }    from './Pager.class.js'
import { Listen }   from './Listen.class.js'

export class Searcher extends Helper
{
    static get CL4SS3D () {
        return Object.freeze({
            BUTTON:     'searcher',
            CANCEL:     'searchCancel',
            INPUT:      'searchTerm',
            INPUTER:    'seracherInputer',
            MODAL:      'searchModalBlock'
        })
    }

    static get FORBEARANCE () { return 1000 }

    static get ID3D () {
        return Object.freeze({
            MODAL:          'searchModal',
            SEARCH_PARAM:   'search'
        })
    }

    static get T3XT () {
        return Object.freeze({
            MODAL: 'Que chercher ❓'
        })
    }

    constructor ()
    {
        if ( Searcher.isInstantiated ) return Searcher.instance

        super();

        this.timer
        Searcher.instance = this
        Searcher.isInstantiated = true
    }

    /**
     * display search modal
     */
    static #display ()
    {
        Loader.show()

        Rights.delete( Searcher.ID3D.MODAL )

        const obj       = new Searcher
        let modal       = obj.__dial()
        let title       = obj.__div()
        let form        = obj.__form()
        let buts        = obj.__div()
        let cancel      = ( new Rights ).cancelButton( Searcher.ID3D.MODAL )
        let inputer     = obj.__div()
        let input       = obj.__input()
        const listen    = new Listen

        obj
            .__att(modal, Searcher.ATTR.ID, Searcher.ID3D.MODAL)
            .__att(input, Searcher.ATTR.TYPE, Searcher.TYPES.TEXT)
            .__att(input, Searcher.ATTR.AUTO, Searcher.ATTR.OFF)
            .__class(modal, Searcher.CL4SS3D.MODAL)
            .__class(inputer, Searcher.CL4SS3D.INPUTER)
            .__class(input, Searcher.CL4SS3D.INPUT)
            .__append([title, inputer, buts], form)
            .__append( obj.__text(Searcher.T3XT.MODAL), title )
            .__append(form, modal)
            .__append(input, inputer)
            .__append(cancel, buts)
            .__append(modal, document.body)

        input.addEventListener( Searcher.EVENTS.KEYUP, (event) => Searcher.#search(event) )
        listen.noQuickSubmission( document.querySelectorAll( `.${Searcher.CL4SS3D.INPUT}` ) )
        modal.showModal()
        Loader.hide()
    }

    /**
     * listens to search call
     */
    static listen ()
    {
        document.querySelectorAll( `.${Searcher.CL4SS3D.BUTTON}` ).forEach( button =>
        {
            button.addEventListener( Ear.CL1CK, () => Searcher.#display() )
        })

        document.querySelectorAll( `.${Searcher.CL4SS3D.CANCEL}` ).forEach( button =>
        {
            button.addEventListener( Ear.CL1CK, () => Pager.reloadWith() )
        })
    }

    /**
     * listens to user's search string, waits and lauches the request
     *
     * @param   {Event} event
     */
    static #search ( event )
    {
        const obj = new Searcher

        clearTimeout(obj.timer)

        obj.timer = setTimeout( () =>
        {
            do
            {
                let term = event.target.closest( Searcher.ELEMS.INPUT )?.value.trim()

                if ( ! term ) break

                Pager.reloadWith( { [Searcher.ID3D.SEARCH_PARAM]: term }, [ Pager.PAGE_ID ] )
            }
            while ( 0 )

        }, Searcher.FORBEARANCE )
    }
}
