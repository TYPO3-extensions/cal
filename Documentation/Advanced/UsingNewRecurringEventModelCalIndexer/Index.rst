.. include:: Images.txt
.. _calIndexer:

=============
Using New Recurring Event Model / Cal Indexer
=============

.. include:: ../../Includes.txt

The New Recurring Event Model uses an index table for recurring
events, reducing memory consumption and improving performance.

The new recurring model uses a “parent”-event for the first instance
of the event and creates "child"-events for the recurring instances.
The advantage is, that the "child"-events have a reference to the
"parent"-event and don't need to store the event information anymore,
just the date they should appear.

Additionally this new model creates the recurring dates during
creation of the “parent” event while saving or updating an event.
Those dates are stored in a separate table. During page rendering, the
extension will search for instances of a given recurring event in this
index, instead of recalculating them over and over again.

Due to the fact, that it is neither possible nor useful to create
recurring dates for the index endlessly, you have to define a
recurrenceStart and recurrenceEnd. They can be defined as a date with
the format YYYYMMDD in the extension options.

If you store your events in a separate sysfolder, please take care
ofStep 3: Create and Configure Record Storage Folder(s)as this is
required to get the basic configuration.

|img-57|
**Illustration 49: Cal Indexer for new recurrence model**

In case that you either change something to the basic recurrence
settings such as changing start / end dates or that you missed setting
the correct pageTS settings before saving your event(s), you can use
the cal indexer. This indexer cleans the internal recurrence table and
generates recurring dates for all recurring events with the current
start / end restrictions.

You need to provide the indexer with valid frontend page that is used
to store a calendar plugin or that provides the basic TS settings.

**Please note:** The cal indexer is a standard backend module
delivered with cal. It is located in the admin tools section and is
shown only if the new Recurring Event Model is activated. If you do
not see the module, please reload your backend page.


