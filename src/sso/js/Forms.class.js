export class Forms
{
    static get CODE () {
        return Object.freeze({
            ACCEPTED:       201,
            BAD_REQUEST:    400,
            FORBIDDEN:      403,
            OK:             200,
            SERVER_ERROR:   500
        })
    }

    static get METHODS () {
        return Object.freeze({
            GET:    'GET'
        })
    }

    static get MSG ()       { return 'msg' }
    static get MSG_OK ()    { return 'ok' }

    constructor ()
    {
        this.error
        this.expected
        this.form
        this.json
        this.messaged
        this.returned
        this.successes = [
            Forms.CODE.ACCEPTED,
            Forms.CODE.OK
        ]
    }

    /**
     * initializes parameters
     *
     * @param   {Element}   form
     */
    #init ( form )
    {
        this.error      = false
        this.expected   = form.attributes['expected'] ?? Forms.CODE.OK
        this.form       = form
        this.json       = false
        this.messaged   = false
        this.returned   = false

        return this
    }

    /**
     * check if returned code is a 403 refusal (must renew password)
     *
     * @returns {boolean}
     */
    isRefusal ()
    {
        return this.returned === Forms.CODE.FORBIDDEN
    }

    /**
     * gets returned message
     *
     * @returns {string|false}
     */
    msg ()
    {
        return this.error !== false ? this.error : this.messaged
    }


    /**
     * refreshes current page without submitting forms agains
     *
     * @param   {string}   [target]   for relocation
     */
    reloading ( target )
    {
        location.href = target || location.href
    }

    /**
     * sends form, awaits response
     *
     * @param   {Element}   form
     */
    async sends ( form )
    {
        try
        {
            this.#init(form)

            const param = {method: form.attributes['method']?.value}

            if ( param.method !== Forms.METHODS.GET ) param.body = new FormData(form)

            const response = await fetch( form.attributes['action']?.value, param )

            this.json       = await response.json()
            this.returned   = response.status
            this.messaged   = this.json[ Forms.MSG ] ?? false
        }
        catch ( e ) { this.error = e }
    }

    /**
     * tells if last operation succeeded
     *
     * @returns {boolean}
     */
    succeeded ()
    {
        return ! this.error && this.successes.indexOf( this.returned ) > -1
    }
}
