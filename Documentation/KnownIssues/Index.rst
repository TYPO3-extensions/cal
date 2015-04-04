.. _KnownIssues:

=============
Known Issues
=============

.. include:: ../Includes.txt

Due to limitations in the TYPO3 SQL Parser, one database change in
version 1.3.0 cannot be applied cleanly from the Extension Manager.
The tx\_cal\_fe\_user\_event\_monitor\_mm table adds a uid field and
the combination of auto\_increment and PRIMARY KEY causes issues if
you already have data in this table. Instead of using TYPO3 to create
this field, you can execute the following SQL.

::

   ALTER TABLE `tx_cal_fe_user_event_monitor_mm` CHANGE `uid` `uid` int(11) UNSIGNED NOT NULL  auto_increment PRIMARY KEY;


