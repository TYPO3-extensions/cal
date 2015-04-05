.. _ArchitectureView:

=============
View
=============

.. include:: ../../../Includes.txt

The view is a service object. View objects, depending on service type, must provide either a draw() or a drawSingle() method. Views that handle multiple events, such as a list view, a month view, or a day view, should provide the draw() method while a single view should provide the drawSingle() method.

Views for multiple events must be cautious to operate on only the base data format, since different events may come from different types that provide special views. Views for single events, however, can utilize all the custom fields available in their corresponding model, as there is a guarantee that the model and view match.

The end result of this is shared views for multiple events that can show data from many different models with unique single views that are tiedclosely to the custom features of a concrete model.

