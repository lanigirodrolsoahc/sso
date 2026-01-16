import { Ear }      from './Ear.class.js'
import { Rights }   from './Rights.class.js'
import { Searcher } from './Searcher.class.js'

export class Pager
{
    static get EMPTY_PARAMS     () { return '' }
    static get PAGE_ID          () { return 'pageid' }
    static get PAGE_MOB         () { return 'pageMobility' }

    /**
     * forgets page marker while search is runned
     *
     * @param   {Object}    params
     * @param   {Array}     exclusions
     *
     * @returns {Object}
     */
    static #forget ( params, exclusions )
    {
        exclusions.forEach( (index) => params.delete(index) )

        return params
    }

    /**
     * listens to page navigation events
     */
    static listen ()
    {
        document.querySelectorAll( `.${Pager.PAGE_MOB}` ).forEach( button =>
        {
            button.addEventListener( Ear.CL1CK, (event) => Pager.navigate(event) )
        })
    }

    /**
     * navigates to page according to buttons's parameters
     *
     * @param   {Event}   cliked
     */
    static navigate ( cliked )
    {
        Loader.show()

        do
        {
            let button = cliked.target.closest( Rights.ELEMS.DIV )

            if ( ! button ) break

            let target = button.dataset[ Pager.PAGE_ID ]

            if ( ! target ) break

            Pager.reloadWith( { [Pager.PAGE_ID]: target } )
        }
        while ( 0 )

        Loader.hide()
    }

    /**
     * reloads page with given altered params
     *
     * @param   {Object}        [parameters]
     * @param   {Array}         [exclusions]
     */
    static reloadWith ( parameters, exclusions )
    {
        let url     = new URL( window.location )
        let params  = url.searchParams

        if ( ! parameters )
            url.search = Pager.EMPTY_PARAMS
        else if ( parameters instanceof Object )
        {
            for ( const [key, val] of Object.entries(parameters) )
                params.set(key, val)

            url.search = Pager.#forget( params, ! exclusions ? [] : exclusions ).toString()
        }

        window.location.replace( url.toString() )
    }
}
