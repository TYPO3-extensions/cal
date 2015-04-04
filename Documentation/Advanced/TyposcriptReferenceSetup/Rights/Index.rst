.. _Rights:

=============
Rights
=============

.. include:: ../../../Includes.txt

The Typoscript object for rights is used to configure frontend editing
of calendar, category, event, locations and organizer.

plugin.tx\_cal\_controller.rights

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
         edit
   
   Data type
         Boolean
   
   Description
         Turns frontend editing on. If this is not enabled, none of the other
         rights options will have any effect.
   
   Default
         {$plugin.tx\_cal\_controller.rights.edit}


.. container:: table-row

   Property
         admin.user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all administrator users. These users will have
         full privileges for frontend editing.
   
   Default
         {$plugin.tx\_cal\_controller.rights.admin.user}


.. container:: table-row

   Property
         admin.group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all administrator groups. These groups will
         have full privileges for frontend editing.
   
   Default
         {$plugin.tx\_cal\_controller.rights.admin.group}


.. container:: table-row

   Property
         allowedUsers
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users allowed to be selected for
         notification or shared
   
   Default


.. container:: table-row

   Property
         allowedGroups
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups allowed to be selected for
         notification or shared
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights]


create
""""""

plugin.tx\_cal\_controller.rights.create.calendar

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
         saveCalendarToPid
   
   Data type
         Integer / PID
   
   Description
         Page to save frontend-created calendars to.
         
         See Constants
   
   Default
         {$plugin.tx\_cal\_controller.rights.defaultSavePid}


.. container:: table-row

   Property
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to create
         calendars.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to create
         calendars.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be saved inside the tx\_cal\_calendar record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to create calendar.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar]

plugin.tx\_cal\_controller.rights.create.calendar.enableAllField

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         **Property:**
   
   b
         **Data type:**
   
   c
         **Description:**
   
   d
         **Default:**


.. container:: table-row

   a
         user
   
   b
         String / CVS
   
   c
         Comma separated list of all users that should have all calendar fields
         enabled.
   
   d


.. container:: table-row

   a
         group
   
   b
         String / CVS
   
   c
         Comma separated list of all groups that should have all calendar
         fields enabled.
   
   d


.. container:: table-row

   a
         public
   
   b
         Boolean
   
   c
         1 if public user should have all calendar fields enabled.
   
   d
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.enableAllFiel
d]

plugin.tx\_cal\_controller.rights.create.calendar.fields.hidden

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.hidden
]

plugin.tx\_cal\_controller.rights.create.calendar.fields.title

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         String
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.title]

plugin.tx\_cal\_controller.rights.create.calendar.fields.calendarType

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.calend
arType]

plugin.tx\_cal\_controller.rights.create.calendar.fields.owner

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         allowedUsers
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users allowed to be selected
   
   Default


.. container:: table-row

   Property
         allowedGroups
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups allowed to be selected
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.owner]

plugin.tx\_cal\_controller.rights.create.calendar.fields.activateFreeA
ndBusy

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.activa
teFreeAndBusy]

plugin.tx\_cal\_controller.rights.create.calendar.fields.freeAndBusyUs
er

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         allowedUsers
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users allowed to be selected
   
   Default
         plugin.tx\_cal\_controller.rights.create.calendar.fields.owner.allowed
         Users


.. container:: table-row

   Property
         allowedGroups
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups allowed to be selected
   
   Default
         plugin.tx\_cal\_controller.rights.create.calendar.fields.owner.allowed
         Groups


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.freeAn
dBusyUser]

plugin.tx\_cal\_controller.rights.create.calendar.fields.ics\_file

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.ics\_f
ile]

plugin.tx\_cal\_controller.rights.create.calendar.fields.ext\_url

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.ext\_u
rl]

plugin.tx\_cal\_controller.rights.create.calendar.fields.refresh

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have a specific calendar
         field enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have a specific
         calendar field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this calendar field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.calendar.fields.refres
h]

plugin.tx\_cal\_controller.rights.create.category

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
         saveCategoryToPid
   
   Data type
         Integer / PID
   
   Description
         Page to save frontend-created categories to.
         
         See Constants
   
   Default
         {$plugin.tx\_cal\_controller.rights.defaultSavePid}


.. container:: table-row

   Property
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to create
         categories.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to create
         categories.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be saved inside the tx\_cal\_category record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to create categories.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category]

plugin.tx\_cal\_controller.rights.create.category.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all category fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all category
         fields enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all category fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.enableAllFiel
ds]

plugin.tx\_cal\_controller.rights.create.category.generalCategory

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all category fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all category
         fields enabled.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.generalCatego
ry]

plugin.tx\_cal\_controller.rights.create.category.publicCategory

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all category fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all category
         fields enabled.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.publicCategor
y]

plugin.tx\_cal\_controller.rights.create.category.fields.hidden

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this category field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have athis category
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this category field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.fields.hidden
]

plugin.tx\_cal\_controller.rights.create.category.fields.title

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this category field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this category
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this category field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         String
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.fields.title]

plugin.tx\_cal\_controller.rights.create.category.fields.headerstyle

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         **Property:**
   
   b
         **Data type:**
   
   c
         **Description:**
   
   d
         **Default:**


.. container:: table-row

   a
         user
   
   b
         String / CSV
   
   c
         Comma separated list of all users that should have this category field
         enabled.
   
   d


.. container:: table-row

   a
         group
   
   b
         String / CSV
   
   c
         Comma separated list of all groups that should have this category
         field enabled.
   
   d


.. container:: table-row

   a
         public
   
   b
         Boolean
   
   c
         1 if public user should have this category field enabled.
   
   d
         0


.. container:: table-row

   a
         default
   
   b
         String
   
   c
         The default value for this field
   
   d
         default\_categoryheader


.. container:: table-row

   a
         required
   
   b
         Boolean
   
   c
         Set this to 1, if it should be a required field
   
   d
         0


.. container:: table-row

   a
         available
   
   b
         String / CSV
   
   c
         Comma separated list of available category headerStyles
   
   d
         default\_categoryheader,yellow\_catheader,orange\_catheader,red\_cathe
         ader,pink\_catheader,green\_catheader,grey\_catheader


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.fields.header
style]

plugin.tx\_cal\_controller.rights.create.category.fields.bodystyle

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this category field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this category
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this category field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         String
   
   Description
         The default value for this field
   
   Default
         default\_categorybody


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         available
   
   Data type
         String / CSV
   
   Description
         Comma separated list of available category bodyStyles
   
   Default
         default\_categorybody,yellow\_catbody,orange\_catbody,red\_catbody,pin
         k\_catbody,green\_catbody,grey\_catbody


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.fields.bodyst
yle]

plugin.tx\_cal\_controller.rights.create.category.fields.calendar\_id

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this category field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this category
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this category field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         String
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.fields.calend
ar\_id]

plugin.tx\_cal\_controller.rights.create.category.fields.parent\_categ
ory

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this category field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this category
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this category field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         String
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.fields.parent
\_category]

plugin.tx\_cal\_controller.rights.create.category.fields.shared\_user\
_allowed

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this category field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this category
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this category field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         String
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.category.fields.shared
\_user\_allowed]

plugin.tx\_cal\_controller.rights.create.event

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
         saveEventToPid
   
   Data type
         Integer / PID
   
   Description
         Page to save frontend-created events to.
         
         See Constants
   
   Default
         {$plugin.tx\_cal\_controller.rights.defaultSavePid}


.. container:: table-row

   Property
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to create events.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to create events.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be saved inside the tx\_cal\_event record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to create events.
   
   Default
         0


.. container:: table-row

   Property
         notifyUsersOnPublicCreate
   
   Data type
         String / CSV
   
   Description
         Comma separated list of user ids of fe-users to be notified if a
         public event has been created
   
   Default


.. container:: table-row

   Property
         publicEvents.user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that are allowed to create events in
         a public calendar.
   
   Default


.. container:: table-row

   Property
         publicEvents.group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that are allowed to create events
         in a public calendar.
   
   Default


.. container:: table-row

   Property
         addFeUserToNotify
   
   Data type
         Boolean
   
   Description
         Adds the frontend user who created an event to the notification field.
   
   Default
         0


.. container:: table-row

   Property
         addFeUserToShared
   
   Data type
         Boolean
   
   Description
         Adds the frontend user who created an event to the shared field.
   
   Default
         0


.. container:: table-row

   Property
         addFeGroupToShared.ignore
   
   Data type
         String / CSV
   
   Description
         Comma separated list of frontend user groups, which are to be ignored
         and NOT to be added as shared
   
   Default


.. container:: table-row

   Property
         enableRTE
   
   Data type
         Boolean
   
   Description
         Replaces the standard textarea with the installed rte.
   
   Default
         1


.. container:: table-row

   Property
         timeOffset
   
   Data type
         Integer
   
   Description
         Offset in minutes. The user is allowed to create a new event in now +
         timeOffset
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event]

plugin.tx\_cal\_controller.rights.create.event.inPast

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to create events
         also in the past
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to create events
         also in the past.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to create events also in the past.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.inPast]

plugin.tx\_cal\_controller.rights.create.event.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all event fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all event fields
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all event fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.enableAllFields]

plugin.tx\_cal\_controller.rights.create.event.fields.hidden

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default
         0


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.hidden]

plugin.tx\_cal\_controller.rights.create.event.fields.calendar\_id

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.calendar\_id]

plugin.tx\_cal\_controller.rights.create.event.fields.category

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         allowedUids
   
   Data type
         String / CSV
   
   Description
         Comma separated list of allowed category uids
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.category]

plugin.tx\_cal\_controller.rights.create.event.fields.startdate

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. container:: table-row

   Property
         constrain.1
   
   Data type
         Array
   
   Description
         #less/before/greater/after/equals/regexp/userfunc
         
         rule = regexp
         
         regexp = /(\d{4})-(\d{2})-(\d{2})/
         
         message = TEXT
         
         message {
         
         dataWrap = <span class="constrain">{LLL:EXT:cal/controller/locallang.x
         ml:l\_wrong\_date} (yyyy-mm-dd)</span>
         
         }
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.startdate
]

plugin.tx\_cal\_controller.rights.create.event.fields.enddate

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. container:: table-row

   Property
         constrain.1
   
   Data type
         Array
   
   Description
         #less/before/greater/after/equals/regexp/userfunc
         
         rule = regexp
         
         regexp = /(\d{4})-(\d{2})-(\d{2})/
         
         message = TEXT
         
         message {
         
         dataWrap = <span class="constrain">{LLL:EXT:cal/controller/locallang.x
         ml:l\_wrong\_date} (yyyy-mm-dd)</span>
         
         }
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.enddate]

plugin.tx\_cal\_controller.rights.create.event.fields.starttime

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. container:: table-row

   Property
         dynamicStarttimeOffset
   
   Data type
         Integer
   
   Description
         If set, the start time of a new event is calculated based on now+this
         offset (in seconds)
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.starttime
]

plugin.tx\_cal\_controller.rights.create.event.fields.endtime

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.endtime]

plugin.tx\_cal\_controller.rights.create.event.fields.start.constrain

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
         1
   
   Data type
         Array
   
   Description
         #less/before/greater/after/equals/regexp/userfunc
         
         rule = before\|equals
         
         field = end
         
         message = TEXT
         
         message {
         
         dataWrap = <span class="constrain">{LLL:EXT:cal/controller/locallang.x
         ml:l\_constrain\_start\_before\_end}</span>
         
         }
   
   Default


.. container:: table-row

   Property
         2
   
   Data type
         Array
   
   Description
         #less/before/greater/after/equals/regexp/userfunc
         
         rule = after
         
         field = now
         
         message = TEXT
         
         message {
         
         dataWrap = <span class="constrain">{LLL:EXT:cal/controller/locallang.x
         ml:l\_is\_in\_past}</span>
         
         }
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.start.con
strain]

plugin.tx\_cal\_controller.rights.create.event.fields.end.constrain

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
         1
   
   Data type
         Array
   
   Description
         #less/before/greater/after/equals/regexp/userfunc
         
         rule = after\|equals
         
         field = start
         
         message = TEXT
         
         message {
         
         dataWrap = <span class="constrain">{LLL:EXT:cal/controller/locallang.x
         ml:l\_constrain\_end\_after\_start}</span>
         
         }
   
   Default


.. container:: table-row

   Property
         2
   
   Data type
         Array
   
   Description
         #less/before/greater/after/equals/regexp/userfunc
         
         rule = after
         
         field = now
         
         message = TEXT
         
         message {
         
         dataWrap = <span class="constrain">{LLL:EXT:cal/controller/locallang.x
         ml:l\_is\_in\_past}</span>
         
         }
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.end.const
rain]

plugin.tx\_cal\_controller.rights.create.event.fields.title

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.title]

plugin.tx\_cal\_controller.rights.create.event.fields.cal\_organizer

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         allowedUids
   
   Data type
         String / CSV
   
   Description
         Comma separated list of allowed organizer uids
   
   Default


.. container:: table-row

   Property
         onlyOwn
   
   Data type
         Boolean
   
   Description
         Set this to 1 if editors can only edit their own records.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.cal\_orga
nizer]

plugin.tx\_cal\_controller.rights.create.event.fields.organizer

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         allowedUids
   
   Data type
         String / CSV
   
   Description
         Comma separated list of allowed organizer uids
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.organizer
]

plugin.tx\_cal\_controller.rights.create.event.fields.cal\_location

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         allowedUids
   
   Data type
         String / CSV
   
   Description
         Comma separated list of allowed location uids
   
   Default


.. container:: table-row

   Property
         onlyOwn
   
   Data type
         Boolean
   
   Description
         Set this to 1 if editors can only edit their own records.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.cal\_loca
tion]

plugin.tx\_cal\_controller.rights.create.event.fields.location

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. container:: table-row

   Property
         allowedUids
   
   Data type
         String / CSV
   
   Description
         Comma separated list of allowed location uids
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.location]

plugin.tx\_cal\_controller.rights.create.event.fields.teaser

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.teaser]

plugin.tx\_cal\_controller.rights.create.event.fields.description

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         1


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.descripti
on]

plugin.tx\_cal\_controller.rights.create.event.fields.recurring

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.recurring
]

plugin.tx\_cal\_controller.rights.create.event.fields.notify

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         defaultUser
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.notify]

plugin.tx\_cal\_controller.rights.create.event.fields.shared

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.shared]

plugin.tx\_cal\_controller.rights.create.event.fields.exception

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.exception
]

plugin.tx\_cal\_controller.rights.create.event.fields.attendee

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.attendee]

plugin.tx\_cal\_controller.rights.create.event.fields.image

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.image]

plugin.tx\_cal\_controller.rights.create.event.fields.image\_caption

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.image\_ca
ption]

plugin.tx\_cal\_controller.rights.create.event.fields.image\_title

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.image\_ti
tle]

plugin.tx\_cal\_controller.rights.create.event.fields.image\_alt

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.image\_al
t]

plugin.tx\_cal\_controller.rights.create.event.fields.attachment

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.attachmen
t]

plugin.tx\_cal\_controller.rights.create.event.fields.attachment\_capt
ion

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this event field
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this event field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.fields.attachmen
t\_caption]

plugin.tx\_cal\_controller.rights.create.location

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
         saveLocationToPid
   
   Data type
         Integer / PID
   
   Description
         Page to save frontend-created locations to.
         
         See Constants
   
   Default
         {$plugin.tx\_cal\_controller.rights.defaultSavePid}


.. container:: table-row

   Property
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to create
         locations.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to create
         locations.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be saved inside the tx\_cal\_location record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to create locations.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.event.location]

plugin.tx\_cal\_controller.rights.create.location.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all location fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all location
         fields enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all location fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.enableAllFiel
ds]

plugin.tx\_cal\_controller.rights.create.location.fields.hidden

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default
         0


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.hidden
]

plugin.tx\_cal\_controller.rights.create.location.fields.name

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.name]

plugin.tx\_cal\_controller.rights.create.location.fields.description

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.descri
ption]

plugin.tx\_cal\_controller.rights.create.location.fields.street

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.street
]

plugin.tx\_cal\_controller.rights.create.location.fields.zip

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.zip]

plugin.tx\_cal\_controller.rights.create.location.fields.city

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.city]

plugin.tx\_cal\_controller.rights.create.location.fields.countryZone

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.countr
yZone]

plugin.tx\_cal\_controller.rights.create.location.fields.country

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.countr
y]

plugin.tx\_cal\_controller.rights.create.location.fields.phone

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.phone]

plugin.tx\_cal\_controller.rights.create.location.fields.email

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.email]

plugin.tx\_cal\_controller.rights.create.location.fields.image

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.image]

plugin.tx\_cal\_controller.rights.create.location.fields.image\_captio
n

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.image\
_title]

plugin.tx\_cal\_controller.rights.create.location.fields.image\_title

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.image\
_title]

plugin.tx\_cal\_controller.rights.create.location.fields.image\_alt

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.image\
_alt]

plugin.tx\_cal\_controller.rights.create.location.fields.link

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
         user
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all users that should have this location field
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CSV
   
   Description
         Comma separated list of all groups that should have this location
         field enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have this location field enabled.
   
   Default
         0


.. container:: table-row

   Property
         default
   
   Data type
         Integer
   
   Description
         The default value for this field
   
   Default


.. container:: table-row

   Property
         required
   
   Data type
         Boolean
   
   Description
         Set this to 1, if it should be a required field
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.location.fields.link]

plugin.tx\_cal\_controller.rights.create.organizer <
plugin.tx\_cal\_controller.rights.create.location

plugin.tx\_cal\_controller.rights.create.organizer

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
         saveOrganizerToPid
   
   Data type
         Integer / PID
   
   Description
         Page to save frontend-created organizer to.
   
   Default
         {$plugin.tx\_cal\_controller.rights.defaultSavePid}


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.organizer]

plugin.tx\_cal\_controller.rights.create.translation

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         Property:
   
   b
         Data type:
   
   c
         Description:
   
   d
         Default:


.. container:: table-row

   a
         user
   
   b
         String / CVS
   
   c
         Comma separated list of all users that are allowed to create
         translations.
   
   d


.. container:: table-row

   a
         group
   
   b
         String / CVS
   
   c
         Comma separated list of all groups that are allowed to create
         translations.
   
   d


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.create.translation]


edit
""""

plugin.tx\_cal\_controller.rights.edit.calendar

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit calendars.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit calendars.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be updated inside the tx\_cal\_calendar
         record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to edit calendar.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.calendar]

plugin.tx\_cal\_controller.rights.edit.calendar.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all calendar fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all calendar
         fields enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all calendar fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.calendar.enableAllFields
]

plugin.tx\_cal\_controller.rights.edit.calendar.fields <
plugin.tx\_cal\_controller.rights.create.calendar.fields

plugin.tx\_cal\_controller.rights.edit.calendar.onlyOwnCalendar

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be allowed to edit only their own
         calendar
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be allowed to edit only their
         own calendar
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.calendar.onlyOwnCalendar
]

plugin.tx\_cal\_controller.rights.edit.calendar.publicCalendar

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be allowed to edit a public
         calendar
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be allowed to edit a public
         calendar
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.calendar.publicCalendar]

plugin.tx\_cal\_controller.rights.edit.category

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit categories.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit
         categories.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be updated inside the tx\_cal\_category
         record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to edit categories.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.category]

plugin.tx\_cal\_controller.rights.edit.category.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all category fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all category
         fields enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all category fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.category.enableAllFields
]

plugin.tx\_cal\_controller.rights.edit.category.field <
plugin.tx\_cal\_controller.rights.create.category.fields

plugin.tx\_cal\_controller.rights.edit.category.onlyOwnCategory

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be able to edit only their own
         categories
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be able to edit only their own
         categories
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.category.onlyOwnCategory
]

plugin.tx\_cal\_controller.rights.edit.category.generalCategory

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be able to edit general
         categories
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be able to edit general
         categories
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.category.generalCategory
]

plugin.tx\_cal\_controller.rights.edit.category.publicCategory

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be able to edit public categories
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be able to edit public
         categories
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.category.publicCategory]

plugin.tx\_cal\_controller.rights.edit.event

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit events.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit events.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be updated inside the tx\_cal\_event record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to edit events.
   
   Default
         0


.. container:: table-row

   Property
         enableRTE
   
   Data type
         Boolean
   
   Description
         Replaces the standard textarea with the installed rte.
   
   Default
         1


.. container:: table-row

   Property
         timeOffset
   
   Data type
         Integer
   
   Description
         Offset in minutes. The user is allowed to edit an event in now +
         timeOffset
   
   Default
         0


.. container:: table-row

   Property
         addFeUserToShared
   
   Data type
         Boolean
   
   Description
   
   
   Default
         0


.. container:: table-row

   Property
         addFeGroupToShared
   
   Data type
         Boolean
   
   Description
   
   
   Default
         0


.. container:: table-row

   Property
         addFeGroupToShared.ignore
   
   Data type
         String / CSV
   
   Description
   
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.event]

plugin.tx\_cal\_controller.rights.edit.event.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all event fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all event fields
         enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all event fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.event.enableAllFields]

plugin.tx\_cal\_controller.rights.edit.event.inPast

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit events also
         in the past
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit events
         also in the past.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to edit events also in the past.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.event.inPast]

plugin.tx\_cal\_controller.rights.edit.event.field <
plugin.tx\_cal\_controller.rights.create.event.fields

plugin.tx\_cal\_controller.rights.edit.event.onlyOwnEvents

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit only their
         own events.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit only their
         own events.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.event.onlyOwnEvents]

plugin.tx\_cal\_controller.rights.edit.event.startedEvents

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit events
         after they have started.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit events
         after they have started.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.event.startedEvents]

plugin.tx\_cal\_controller.rights.edit.location

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit locations.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit locations.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be updated inside the tx\_cal\_location
         record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to edit locations.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.location]

plugin.tx\_cal\_controller.rights.edit.location.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all location fields
         enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all location
         fields enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all location fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.location.enableAllFields
]

plugin.tx\_cal\_controller.rights.edit.location.fields <
plugin.tx\_cal\_controller.rights.create.location.fields

plugin.tx\_cal\_controller.rights.edit.organizer

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to edit organizers.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to edit
         organizers.
   
   Default


.. container:: table-row

   Property
         additionalFields
   
   Data type
         String / CVS
   
   Description
         Comma separated list of fields, that are not shipped with the standard
         cal extension, but are to be updated inside the tx\_cal\_organizer
         record
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to edit organizers.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.organizer]

plugin.tx\_cal\_controller.rights.edit.organizer.enableAllFields

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that should have all organizer
         fields enabled.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that should have all organizer
         fields enabled.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should have all organizer fields enabled.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.edit.organizer.enabeAllFields
]

plugin.tx\_cal\_controller.rights.edit.organizer.fields <
plugin.tx\_cal\_controller.rights.create.organizer.fields


delete
""""""

plugin.tx\_cal\_controller.rights.delete.calendar

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to delete
         calendars.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to delete
         calendars.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to delete calendar.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.calendar]

plugin.tx\_cal\_controller.rights.delete.calendar.onlyOwnCalendar

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be allowed to delete only their
         own calendar
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be allowed to delete only their
         own calendar
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.calendar.onlyOwnCalend
ar]

plugin.tx\_cal\_controller.rights.delete.calendar.publicCalendar

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be allowed to delete a public
         calendar
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be allowed to delete a public
         calendar
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.calendar.publicCalenda
r]

plugin.tx\_cal\_controller.rights.delete.category

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to delete
         categories.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to delete
         categories.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to delete categories.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.category]

plugin.tx\_cal\_controller.rights.delete.category.onlyOwnCategory

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be able to delete only their own
         categories
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be able to delete only their own
         categories
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.category.onlyOwnCatego
ry]

plugin.tx\_cal\_controller.rights.delete.category.generalCategory

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users to be able to delete general
         categories
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups to be able to delete general
         categories
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.category.generalCatego
ry]

plugin.tx\_cal\_controller.rights.delete.event

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to delete events.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to delete events.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to delete events.
   
   Default
         0


.. container:: table-row

   Property
         timeOffset
   
   Data type
         Integer
   
   Description
         Offset in minutes. The user is allowed to delete an event in now +
         timeOffset
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.event]

plugin.tx\_cal\_controller.rights.delete.event.onlyOwnEvents

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to delete only
         their own events.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to delete only
         their own events.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.event.onlyOwnEvents]

plugin.tx\_cal\_controller.rights.delete.event.startedEvents

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to delete events
         after they have started.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to delete events
         after they have started.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.startedEvents]

plugin.tx\_cal\_controller.rights.delete.location

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
         user
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all users that are allowed to delete
         locations.
   
   Default


.. container:: table-row

   Property
         group
   
   Data type
         String / CVS
   
   Description
         Comma separated list of all groups that are allowed to delete
         locations.
   
   Default


.. container:: table-row

   Property
         public
   
   Data type
         Boolean
   
   Description
         1 if public user should be allowed to delete locations.
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rights.delete.location]

plugin.tx\_cal\_controller.rights.delete.organizer <
plugin.tx\_cal\_controller.rights.delete.location

