.. _TypoScriptReferenceSetup:

============================
TypoScript Reference: setup
============================

.. include:: ../../Includes.txt

Important: TypoScript values are overruled by the flexform value!

plugin.tx\_cal\_controller

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:

   Data type
         Data type:

   Description
         Description:

   Default
         Default:


.. container:: table-row

   Property
         calendarName

   Data type
         String

   Description
         Name of the calendaralso: Flexform

   Default
         My Calendar


.. container:: table-row

   Property
         pages

   Data type
         String / CSV

   Description
         List of startingpoints gets merged with pidList

   Default


.. container:: table-row

   Property
         recursive

   Data type
         Integer +

   Description
         An integer >=0 telling how deep to dig for pids under each entry

   Default
         0


.. container:: table-row

   Property
         pidList

   Data type
         String / CSV

   Description
         List of startingpoints gets merged with pages

   Default


.. container:: table-row

   Property
         language

   Data type
         String

   Description
         Overwrites the current language

   Default


.. container:: table-row

   Property
         startLinkRange

   Data type
         String

   Description
         Views before that date will get a no\_follow meta tag

   Default
         -5 month


.. container:: table-row

   Property
         endLinkRange

   Data type
         String

   Description
         Views after that date will get a no\_follow meta tag

   Default
         +5 month


.. container:: table-row

   Property
         clear\_anyway

   Data type
         Boolean

   Description
         Clears the cache

   Default
         0


.. container:: table-row

   Property
         additionalWrapperClasses

   Data type
         String

   Description
         Add your own classes to the wrapping div of the plugin

   Default


.. container:: table-row

   Property
         allowSubscribe

   Data type
         Boolean

   Description
         Allows visitors to subscribe to an event and be notified when it
         changes.

         also: Flexform

   Default
         1


.. container:: table-row

   Property
         subscribeFeUser

   Data type
         Boolean

   Description
         Allows registered frontend users to subscribe based on the email
         address in their account.

         also: Flexform

   Default
         0


.. container:: table-row

   Property
         subscribeWithCaptcha

   Data type
         Boolean

   Description
         Enables CAPTCHA-based validation before a visitor can subscribe to an
         event. Requires CAPTCHA Library extension.

         also: Flexform

         also: `http://typo3.org/extensions/repository/search/captcha/
         <http://typo3.org/extensions/repository/search/captcha/>`_

   Default
         0


.. container:: table-row

   Property
         additionalWrapperClasses

   Data type
         String

   Description
         Add your own classes to the wrapping div of the plugin

   Default


.. container:: table-row

   Property
         noWrapInBaseClass

   Data type
         Boolean

   Description
         Set this to 1 if you do not want the plugin to have a wrapping div tag

   Default
         0


.. container:: table-row

   Property
         sessionPiVars

   Data type
         String / CSV

   Description
         List of csv of piVars, which should not appear in the url, but should
         be stored inside the users session. Attention: this works only, if the
         piVar is the same through a whole single page

   Default
         page\_id


.. container:: table-row

   Property
         showRecordsWithoutDefaultTranslation

   Data type
         Boolean

   Description
         Displays translated records, even if there is no default translation.
         Useful if an event should only show up for one language.

   Default
         0


.. container:: table-row

   Property
         date\_stdWrap

   Data type
         stdWrap

   Description
         stdWrap that is applied to every date value coming from cal. You can
         use this f.e. to do some charset conversion when no locale with the
         correct charset is available on the webserver.

         Example for charset conversion, where the locale of the server uses
         iso-8859-5, but your rendering charset is UTF-8.

         date\_stdWrap {

         csConv = iso-8859-5

         }

         Note: it's recommended to install and use a locale with the correct
         charset if possible.

   Default


.. container:: table-row

   Property
         clearPiVars

   Data type
         String / CSV

   Description
         Comma separated list of piVars, that should be cleared by any means
         for this instance of cal. Mostly usefull for cal instances generated
         with TS or inside flexformTS.

         Special Keyword: all

         When you set this value to the keyword “all”, then cal will not use
         any piVars coming from GPvars. \_DEFAULT\_PI\_VARS will still be
         processed as well as session stored vars.

   Default


.. container:: table-row

   Property
         dontListenToPiVars

   Data type
         Boolean

   Description
         If this is set, cal will not use any piVars or session stored vars for
         rendering. It will only use preconfigured values. This might come
         handy if you have several instances of cal on one page and don't want
         a certain instance to react on any other piVar.

   Default


.. container:: table-row

   Property
         dontListenToFlexForm

   Data type
         Boolean

   Description
         If set, any options configured in the Flexform will be ignored. This
         means Calendar Base will only be configured via Typoscript.

   Default


.. container:: table-row

   Property
         dontListenToFlexForm.<sheet>.<field>

   Data type
         Boolean

   Description
         If set, the configuration option for <field> within <sheet> will be
         ignored.

         For example, Flexform definitions for the day start time can be
         ignored with:

         dontListenToFlexForm.day.dayStart = 1

   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller]

plugin.tx\_cal\_controller.dateParserConf

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:

   Data type
         Data type:

   Description
         Description:

   Default
         Default:


.. container:: table-row

   Property
         USmode

   Data type
         Boolean

   Description
         Changes the order of day and month

   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.dateParserConf]

plugin.tx\_cal\_controller.dateConfig

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:

   Data type
         Data type:

   Description
         Description:

   Default
         Default:


.. container:: table-row

   Property
         dayPosition

   Data type
         Integer

   Description
         Position of the day in a date string (0/1/2).

         Example: 2007-07-30 -> 2

   Default
         2


.. container:: table-row

   Property
         monthPosition

   Data type
         Integer

   Description
         Position of the month in a date string (0/1/2).

         Example: 2007-07-30 -> 1

   Default
         1


.. container:: table-row

   Property
         yearPosition

   Data type
         Integer

   Description
         Position of the year in a date string (0/1/2).

         Example: 2007-07-30 -> 0

   Default
         0


.. container:: table-row

   Property
         splitSymbol

   Data type
         String

   Description
         The character, which splits the day, month and year in a date string

   Default
         -


.. container:: table-row

   Property
         monthAbbreviationLength

   Data type
         Integer

   Description
         Number of characters that should be used for month name abbreviations

   Default
         3


.. container:: table-row

   Property
         weekdayAbbreviationLength

   Data type
         Integer

   Description
         Number of characters that should be used for weekday abbreviations

   Default
         3


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.dateConfig]


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   View/Index
   Rights/Index
   Module/Index
   Ics/Index
   Rss/Index

