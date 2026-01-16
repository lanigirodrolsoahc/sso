export class Alerts
{
    static get ALERTS_CLASS () { return 'customAlerts' }

    static get ELEMS () {
        return Object.freeze({
            CLASS:  'class',
            DIAL:   'dialog',
            DIV:    'div'
        })
    }

    static get ICONS () {
        return Object.freeze({
            HAPPY: '😁',
            HATER: '🤬'
        })
    }

    static get TYPES () {
        return Object.freeze({
            FADING:     'fading',
            USER_CLOSE: 'userClose'
        })
    }

    constructor ()
    {
        this.positive   = true
        this.typed      = Alerts.TYPES.USER_CLOSE
    }

    /**
     * determines if alert should be a happy one
     *
     * @param   {boolean}   is
     *
     * @returns {Alerts}
     */
    isOk ( is )
    {
        this.positive = is

        return this
    }

    /**
     * removes all previously created alerts
     *
     * @returns {Alerts}
     */
    #remove ()
    {
        document.querySelectorAll(`.${Alerts.ALERTS_CLASS}`).forEach( elem =>
        {
            elem.remove()
        })

        return this
    }

    /**
     * sets modal type, default would be user closing
     *
     * @param   {string}    type
     *
     * @returns {Alerts}
     */
    typing ( type )
    {
        this.typed = type

        return this
    }

    /**
     * displays an alert
     *
     * @param   {string}    msg
     *
     * @returns {Alerts}
     */
    view ( msg )
    {
        this.#remove()

        let modal   = document.createElement(Alerts.ELEMS.DIAL)
        let holder  = document.createElement(Alerts.ELEMS.DIV)
        let icon    = document.createElement(Alerts.ELEMS.DIV)
        let text    = document.createElement(Alerts.ELEMS.DIV)

        modal.appendChild(holder)
        holder.appendChild(icon)
        holder.appendChild(text)
        icon.appendChild( document.createTextNode(this.positive ? Alerts.ICONS.HAPPY : Alerts.ICONS.HATER ) )
        text.appendChild( document.createTextNode(msg) )

        modal.setAttribute(Alerts.ELEMS.CLASS, Alerts.ALERTS_CLASS)

        document.body.appendChild(modal)

        modal.showModal()

        document.querySelectorAll(`.${Alerts.ALERTS_CLASS} > ${Alerts.ELEMS.DIV}`).forEach( elem =>
        {
            if ( this.typed == Alerts.TYPES.USER_CLOSE )
                elem.addEventListener('click', () => { elem.closest(Alerts.ELEMS.DIAL).close() } )
            else
                setTimeout( () => { elem.closest(Alerts.ELEMS.DIAL).close() }, 2000)

        })

        return this
    }
}
