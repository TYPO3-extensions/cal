.. _RenderingNewDatabaseFields:

==============================
Rendering new database fields
==============================

.. include:: ../../../Includes.txt

If you want to render additional fields that are already available in
the database (e.g., because you added them with your own extension),
all you need to do is to add a marker in to the corresponding model
file. If you provide a distinct database field named “agenda” and want
to show it in the event detail view, simply put a marker
“###AGENDA###” wherever you want to see it and you are done.

