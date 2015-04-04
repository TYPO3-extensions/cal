.. _Ics:

=============
ICS
=============

.. include:: ../../../Includes.txt

ICS is a special view and has its own page:

::

   ics = PAGE
   ics {
           typeNum = 150
           10 < plugin.tx_cal_controller
   ics.10

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         view
   
   Data type
         Array
   
   Description
         Clearing not needed typoscript objects, to reduce the overall memory
         consumption of the typoscript
   
   Default
         day >
         
         week >
         
         month >
         
         year >
         
         event >
         
         calendar >
         
         category >
         
         list >
         
         freeAndBusy >
         
         other >
         
         search >
         
         search\_event >
         
         search\_location >
         
         search\_organizer >
         
         admin >
         
         location >
         
         organizer >
         
         rss >
         
         create\_event >
         
         edit\_event >
         
         confirm\_event >
         
         delete\_event >
         
         create\_calendar >
         
         edit\_calendar >
         
         confirm\_calendar >
         
         delete\_calendar >
         
         create\_category >
         
         edit\_category >
         
         confirm\_category >
         
         delete\_category >
         
         create\_location >
         
         edit\_location >
         
         confirm\_location >
         
         delete\_location >
         
         create\_organizer >
         
         edit\_organizer >
         
         confirm\_organizer >
         
         delete\_organizer >
         
         translation >


.. container:: table-row

   Property
         recursive
   
   Data type
         Integer +
   
   Description
         Include X levels underneath the defined pids
   
   Default
         1


.. container:: table-row

   Property
         rights
   
   Data type
         Array
   
   Description
         Clearing not needed typoscript objects, to reduce the overall memory
         consumption of the typoscript
   
   Default
         create >
         
         edit >
         
         delete >


.. container:: table-row

   Property
         pages
   
   Data type
         String / CSV
   
   Description
         Comma separated list of pids to get the information (records) for the
         calendar from
   
   Default
         {$plugin.tx\_cal\_controller.pidList}


.. container:: table-row

   Property
         view.allowedViews
   
   Data type
         String / CSV
   
   Description
         The ics view only allows the ics views
   
   Default
         ics,single\_ics


.. container:: table-row

   Property
         view.ics.calUid
   
   Data type
         String
   
   Description
         A unique id to identify the calendar
   
   Default
         {$plugin.tx\_cal\_controller.view.ics.calUid}


.. container:: table-row

   Property
         view.ics.maxDate
   
   Data type
         Integer / YYYYMMDD
   
   Description
         The maximum date for recurring events
   
   Default
         20090101


.. container:: table-row

   Property
         view.ics.event.description
   
   Data type
         cObj
   
   Description
         Disabling parseFunc
         
         parseFunc >
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:ics.10]

Hint: If you want to convert your output to UTF-8, make use of this
example:

::

   #stdWraps, e.g. to convert Text to utf8
     10.view.ics.event.summary {
       csConv = utf-8
     }
     10.view.ics.event.description {
       csConv = utf-8
     }
     10.view.ics.event.location {
       csConv = utf-8
     }
     10.view.ics.event.category {
       csConv = utf-8
     }
   
   ics.10.view.single_ics.event < ics.10.ics.event

ics.config

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         disableAllHeaderCode
   
   Data type
         Boolean
   
   Description
         See TSRef
   
   Default
         1


.. container:: table-row

   Property
         additionalHeaders
   
   Data type
         String
   
   Description
         See TSRef
   
   Default
         Content-type:application/text


.. container:: table-row

   Property
         xhtml\_cleaning
   
   Data type
         Boolean
   
   Description
         See TSRef
   
   Default
         0


.. container:: table-row

   Property
         admPanel
   
   Data type
         Boolean
   
   Description
         See TSRef
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:ics.config]

