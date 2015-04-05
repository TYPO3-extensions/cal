.. include:: Images.txt
.. _CalendarBaseGeneralConstantEditorForm:

=============================================
Calendar Base (General) Constant Editor Form
=============================================

.. include:: ../../../Includes.txt

|img-52|
**Illustration 45: Constant Editor (General)**

Refer to Illustration 45. There are a variety of options available though this form. You will want to read through the list carefully and make changes as needed for your particular configuration.Dimensions, widths, heights, pixels

- **Event Image Max Width:** Sets the max width of an image in Event View.

- **Event Image Max Height:** Sets the max height of an image in Event View.

- **List Image Max Width:** Sets the max width of an image in List View.

- **List Image Max Height:** Sets the max height of an image in List View.

Others

- **Enable Frontend Editing:** Enables Frontend Editing for admin and
  specified user groups.

- **Frontend Calendar Admin Users:** The ID numbers of Website Users who
  have been granted Administrative Frontend Editing privileges.

- **Frontend Calendar Admin Groups:** You can create a User Group and
  populate it with Website Users and put it's ID number here rather than
  listing each user individually in the above field.

- **Email Address:** This is the email address that will appear in the
  From line of reminder emails.

- **Email Name:** This is the name that will appear in the From line of
  reminder emails.

- **Email Organization:** This is the organization's name that will
  appear in the From line of reminder emails.

- **Reminder Time:** This allows you to set how many minutes in advance
  reminder emails are sent out to those who are subscribed to events.

- **Subscription Page:** If you are allowing users to subscribe to
  events, this is the Page ID (PID) where the Subscription Manager is
  viewed. Typically, you will use the Calendar Base page and select the
  Subscription Manager as an Allowed View in the Flexform.

- **Enable the Google Map (wec\_map need...):** Enables the WEC Map
  Extension to work with the Location Views and displays a Google Map
  for all Location Records.

- **Page ID that Frontend-Created Recor...:** The Page ID (PID) that new
  Frontend created records should be saved on. This is typically the
  Cal-Base Storage page, if you created one. If left blank, the records
  will be saved on whatever page the active plugin is installed.

- **PID List for ICS and XML View:** This is the Page ID (PID) where
  your event records are stored. If records are stored on multiple
  pages, all PIDs must be included in a comma separated list. This
  constant must be set if you want RSS and ICS output to work.

