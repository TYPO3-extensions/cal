.. include:: Images.txt
.. _FrontendEditing:

=============
Frontend Editing
=============

.. include:: ../Includes.txt

One of the features the Calendar Base extension offers is the ability
to add, edit, and delete calendar, calendar event records, categories,
etc. from the Frontend. There are two levels of editors for Frontend
users: Basic Frontend Calendar Editors typically have the ability to
create, edit, and delete Calendar Event records; Calendar
Administrators typically have the ability to create, edit, and delete
all associated Calendar Base records. However, you can limit the
rights to either Basic or Administrators through the choices made in
the Calendar Base's plugin (General Tab). Basic Calendar editing is
enabled by assigning the function to users in Website Users records
and Frontend User Group records (typically found in the Website's
General Storage Folder). Frontend Calendar Administration is enabled
in the Constant Editor General option (see :ref:`CalendarBaseGeneralConstantEditorForm`).

When a registered and authorized Calendar Editor is logged in, the
Frontend displays a variety of icons to facilitate editing. (See
Illustration 39.) Basic Calendar Editors will not see the Frontend
Calendar Admin Link in their views.

|img-46|
**Illustration 39: Frontend Editing Icons (Month View)**

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   EnablingFrontendEditing/Index
   CreatingEditingEvents/Index
   FrontendCalendarAdmin/Index