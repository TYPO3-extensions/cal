.. include:: Images.txt
.. _Step2EditTheTemplateFile:

=============
Step 2: Edit the Template File
=============

.. include:: ../../../Includes.txt

|img-61|
**Illustration 53: Typical Template with Subpart Markers and Markers**

Once you've downloaded and saved the template file on your local computer, it is simply a matter of opening the file in your favorite HTML editor (MS Notepad, Write, Dreamweaver, etc.) and modifying the file. See Illustration 51 for an example of a typical template.**Technical Stuff: How the Template Works.** As you view the template
in your HTML editor, you will see what looks like a “nearly” standard
HTML file. The file is wrapped in <html>, <head>, and <body> tags like
most web pages. However, the template uses markers to direct TYPO3 to
reference and render only relevant portions of the Calendar Base
records. Each marker is itself wrapped in triple hash marks such as
###PAGES###. Each of the markers are defined at the top of the
template itself for your convenience (release 1.0).

Modifying the template is a matter of combining standard HTML tags
with the Calendar Base Markers. In general, the HTML tags dictate the
format and layout of your Frontend display, while the Markers dictate
what information is displayed. However, the importance of  **Subpart
Markers** cannot be overstated. Subpart Markers denote particular
subsections of the layout. **Subpart Markers** are encapsulating
markers, that is, they work like opening and closing tags. However,
note that the formatting is unlike HTML tags; instead, they simply
repeat themselves at the beginning and end of a section and look a bit
like a pair of HTML comments:

::

   <!-- ###SUBPART_MARKER### --> 
           Your <HTML TAGS> and 
           ###CalBase_Markers### 
           are nested between Subpart Markers
   <!-- ###SUBPART_MARKER### --> 

In addition, the Calendar Base Subpart Markers are appended with Begin
and End comments within the  **Subpart Markers** for clarification:

::

   <!-- ###SUBPART_MARKER### begin -->

Note:Although the convention is to capitalize the name of the Markers,
they are not case sensitive.

Most of the modifications of the template itself are self-evident,
assuming you have some HTML formatting experience. By looking at the
existing template file, you should be able to discern the general
syntax of the template itself.

Once you have finished editing the  **Template** file, save it. You
may rename the file for your own reference if desired, but the
extension  **.tmpl** should not be changed.

