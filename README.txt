CALENDAR BASE (cal)

GOALS
==============================================================================
The Calendar Base extension is designed to be a flexible framework that other
extensions can plug into to display calendar data in a common format and
location, without in any way limiting the functionality available for these
extensions.

To this end, the overall architecture relies heavily on the 
Model-View-Controller (MVC) pattern to separate the layout from the data and
on TYPO3 Services to provide pluggable models and views.

ARCHITECTURE
==============================================================================

  Controller 
  ----------------------------------------------------------------------------
  The controller serves as the entry point into Calendar Base extension from
  the outside world.  In a typical TYPO3 extension, all requests come in
  through the _pi1 class.  Within the Calendar Base extension, these same
  requests are received by the controller.  Based on conf variables, as well
  as GET/POST vars, the controller is able to determine what requests to make
  of the model.  A URL such as 
  http://mysite.com/index.php?id=17&view=single&type=tx_cal_example&uid=18 
  would kick off processing in the controller to request an event of type
  tx_cal_example with uid 18 from the model.  Once this event was returned,
  it would be passed along to the single view.

  Separated from the main controller are a model (back) controller and a view
  (front) controller.  These controllers serve to hide implementation details
  from the main controller while also providing a simplified interface.  Each
  controller basically serves as a wrapper to TYPO3 Services of the cal_view
  or cal_model type.

  The model controller provides two methods: find() and findAll().  The find ()
  method is used to look up a specific event, based on its type and uid.
  From the type, we can determine its service key, and get the event's class
  from all of the calendar models available.  One we have the class, we can
  call its find() method to return the correct event object and pass that
  object back to the main controller.  The findAll() method operates in a 
  similar way, but rather than getting a single event class, it uses
  TYPO3 Services to get all event classes.  For each event class, it calls
  the findAll() method to return every event, and pass the compiled list of
  events back to the main controller.

  The view controller acts in a very similar manner to the model controller.
  Rather than providing find() and findAll() methods, however, it provides
  draw() and drawSingle() methods.  The draw() method takes a view name and
  a list of events as arguments.  From this view name, we can perform a
  service lookup to get the object representing that view and call its draw()
  method.  HTML is returned from this draw method and returned to the
  main controller.  The drawSingle() method behaves the same way as the draw()
  method but takes a single event rather than many events.

  Model 
  ----------------------------------------------------------------------------
  The model is based on an object representation of the iCal data format and 
  relies heavily on TYPO3 services.  At the top level is a parent class that
  defines many default fields and methods related to the iCal data format.
  This class extends the service class as well, by providing find() and 
  findAll() methods.  These methods are not meant to be called directly in
  this class however, as it is somewhat of abstract class.  Eventually,
  functionality for dealing with recurring events and other calendar features
  will be added here but for the time being its primarily limited to accessor
  methods.

  Sitting beneath this top level data model are the many concrete models that
  implement its data type.  The only requirements on these models are that
  they implement find() and findAll() methods and map data into the required
  fields of the top level data model.  How and where data storage occurs (in
  a database or otherwise) is the decision of the data model in question.
  While the top level data model may provide default functionality for common
  functionlity, the concrete models are in no way limited by this as they can
  always override methods from a parent class.  In addition, concrete models
  may provide additional fields to augment the data model as well, so the
  parent class only serves as a starting point for shared functionality.


  View
  ----------------------------------------------------------------------------
  The view is a service object much like the model, in that it must provide
  a very limited set of functionality in order to interact with the larger
  system.  View objects, depending on service type, must provide either a
  draw() or a drawSingle() method.  Views that handle multiple events, such as
  a list view, a month view, or a day view, should provide the draw() method
  while a single view should provide the drawSingle() method.

  Views for multiple events must be cautious to operate on only the base data
  format, since different events may come from different types that provide
  special views.  Views for single events, however, can utilize all the custom
  fields available in their corresponding model, as there is a guarantee that
  the model and view match.

  The end result of this is shared views for multiple events that can show
  data from many different models with unique single views that are tied
  closely to the custom features of a concrete model.




