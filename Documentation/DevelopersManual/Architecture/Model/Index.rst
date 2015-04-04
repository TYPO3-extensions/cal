.. _Model:

=============
Model
=============

.. include:: ../../../Includes.txt

The model is based on an object representation of the iCal data format
and relies heavily on TYPO3 services. At the top level is a parent
class that defines many default fields and methods related to the iCal
data format.

Sitting beneath this top level data model are the many concrete models
that implement its data type. The only requirement on these models
are that they map data into
the required fields of the top level data model. How and where data
storage occurs (in a database or otherwise) is the decision of the
data model in question. While the top level data model may provide
default functionality for common functionlity, the concrete models are
in no way limited by this as they can always override methods from a
parent class. In addition, concrete models may provide additional
fields to augment the data model as well, so the parent class only
serves as a starting point for shared functionality.

