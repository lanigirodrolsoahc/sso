import { Listen }   from './Listen.class.js'
import { Tooltip }  from './Tooltip.class.js'
import { Ear }      from './Ear.class.js'
import { Pager }    from './Pager.class.js'
import { Sorter }   from './Sorter.class.js'
import { Searcher } from './Searcher.class.js'
import { Users }    from './Users.class.js'

document.addEventListener('DOMContentLoaded', function ()
{
    ( new Listen() )
        .noQuickSubmission()
        .menuClick()
        .hidableContent()
        .pwdDisplay()
        .formsSubmit()

    Tooltip.setListeners()

    Ear.listen()
    Ear.toGet()
    Pager.listen()
    Sorter.listen()
    Searcher.listen()
    Users.listen()
})
