.. include:: Images.txt
.. _SchedulerExtension:

====================
Scheduler extension
====================

.. include:: ../../Includes.txt

The TYPO3 System extension “scheduler” (available since TYPO3 4.3) automates several tasks to keep your calendars up to date, to remind your users about upcoming events and index your recurring events.

When scheduler has been configured, external calendars such as those from Google Calendar are checked every 30 minutes to see if new events have been added. If new events have been entered in Google Calendar, they'll be imported into the Backend and displayed in the Frontend.

Scheduler also creates reminder emails for Frontend users who subscribe to an event. Once you have installed and configured the extension (please see the Manual included with the extension), you can set the following options in the Constant Editor (see Illustration 37):

|img-44| **Illustration 37: Setting Constants for the Scheduler Extension**

- Email Address: This is the email address that will appear in the From line of the reminder.

- Email Name: The name that will appear in the From line of the reminder.

- Email Organization: The name of the organization sending the reminder.

- Reminder Time: The number of minutes before an event that a reminder email will be generated. The default is 30 minutes. If you want a reminder sent out one day in advance, enter 1440.

|img-6| **Technical Stuff:** Setting up the Scheduler extension requires access to cron at the server level. If you have access to your hosting control panel, you can quickly set it up the Scheduler.

Once everything is set up, you can use the Scheduler tasks for updating calendars (“Updating external calendars (created by saving the calendar record)”) and/or to send Email reminders(“Sending reminder for events (created by saving the event record)”).
Please be aware that uncommon settings for the Scheduler cron job, Scheduler job frequency and Reminder time might lead to unwanted results such as sending out reminders too late.
Both tasks are generated for you when you save the according calendar/event record.

|img-45| **Illustration 37b: Creating a recurring Event Indexer Task for the Scheduler Extension**

The indexer task (“Indexer for recurring events”) is a task you can use to dynamically index your events (e.g. you want to keep your index table as small as possible).

You need to define:

- Page/Folder: The page or folder containing your cal events.

- Frontend Page with calendar plugin: A frontend page id with access to the typoscript configuration of cal (e.g. a page containing the cal plugin)

- Index start (YYYYMMDD or a dynamic value like: -3 month, -2 years): The date in time the indexer will start indexing.

- Index end (YYYYMMDD or a dynamic value like: +5 month, +4 years): The date in time the indexer will stop indexing.


