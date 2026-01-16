class Loader
{
    static get LOADER_ID            () { return 'loading' }
    static get COMPANY_COLOR        () { return 'rgba(148, 53, 113, 0.75)' }
    static get LOAD_MARBLES_SIZE    () { return 75 }

    /**
     * - creates loader if needed
     * - creates suitable style too
     *
     * @returns {Loader}
     */
    static createLoader ()
    {
        do
        {
            if ( Loader.exists() ) break

            let loading = document.createElement('div', 'Loading&#8230;')
            loading.setAttribute('id', Loader.LOADER_ID)
            loading.setAttribute('class', Loader.LOADER_ID)
            document.body.appendChild(loading)

            let style = document.createElement('style')

            style.appendChild( document.createTextNode(`.${Loader.LOADER_ID} {
                position: fixed;
                z-index: 999999999999;
                height: 2em;
                width: 2em;
                overflow: show;
                margin: auto;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                visibility: hidden;
            }

            .${Loader.LOADER_ID}:before {
                content: '';
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.3);
            }

            .${Loader.LOADER_ID}:not(:required) {
                font: 0/0 a;
                color: transparent;
                text-shadow: none;
                background-color: transparent;
                border: 0;
            }

            .${Loader.LOADER_ID}:not(:required):after {
                content: '';
                display: block;
                font-size: ${Loader.LOAD_MARBLES_SIZE}px;
                width: 1em;
                height: 1em;
                margin-top: -0.5em;
                animation: spinningloader 1500ms infinite linear;
                border-radius: 0.5em;
                box-shadow:
                    ${Loader.COMPANY_COLOR} 1.5em 0 0 0,
                    ${Loader.COMPANY_COLOR} 1.1em 1.1em 0 0,
                    ${Loader.COMPANY_COLOR} 0 1.5em 0 0,
                    ${Loader.COMPANY_COLOR} -1.1em 1.1em 0 0,
                    ${Loader.COMPANY_COLOR} -1.5em 0 0 0,
                    ${Loader.COMPANY_COLOR} -1.1em -1.1em 0 0,
                    ${Loader.COMPANY_COLOR} 0 -1.5em 0 0,
                    ${Loader.COMPANY_COLOR} 1.1em -1.1em 0 0;
            }

            @keyframes spinningloader {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }`) )

            document.body.appendChild(style)
        }
        while ( 0 )

        return this
    }

    /**
     * gets laoder container identifier
     *
     * @returns {Element|null}
     */
    static getLoader ()
    {
        return document.querySelector(`#${Loader.LOADER_ID}`)
    }

    /**
     * hides loader
     */
    static hide ()
    {
        Loader.createLoader().getLoader().style.visibility = 'hidden'
    }

    /**
     * checks if loader exists
     *
     * @returns {boolean}
     */
    static exists ()
    {
        return Loader.getLoader() !== null
    }

    /**
     * shows loader
     */
    static show ()
    {
        Loader.createLoader().getLoader().style.visibility = 'visible'
    }
}
