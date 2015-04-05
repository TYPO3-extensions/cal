.. include:: Images.txt
.. _CalendarBaseConfiguration:

============================
Calendar Base Configuration
============================

.. include:: ../../Includes.txt

The Calendar Base Configuration form can be accessed through the
Extension Manager Module. Refer to Illustration 48. Select the Loaded
Extensions options from the drop-down Menu and then click on the
Calendar Base link (the title of the Calendar Base extension). Ensure
the Information option appears in the drop-down box ( **1** ).

|img-55| |img-56|

**Illustration 48: The Calendar Base Configuration Form**

- Do not use Tab-Dividers: Places all the fields on along singleform
  when creating a new form rather than sorting and displaying them
  behind tabs.

- **Hide location textfield:** Enabling this option removes the Location
  text field in the Event form.

- Hide organizer textfield: Enabling this option removes the organizer
  text field in the Event form.

- **Select the cal location data model:** Depending on whether or not
  you installed or are using any of the suggested extensions, you can
  choose your location data model here.

- **Select the cal organizer data model:** Depending on whether or not
  you installed or are using any of the suggested extensions, you can
  choose your organizer data model here.

- Nested Categories: Selecting this option allows the display of
  categories to be nested in the Backend, similar to the tt\_news
  category tree.

- **Select the default height of categories:** Sets the textbox height
  in the Categories' record form. The default is 280.

- **Show time values in BE lists:** Toggle on/off if you want to show
  the start time of regular events in the list view.

- **Enable New Recurring Event Interface:** If you are running a version
  of TYPO3 older than 4.1, you will likely want to disable this option.
  Otherwise, the new Recurring Event Interface is a particularly user-
  friendly interface.

- **Use Teaser Field:** Adds an alternative description to the Display
  List View.

- **Use Record Selector:** Displays the record selector instead of
  dropdown boxes for fe\_users and fe\_groups selection in backend.

- **Use New Recurring Event Model:** The New Recurring Event Model will
  use an index table for recurring events, reducing memory consumption
  and improving performance. Please see chapter “Using New Recurring
  Event Model / Cal Indexer” for more details.

|img-4| **Note:** The old recurring event model will become deprecated in the
  next release of the extension


