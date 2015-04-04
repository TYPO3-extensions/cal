.. _Localization:

=============
Localization
=============

.. include:: ../../Includes.txt

Calendar Base supports localization using the same configuration as
TYPO3 and other extensions. The most critical and often missed step in
this location is to set the appropriate locale in Typoscript. This
locale information is used to generate day and month names in the
correct language.

#. Open `http://www.YOUR-SITE-URL.com/typo3conf/ext/cal/misc/locales.php
   <http://www.YOUR-SITE-URL.com/typo3conf/ext/cal/misc/locales.php>`_ in
   your browser. This will show you a listing of all the locales
   available on your system. Copy the the one that best matches your
   language and/or location.

#. Within the Typoscript Setup for your page, add

::

   config.locale_all = MY_LOCALE 

where MY\_LOCALE is the value you copied in step 1.

#. View the Frontend Calendar pages. Day and month names should now be
   shown in the correct language.


