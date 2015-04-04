.. _Step3CreateAndConfigureRecordStorageFolder:

======================================================
Step 3: Create and Configure Record Storage Folder(s)
======================================================

.. include:: ../../Includes.txt

It is highly recommended to create a distinct sysfolder structure to store your calendar records.

For certain actions (e.g. notification or processing ics files), the extension needs some typoscript information to work properly. In the
pageTS of your event / calendar storage sysfolder you need to define

::

           options.tx_cal_controller.pageIDForPlugin = {page id where a calendar base plugin is
                   included / the typoscript options for plugin.tx_cal_controller can be found} 

This configuration is extremely helpful for users of the new
recurrence model as it allows that the recurrence index is generated
or updated whenever a recurring event is saved.

If you want to clear cached pages whenever a record is saved or
updated, please see

::

      TCEMAIN.clearCacheCmd = { ... }

in the TYPO3 core TSconfig reference.


