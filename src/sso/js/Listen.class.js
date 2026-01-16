import { Forms }        from './Forms.class.js'
import { Alerts }       from './Alerts.class.js'
import { Animations }   from './Animations.class.js'

export class Listen
{
    static get INNER_FORMS  () { return 'formInnerService' }
    static get MENU         () { return 'menu' }
    static get PWD_EYES     () { return 'pwdEyed' }

    constructor ()
    {
        this.forms  = new Forms()
        this.alerts = new Alerts()
    }

    /**
     * listens to forms submission
     *
     * @returns {Listen}
     */
    async formsSubmit ()
    {
        Listen.listenForForms( document.querySelectorAll( `.${Listen.INNER_FORMS}` ) )

        return this
    }

    /**
     * hides content taking a lot of space
     *
     * @returns {Listen}
     */
    hidableContent ()
    {
        document.querySelectorAll( `.${Animations.CLASSES.HIDING} div` ).forEach( item =>
        {
            item.addEventListener('click', function (e)
            {
                e.target.closest( `.${Animations.CLASSES.HIDING}` ).querySelectorAll( `.${Animations.CLASSES.HIDABLE}` ).forEach( child =>
                {
                    child.classList.toggle(Animations.CLASSES.HIDED)

                })
            })
        })

        return this
    }

    /**
     * sets listener for Elements
     *
     * @param   {NodeListOf<Element>}   elems
     */
    static async listenForForms ( elems )
    {
        elems.forEach( elem =>
        {
            const obj = new Listen()

            elem.addEventListener('submit', async function (event)
            {
                event.preventDefault()

                Loader.show()

                await obj.forms.sends(elem)

                if ( obj.forms.succeeded() ) obj.forms.reloading( elem.dataset.redirected ?? false )
                else if ( obj.forms.isRefusal() ) location.href = obj.forms.messaged
                else
                {
                    Loader.hide()

                    obj.alerts
                        .isOk(false)
                        .view( obj.forms.msg() )
                }
            }
            .bind(obj))
        })
    }

    /**
     * creates loader on each menu link
     *
     * @returns {Listen}
     */
    menuClick ()
    {
        document.querySelectorAll( `.${Listen.MENU} a` ).forEach( link =>
        {
            link.addEventListener('click', () => Loader.show() )
        })

        return this
    }

    /**
     * shows passwords
     *
     * @returns {Listen}
     */
    pwdDisplay ()
    {
        document.querySelectorAll( `.${Listen.PWD_EYES} > div ` ).forEach( eye =>
        {
            eye.addEventListener('click', function (e)
            {
                let elem    = e.target.closest(`.${Listen.PWD_EYES}`).querySelector('input')
                let pwd     = 'password'

                elem.type = elem.type == pwd ? 'text' : pwd
            })
        })

        return this
    }

    /**
     * prevents `Enter` key submission
     *
     * @param   {NodeListOf<Element>}   [elements]
     *
     * @returns {Listen}
     */
    noQuickSubmission ( elements )
    {
        if ( ! elements ) elements = document.querySelectorAll('form')

        elements.forEach( element =>
        {
            element.onkeydown = function ( key )
            {
                if ( key.key == 'Enter' ) key.preventDefault()
            }
        })

        return this
    }
}
