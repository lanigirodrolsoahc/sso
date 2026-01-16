export class Tooltip
{
    static get CLASS () {
        return Object.freeze({
            TIP:        'jsTip',
            TIPPED:     'jsTipped'
        })
    }

    /**
     * sets listener for Elements
     *
     * @param   {NodeListOf<Element>}   elems
     */
    static listenFor ( elems )
    {
        elems.forEach( element =>
        {
            element.addEventListener('mouseover', function (e)
            {
                Tooltip.tooltiped(element.dataset.title, e)
            })

            element.addEventListener('mouseout', function (e)
            {
                Tooltip.removeTips()
            })
        })
    }

    /**
     * sets listener once elements are created
     */
    static setListeners ()
    {
        Tooltip.listenFor( document.querySelectorAll(`.${Tooltip.CLASS.TIPPED}`) )
    }

    /**
     * manages tooltip for given event
     *
     * @param   {string}    text
     * @param   {Event}     event
     */
    static tooltiped ( text, event )
    {
        if ( text.length )
        {
            let div = document.createElement('div')
            let style = [
                'width: auto',
                'position: fixed',
                `top: ${event.clientY - 45}px`,
                `left: ${event.clientX - 45}px`,
                'pointer-events: none',
                'user-select: none',
                'text-align: center',
                'color: white',
                'padding: .5em',
                'border: 1px solid rgba(148, 53, 113, 1)',
                'background-color: rgba(148, 53, 113, 0.4)',
                'text-shadow: 1px 1px 2px rgba(148, 53, 113, 1)',
                'border-radius: .3em',
                'font-family: "Bradley Hand", cursive',
                'font-weight: bold',
                'z-index: 9999'
            ]

            div.appendChild( document.createTextNode(text) )
            div.setAttribute('class', Tooltip.CLASS.TIP)
            div.setAttribute('style', style.join('; '))

            document.body.appendChild(div)
        }
    }

    /**
     * removes any tooltip created with `this.tooltip`
     */
    static removeTips ()
    {
        document.querySelectorAll(`.${Tooltip.CLASS.TIP}`).forEach( elem =>
        {
            elem.remove()
        })
    }
}
