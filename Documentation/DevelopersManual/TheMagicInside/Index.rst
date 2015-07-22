.. _TheMagicInside:

===================
The 'Magic' Inside
===================

.. include:: ../../Includes.txt


The 'Magic' Inside
------------------

This section describes how cal works. How the core (base) interacts with the different services. It is necessary that the reader has knowledge about the normal typo3 extension structure.

The difference between a service and a classic extension is that the extension gets called after it has been inserted into a page. The caller expects some html content in return.

A service is different. The installation of a service is the same as a classic extension, except that in addition it registers itself in a global variable array. Through this array it can be called from any place at any time from within the system. All you have to know is the service type and key:

::

   $service = t3lib_div::makeInstanceService($key, $type);

Using this function you will obtain an instance of the service. Looking at the service configuration in ext\_localconf.php of the extension more details are reviled:

::

   /* Cal Example Concrete Model */
   t3lib_extMgm::addService($_EXTKEY, 'cal_event_model' /* sv type */, 'tx_cal_phpicalendar' /* sv key */,
     array(
             'title' => 'Cal PHPiCalendar Model', 'description' => '', 'subtype' => 'event',
             'available' => TRUE, 'priority' => 50, 'quality' => 50,
             'os' => '', 'exec' => '',
             'className' => 'TYPO3\\CMS\\Cal\\Service\\EventService'
           )
   );

The type: cal\_event\_model

The key: tx\_cal\_phpicalendar

A priority: 50

The class name: TYPO3\\CMS\\Cal\\Service\\EventService

As described an instance of a service can be obtained through calling the makeInstanceService($key, $type) function. This will return an instance of the class configured as class name at the configured path.
And if there are multiple services with the same type and key only the one with the highest priority will be returned. This way it is possible to install a service extension and overwrite a service which is already available in the system. Using this method, you don't have to reinstall the overwriting service after updating the original extension. The higher priority takes care of always returning the overwritten service.

Whats going on inside cal:

The calendar base extension is a normal extension as many other typo3 extension's. It has a main() function and will return the requested content. The extension folder structure has some slight modifications: the standard pi1 folder is called 'controller' and besides the controller there is also a 'model' and 'view' folder.

The main() function is located in the Controller/Controller class. If this class gets called it instantiates two other classes: ModelController and ViewController. For security reasons we have a RightsController too. But this RightsController has been implemented as a service to make it flexible.

After the RightsController has determined what view should be rendered the according function inside the controller is called. Lets say the 'day' view should be rendered, than the day() function will be called.

First inside the day() function, like most of the other functions too, the ModelController object is used to retrieve all events to be rendered. The ModelController object has a special wrapper-function for each view. For the day view there is the

::

   findEventsForDay($timestamp, $type='', $pidList='') .

We have to hand over a timestamp for that day. Inside the function a starttime and endtime will be calculated and finally the

::

   findAllWithin('cal_event_model', $starttime, $endtime, $subtype, 'event', $pidList);

function will be called. The findAllWithin function will search for according services:

::

    t3lib_div::makeInstanceService($serviceName, $type,$serviceChain).

Here we are using a third parameter with the makeInstanceService function: $serviceChain. Using this parameter we now can iterate through all service with the same name and type. This is especially useful if you want to implement other model-service to include other records than calendar\_events into the calendar, e.g. tt\_news (cal\_tt\_news\_service).

To make sure the service gets the configuration and can also access other parts from within the calendar base extension each service has to have a setController(&$controller);function on order to place a reference to the controller inside the service.

After retrieving a service we call the findAllWithin() function on the service. Therefore this service has to have such a function, thats why all event model-services should extend the tx\_cal\_model class. Now, the service should return an array with the found events. This array has to follow a certain structure:

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         KEYWORD: legend

   b
         {calendarId}

   c
         {CalendarTitle}

   d
         { categoryId}

   e
         {categoryHeaderStyle}

   f
         {Array of category properties}


.. container:: table-row

   a
         { categoryId}

   b
         {categoryHeaderStyle}

   c
         {Array of category properties}


.. container:: table-row

   a
         {calendarId}

   b
         {CalendarTitle}

   c
         { categoryId}

   d
         {categoryHeaderStyle}

   e
         {Array of category properties}


.. container:: table-row

   a
         { categoryId}

   b
         {categoryHeaderStyle}

   c
         {Array of category properties}


.. container:: table-row

   a
         {date format:YYYMMDD}

   b
         {time format: HHMM}

   c
         {event uid->eventObject}


.. container:: table-row

   a
         {event uid->eventObject}


.. container:: table-row

   a
         {time format: HHMM}

   b
         {event uid->eventObject}


.. container:: table-row

   a
         {date format:YYYMMDD}

   b
         {time format: HHMM}

   c
         {event uid->eventObject}


.. ###### END~OF~TABLE ######

......

All returned arrays from the different services will get merged and sorted ascending by starttime and returned back to the main controller.

Now, the day() function passes this array to the ViewController object and calls the according rendering function: drawDay(). The ViewController does almost the same as the ModelController: it searches for a view-service. But this time it takes the first one it can find and calls the drawDay() function on the view-service. The view-service has now the control about what is being rendered and will return the result back to the ViewController which will pass it back to the main-Controller, which will wrap the content in base class and finally return the calendar html code.

And thats all about the 'magic' inside calendar base :)


