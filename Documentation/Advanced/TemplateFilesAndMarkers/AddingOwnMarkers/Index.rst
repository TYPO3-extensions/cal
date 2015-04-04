.. _AddingOwnMarkers:

=============
Adding own markers
=============

.. include:: ../../../Includes.txt

If you want to add your own markers – filled by some own TypoScript –
all you need to add is

#. the marker itself to the corresponding model and

#. fill the marker with something meaningful

For example, if you want to add some fixed text to every output that
gets rendered in the list view as “odd” event, open event\_model.tmpl,
search for the marker named “TEMPLATE\_PHPICALENDAR\_EVENT\_LIST\_ODD”
and add your own marker “###MYSTATICTEXT###”.

Then, add the following snippet to your TS:

::

   plugin.tx_cal_controller.view.list.event.mystatictext = TEXT 
   plugin.tx_cal_controller.view.list.event.mystatictext.value = test123 

Please notice that this TypoScript will override any existing value
(e.g., coming from the database itself). To avoid this, use the
.current attribute (refer to Typo3 TS core documentation for details):

::

   plugin.tx_cal_controller.view.list.event.mystatictext = TEXT 
   plugin.tx_cal_controller.view.list.event.mystatictext { 
         # inject the value from the DB 
        current = 1 
           # or use a special field from the DB 
          #field = agenda 
       wrap = The content is here &gt;&gt;|&lt;&lt; 
   } 

