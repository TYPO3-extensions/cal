.. _Notification:

=============
Notification
=============

.. include:: ../../Includes.txt

In order to send out emails, it is important that you configure the constants described in the next chapter (Scheduler extension). If your event records are located in a Sysfolder, you also have to define in its pageTS where to find your Typoscript configuration:

::

      options.tx_cal_controller.pageIDForPlugin = {PID}


