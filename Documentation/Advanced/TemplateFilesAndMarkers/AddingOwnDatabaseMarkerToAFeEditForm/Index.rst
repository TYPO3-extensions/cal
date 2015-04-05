.. _AddingOwnDatabaseMarkerToAFeEditForm:

=============================================
Adding own database marker to a FE-Edit form
=============================================

.. include:: ../../../Includes.txt

If you want to add your custom marker to a frontend edit form, you
need to follow these steps:

#. Create your custom marker according to the previous sub-chapter
   “Rendering new database fields”, e.g. named “tx\_myext\_newfield”

#. Add your marker ###TX\_MYEXT\_NEWFIELD### to the templates
   “create\_event.tmpl” and “confirm\_event.tmpl”

#. Add some TypoScript setup to replace the marker with valid HTML form
   data:

::

   plugin.tx_cal_controller.view {
    create_event {
     tx_myext_newfield_stdWrap {
      dataWrap = <input type="hidden" name="tx_cal_controller[tx_myext_newfield]" value="{GP:someGPvar}" />
     }
    }
    confirm_event < create_event
   }

#. Adjust your TS setup to allow the rendering of the marker

::

   plugin.tx_cal_controller.rights {
    create.event {
     additionalFields = tx_myext_newfield
     fields {
      tx_myext_newfield.public= 1
     }
    }
   }

With this, your marker should get replaced with the corresponding HTML
code, and the value will be stored in the database without additional
programming effort.

