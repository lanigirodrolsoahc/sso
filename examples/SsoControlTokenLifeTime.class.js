class SsoControlTokenLifeTime
{
    static get CLICK        () { return 'click' }
    static get LEFT         () { return 'expiresIn' }
    static get REG_INTRA    () { return /\/Intranet\//i }

    static get CODES () {
        return Object.freeze({
            GONE:   410,
            OK:     200
        })
    }

    static get PATHS () {
        return Object.freeze({
            SERVICE:    'shared/components/sso/services/sessionCheck',
            VENDOR:     'vendor/corporate/'
        })
    }

    /**
     * @returns {SsoControlTokenLifeTime}   singleton
     */
    constructor ()
    {
        if ( SsoControlTokenLifeTime.exists )
            return SsoControlTokenLifeTime.instance

        SsoControlTokenLifeTime.instance   = this
        SsoControlTokenLifeTime.exists     = true

        this
            .#defineUrl()
            .#listen()

        return this
    }

    /**
     * defines an URL as target
     *
     * @returns {SsoControlTokenLifeTime}
     */
    #defineUrl ()
    {
        let current     = window.location
        let vendored    = current.href.match(SsoControlTokenLifeTime.REG_INTRA)
                            ? ''
                            : SsoControlTokenLifeTime.PATHS.VENDOR

        let splitted    = current.pathname.split('/').filter( (item) => item != '' )

        this.url        = `http://${current.host}/${splitted[0]}/${vendored}${SsoControlTokenLifeTime.PATHS.SERVICE}`

        return this
    }

    /**
     * listens to any click on page's body
     *
     * @returns {SsoControlTokenLifeTime}
     */
    #listen ()
    {
        document.addEventListener(
            SsoControlTokenLifeTime.CLICK,
            () => ( new SsoControlTokenLifeTime ).timeCheck()
        )

        return this
    }

    /**
     * fetches a certainty about session's validity
     */
    async timeCheck ()
    {
        const res = await fetch( this.url )

        try
        {
            if ( res.status == SsoControlTokenLifeTime.CODES.GONE )     throw new Error('session gone')

            const dta = await res.json()

            if ( ! res.ok )                                             throw new Error('nok result')
            if ( dta.length < 1 )                                       throw new Error('invalid length')
            if ( ! dta.hasOwnProperty(SsoControlTokenLifeTime.LEFT) )   throw new Error('inconsistent property')
            if ( parseInt( dta[ SsoControlTokenLifeTime.LEFT ] ) < 1 )  throw new Error('invalid token')
        }
        catch ( error )
        {
            console.log(error.message)

            this.#toRoot()
        }
    }

    /**
     * redirects to assumed index
     */
    #toRoot ()
    {
        let separator   = '/'
        let current     = URL.parse(location.href)
        let url         = current.pathname.split(separator).filter( (item) => item != '' )

        location.href = `${[ current.origin, url[0] ].join(separator)}${separator}`
    }
}
