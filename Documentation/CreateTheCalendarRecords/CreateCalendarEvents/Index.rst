.. include:: Images.txt
.. _CreateCalendarEvents:

=============
Create Calendar Events
=============

.. include:: ../../Includes.txt

If you have imported events by creating an External Calendar Type or
Include ICS File Calendar Type, you already have Event records created
for you. However, if you haven't imported Event records, or if you
want to add additional Event records, you can do so manually from the
Backend.

There are six different tabs in the Calendar Event's form. Complete
the fields as outlined below and then save and close your work.

|img-28|
**Illustration 21: The Event Record: General Tab**

The General Tab Refer to Illustration 21.

- **Event Type:** The default is  **Event with Description** (shown).
  You can also select  **Shortcut to Page** or  **Link to External URL**
  to direct a Frontend user to another page when they click on the Event
  link in the calendar.

- **Title:** This is a required field. Shorter event titles are
  preferable because they show up better in the default Month View
  calendar.

- **Start:** Designates the first day and time of the event. If the
  event is an  **Allday Event** , the Start and End Time fields will be
  omitted.

- **End:** Designates the last day and time of the event. For recurring
  events, this is the end time for the first recurrence of the event
  (typically the same day), not when the event should stop recurring
  (typically several months in the future).

- **Calendar:** If you have created multiple Calendar records, you can
  choose which one you want to associate the event with. The options
  you've set for the Calendar record will determine which Frontend users
  will have access to this event. (See :ref:`UsingMultipleCalendars` in the Advanced section
  for more information.)

- **Category:** Choose which Categories, if any, you would like to associate
  the Event record with. The options you've set for the Category and its
  associated Calendar record will determine which Frontend users will
  have access to this event. (See :ref:`UsingMultipleCalendars` in the Advanced section
  for more information.)

- **Teaser:** This is an optional field that is only available when the
  Use Teaser Field option is selected in the Configuration form (see
  :ref:`CalendarBaseConfiguration`). Allows a
  shortened or different version of the event's description.

- **Description:** This is the full description of the event that is
  available in the Event view.


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   TheRecurrenceTab/Index
   TheLocationTab/Index
   TheOrganizerTab/Index
   TheFilesTab/Index
   TheOtherTab/Index

