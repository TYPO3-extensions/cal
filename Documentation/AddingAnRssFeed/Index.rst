.. include:: Images.txt
.. _AddingAnRssFeed:

=============
Adding an RSS Feed
=============

.. include:: ../Includes.txt

One of the advanced features of the Calendar Base extension is the
ability to offer your Frontend users the luxury of an RSS Feed that
will keep them apprised of event changes in your site's calendar.
There are three steps to this process.

#. **Add the News-Feed Static Template.** Refer to Illustration 42. Add the
   **News-Feed (RSS, RDF, ATOM) (cal)** Static Template to either your root template or the Calendar Page template.
   
   |img-49|
   
   **Illustration 42: Adding the RSS Feed (News-Feed)**
   
   
#. **Edit the Related Constants in the Constant Editor.** Refer to Illustration 43. Select the
   Calendar Base (RSS) constants from the Category drop-down menu. Then
   scroll down the Constant Editor to change the following three
   Constants: ( **1** )  **News-Feed XML-Title.** Change this to whatever
   you would like to call your feed. ( **2** )  **News-Feed XML-Link.**
   Change this to your homepage URL. ( **3** )  **News-Feed XML-
   Description.** Change this to a brief description of your feed. Click
   the update button to save your changes.
   
   |img-50|
   
   **Illustration 43: Changing RSS Constants in the Constant Editor**

#. **Set the Storage Location for Events.** After setting the Calendar
   Base (RSS) constants, you'll also want to make sure that the **Page ID
   where are events are stored** has also been set properly. You'll find
   this setting within the Calendar Base (General) section of the
   Constant Editor.

#. **Add a Subscription Link.** Invite Frontend users to subscribe to
   your RSS feed by adding a subscription link. The easiest way to do
   this is to add a Text Content Element to your calendar page (probably
   right above the Calendar Base plugin). Type  *Subscribe to Our RSS
   Feed* in the Header field and then include a link (in the header
   field) to your RSS feed. To determine what your link will be, first
   find the PID of your Calendar page (see Illustration 44) by hovering
   your mouse over the Calendar page in the Page Tree. The number that
   appears will be the PID. With this information, your link will be as
   follows:
   
   |img-51|
   
   **Illustration 44: Finding a Page's PID**
   
   http://www.YOUR-SITE-URL.com/index.php?id=PID&type=151

There are three parts to this link. (1) The link to the index page ( `
**http://www.YOUR-SITE-URL.com/index.php**  <http://www.YOUR-SITE-
URL.com/index.php>`_  **)** ; (2) The id number to the page
(:underline:`**?id=PID**` where PID is the Page ID number of your
Calendar page); and (3) The RSS Feed Type (:underline:`**&type=151**`
). All three parts must be a part of your link.

When you have finished, save the Content Element, view the page, and
click on the link. This will take you to an RSS Subscription page and
you will be able to subscribe to the Calendar's Feed, which will keep
you apprised of any changes made to the calendar.


