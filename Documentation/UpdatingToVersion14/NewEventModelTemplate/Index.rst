.. _NewEventModelTemplate:

=============
New event model template
=============

.. include:: ../../Includes.txt

During the development of Calendar Base 1.4, several section of the
extension were refactoring, and naming annoyances and inconsistencies
were resolved.

One major part is that the event model was renamed. In previous
versions, it was defined in TypoScript by default with

::

         view.event.phpicalendarEventTemplate = EXT:cal/template/phpicalendar_event.tmpl

To make it compliant to other model names, it is now defined as

::

           view.event.eventModelTemplate = EXT:cal/template/event_model.tmpl

|img-5| The old definition still works, but please be aware that it won't be
supported in future versions.


