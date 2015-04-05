.. include:: Images.txt
.. _TemplateFilesAndMarkers:

===========================
Template Files and Markers
===========================

.. include:: ../../Includes.txt

With release 1.4, the extension is delivered with two different
templates:

- A classic template: This is historically grown and proven as stable
  for years now. They are named “Classic CSS-based template (cal)” and
  “Classic CSS styles (cal)”

- A new standard template: The goal was to create a more modern template
  with better accessibility. It was introduced with release 1.4 and
  already is used in production installations. If you want to use this
  template, simply include the “Standard CSS-based template (cal)” and
  “Standard CSS styles (cal)” instead of the default ones.

This extension itself has a MVC (model-view-controller) structure, and
the same corresponds to the templates. Each view has an own template
with a self-speaking name such as create\_event.tmpl for the “Create
Event”-view, list.tmpl for the “List”-view and so on.

The different templates describe how a view should look like in
general. Within those views, events (or other models) are to be shown.
The description for those models are located in the \*\_model.tmpl
files. These model files contain subparts for the different views,
defining the final output.There are also some templates, which get
injected if the according marker gets inserted: list, month
(month\_small, month\_medium, month\_large), sidebar and
calendar\_nav. This means, that other templates can simply insert
these templates as markers, without having to redefine them.In the
view templates, you can find {VIEW}\_TEMPLATE subparts. These are
essential for the view. There can be other subpart markers for
additional configuration though.

The most important model if you want to change any event-related
output is the event model, stored in the event\_model.tmpl file.
Please take a closer look to this file to get an impression what
marker is used for what purpose.

|img-3| Please note: Please refer also to the template and model
files to get some more information. They hold some more interesting
information which should not be duplicated to the manual since it will
be outdated very fast

Example: If you want to change the output of the list view, open the
list.tmpl file. There, you will find only the general things like the
page browser and where the event list shall be displayed. So, open the
event\_model.tmpl file and search for “list”. You will find four
subpart definitions:

::

   ###TEMPLATE_PHPICALENDAR_EVENT_LIST_ODD###
   ###TEMPLATE_PHPICALENDAR_EVENT_LIST_EVEN###
   ###TEMPLATE_PHPICALENDAR_EVENT_LIST_ODD_ALLDAY###
   ###TEMPLATE_PHPICALENDAR_EVENT_LIST_EVEN_ALLDAY###

Can you image what these are for? Yes, for the odd and even entries in
the list, allowing to distinguish between standard events with
start/end dates and allday events. Now you can proceed to remove
unwanted markers or add your own.


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   RenderingNewDatabaseFields/Index
   AddingOwnMarkers/Index
   AddingOwnDatabaseMarkerToAFeEditForm/Index

