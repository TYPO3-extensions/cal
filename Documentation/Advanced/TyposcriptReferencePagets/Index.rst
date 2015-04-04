.. _TypoScriptReferencePageTs:

=============
TypoScript Reference: pageTS
=============

.. include:: ../../Includes.txt

options.tx\_cal\_controller:

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
         pageIDForPlugin
   
   Data type
         Integer / PID
   
   Description
         PID where calendar base plugin is included or the TS options for
         plugin.tx\_cal\_controller can be found
   
   Default


.. container:: table-row

   Property
         eventViewPid
   
   Data type
         Integer / PID
   
   Description
         PID where calendar base plugin is available to preview a single event
         (enables the “Save & Preview”-icon in backend)
   
   Default


.. container:: table-row

   Property
         view.event.remind.time
   
   Data type
         Integer
   
   Description
         Adds an additional time offset for reminders
   
   Default


.. container:: table-row

   Property
         headerStyles
   
   Data type
         String / CSV
   
   Description
         Defines event category header styles in format
         “css\_style1=color,css\_style2=color”
   
   Default
         See “Create Calendar Categories“


.. container:: table-row

   Property
         bodyStyles
   
   Data type
         String / CSV
   
   Description
         Defines event category body styles in format
         “css\_style1=color,css\_style2=color”
   
   Default
         See “Create Calendar Categories“


.. ###### END~OF~TABLE ######


