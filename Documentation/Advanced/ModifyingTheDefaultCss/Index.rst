.. _ModifyingTheDefaultCss:

==========================
Modifying the Default CSS
==========================

.. include:: ../../Includes.txt

This extension has extensive CSS support for each of the templates. If you choose to modify one or more of the templates, you can place some CSS styles directly into the template files. However, we recommend that you change the CSS and setup your own file if you will be modifying the look extensively. 
You can override the CSS by creating your own CSS file (title it something like CalBase.css) and uploading it into a Fileadmin folder. We recommend uploading the new CSS file to the fileadmin/ext-templates/ directory. For the Calendar Base extension to access this file, you will need to set the TypoScript page variable  *includeCSS* to include the CSS file. You can do this by changing the TypoScript Setup for a page. In addition, you also must suppress the old CSS references. If you have uploaded the CSS file into the recommended directory, you can cut and paste the following TypoScript example code into the Setup field of the Calendar Base Template:

::

   # Clear out the existing CSS
   plugin.tx_cal_controller._CSS_DEFAULT_STYLE >

   # Include  the new CSS file on your page.
   page.includeCSS.cal = fileadmin/ext-templates/CalBase.css


