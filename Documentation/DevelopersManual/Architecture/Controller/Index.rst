.. _Controller:

=============
Controller
=============

.. include:: ../../../Includes.txt

The controller serves as the entry point into Calendar Base extension
from the outside world. In a typical TYPO3 extension, all requests
come in through the \_pi1 class. Within the Calendar Base extension,
these same requests are received by the controller. Based on conf
variables, as well as GET/POST vars, the controller is able to
determine what requests to make of the model. A URL such as http://mys
ite.com/index.php?id=17&tx\_cal\_controller[view]=event&tx\_cal\_contr
oller[type]=tx\_cal\_example&tx\_cal\_controller[uid]=18 would kick
off processing in the controller to request an event of type
tx\_cal\_example with uid 18 from the model. Once this event was
returned, it would be passed along to the single view.

Separated from the main controller are a model (back) controller and a
view (front) controller. These controllers serve to hide
implementation details from the main controller while also providing a
simplified interface. Each controller basically serves as a wrapper to
TYPO3 Services of the cal\_view or cal\_model type.

The model controller provides two methods: find() and findAll(). The
find() method is used to look up a specific event, based on its type
and uid. From the type, we can determine its service key, and get the
event's class from all of the calendar models available. One we have
the class, we can call its find() method to return the correct event
object and pass that object back to the main controller. The findAll()
method operates in a similar way, but rather than getting a single
event class, it uses TYPO3 Services to get all event classes. For each
event class, it calls the findAll() method to return every event, and
pass the compiled list of events back to the main controller.

The view controller acts in a very similar manner to the model
controller. Rather than providing find() and findAll() methods,
however, it provides draw() and drawSingle() methods. The draw()
method takes a view name and a list of events as arguments. From this
view name, we can perform a service lookup to get the object
representing that view and call its draw() method. HTML is returned
from this draw method and returned to the main controller. The
drawSingle() method behaves the same way as the draw() method but
takes a single event rather than many events.

