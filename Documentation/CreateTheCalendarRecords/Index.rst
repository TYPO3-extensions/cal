.. include:: Images.txt
.. _CreateTheCalendarRecords:

============================
Create the Calendar Records
============================

.. include:: ../Includes.txt

To use the Calendar Base extension, you will need to create at least two different records. You must first create a Calendar record. A Calendar record is the calendar that you will associate events with. For instance, you could create a Church Calendar record and associate all of your church events with it. You could also create a Staff Calendar and associate staff appointments and meetings on it. There are multiple ways to display two calendars. You could put them on
different pages, multiple calendars could share a single page and you could optionally hide the Staff Calendar unless a staff member was logged in, or you could have the two calendar's events share a single calendar and restrict which events appear based on different logins. (For more information on how configure a page using multiple calendars, see :ref:`UsingMultipleCalendars` in the Advanced section.)

|img-3| Tip: By default, TYPO3 generates dates in the Backend in European format (2007-04-23) and the 24-hour clock (18:00). You can change the format for both of these by using the Install Module, clicking on the All Configuration link, and scrolling down to the ddmmyy field. Change that setting to m-d-y. Then scroll down (right below the ddmmyy field)
to the hhmm field. Change that setting to g:i a (g colon i space a). Then scroll to the bottom of the page and Update your changes. Your Backend will be more user-friendly.


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   CreateACalendarRecord/Index
   CreateCalendarCategories/Index
   CreateCalendarEvents/Index
   ExceptionEventRecord/Index
   ExceptionEventGroupsRecord/Index
   DeviationEventRecord/Index
   CalendarEventOrganizerRecord/Index
   LocationsRecord/Index

