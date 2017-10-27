.. _View:

=============
View
=============

.. include:: ../../../Includes.txt

plugin.tx\_cal\_controller.view

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         allowedViews
   
   Data type
         String / CSV
   
   Description
         Allowed calendar views. First one in the list is the default view.
         
         Options are day,week,month,year,list,event,search\_all,search\_event,s
         earch\_location,search\_organizer,organizer,location,admin,create\_eve
         nt,confirm\_event,save\_event,edit\_event,delete\_event,remove\_event,
         create\_location,confirm\_location,save\_location,edit\_loaction,delet
         e\_location,remove\_location,create\_organizer,confirm\_organizer,save
         \_organizer,edit\_organizer,delete\_organizer,remove\_organizer,create
         \_calendar,confirm\_calendar,save\_calendar,edit\_calendar,delete\_cal
         endar,remove\_calendar,create\_category,confirm\_category,save\_catego
         ry,edit\_category,delete\_category,remove\_category.
         
         also: Flexform
   
   Default


.. container:: table-row

   Property
         customViews
   
   Data type
         String / CSV
   
   Description
         Additional views
   
   Default


.. container:: table-row

   Property
         noViewFoundHelpText
   
   Data type
         String
   
   Description
         Info text if there is no function nor service to handle a desired view
   
   Default
         Controller function not found:


.. container:: table-row

   Property
         calendar
   
   Data type
         String / CSV
   
   Description
         Calendars can be preselected. Enter a single ID or a comma separated
         list
   
   Default


.. container:: table-row

   Property
         calendarMode
   
   Data type
   
   
   Description
   
   
   Default


.. container:: table-row

   Property
         category
   
   Data type
         String / CSV
   
   Description
         Categories can be preselected. Enter a single ID or a comma separated
         list
   
   Default


.. container:: table-row

   Property
         categoryMode
   
   Data type
   
   
   Description
         0 = Show all
         
         1 = show selected
         
         2 = exclude selected
   
   Default


.. container:: table-row

   Property
         imagePath
   
   Data type
         String / Path
   
   Description
         Relative path (from TYPO3 site root) that images should be loaded
         from.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.imagePath}


.. container:: table-row

   Property
         javascriptPath
   
   Data type
         String / Path
   
   Description
         Relative path (from TYPO3 site root) that javascript should be loaded
         from.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.javascriptPath}


.. container:: table-row

   Property
         weekStartDay
   
   Data type
         Monday or Sunday
   
   Description
         First day of the week.
         
         also: Flexform
   
   Default
         Monday


.. container:: table-row

   Property
         dayLinkTarget
   
   Data type
         String
   
   Description
         The view to be rendered when clicking on a day link
   
   Default
         day


.. container:: table-row

   Property
         weekLinkTarget
   
   Data type
         String
   
   Description
         The view to be rendered when clicking on a week link
   
   Default
         week


.. container:: table-row

   Property
         monthLinkTarget
   
   Data type
         String
   
   Description
         The view to be rendered when clicking on a month link
   
   Default
         month


.. container:: table-row

   Property
         yearLinkTarget
   
   Data type
         String
   
   Description
         The view to be rendered when clicking on a year link
   
   Default
         year


.. container:: table-row

   Property
         locationLinkTarget
   
   Data type
         String
   
   Description
         The view to be rendered when clicking on a location link
   
   Default
         location


.. container:: table-row

   Property
         organizerLinkTarget
   
   Data type
         String
   
   Description
         The view to be rendered when clicking on a organizer link
   
   Default
         organizer


.. container:: table-row

   Property
         startLinkRange
   
   Data type
         strtotime() expression
   
   Description
         Views before that date will get a no\_follow meta tag
         
         also: `http://www.php.net/manual/en/function.strtotime.php
         <http://www.php.net/manual/en/function.strtotime.php>`_
   
   Default
         -5 month


.. container:: table-row

   Property
         endLinkRange
   
   Data type
         strtotime() expression
   
   Description
         Views after that date will get a no\_follow meta tag
         
         also: `http://www.php.net/manual/en/function.strtotime.php
         <http://www.php.net/manual/en/function.strtotime.php>`_
   
   Default
         +5 month


.. container:: table-row

   Property
         required
   
   Data type
         String
   
   Description
         String to be displayed in create and edit forms for required fields
   
   Default
         <span class="cal\_required">\*</span>


.. container:: table-row

   Property
         defaultLinkSetup
   
   Data type
         cObj
   
   Description
         Content object, that defines how a link is rendered by default:
         
         defaultLinkSetup = TEXT
         
         defaultLinkSetup {
         
         current = 1
         
         typolink {
         
         parameter.field = link
         
         ATagParams.field = ATagParams
         
         additionalParams.field = additionalParams
         
         #section.field = section
         
         title.current = 1
         
         title.override {
         
         field = title
         
         required = 1
         
         }
         
         no\_cache.field = no\_cache
         
         }
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         defaultViewLinkSetup
   
   Data type
         cObj
   
   Description
         Content object, that defines how a link to a different view type is
         rendered by default.
         
         defaultViewLinkSetup < .defaultLinkSetup
         
         defaultViewLinkSetup {
         
         typolink.title.override.override.cObject = TEXT
         
         typolink.title.override.override.cObject {
         
         field = view
         
         wrap = {LLL:EXT:cal/controller/locallang.xml:l\_\|\_view}
         
         insertData = 1
         
         required = 1
         
         }
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         backLink
   
   Data type
         cObj
   
   Description
         The content object used for back links
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultViewLinkSetup


.. container:: table-row

   Property
         showEditableEventsOnly
   
   Data type
         boolean
   
   Description
         Displays only events which can be edited or deleted
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view]


For each view
"""""""""""""

Example: plugin.tx\_cal\_controller.view.list or
plugin.tx\_cal\_controller.view.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         sendOutWithXMLHeader
   
   Data type
         boolean
   
   Description
         Sets the header to 'Content-Type: text/xml'
   
   Default
         Depends on view


.. container:: table-row

   Property
         categoryLink\_stdWrap
   
   Data type
         stdWrap
   
   Description
         stdWrap for the link text of category
   
   Default


.. container:: table-row

   Property
         categoryLink\_splitChar
   
   Data type
         cObj
   
   Description
         for more than one category this is the separator
         
         categoryLink\_splitChar {
         
         value = ,
         
         noTrimWrap= \|\| \|
         
         }
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.<each view>]


Event
"""""

plugin.tx\_cal\_controller.view.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         eventViewPid
   
   Data type
         Integer / PID
   
   Description
         Page to display the event view on. If this is not configured, then the
         current page will be used instead.
         
         also: Flexform
   
   Default


.. container:: table-row

   Property
         createEventViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating an event. If this is not configured,
         then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         editEventViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing an event. If this is not configured,
         then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         deleteEventViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting an event. If this is not configured,
         then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         eventTemplate
   
   Data type
         String / Path
   
   Description
         Template for the standard event view. Any events following the
         standard event structure can be used with this template.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.eventTemplate}


.. container:: table-row

   Property
         eventModelTemplate
   
   Data type
         String / Path
   
   Description
         Template for the phpicalendar event view. This is an example of how
         different event types can provide their own views.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.eventModelTemplate}


.. container:: table-row

   Property
         subscriptionManagerTemplate
   
   Data type
         String / Path
   
   Description
         Template for the event subscription manager view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.subscriptionManagerTemplate}


.. container:: table-row

   Property
         substitutePageTitle
   
   Data type
         Boolean
   
   Description
         Sets the event title as page title
   
   Default
         1


.. container:: table-row

   Property
         isPreview
   
   Data type
         Boolean
   
   Description
         Enables a preview of the event
         
         also: flexform
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.event]

plugin.tx\_cal\_controller.view.event.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         **Property:**
   
   b
         **Data type:**
   
   c
         **Description:**
   
   d
         **Default:**


.. container:: table-row

   a
         useTitleForLinkTitle
   
   b
         Boolean
   
   c
         Deprecated: Enables the link title to be the title of the event. If
         disabled you have to define your own link title.
         
         also: plugin.tx\_cal\_controller.view.event.event.ownLinkTitleText
         
         This can be now done specifically in each link cObj.
   
   d
         1


.. container:: table-row

   a
         ownLinkTitleText
   
   b
         cObj
   
   c
         Deprecated: Defines an own link title, if useTitleForLinkTitle has
         been disabled.
         
         plugin.tx\_cal\_controller.view.event.event.useTitleForLinkTitle.
         
         This can be now done specifically in each link cObj.
   
   d
         TEXT


.. container:: table-row

   a
         eventLink
   
   b
         cObj
   
   c
         Content object that defines how a link to a event is rendered.
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         moreLink
   
   b
         cObj
   
   c
         Content object that defines how the so called 'more link' is rendered
         in preview mode.
         
         moreLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_event\_more}
         
         }
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         dontShowEndDateIfEqualsStartDate
   
   b
         Boolean
   
   c
         If start date and end date are the same, only show start.
   
   d
         1


.. container:: table-row

   a
         dontShowEndDateIfEqualsStartDateAllday = 1
   
   b
         Boolean
   
   c
         Same as dontShowEndDateIfEqualsStartDate but for allday events.
   
   d
         1


.. container:: table-row

   a
         differentStyleIfOwnEvent
   
   b
         Boolean
   
   c
         Enable this and you can define a special style for events a fe-user is
         owner of
         
         also:
         plugin.tx\_cal\_controller.view.event.event.headerStyleOfOwnEvent
         
         also: plugin.tx\_cal\_controller.view.event.event.bodyStyleOfOwnEvent
   
   d
         0


.. container:: table-row

   a
         headerStyleOfOwnEvent
   
   b
         String
   
   c
         Defines a special header style for events a fe-user is owner of
         
         also:
         plugin.tx\_cal\_controller.view.event.event.differentStyleIfOwnEvent
   
   d
         green\_catheader


.. container:: table-row

   a
         bodyStyleOfOwnEvent
   
   b
         String
   
   c
         Defines a special body style for events a fe-user is owner of
         
         also:
         plugin.tx\_cal\_controller.view.event.event.differentStyleIfOwnEvent
   
   d
         green\_catbody


.. container:: table-row

   a
         defaultEventLength
   
   b
         Integer
   
   c
         The default length in seconds, if no or a wrong end has been specified
   
   d
         1800


.. container:: table-row

   a
         statusIcon
   
   b
         String
   
   c
         Image for the event status. %s will be substituted by the status.
   
   d
         <img src="###IMG\_PATH###/%s.gif" width="9" height="9" alt=""
         border="0" hspace="0" vspace="0" />&nbsp;


.. container:: table-row

   a
         recurringIcon
   
   b
         String
   
   c
         Image tag for icon used to indicate recurring events.
   
   d
         <img src="###IMG\_PATH###/recurring.gif" width="9" height="9" alt=""
         border="0" hspace="0" vspace="0" />&nbsp;


.. container:: table-row

   a
         addIcon
   
   b
         String
   
   c
         Image tag for icon used in link to frontend creation of an event.
   
   d
         <img src="###IMG\_PATH###/add\_small.png" border="0"/>


.. container:: table-row

   a
         editIcon
   
   b
         String
   
   c
         Image tag for icon used in link to frontend editing of an event.
   
   d
         <img src="###IMG\_PATH###/edit.gif" border="0"/>


.. container:: table-row

   a
         deleteIcon
   
   b
         String
   
   c
         Image tag for icon used in link to frontend deletion of an event.
   
   d
         <img src="###IMG\_PATH###/delete.gif" border="0"/>


.. container:: table-row

   a
         categoryIcon
   
   b
         String
   
   c
         Image tag for icon used to visualize event category
   
   d
         <img src="%%%CATICON%%%" border="0" height="24"
         title="%%%CATTITLE%%%"/>


.. container:: table-row

   a
         categoryIconDefault
   
   b
         String
   
   c
         Default category 'icon' used to visualize event category
   
   d
         &bull;


.. container:: table-row

   a
         additionalCategoryWhere
   
   b
         String
   
   c
         Add an additional part to the sql statement for events with categories
         (must include logical operator!)
   
   d


.. container:: table-row

   a
         additionalWhere
   
   b
         String
   
   c
         Add an additional part to the sql statement for any event queries
         (must include logical operator!)
         
         additionalWhere = AND calendar\_id>22
   
   d


.. container:: table-row

   a
         addLink
   
   b
         cObj
   
   c
         Configuration for the add event link. Default setting is to act
         backwards compatible, but it can be altered with TS.
         
         addLink {
         
         typolink.useCacheHash = 1
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_create\_event}
         
         }
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         editLink
   
   b
         cObj
   
   c
         Configuration for the edit event link. Default setting is to act
         backwards compatible, but it can be altered with TS.
         
         editLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_edit\_event}
         
         }
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         deleteLink
   
   b
         cObj
   
   c
         Configuration for the delete event link. Default setting is to act
         backwards compatible, but it can be altered with TS.
         
         deleteLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_delete\_event}
         
         }
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         eventDateFormat
   
   b
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   c
         Date format
         
         also: Constants
   
   d
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   a
         dateFormat
   
   b
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   c
         Date format
         
         also: Constants
   
   d
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   a
         timeFormat
   
   b
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   c
         Time format
         
         also: Constants
   
   d
         {$plugin.tx\_cal\_controller.view.timeFormat}


.. container:: table-row

   a
         cruser\_name
   
   b
         cObj
   
   c
         Content object for the create-user name
         
         cruser\_name {
         
         dataWrap = <div>CrUserName:&nbsp; \|</div>
         
         db\_field = username
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         calendarStyle
   
   b
         cObj
   
   c
         Defines an additional style class for an event, according to the
         calendar
         
         calendarStyle {
         
         wrap = calendar\|
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         title
   
   b
         cObj
   
   c
         Content object for the event title
         
         title {
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_title}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         alldayTitle
   
   b
         cObj
   
   c
         Content object for the event title of allDay Events
         
         alldayTitle = TEXT
         
         alldayTitle {
         
         current = 1
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         startdate
   
   b
         cObj
   
   c
         Content object for the event start date
         
         startdate {
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_startdate}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         enddate
   
   b
         cObj
   
   c
         Content object for the event end date
         
         enddate {
         
         noTrimWrap = \| - \|\|
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_enddate}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         starttime
   
   b
         cObj
   
   c
         Content object for the event start time
         
         starttime {
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_starttime}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         endtime
   
   b
         cObj
   
   c
         Content object for the event end time
         
         endtime {
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_endtime}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         category
   
   b
         cObj
   
   c
         Content object for the event category
         
         category {
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_category}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         categoryLink
   
   b
         cObj
   
   c
         Content object for the event category link(s)
         
         categoryLink {
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_category}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         calendar\_title
   
   b
         cObj
   
   c
         Content object for rendering the calendar name
         
         calendar\_title {
         
         current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_calendar}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         organizer
   
   b
         cObj
   
   c
         Content object for the event organizer
         
         organizer {
         
         current = 1
         
         typolink {
         
         title {
         
         current = 1
         
         htmlSpecialChars = 1
         
         }
         
         parameter.field = link
         
         }
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_organizer}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         location
   
   b
         cObj
   
   c
         Content object for the event location
         
         llocation {
         
         current = 1
         
         typolink {
         
         title {
         
         current = 1
         
         htmlSpecialChars = 1
         
         }
         
         parameter.field = link
         
         }
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         teaser
   
   b
         cObj
   
   c
         Content object for the event teaser
         
         teaser {
         
         current = 1
         
         required = 1
         
         \# if the teaser field is empty, use the description cropped to 150
         chars
         
         override {
         
         cObject = TEXT
         
         cObject {
         
         if.isFalse.field = teaser
         
         field = description
         
         required = 1
         
         crop = 150\|...\|1
         
         }
         
         }
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_teaser}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         description
   
   b
         cObj
   
   c
         Content object for the event description
         
         description {
         
         field >
         
         required = 1
         
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_descri
         ption}:&nbsp; \|</div>
         
         }
   
   d
         =< tt\_content.text.20


.. container:: table-row

   a
         image
   
   b
         cObj
   
   c
         Content object for the event image
         
         image {
         
         //17 = in text right
         
         textPos.override = 17
         
         layout.key.override = 17
         
         1 {
         
         altText.override.field = imagealttext
         
         titleText.override.field = imagetitletext
         
         caption.override.field = imagecaption
         
         }
         
         imgMax = 4
         
         imgList >
         
         imgList.override.current = 1
         
         imgPath = {$plugin.tx\_cal\_controller.uploadPath.image}
         
         stdWrap.dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_event\_image} \|</div>
         
         stdWrap.required = 1
         
         1.imageLinkWrap.enable.field >
         
         1.imageLinkWrap.enable.override = 1
         
         maxW = {$plugin.tx\_cal\_controller.singleMaxW}
         
         \# switch to turn on the lightbox: kj\_imagelightbox2
         
         \# 1.imageLightbox2 = 1
         
         }
   
   d
         =< tt\_content.image.20


.. container:: table-row

   a
         description\_image
   
   b
         cObj
   
   c
         Content object for rendering the description and the images with the
         regular 'text with image' (TEXTPIC) cObject
         
         description\_image {
         
         imgList.override {
         
         current >
         
         field = image
         
         }
         
         stdWrap.dataWrap >
         
         text < tt\_content.textpic.20.text
         
         text.20.field = description
         
         }
   
   d
         .image


.. container:: table-row

   a
         preview
   
   b
         cObj
   
   c
         Defines cropping for event description in event view.
         
         preview {
         
         crop = 100\|..
         
         stripHtml = 1
         
         }
   
   d
         plugin.tx\_cal\_controller.view.event.description


.. container:: table-row

   a
         attachment
   
   b
         cObj
   
   c
         Content object for event attachment
         
         attachment {
         
         layout = 1
         
         showFileSize = 1
         
         filePath = {$plugin.tx\_cal\_controller.uploadPath.media}
         
         20.stdWrap.if.isTrue.field = media
         
         20.stdWrap.dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_ev
         ent\_attachment}:&nbsp;\|</div>
         
         }
   
   d
         =< tt\_content.uploads


.. container:: table-row

   a
         attendee
   
   b
         cObj
   
   c
         Content object for event attendee
         
         attendee {
         
         current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_attendee}:&nbsp;
         \|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         attendeeIcon
   
   b
         String
   
   c
         Icon definition for the different attendee status: CHAIR, ACCEPTED or
         DECLINE
   
   d
         <img src="###IMG\_PATH###/%s.png" alt="%s" title="%s"/>


.. container:: table-row

   a
         isMonitoringEventLink
   
   b
         cObj
   
   c
         Content object for event subscription link of a logged in user when
         the user is currently monitoring the event
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         isNotMonitoringEventLink
   
   b
         cObj
   
   c
         Content object for event subscription link of a logged in user when
         the user is currently NOT monitoring the event
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         declineMeetingLink
   
   b
         cObj
   
   c
         Content object for decline meeting attendance link
         
         declineMeetingLink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_meeting\_changestatus}
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         acceptMeetingLink
   
   b
         cObj
   
   c
         Content object for accept meeting attendance link
         
         acceptMeetingLink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_meeting\_changestatus}
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         ics
   
   b
         cObj
   
   c
         Content object for event ics link
         
         ics {
         
         value = \|
         
         required = 1
         
         typolink {
         
         parameter.data = TSFE:id
         
         \# wrapping the parameter with the typenum of the ics page
         
         parameter.wrap = \|,{$plugin.tx\_cal\_controller.view.ics.typeNum}
         
         additionalParams.field = additionalParams
         
         title.dataWrap =
         \|{LLL:EXT:cal/controller/locallang.xml:l\_event\_icslink}
         
         }
         
         wrap = <div>\|</div>
         
         }
   
   d
         TEXT


.. container:: table-row

   a
         noEventFound
   
   b
         cObj
   
   c
         Content object if no event has been found
         
         noEventFound {
         
         dataWrap = {LLL:EXT:cal/controller/locallang.xml:l\_no\_results}
         
         }
   
   d
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.event.event]

plugin.tx\_cal\_controller.view.event.event.notify

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         subscriptionViewPid
   
   Data type
         Integer / PID
   
   Description
         The page id where the subscription manager view is allowed
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.notify.subscriptionViewPid}


.. container:: table-row

   Property
         confirmTemplate
   
   Data type
         String / Path
   
   Description
         Template for subscription confirmation
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.notify.confirmTemplate}


.. container:: table-row

   Property
         confirmTitle
   
   Data type
         String
   
   Description
         Title for subscription confirmation email
   
   Default
         Please confirm the event monitoring on www.abc.com


.. container:: table-row

   Property
         unsubscribeConfirmTemplate
   
   Data type
         String / Path
   
   Description
         Template for subscription stop confirmation
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.notify.unsubscribeConfirmTempl
         ate}


.. container:: table-row

   Property
         unsubscribeConfirmTitle
   
   Data type
         String
   
   Description
         Title for subscription stop confirmation email
   
   Default
         Please confirm the event monitoring stop on www.abc.com


.. container:: table-row

   Property
         all.onCreateTemplate
   
   Data type
         String / Path
   
   Description
         Template for email notification if an event is created.
         
         also: Constants
         
         Additionally to “all” you can define templates for individuals, like:
         3.onCreateTemplate. Now if the fe-user with the uid 3 is in the list
         of users to be notified, he will receive a notification based on
         “3.onCreateTemplate”
   
   Default
         {$plugin.tx\_cal\_controller.view.event.notify.all.onCreateTemplate}


.. container:: table-row

   Property
         all.onChangeTemplate
   
   Data type
         String / Path
   
   Description
         Template for email notification if an event has been changed.
         
         also: Constants
         
         Additionally to “all” you can define templates for individuals, like:
         3.onChangeTemplate. Now if the fe-user with the uid 3 is in the list
         of users to be notified, he will receive a notification based on
         “3.onChangeTemplate”
   
   Default
         {$plugin.tx\_cal\_controller.view.event.notify.all.onChangeTemplate}


.. container:: table-row

   Property
         all.onDeleteTemplate
   
   Data type
         String / Path
   
   Description
         Template for email notification if an event has been deleted.
         
         also: Constants
         
         Additionally to “all” you can define templates for individuals, like:
         3.onDeleteTemplate. Now if the fe-user with the uid 3 is in the list
         of users to be notified, he will receive a notification based on
         “3.onDeleteTemplate”
   
   Default
         {$plugin.tx\_cal\_controller.view.event.notify.all.onDeleteTemplate}


.. container:: table-row

   Property
         all.onCreateEmailTitle
   
   Data type
         String
   
   Description
         Title for notification emails on create.
         
         Additionally to “all” you can define a title for individuals, like:
         3.onCreateEmailTitle. Now if the fe-user with the uid 3 is in the list
         of users to be notified, he will receive a notification based on
         “3.onCreateEmailTitle ”
   
   Default
         The event ###TITLE### has been created


.. container:: table-row

   Property
         all.onChangeEmailTitle
   
   Data type
         String
   
   Description
         Title for notification emails on change.
         
         Additionally to “all” you can define a title for individuals, like:
         3.onChangeEmailTitle. Now if the fe-user with the uid 3 is in the list
         of users to be notified, he will receive a notification based on
         “3.onChangeEmailTitle ”
   
   Default
         The event ###TITLE### has been changed


.. container:: table-row

   Property
         all.onDeleteEmailTitle
   
   Data type
         String
   
   Description
         Title for notification emails on delete.
         
         Additionally to “all” you can define a title for individuals, like:
         3.onDeleteEmailTitle. Now if the fe-user with the uid 3 is in the list
         of users to be notified, he will receive a notification based on
         “3.onDeleteEmailTitle ”
   
   Default
         The event ###TITLE### has been deleted


.. container:: table-row

   Property
         emailAddress
   
   Data type
         String / Email
   
   Description
         Email address that notification emails are sent from.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailAddress}


.. container:: table-row

   Property
         emailReplyAddress
   
   Data type
         String / Email
   
   Description
         Reply-to address for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailAddress}


.. container:: table-row

   Property
         fromName
   
   Data type
         String
   
   Description
         From name for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailName}


.. container:: table-row

   Property
         replyToName
   
   Data type
         String
   
   Description
         Reply-to name for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailName}


.. container:: table-row

   Property
         organisation
   
   Data type
         String
   
   Description
         Organization for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailOrganisation}


.. container:: table-row

   Property
         dateFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         timeFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Time format
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.timeFormat}


.. container:: table-row

   Property
         currentUser
   
   Data type
         Configuration Array
   
   Description
         Add the (configurable) details of the currently logged in user for the notification-mail.
	 	 The detailed info of the currently logged in user is retrieved from the template (notifyOnCreate.tmpl, notifyOnChange.tmpl or notifyOnDelete.tmpl)
	 	 with the tag ###CURRENT_USER###. The structure of the info is given between ###CURRENT_USER_SUBPART###. Every field of the 'fe_users' record can
	 	 be used by converting the field-name to uppercase and putting it between '###', e.g. first_name --> ###FIRST_NAME###.
	 	 The fields can be wrapped by specifying tx_cal_controller.view.event.notify.currentUser.<field-name>_stdWrap { dataWrap = ... }, e.g.
	 	 tx_cal_controller.view.event.notify.currentUser.first_name_stdWrap { dataWrap = Firstname: | }


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.event.event.notify]

plugin.tx\_cal\_controller.view.event.event.remind

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         time
   
   Data type
         Integer
   
   Description
         Time in minutes, to send out the reminder before the event starts.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.remind.time}


.. container:: table-row

   Property
         all.template
   
   Data type
         String / Path
   
   Description
         Template for email reminder for an event.
         
         also: Constants
         
         Additionally to “all” you can define templates for individuals, like:
         3.template. Now if the fe-user with the uid 3 is in the list of users
         to be notified, he will receive a reminder based on “3.template ”
   
   Default
         {$plugin.tx\_cal\_controller.view.event.remind.all.template}


.. container:: table-row

   Property
         all.emailTitle
   
   Data type
         String
   
   Description
         Title for reminder emails.
         
         Additionally to “all” you can define a title for individuals, like:
         3.emailTitle. Now if the fe-user with the uid 3 is in the list of
         users to be notified, he will receive a reminder based on
         “3.emailTitle ”
   
   Default
         Reminder for event: ###TITLE###


.. container:: table-row

   Property
         emailAddress
   
   Data type
         String / Email
   
   Description
         Email address that notification emails are sent from.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailAddress}


.. container:: table-row

   Property
         emailReplyAddress
   
   Data type
         String / Email
   
   Description
         Reply-to address for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailAddress}


.. container:: table-row

   Property
         fromName
   
   Data type
         String
   
   Description
         From name for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailName}


.. container:: table-row

   Property
         replyToName
   
   Data type
         String
   
   Description
         Reply-to name for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailName}


.. container:: table-row

   Property
         organisation
   
   Data type
         String
   
   Description
         Organization for notification emails.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.emailOrganisation}


.. container:: table-row

   Property
         dateFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         timeFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Time format
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.timeFormat}


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.event.event.remind]

plugin.tx\_cal\_controller.view.event.event.meeting

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         ManagerTemplate
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.meeting.template}


.. container:: table-row

   Property
         onChangeTemplate
   
   Data type
         String / Path
   
   Description
         Template for email rescheduling a meeting.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.meeting.onChangeTemplate}


.. container:: table-row

   Property
         statusViewPid
   
   Data type
         Integer / PID
   
   Description
         The page id where the meeting-status view is allowed.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.meeting.statusViewPid}


.. container:: table-row

   Property
         managerTemplate
   
   Data type
         String / Path
   
   Description
         Template for the meeting manager view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.meeting.managerTemplate}


.. container:: table-row

   Property
         lookingAhead
   
   Data type
         Integer
   
   Description
         The time in seconds meetings without a status shall be displayed
   
   Default
         300000


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.event.event.meeting]


Location
""""""""

plugin.tx\_cal\_controller.view.location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         locationViewPid
   
   Data type
         Integer / PID
   
   Description
         Page to display the location view on. If this is not configured, then
         the current page will be used instead.
         
         also: FlexForm
   
   Default


.. container:: table-row

   Property
         createLocationViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating a location. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         editLocationViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing a location. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         deleteLocationViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting a location. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         locationTemplate
   
   Data type
         String / Path
   
   Description
         Template for generic location view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.location.locationTemplate}


.. container:: table-row

   Property
         locationTemplate4Partner
   
   Data type
         String / Path
   
   Description
         Template for location view when using the partner framework.
   
   Default
         {$plugin.tx\_cal\_controller.view.location.locationTemplate4Partner}


.. container:: table-row

   Property
         locationTemplate4Address
   
   Data type
         String / Path
   
   Description
         Template for location view when using tt\_address.
   
   Default
         {$plugin.tx\_cal\_controller.view.location.locationTemplate4Address}


.. container:: table-row

   Property
         maxDate
   
   Data type
         String / Date
   
   Description
         Maximum date to search for events in the future
   
   Default
         20200101


.. container:: table-row

   Property
         minDate
   
   Data type
         String / Date
   
   Description
         Minimum date to search for events in the past
   
   Default
         00000001


.. container:: table-row

   Property
         event.dateFormat
   
   Data type
         String
   
   Description
   
   
   Default
         %m.%d.%Y


.. container:: table-row

   Property
         event.startdate
   
   Data type
         cObj
   
   Description
   
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.location]

plugin.tx\_cal\_controller.view.location.location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         showMap
   
   Data type
         Boolean
   
   Description
         Enables the rendering of the wec\_map.
   
   Default
         {$plugin.tx\_cal\_controller.view.location.showMap}


.. container:: table-row

   Property
         map.apiKey
   
   Data type
         String
   
   Description
         API Key for Google Maps.
         
         see: `http://www.google.com/apis/maps/signup.html
         <http://www.google.com/apis/maps/signup.html>`_
   
   Default


.. container:: table-row

   Property
         map.mapWidth
   
   Data type
         Integer
   
   Description
         Width of the map.
   
   Default
         300


.. container:: table-row

   Property
         map.mapHeight
   
   Data type
         Integer
   
   Description
         Height of the map.
   
   Default
         300


.. container:: table-row

   Property
         map.showMapType
   
   Data type
         Boolean
   
   Description
         Defines whether the map type control should be shown.
   
   Default
         0


.. container:: table-row

   Property
         map.showScale
   
   Data type
         Boolean
   
   Description
         Defines whether the scale should be shown.
   
   Default
         0


.. container:: table-row

   Property
         map.showInfoWindow
   
   Data type
         Boolean
   
   Description
         Defines whether the info window should be opened when the page loads.
   
   Default
         0


.. container:: table-row

   Property
         map.showDirections
   
   Data type
         Boolean
   
   Description
         Defines whether directions should be available.
   
   Default
         1


.. container:: table-row

   Property
         map.showWrittenDirections
   
   Data type
         Boolean
   
   Description
         Defines whether written directions should be shown in addition to the
         map.
   
   Default
         1


.. container:: table-row

   Property
         map.prefillAddress
   
   Data type
         Boolean
   
   Description
         Defines whether an address should be prefilled for logged in users.
   
   Default
         1


.. container:: table-row

   Property
         map.zoomLevel
   
   Data type
         Integer
   
   Description
         Default zoom level. If not set, autozoom will be used.
   
   Default


.. container:: table-row

   Property
         map.centerLat
   
   Data type
         Double
   
   Description
         Default center latitude. If not set, autocenter will be used.
   
   Default


.. container:: table-row

   Property
         map.centerLong
   
   Data type
         Double
   
   Description
         Default center longitude. If not set, autocenter will be used.
   
   Default


.. container:: table-row

   Property
         name
   
   Data type
         cObj
   
   Description
         Content object for location name
         
         name {
         
         current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_name}:&nbsp;
         \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         street
   
   Data type
         cObj
   
   Description
         Content object for location street
         
         street {
         
         current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_street}:&nbsp;
         \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         city
   
   Data type
         cObj
   
   Description
         Content object for location city
         
         city {
         
         current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_city}:&nbsp;
         \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         zip
   
   Data type
         cObj
   
   Description
         Content object for location zip
         
         zip {
         
         current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_zip}:&nbsp;
         \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         phone
   
   Data type
         cObj
   
   Description
         Content object for location phone number
         
         phone {
         
         current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_phone}:&nbsp;
         \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         countryzone
   
   Data type
         cObj
   
   Description
         Content object for location countryzone
         
         countryzone {
         
         current = 1
         
         required = 1
         
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_cou
         ntryzone}:&nbsp; \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         countryzoneStaticInfo
   
   Data type
         cObj
   
   Description
         Content object for location countryzoneStaticInfo
         
         countryzoneStaticInfo {
         
         current = 1
         
         required = 1
         
         ifEmpty.field = countryzone
         
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_cou
         ntryzone}:&nbsp; \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         country
   
   Data type
         cObj
   
   Description
         Content object for location country
         
         country {
         
         current = 1
         
         required = 1
         
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_cou
         ntry}:&nbsp; \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         countryStaticInfo
   
   Data type
         cObj
   
   Description
         Content object for location countryStaticInfo
         
         countryStaticInfo {
         
         current = 1
         
         required = 1
         
         ifEmpty.field = countr
         
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_cou
         ntry}:&nbsp; \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         description
   
   Data type
         cObj
   
   Description
         Content object for location description
         
         description {
         
         field = description
         
         required = 1
         
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_des
         cription}:&nbsp; \|</div>
         
         }
   
   Default
         Reference to tt\_content.text.20


.. container:: table-row

   Property
         email
   
   Data type
         cObj
   
   Description
         Content object for location email
         
         email {
         
         current = 1
         
         typolink.parameter.current = 1
         
         required = 1
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_email}:&nbsp;
         \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         link
   
   Data type
         cObj
   
   Description
         Content object for location link
         
         link {
         
         dataWrap =
         <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_link}:&nbsp;
         \|</div>
         
         }
   
   Default
         .email


.. container:: table-row

   Property
         image
   
   Data type
         cObj
   
   Description
         Content object for location image
   
   Default
         =< plugin.tx\_cal\_controller.view.event.event.image


.. container:: table-row

   Property
         includeEventsInResult
   
   Data type
         Boolean
   
   Description
         Displays location related events
   
   Default
         1


.. container:: table-row

   Property
         eventLink
   
   Data type
         cObj
   
   Description
         Content object for location event link
         
         eventLink {
         
         current = 1
         
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_location\_rel
         atedevents}:&nbsp; \|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         noLocationFound
   
   Data type
         cObj
   
   Description
         Content object if no location has been found
         
         noLocationFound {
         
         dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_no\_location\_results}
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         addIcon
   
   Data type
         String
   
   Description
         Image tag for icon used in link to frontend editing of a location.
   
   Default
         <img src="###IMG\_PATH###/add.gif" border="0"/>


.. container:: table-row

   Property
         editIcon
   
   Data type
         String
   
   Description
         Image tag for icon used in link to frontend editing of a location.
   
   Default
         <img src="###IMG\_PATH###/edit.gif" border="0"/>


.. container:: table-row

   Property
         deleteIcon
   
   Data type
         String
   
   Description
         Image tag for icon used in link to frontend editing of a location.
   
   Default
         <img src="###IMG\_PATH###/delete.gif" border="0"/>


.. container:: table-row

   Property
         addLink
   
   Data type
         cObj
   
   Description
         Configuration for the add location link.
         
         addLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_create\_location}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         editLink
   
   Data type
         cObj
   
   Description
         Configuration for the edit location link.
         
         editLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_edit\_location}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         deleteLink
   
   Data type
         cObj
   
   Description
         Configuration for the delete location link.
         
         deleteLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_delete\_location}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.location.location]


Organizer
"""""""""

plugin.tx\_cal\_controller.view.organizer

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         organizerViewPid
   
   Data type
         Integer / PID
   
   Description
         Page to display the organizer view on. If this is not configured, then
         the current page will be used instead.
         
         also: FlexForm
   
   Default


.. container:: table-row

   Property
         createOrganizerViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating an organizer. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         editOrganizerViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing an organizer. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         deleteOrganizerViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting an organizer. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         organizerTemplate
   
   Data type
         String / Path
   
   Description
         Template for generic organizer view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.organizer.organizerTemplate}


.. container:: table-row

   Property
         organizerTemplate4Partner
   
   Data type
         String / Path
   
   Description
         Template for organizer view when using the partner framework.
   
   Default
         {$plugin.tx\_cal\_controller.view.organizer.organizerTemplate4Partner}


.. container:: table-row

   Property
         organizerTemplate4Address
   
   Data type
         String / Path
   
   Description
         Template for organizer view when using tt\_address.
   
   Default
         {$plugin.tx\_cal\_controller.view.organizer.organizerTemplate4Address}


.. container:: table-row

   Property
         organizerTemplate4FEUser
   
   Data type
         String / Path
   
   Description
         Template for organizer view when using fe\_users.
   
   Default
         {$plugin.tx\_cal\_controller.view.organizer.organizerTemplate4FEUser}


.. container:: table-row

   Property
         maxDate
   
   Data type
         String / Date
   
   Description
         Maximum date to search for events in the future
   
   Default
         20200101


.. container:: table-row

   Property
         minDate
   
   Data type
         String / Date
   
   Description
         Minimum date to search for events in the past
   
   Default
         00000001


.. container:: table-row

   Property
         event.dateFormat
   
   Data type
         String
   
   Description
   
   
   Default
         %m.%d.%Y


.. container:: table-row

   Property
         event.startdate
   
   Data type
         cObj
   
   Description
   
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.organizer]

plugin.tx\_cal\_controller.view.organizer.organizer <
plugin.tx\_cal\_controller.view.location.location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         noOrganizerFound
   
   Data type
         cObj
   
   Description
         Content object if no organizer has been found
         
         noOrganizerFound {
         
         dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_no\_organizer\_results}
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         deleteIcon
   
   Data type
         String
   
   Description
         Image tag for icon used in link to frontend editing of a location.
   
   Default
         <img src="###IMG\_PATH###/delete.gif" border="0"/>


.. container:: table-row

   Property
         addLink
   
   Data type
         cObj
   
   Description
         Configuration for the add organizer link.
         
         addLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_create\_organizer}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         editLink
   
   Data type
         cObj
   
   Description
         Configuration for the edit organizer link.
         
         editLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_edit\_organizer}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         deleteLink
   
   Data type
         cObj
   
   Description
         Configuration for the delete organizer link.
         
         deleteLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_delete\_organizer}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.organizer.organizer]


Calendar
""""""""

plugin.tx\_cal\_controller.view.calendar

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         createCalendarViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating a calendar. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         editCalendarViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing a calendar. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         deleteCalendarViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting a calendar. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         nearbyDistance
   
   Data type
         Integer
   
   Description
         The distance for events in a nearby calendar.
   
   Default
         50


.. container:: table-row

   Property
         nearbyAdditionalTable
   
   Data type
         String
   
   Description
         Table where event locations are stored.
   
   Default
         tx\_cal\_location


.. container:: table-row

   Property
         nearbyAdditionalWhere
   
   Data type
         String
   
   Description
         WHERE clause to select nearby locations.
         
         nearbyAdditionalWhere = AND tx\_cal\_calendar.nearby = 1 AND
         tx\_cal\_event.location\_id > 0 AND tx\_cal\_event.location\_id =
         tx\_cal\_location.uid AND 6367.41\*SQRT(2\*(1-cos(RADIANS(tx\_cal\_loc
         ation.latitude))\*cos(RADIANS(###LATITUDE###))\*(sin(RADIANS(tx\_cal\_
         location.longitude))\*sin(RADIANS(###LONGITUDE###))+cos(RADIANS(tx\_ca
         l\_location.longitude))\*cos(RADIANS(###LONGITUDE###)))-sin(RADIANS(tx
         \_cal\_location.latitude))\*sin(RADIANS(###LATITUDE###)))) <=
         ###DISTANCE###
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.calendar]

plugin.tx\_cal\_controller.view.calendar.calendar

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         Property:
   
   b
         **Data type:**
   
   c
         **Description:**
   
   d
         **Default:**


.. container:: table-row

   a
         addIcon
   
   b
         String
   
   c
         Image tag for icon used in link to frontend editing of a calendar.
   
   d
         <img src="###IMG\_PATH###/create\_calendar.gif" border="0"/>


.. container:: table-row

   a
         editIcon
   
   b
         String
   
   c
         Image tag for icon used in link to frontend editing of a calendar.
   
   d
         <img src="###IMG\_PATH###/edit.gif" border="0"/>


.. container:: table-row

   a
         deleteIcon
   
   b
         String
   
   c
         Image tag for icon used in link to frontend editing of a calendar.
   
   d
         <img src="###IMG\_PATH###/delete.gif" border="0"/>


.. container:: table-row

   a
         addLink
   
   b
         cObj
   
   c
         Configuration for the add organizer link.
         
         addLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_create\_calendar}
         
         }
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         editLink
   
   b
         cObj
   
   c
         Configuration for the add calendar link.
         
         editLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_edit\_calendar}
         
         }
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   a
         deleteLink
   
   b
         cObj
   
   c
         Configuration for the delete calendar link.
         
         deleteLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_delete\_calendar}
         
         }
   
   d
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.calendar.calendar]


Category
""""""""

plugin.tx\_cal\_controller.view.category

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         createCategoryViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating a category. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         editCategoryViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing a category. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         deleteCategoryViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting a category. If this is not
         configured, then the current page will be used instead.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.category]

plugin.tx\_cal\_controller.view.category.category

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         defaultHeaderStyle
   
   Data type
         String
   
   Description
         Defines the default header style
   
   Default
         default\_categoryheader


.. container:: table-row

   Property
         defaultBodyStyle
   
   Data type
         String
   
   Description
         Defines the default body style
   
   Default
         default\_categorybody


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.category.category]

plugin.tx\_cal\_controller.view.category.tree

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         calendar
   
   Data type
         String / CSV
   
   Description
         Defines the calendars shown in the tree (csv of ids)
   
   Default


.. container:: table-row

   Property
         category
   
   Data type
         String / CSV
   
   Description
         Defines the categories shown in the tree (csv of ids)
   
   Default


.. container:: table-row

   Property
         calendarTitle
   
   Data type
         cObj
   
   Description
         Content object to render the calendar title
         
         calendarTitle {
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         rootElement
   
   Data type
         cObj
   
   Description
         Content object to render each root element of the tree
         
         rootElement {
         
         wrap = <table class="treelevel0"><tr><td>\|</td></tr></table>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         selector
   
   Data type
         cObj
   
   Description
         Content object to render the selector
         
         selector {
         
         wrap = <input type="checkbox" name="tx\_cal\_controller[category][]"
         value="###UID###" \| />
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         element
   
   Data type
         String
   
   Description
         Defines a root node of the tree
   
   Default
         <span class="###HEADERSTYLE###\_bullet
         ###HEADERSTYLE###\_legend\_bullet" >&bull;</span><span
         class="###HEADERSTYLE###\_text">###TITLE###</span>


.. container:: table-row

   Property
         emptyElement
   
   Data type
         String
   
   Description
         Defines an element if the tree has no nodes
   
   Default
         <br/><br/>


.. container:: table-row

   Property
         subElement
   
   Data type
         String
   
   Description
         Defines a sub node of the tree
   
   Default
         <br /><table class="treelevel###LEVEL###" id="treelevel###UID###">


.. container:: table-row

   Property
         subElement\_wrap
   
   Data type
         String
   
   Description
         Defines a wrap for sub node of the tree
   
   Default
         <tr><td>\|</td></tr>


.. container:: table-row

   Property
         subElement\_pre
   
   Data type
         String
   
   Description
         Defines the trailer for a branch level
   
   Default
         </table>


.. container:: table-row

   Property
         categorySelectorSubmit
   
   Data type
         String
   
   Description
         Defines the submit button
   
   Default
         <input type="image" class="refresh\_calendar"
         src="###IMG\_PATH###/refresh.gif" alt="###REFRESH\_LABEL###"
         title="###REFRESH\_LABEL###">


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.category.tree]


Day
"""

plugin.tx\_cal\_controller.view.day

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         dayViewPid
   
   Data type
         String / PID
   
   Description
         Page to display the day view on. If this is not configured, then the
         current page will be used instead.
         
         also: FlexForm
   
   Default


.. container:: table-row

   Property
         dayViewLink
   
   Data type
         cObj
   
   Description
         Configuration for the day view link.
         
         nextDayLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_next\_day}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultViewLinkSetup


.. container:: table-row

   Property
         nextDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the next day link.
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultViewLinkSetup


.. container:: table-row

   Property
         legendNextDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the next day link displayed in the legend.
   
   Default
         .nextDayLink


.. container:: table-row

   Property
         prevDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the previous day link.
         
         prevDayLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_last\_day}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         legendPrevDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the next day link displayed in the legend.
   
   Default
         .prevDayLink


.. container:: table-row

   Property
         dayStart
   
   Data type
         Integer with leading zeros
   
   Description
         Start time for the day view.
         
         also: FlexForm
   
   Default
         0700


.. container:: table-row

   Property
         dayEnd
   
   Data type
         Integer with leading zeros
   
   Description
         End time for the day view.
         
         also: FlexForm
   
   Default
         2300


.. container:: table-row

   Property
         dynamic
   
   Data type
         Boolean
   
   Description
         Cuts off empty times before the first and after the last event of a
         day.
   
   Default
         0


.. container:: table-row

   Property
         gridLength
   
   Data type
         Integer
   
   Description
         Length of time in minutes for each grid on the day view. Should be
         evenly divisible into 60 minutes (ex. 15,30,60)
         
         also: FlexForm
   
   Default
         15


.. container:: table-row

   Property
         startPointCorrection
   
   Data type
         Integer
   
   Description
         Corrects the starting point to fetch events. If you only have the
         dayview to display you can set it to 0, but if you have e.g. a month
         also in your dayview, you should enter a value in seconds, so the
         month will be filled with events too - not only the one day
   
   Default
         5616000


.. container:: table-row

   Property
         endPointCorrection
   
   Data type
         Integer
   
   Description
         Same as startingPointCorrection but for the end point
   
   Default
         5616000


.. container:: table-row

   Property
         dayTemplate
   
   Data type
         String / Path
   
   Description
         Template for the day view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.day.dayTemplate}


.. container:: table-row

   Property
         nextDaySymbol
   
   Data type
         String
   
   Description
         Symbol to use for browsing to next day.
   
   Default
         &rsaquo;


.. container:: table-row

   Property
         previousDaySymbol
   
   Data type
         String
   
   Description
         Symbol to use for browsing to previous day.
   
   Default
         &lsaquo;


.. container:: table-row

   Property
         legendPrevDayLink
   
   Data type
         String
   
   Description
         Arrow image within the legend for going back to the previous day.
   
   Default
         <img src="###IMG\_PATH###/left\_arrows.gif" width="16" height="20"
         border="0" align="left" />


.. container:: table-row

   Property
         legendNextDayLink
   
   Data type
         String
   
   Description
         Arrow image within the legend for going forward to the next day.
   
   Default
         <img src="###IMG\_PATH###/right\_arrows.gif" width="16" height="20"
         border="0" align="right" />


.. container:: table-row

   Property
         dayTimeCell
   
   Data type
         String
   
   Description
         Cell containing the time
   
   Default
         <tr><td rowspan='%s' align="center" valign="top" width="60"
         class="timeborder">%s</td><td bgcolor="#a1a5a9" width="1"
         height='%s'></td>


.. container:: table-row

   Property
         dayTimeCell2
   
   Data type
         String
   
   Description
         Cell inbetween time and the day table
   
   Default
         <tr><td bgcolor="#a1a5a9" width="1" height="%s"></td>


.. container:: table-row

   Property
         dayEventPre
   
   Data type
         String
   
   Description
         Pre event wrap
   
   Default
         <td rowspan="%s" align="left" valign="top"


.. container:: table-row

   Property
         dayEventPost
   
   Data type
         String
   
   Description
         Post event wrap
   
   Default
         </td>


.. container:: table-row

   Property
         classDayborder
   
   Data type
         String
   
   Description
         Major time divider in day view. By default, this is the solid line
         every 30 minutes.
   
   Default
         class="dayborder"


.. container:: table-row

   Property
         classDayborder2
   
   Data type
         String
   
   Description
         Minor time divider in day view. By default, this is the dotted line
         every 15 minutes.
   
   Default
         class="dayborder2"


.. container:: table-row

   Property
         normalCell
   
   Data type
         String
   
   Description
         Wrap for a single cell in day view.
   
   Default
         <td colspan="%s" %s>%s&nbsp;</td>


.. container:: table-row

   Property
         dayFinishRow
   
   Data type
         String
   
   Description
         Final element for a row in day view.
   
   Default
         </tr>


.. container:: table-row

   Property
         dateFormatWeekList
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for weeks within day view.
   
   Default
         %A, %b %d


.. container:: table-row

   Property
         dateFormatDay
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format of the day displayed at the top of the view.
   
   Default
         %a, %b %d


.. container:: table-row

   Property
         timeFormatDay
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Time format for hours shown within the day view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.timeFormat}


.. container:: table-row

   Property
         strftimeTitleStartFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the title start date
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         strftimeTitleEndFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the title end date
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         titleWrap
   
   Data type
         cObj
   
   Description
         Formats the week title
         
         titleWrap.1 = TEXT
         
         titleWrap.1 {
         
         data = register:cal\_day\_starttime
         
         }
         
         #titleWrap.3 = TEXT
         
         #titleWrap.3 {
         
         \# data = register:cal\_day\_starttime
         
         \# date = W
         
         \# wrap = &nbsp;(WK \|)
         
         #}
         
         Remove the “#” if you want to have the weeknumber
   
   Default
         COA


.. container:: table-row

   Property
         dontShowOldEvents
   
   Data type
         boolean
   
   Description
         Hide events in the past:0 = no filtering1 = filter everything that has
         start\_time < “now”
         
         2 = filter old events, but keep those from “today”
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.day]

plugin.tx\_cal\_controller.view.day.event <
plugin.tx\_cal\_controller.view.event.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         alldayTitle
   
   Data type
         cObj
   
   Description
         Content object for the event title of an all day event.
         
         alldayTitle {
         
         crop = 15\|..
         
         dataWrap = <div>\|</div>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         title
   
   Data type
         cObj
   
   Description
         Content object for the event title.
         
         title {
         
         dataWrap >
         
         }
   
   Default


.. container:: table-row

   Property
         starttime
   
   Data type
         cObj
   
   Description
         Content object for the event start time
         
         starttime {
         
         dataWrap >
         
         }
   
   Default


.. container:: table-row

   Property
         endtime
   
   Data type
         cObj
   
   Description
         Content object for the event end time
         
         endtime {
         
         required = 1
         
         dataWrap >
         
         }
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.day.event]


Week
""""

plugin.tx\_cal\_controller.view.week

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         weekViewPid
   
   Data type
         String / PID
   
   Description
         Page to display the week view on. If this is not configured, then the
         current page will be used instead.
         
         also: FlexForm
   
   Default


.. container:: table-row

   Property
         weekViewLink
   
   Data type
         cObj
   
   Description
         Configuration for the day view link.
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultViewLinkSetup


.. container:: table-row

   Property
         nextWeekLink
   
   Data type
         cObj
   
   Description
         Configuration for the next week link.
         
         nextWeekLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_next\_week}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         prevWeekLink
   
   Data type
         cObj
   
   Description
         Configuration for the previous week link.
         
         prevWeekLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_last\_week}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         nextDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the next day link.
         
         See: plugin.tx\_cal\_controller.view.day.nextDayLink
   
   Default
         plugin.tx\_cal\_controller.view.day.nextDayLink


.. container:: table-row

   Property
         legendNextDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the next day link displayed in the legend.
         
         See: plugin.tx\_cal\_controller.view.day.legendNextDayLink
   
   Default
         plugin.tx\_cal\_controller.view.day.legendNextDayLink


.. container:: table-row

   Property
         prevDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the previous day link.
         
         See: plugin.tx\_cal\_controller.view.day.prevDayLink
   
   Default
         plugin.tx\_cal\_controller.view.day.prevDayLink


.. container:: table-row

   Property
         legendPrevDayLink
   
   Data type
         cObj
   
   Description
         Configuration for the next day link displayed in the legend.
         
         See: plugin.tx\_cal\_controller.view.day.legendPrevDayLink
   
   Default
         plugin.tx\_cal\_controller.view.day.legendPrevDayLink


.. container:: table-row

   Property
         dynamic
   
   Data type
         Boolean
   
   Description
         Cuts off empty times before the first and after the last event of a
         day.
   
   Default
         0


.. container:: table-row

   Property
         startPointCorrection
   
   Data type
         Integer
   
   Description
         Corrects the starting point to fetch events. If you only have the
         weekview to display you can set it to 0, but if you have e.g. a month
         also in your weekview, you should enter a value in seconds, so the
         month will be filled with events too - not only the one week
   
   Default
         5616000


.. container:: table-row

   Property
         endPointCorrection
   
   Data type
         Integer
   
   Description
         Same as startingPointCorrection but for the end point
   
   Default
         5616000


.. container:: table-row

   Property
         weekTemplate
   
   Data type
         String / Path
   
   Description
         Template for the week view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.week.weekTemplate}


.. container:: table-row

   Property
         nextWeekSymbol
   
   Data type
         String
   
   Description
         Symbol to use for browsing to next week.
   
   Default
         &raquo;


.. container:: table-row

   Property
         previousWeekSymbol
   
   Data type
         String
   
   Description
         Symbol to use for browsing to previous week.
   
   Default
         &laquo;


.. container:: table-row

   Property
         nextDaySymbol
   
   Data type
         String
   
   Description
         Symbol to use for browsing to next day.
         
         see: plugin.tx\_cal\_controller.view.day.nextDaySymbol
   
   Default
         plugin.tx\_cal\_controller.view.day.nextDaySymbol


.. container:: table-row

   Property
         previousDaySymbol
   
   Data type
         String
   
   Description
         Symbol to use for browsing to previous day.
         
         see: plugin.tx\_cal\_controller.view.day.previousDaySymbol
   
   Default
         plugin.tx\_cal\_controller.view.day.previousDaySymbol


.. container:: table-row

   Property
         weekDisplayFullHour
   
   Data type
         String
   
   Description
         Row for a full hour.
   
   Default
         <tr><td colspan="4" rowspan="%s" align="center" valign="top"
         width="60" class="timeborder">%s</td><td bgcolor="#a1a5a9" width="1"
         height="%s"></td>


.. container:: table-row

   Property
         weekDisplayInbetween
   
   Data type
         String
   
   Description
         Cells within the weekDisplayFullHour
         
         see: plugin.tx\_cal\_controller.view.week.weekDisplayFullHour
   
   Default
         <tr><td bgcolor="#a1a5a9" width="1" height="%s"></td>


.. container:: table-row

   Property
         weekday\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Wrap around each weekday title.
   
   Default
         wrap = <span class="V9BOLD">\|</span>


.. container:: table-row

   Property
         classWeekborder
   
   Data type
         String
   
   Description
         Major time divider in day view. By default, this is the dotted line
         every 15 minutes.
   
   Default
         class="weekborder"


.. container:: table-row

   Property
         weekEventPre
   
   Data type
         String
   
   Description
         Pre event wrap.
         
         see: plugin.tx\_cal\_controller.view.day.dayEventPre
   
   Default
         plugin.tx\_cal\_controller.view.day.dayEventPre


.. container:: table-row

   Property
         weekEventPost
   
   Data type
         String
   
   Description
         Post event wrap.
         
         see: plugin.tx\_cal\_controller.view.day.weekEventPost
   
   Default
         plugin.tx\_cal\_controller.view.day.weekEventPost


.. container:: table-row

   Property
         normalCell
   
   Data type
         String
   
   Description
         Wrap for a single cell in week view.
         
         see: plugin.tx\_cal\_controller.view.day.normalCell
   
   Default
         plugin.tx\_cal\_controller.view.day.normalCell


.. container:: table-row

   Property
         weekFinishRow
   
   Data type
         String
   
   Description
         Final element for a row in week view.
         
         see: plugin.tx\_cal\_controller.view.day.dayFinishRow
   
   Default
         plugin.tx\_cal\_controller.view.day.dayFinishRow


.. container:: table-row

   Property
         dateFormatWeekList
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for weeks within week view.
   
   Default
         %a, %b %d


.. container:: table-row

   Property
         dateFormatWeek
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for week show at the top of view.
         
         see: plugin.tx\_cal\_controller.view.dateFormat
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         timeFormatWeek
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Time format for hours shown within the week view.
         
         see: plugin.tx\_cal\_controller.view.timeFormat
   
   Default
         {$plugin.tx\_cal\_controller.view.timeFormat}


.. container:: table-row

   Property
         timeFormatWeek
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Time format for hours shown in the very left column of the week view.
   
   Default
         %I%p


.. container:: table-row

   Property
         legendPrevDayLink
   
   Data type
         String
   
   Description
         Arrow image within the legend for going back to the previous day.
   
   Default
         <img src="###IMG\_PATH###/left\_arrows.gif" alt="###L\_PREV###"
         class="nextweek\_arrow" />


.. container:: table-row

   Property
         legendNextDayLink
   
   Data type
         String
   
   Description
         Arrow image within the legend for going forward to the next day.
   
   Default
         <img src="###IMG\_PATH###/right\_arrows.gif" alt="###L\_NEXT###"
         class="previousweek\_arrow" />


.. container:: table-row

   Property
         strftimeTitleStartFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the title start date
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         strftimeTitleEndFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the title end date
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         titleWrap
   
   Data type
         cObj
   
   Description
         Formats the week title
         
         titleWrap.1 = TEXT
         
         titleWrap.1 {
         
         data = register:cal\_week\_starttime
         
         }
         
         titleWrap.2 = TEXT
         
         titleWrap.2 {
         
         data = register:cal\_week\_endtime
         
         wrap = &nbsp;-&nbsp;\|
         
         }
         
         #titleWrap.3 = TEXT
         
         #titleWrap.3 {
         
         \# data = register:cal\_week\_starttime
         
         \# date = W
         
         \# wrap = &nbsp;(WK \|)
         
         #}
         
         Remove the “#” if you want to have the weeknumber
   
   Default
         COA


.. container:: table-row

   Property
         dontShowOldEvents
   
   Data type
         boolean
   
   Description
         Hide events in the past:0 = no filtering1 = filter everything that has
         start\_time < “now”
         
         2 = filter old events, but keep those from “today”
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.week]

plugin.tx\_cal\_controller.view.week.event <
plugin.tx\_cal\_controller.view.day.event


Month
"""""

plugin.tx\_cal\_controller.view.month

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         monthViewPid
   
   Data type
         String / PID
   
   Description
         Page to display the month view on. If this is not configured, then the
         current page will be used instead.
         
         also: FlexForm
   
   Default


.. container:: table-row

   Property
         monthViewLink
   
   Data type
         cObj
   
   Description
         Configuration for the month view link.
         
         See: plugin.tx\_cal\_controller.view.defaultViewLinkSetupplugin.tx\_ca
         l\_controller.view.defaultViewLinkSetup
   
   Default
         plugin.tx\_cal\_controller.view.defaultViewLinkSetup


.. container:: table-row

   Property
         startPointCorrection
   
   Data type
         Integer
   
   Description
         Corrects the starting point to fetch events. If you only have only one
         month in your view to display you can leave it to 0, but if you have
         e.g. a small months also in your view, you should enter a value in
         seconds, so the other months will be filled with events too
         (60\*60\*24\*35)
   
   Default
         3024000


.. container:: table-row

   Property
         endPointCorrection
   
   Data type
         Integer
   
   Description
         Same as startingPointCorrection but for the end point
   
   Default
         3024000


.. container:: table-row

   Property
         monthTemplate
   
   Data type
         String / Path
   
   Description
         Template for the month view
         
         also: Contants
   
   Default
         {$plugin.tx\_cal\_controller.view.month.monthTemplate}


.. container:: table-row

   Property
         monthSmallTemplate
   
   Data type
         String / Path
   
   Description
         Template for a small month
         
         also: Contants
   
   Default
         {$plugin.tx\_cal\_controller.view.month.monthSmallTemplate}


.. container:: table-row

   Property
         monthMediumTemplate
   
   Data type
         String / Path
   
   Description
         Template for a medium month, like in year view
         
         also: Contants
   
   Default
         {$plugin.tx\_cal\_controller.view.month.monthMediumTemplate}


.. container:: table-row

   Property
         monthLargeTemplate
   
   Data type
         String / Path
   
   Description
         Template for a large month as it is in the default month view
         
         also: Contants
   
   Default
         {$plugin.tx\_cal\_controller.view.month.monthLargeTemplate}


.. container:: table-row

   Property
         monthMiniTemplate
   
   Data type
         String
   
   Description
         Template for a mini month as it is in the mini month view
   
   Default
         ###MONTH\_SMALL\|+0###


.. container:: table-row

   Property
         monthMakeMiniCal
   
   Data type
         Boolean
   
   Description
         Enable this to create a single small calendar
   
   Default
         0


.. container:: table-row

   Property
         navigation
   
   Data type
         Boolean
   
   Description
         Enables the horizontal sidebar
   
   Default
         1


.. container:: table-row

   Property
         horizontalSidebarTemplate
   
   Data type
         String / Path
   
   Description
         Template for a navigation bar in the month view
         
         also: Contants
   
   Default
         {$plugin.tx\_cal\_controller.view.month.horizontalSidebarTemplate}


.. container:: table-row

   Property
         showListInMonthView
   
   Data type
         Boolean
   
   Description
         Show this month's events in a list view. This is only applicable for
         templates that contain the ###LIST### marker, such as the default
         large month template. In the standard configuration, mini calendars do
         not support a list view.
         
         also: FlexForm
   
   Default
         0


.. container:: table-row

   Property
         useListEventRenderSettingsView
   
   Data type
         String
   
   Description
         If you show the month's events in a list view (see setting
         'showListInMonthView'), then you can configure here from which view
         the rendering settings for the events should be used. By default it's
         the regular listView rendering, but you might want to use settings
         from a different view.
   
   Default
         list


.. container:: table-row

   Property
         dateFormatMonth
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for month shown at top of view.
   
   Default
         %B


.. container:: table-row

   Property
         weekdayFormatSmallMonth
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for weekdays in small month.
   
   Default
         %a


.. container:: table-row

   Property
         weekdayLengthSmallMonth
   
   Data type
         Integer
   
   Description
         Max length of weekdays names in small month. 0 = full length
   
   Default
         2


.. container:: table-row

   Property
         weekdayFormatMediumMonth
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for weekdays in medium (year) month.
   
   Default
         %a


.. container:: table-row

   Property
         weekdayLengthMediumMonth
   
   Data type
         Integer
   
   Description
         Max length of weekdays names in medium (year) month. 0 = full length
   
   Default
         0


.. container:: table-row

   Property
         weekdayFormatLargeMonth
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for weekdays in large month.
   
   Default
         %A


.. container:: table-row

   Property
         weekdayLengthLargeMonth
   
   Data type
         Integer
   
   Description
         Max length of weekdays names in large month. 0 = full length
   
   Default
         0


.. container:: table-row

   Property
         smallLink\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for all day events and standard events in small month view.
   
   Default
         wrap = <span class="bold">\|</span>


.. container:: table-row

   Property
         nextMonthLink
   
   Data type
         cObj
   
   Description
         Configuration for the next month link.
         
         See: plugin.tx\_cal\_controller.view.defaultLinkSetup
         
         nextMonthLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_next\_month}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         prevMonthLink
   
   Data type
         cObj
   
   Description
         Configuration for the next month link.
         
         See: plugin.tx\_cal\_controller.view.defaultLinkSetup
         
         prevMonthLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_last\_month}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         monthSmallStyle
   
   Data type
         String
   
   Description
         Additional styling for small month view.
   
   Default
         monthSmallBasic


.. container:: table-row

   Property
         monthMediumStyle
   
   Data type
         String
   
   Description
         Additional styling for medium month view.
   
   Default
         monthMediumBasic


.. container:: table-row

   Property
         monthLargeStyle
   
   Data type
         String
   
   Description
         Additional styling for large month view.
   
   Default
         monthLargeBasic


.. container:: table-row

   Property
         monthMiniTemplate
   
   Data type
         String
   
   Description
         Template for a mini month as it is in the mini month view
   
   Default
         ###MONTH\_SMALL\|+0###


.. container:: table-row

   Property
         monthMakeMiniCal
   
   Data type
         Boolean
   
   Description
         Enable this to create a single small calendar
         
         also: FlexForm
   
   Default
         0


.. container:: table-row

   Property
         navigation
   
   Data type
         Booelan
   
   Description
         Enables the horizontal sidebar
   
   Default
         1


.. container:: table-row

   Property
         monthOffStyle
   
   Data type
         String
   
   Description
         CSS class for days not in the current month.
   
   Default
         monthOff


.. container:: table-row

   Property
         monthSelectedStyle
   
   Data type
         String
   
   Description
         CSS class for the selected day in the current month (ie. today).
   
   Default
         monthSelected


.. container:: table-row

   Property
         monthSelectedWeekStyle
   
   Data type
         String
   
   Description
         CSS class for the selected week in the current month
   
   Default
         monthSelectedWeek


.. container:: table-row

   Property
         monthWeekendStyle
   
   Data type
         String
   
   Description
         CSS class for the weekend in the month (ie. Today).
   
   Default
         monthWeekend


.. container:: table-row

   Property
         monthTodayStyle
   
   Data type
         String
   
   Description
         CSS class for today in the current month.
   
   Default
         monthToday


.. container:: table-row

   Property
         monthCurrentWeekStyle
   
   Data type
         String
   
   Description
         CSS class for the current week in the current month.
   
   Default
         monthCurrentWeek


.. container:: table-row

   Property
         monthCornerStyle
   
   Data type
         String
   
   Description
         CSS class for the upper left corner in a month
   
   Default
         monthCorner


.. container:: table-row

   Property
         monthDayOfWeekStyle
   
   Data type
         String
   
   Description
         CSS class that adds the weekday number to each day, where sunday = 0
         and monday to saturday = 1-6. Doesn't take care of the TS setting
         'weekStartDay' yet.
   
   Default
         cal\_day%s


.. container:: table-row

   Property
         eventDayStyle
   
   Data type
         String
   
   Description
         CSS class for a day containing an event.
   
   Default
         eventDay


.. container:: table-row

   Property
         monthWeekWithEventStyle
   
   Data type
         String
   
   Description
         CSS class for a week containing an event
   
   Default
         monthWeekWithEvent


.. container:: table-row

   Property
         strftimeTitleStartFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the title start date
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         strftimeTitleEndFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the title end date
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         heading
   
   Data type
         cObj
   
   Description
         Formats the list title
         
         heading.1 = TEXT
         
         heading.1 {
         
         data = register:cal\_list\_starttime
         
         strftime = {$plugin.tx\_cal\_controller.view.dateFormat}
         
         }
         
         heading.2 = TEXT
         
         heading.2 {
         
         data = register:cal\_list\_endtime
         
         wrap = &nbsp;-&nbsp;\|
         
         strftime = {$plugin.tx\_cal\_controller.view.dateFormat}
         
         }
   
   Default
         COA


.. container:: table-row

   Property
         dontShowOldEvents
   
   Data type
         boolean
   
   Description
         Hide events in the past:0 = no filtering1 = filter everything that has
         start\_time < “now”
         
         2 = filter old events, but keep those from “today”
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.month]

plugin.tx\_cal\_controller.view.month.event <
plugin.tx\_cal\_controller.view.day.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         title
   
   Data type
         cObj
   
   Description
         Content object of the event title
         
         title {
         
         crop = 11\|..
         
         }
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.month.event]


Year
""""

plugin.tx\_cal\_controller.view.year

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         yearViewPid
   
   Data type
         String / PID
   
   Description
         Page to display the year view on. If this is not configured, then the
         current page will be used instead.
         
         also: FlexForm
   
   Default


.. container:: table-row

   Property
         yearViewLink
   
   Data type
         cObj
   
   Description
         Configuration for the year view link.
         
         See: plugin.tx\_cal\_controller.view.defaultViewLinkSetup
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultViewLinkSetup


.. container:: table-row

   Property
         startPointCorrection
   
   Data type
         Integer
   
   Description
         Corrects the starting point to search for events. If the year ends on
         a monday and you want to have the rest of the weekdays filled
         (60\*60\*24\*6)
   
   Default
         518400


.. container:: table-row

   Property
         endPointCorrection
   
   Data type
         Integer
   
   Description
         Same as startingPointCorrection but for the end point
   
   Default
         518400


.. container:: table-row

   Property
         yearTemplate
   
   Data type
         String / Path
   
   Description
         Template for the year view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.year.yearTemplate}


.. container:: table-row

   Property
         nextYearLink
   
   Data type
         cObj
   
   Description
         Configuration for the next year link.
         
         See: plugin.tx\_cal\_controller.view.defaultViewLinkSetup
         
         nextYearLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_next\_year}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         prevYearLink
   
   Data type
         cObj
   
   Description
         Configuration for the previous year link.
         
         See: plugin.tx\_cal\_controller.view.defaultViewLinkSetup
         
         prevYearLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_last\_year}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         dontShowOldEvents
   
   Data type
         boolean
   
   Description
         Hide events in the past:0 = no filtering1 = filter everything that has
         start\_time < “now”
         
         2 = filter old events, but keep those from “today”
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.year]

plugin.tx\_cal\_controller.view.year.event <
plugin.tx\_cal\_controller.view.month.event


List
""""

plugin.tx\_cal\_controller.view.list

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         listTemplate
   
   Data type
         String / Path
   
   Description
         Template for list view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.list.listTemplate}


.. container:: table-row

   Property
         listWithTeaserTemplate
   
   Data type
         String / Path
   
   Description
         Template for list view with teaser.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.list.listWithTeaserTemplate}


.. container:: table-row

   Property
         alternatingLayoutMarkers
   
   Data type
         Array
   
   Description
         Array that is holding the information how many alternating layouts
         should be used for rendering the events and which marker suffix should
         be used for fetching the according layout subpart. Each defined marker
         suffix has stdWrap properties.
   
   Default
         odd = LIST\_ODD
         
         even = LIST\_EVEN


.. container:: table-row

   Property
         restartAlternationAfterDayWrapper
   
   Data type
         Boolean
   
   Description
         Restarts the alternation of the layouts after each day, when
         enableDayWrapper is used.
   
   Default
         0


.. container:: table-row

   Property
         restartAlternationAfterWeekWrapper
   
   Data type
         Boolean
   
   Description
         Restarts the alternation of the layouts after each week, when
         enableWeekWrapper is used.
   
   Default
         0


.. container:: table-row

   Property
         restartAlternationAfterMonthWrapper
   
   Data type
         Boolean
   
   Description
         Restarts the alternation of the layouts after each month, when
         enableMonthWrapper is used.
   
   Default
         0


.. container:: table-row

   Property
         restartAlternationAfterYearWrapper
   
   Data type
         Boolean
   
   Description
         Restarts the alternation of the layouts after each year, when
         enableYearWrapper is used.
   
   Default
         0


.. container:: table-row

   Property
         listViewLink
   
   Data type
         cObj
   
   Description
         Configuration for the list view link.
         
         See: plugin.tx\_cal\_controller.view.defaultViewLinkSetup
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultViewLinkSetup


.. container:: table-row

   Property
         starttime
   
   Data type
         strtotime() expression
   
   Description
         Show events from this date. Any relative date (such as -1 week) is
         relative to today's date.
         
         also: FlexForm
         
         also: `http://www.php.net/manual/en/function.strtotime.php
         <http://www.php.net/manual/en/function.strtotime.php>`_
         
         Any value starting with “+” or “-” calculates dates relative to the
         current time so that “+1 day” adds 24 hours to the time right now.
         This will give you different results over the day, depending on the
         current time.
         
         Apart from this granularity, more generic calculations are available.
         The keywords “last” and “next” can be used in combination with basic
         calendar definitions like “week”, “month”, “year” and month names.
         
         There are also constants available providing you with a time that
         resets the hours and minutes to 0. Namely, these are
         
         - today / current
         
         - yesterday
         
         - tomorrow
         
         - weekstart / weekend
         
         - monsthstart / monthend
         
         - quarterstart / quarterend
         
         - yearstart / yearend
         
         These values can be used for further calculations.
         
         :underline:`Examples`
         
         Beginning of 2007: starttime = 01 January 2007
         
         1 week ago: starttime = -1 week
         
         1 month ago: starttime = last month
         
         Start of last November: starttime = last monthstart november
         
         Two days ago (include whole day) starttime = today -2 days
         
         Start of the current year: starttime = yearstart
         
         Start of the current quarter: starttime = quarterstart
         
         Start of the current month: starttime = monthstart
         
         Start of the current week: starttime = weekstart
         
         Yesterday (at midnight): starttime = yesterday
         
         Today (at midnight): starttime = today
   
   Default
         now


.. container:: table-row

   Property
         endtime
   
   Data type
         strtotime() expression
   
   Description
         Show events until this date. Any relative date (such as -1 week) is
         relative to today's date and not to the starttime of the list view.
         
         Also: starttime
         
         also: FlexForm
         
         also: `http://www.php.net/manual/en/function.strtotime.php
         <http://www.php.net/manual/en/function.strtotime.php>`_
         
         :underline:`Example`
         
         End of 2011: endtime = 31 December 2011
         
         1 Week From now: endtime = +1 week
         
         Tomorrow (at midnight): endtime = tomorrow
         
         End of the current week: endtime = weekend
         
         End of the current month: endtime = monthend
         
         End of the current quarter: endtime = quarterend
         
         End of the year: endtime = yearend
         
         :underline:`Please note:` If you define an absolute value, this date
         is  *not* included into the list. In the first example above, the list
         rendering will end with 30 December 2011.
   
   Default
         +1 month


.. container:: table-row

   Property
         order
   
   Data type
         String
   
   Description
         listing Order of the events (asc\|desc)
   
   Default
         asc


.. container:: table-row

   Property
         hideStartedEvents
   
   Data type
         Boolean
   
   Description
         Hides events that are already started: multiple day events
   
   Default
         0


.. container:: table-row

   Property
         useGetdate
   
   Data type
         Boolean
   
   Description
         Ignores the starttime and endtime value and displays all events of the
         getdate day.
   
   Default
         0


.. container:: table-row

   Property
         doNotUseGetdateTheFirstTime
   
   Data type
         Boolean
   
   Description
         Ignores the getdate option if there is no "getdate" parameter in the
         url
   
   Default
         0


.. container:: table-row

   Property
         useCustomStarttime
   
   Data type
         Boolean
   
   Description
         Defines the view.list.starttime as starttime relative to the date
         given in parameter getdate (like start of selected year,...)
   
   Default
         0


.. container:: table-row

   Property
         useCustomEndtime
   
   Data type
         Boolean
   
   Description
         Defines the view.list.endtime as endtime for the list starting with
         the parameter getdate. This means, that you can create a dynamic list
         which always shows you a certain timespan from your current position
         in time, not today!
   
   Default
         0


.. container:: table-row

   Property
         customStarttimeRelativeToGetdate
   
   Data type
         Boolean
   
   Description
         If set, the option "useCustomStarttime" will be calculated relative to
         the given getdate and not to the current date.
         
         That means that if you set your starttime to "yearbegin" and the
         getdate-parameter is somewhere in 2014, your starttime would be
         01-01-2014.
         
         See also useCustomStarttime.
   
   Default
         0


.. container:: table-row

   Property
         customEndtimeRelativeToGetdate
   
   Data type
         Boolean
   
   Description
         If set, the option "useCustomEndtime" will be calculated relative to
         the given getdate and not to the current date.
         
         That means that if you set your endtime to "yearend" and the getdate-
         parameter is somewhere in 2014, your endtime would be 12-31-2014.
         
         See also useCustomEndtime
   
   Default
         0


.. container:: table-row

   Property
         maxEvents
   
   Data type
         Integer
   
   Description
         Maximum number of events to display.
         
         also: FlexForm
   
   Default
         100


.. container:: table-row

   Property
         maxRecurringEvents
   
   Data type
         Integer
   
   Description
         Maximum number of instances of a recurring event that should be
         listed.
         
         also: FlexForm
   
   Default


.. container:: table-row

   Property
         addIcon
   
   Data type
         String
   
   Description
         Image tag for icon used in link to frontend creation of an event.
         
         see: plugin.tx\_cal\_controller.view.day.addIcon
   
   Default
         plugin.tx\_cal\_controller.view.day.addIcon


.. container:: table-row

   Property
         found\_stdWrap
   
   Data type
         stdWrap
   
   Description
         StdWrap for found result text
         
         found\_stdWrap {
         
         noTrimWrap = \|<p class="found">\|
         {LLL:EXT:cal/controller/locallang.xml:l\_search\_found}</p>\|
         
         insertData = 1
         
         }
   
   Default


.. container:: table-row

   Property
         heading
   
   Data type
         cObj
   
   Description
         Formats the list title
         
         heading {
         
         1 = TEXT
         
         1 {
         
         data = register:cal\_list\_starttime
         
         strftime = {$plugin.tx\_cal\_controller.view.dateFormat}
         
         }
         
         2 = TEXT
         
         2 {
         
         data = register:cal\_list\_endtime
         
         wrap = &nbsp;-&nbsp;\|
         
         required = 1
         
         strftime = {$plugin.tx\_cal\_controller.view.dateFormat}
         
         }
         
         }
   
   Default
         COA


.. container:: table-row

   Property
         enableDayWrapper
   
   Data type
         Boolean
   
   Description
         Enables separation of different days in the list view
   
   Default
         0


.. container:: table-row

   Property
         dayWrapperFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the day wrapper
   
   Default
         %d. %B


.. container:: table-row

   Property
         dayWrapper
   
   Data type
         cObj
   
   Description
         Wraps each day containing events
         
         dayWrapper {
         
         10 = TEXT
         
         10 {
         
         current = 1
         
         required = 1
         
         wrap = <dt style="background-color:#CCCCCC;">\|</dt>
         
         }
         
         }
   
   Default
         COA


.. container:: table-row

   Property
         enableWeekWrapper
   
   Data type
         Boolean
   
   Description
         Enables separation of different weeks in the list view
   
   Default
         0


.. container:: table-row

   Property
         weekWrapperFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the week wrapper
   
   Default
         %U


.. container:: table-row

   Property
         weekWrapper
   
   Data type
         cObj
   
   Description
         Wraps each week containing events
         
         weekWrapper.10.wrap = <dt style="background-color:#CCCCCC;">\|.
         (Week)</dt>
   
   Default
         .dayWrapper


.. container:: table-row

   Property
         enableMonthWrapper
   
   Data type
         Boolean
   
   Description
         Enables separation of different months in the list view
   
   Default
         0


.. container:: table-row

   Property
         monthWrapperFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the month wrapper
   
   Default
         %U


.. container:: table-row

   Property
         monthWrapper
   
   Data type
         cObj
   
   Description
         Wraps each month containing events
         
         monthWrapper.10 .wrap = <dt style="background-color:#CCCCCC;">\|</dt>
   
   Default
         dayWrapper


.. container:: table-row

   Property
         enableYearWrapper
   
   Data type
         Boolean
   
   Description
         Enables separation of different year in the list view.
   
   Default
         0


.. container:: table-row

   Property
         yearWrapperFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Format for the Year wrapper
   
   Default
         %Y


.. container:: table-row

   Property
         yearWrapper
   
   Data type
         cObj
   
   Description
         Wraps each Year containing events
   
   Default
         .monthWrapper


.. container:: table-row

   Property
         enableCategoryWrapper
   
   Data type
         Boolean
   
   Description
         Enables separation of different categories
   
   Default
         0


.. container:: table-row

   Property
         categoryWrapper
   
   Data type
         cObj
   
   Description
         Wraps each category containing events
         
         categoryWrapper.10.wrap = <dt class="###CATEGORY\_STYLE###">\|</dt>
   
   Default
         .dayWrapper


.. container:: table-row

   Property
         noCategoryWrapper
   
   Data type
         cObj
   
   Description
         Wraps events without categories, if the categoryWrapper has been
         enabled
         
         noCategoryWrapper {
         
         value = <dt style="background-color:#999999;">No category</dt>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         noCategoryWrapper.uid
   
   Data type
         Integer
   
   Description
         Position the noCategoryWrapper block by defining a uid (0 = before, x
         > largest category uid => last)
   
   Default
         999


.. container:: table-row

   Property
         categoryLink\_stdWrap
   
   Data type
         stdWrap
   
   Description
         stdWrap for the link text of category
         
         categoryLink\_stdWrap {
         
         wrap =
         
         }
   
   Default


.. container:: table-row

   Property
         categoryLink\_splitChar
   
   Data type
         cObj
   
   Description
         for more than one category this is the separator
         
         categoryLink\_splitChar {
         
         value = ,
         
         noTrimWrap= \|\| \|
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         categoryLink
   
   Data type
         cObj
   
   Description
         Content Object for rendering the categories as link
         
         See: plugin.tx\_cal\_controller.view.defaultLinkSetup
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         enableCalendarWrapper
   
   Data type
         Boolean
   
   Description
         Enables separation of different calendars
   
   Default
         0


.. container:: table-row

   Property
         calendarWrapper
   
   Data type
         cObj
   
   Description
         Wraps each calendar containing events
         
         calendarWrapper.10.wrap = <dt style="background-
         color:#000099;">\|</dt>
   
   Default
         .dayWrapper


.. container:: table-row

   Property
         pageBrowser.
   
   Data type
         Configuration Container
   
   Description
         Groups all pageBrowser related configuration options
   
   Default


.. container:: table-row

   Property
         pageBrowser.usePageBrowser
   
   Data type
         Boolean
   
   Description
         Enables the pagebrowser for the list
   
   Default
         1


.. container:: table-row

   Property
         pageBrowser.onlyShowIfNeeded
   
   Data type
         Boolean
   
   Description
         flag to only show the pagebrowser when the result exceeds the value
         defined in recordsPerPage
   
   Default
         0


.. container:: table-row

   Property
         pageBrowser.pagesCount
   
   Data type
         Integer
   
   Description
         Limits the maximum number of pages to be shown in the browser
   
   Default
         0


.. container:: table-row

   Property
         pageBrowser.recordsPerPage
   
   Data type
         Integer
   
   Description
         The maximum number of records that are shown per page
   
   Default
         10


.. container:: table-row

   Property
         pageBrowser.useType
   
   Data type
         String
   
   Description
         here you can configure which pagebrowser should generally be used
         possible values are: default, piPageBrowser
   
   Default
         default


.. container:: table-row

   Property
         pageBrowser.pointer
   
   Data type
         String
   
   Description
         name of the pointer that should be used to indicate resultBrowser
         pages
   
   Default
         offset


.. container:: table-row

   Property
         pageBrowser.default.
   
   Data type
         Configuration group
   
   Description
         Groups all configurations and rendering settings for the default
         pageBrowser of cal
   
   Default


.. container:: table-row

   Property
         pageBrowser.default.actPage\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for the active page
   
   Default
         wrap = <span><b>\|</b></span>


.. container:: table-row

   Property
         pageBrowser.default.pageLink
   
   Data type
         cObj
   
   Description
         Rendering definition of the 'page' links
         
         pageLink {
         
         wrap = <span>\|</span>
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_page} {current:1}
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         pageBrowser.default.nextLink
   
   Data type
         cObj
   
   Description
         Rendering definition of the 'next' link
         
         nextLink {
         
         current = 0
         
         value = &gt;&gt;
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_next}
         
         wrap = <span>\|</span>
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         pageBrowser.default.prevLink
   
   Data type
         cObj
   
   Description
         Rendering definition of the 'prev' link, copied from 'next' link.
         
         prevLink {
         
         value = &lt;&lt;
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_prev}
         
         }
   
   Default
         .nextLink


.. container:: table-row

   Property
         pageBrowser.default.spacer
   
   Data type
         cObj
   
   Description
         Rendering definition for the spacer sign when stripping pages
         
         spacer {
         
         value = ...
         
         wrap = <span><b>\|</b></span>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.
   
   Data type
         Configuration group
   
   Description
         Enables the TYPO3 build-in result browser for the list.
         
         This means a bar of page numbers plus a "previous" and "next" link.
         For each entry in the bar the piVars "pointer" will be pointing to the
         "result page" to show.
   
   Default


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.showResultCount
   
   Data type
         Boolean
   
   Description
         This var can have 3 values:
         
         0: only the result-browser will be shown
         
         1: (default) the text "Displaying results..." and the result-browser
         will be shown.
         
         2: only the text "Displaying results..." will be shown
   
   Default
         1


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.showPBrowserText
   
   Data type
         Boolean
   
   Description
         Here you can choose if the pagebrowser should show texts like "page 1,
         page..." in the pagelinks or if it should show only numbers.
   
   Default
         0


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.tableParams
   
   Data type
         String
   
   Description
         If you didn't set a "browseLinksWrap" you can add parameters for the
         table that wraps the pagebrowser here.
   
   Default
         cellpadding="2" align="center"


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.pagefloat
   
   Data type
         Integer / Keyword
   
   Description
         This defines were the current page is shown in the list of pages in
         the pagebrowser.
         
         If this var is an integer it will be interpreted as position in the
         list of pages.
         
         If its value is the keyword "center" the current page will be shown in
         the middle of the browse links.
   
   Default
         center


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.showFirstLast
   
   Data type
         Boolean
   
   Description
         This is used as switch if the two links named "<< First" and "Last >>"
         will be shown and point to the first or last page. If "showFirstLast"
         is enabled "alwaysPrev" will be overwritten (set to 1).
   
   Default
         1


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.showRange
   
   Data type
         Booelan
   
   Description
         This var switches the display of the pagelinks from pagenumbers to
         ranges f.e.: 1-5 6-10 11-15... instead of 1 2 3...
   
   Default
         1


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.dontLinkActivePage
   
   Data type
         Boolean
   
   Description
         A switch if the active (current) page should be displayed as pure text
         or as a link to itself
   
   Default
         1


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.alwaysPrev
   
   Data type
         Boolean
   
   Description
         If this is enabled the "previous" link will always be visible even
         when the first page is displayed.
   
   Default
         0


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.hscText
   
   Data type
         Boolean
   
   Description
         Here you can choose if the texts for the pagebrowser (eg: "next",
         "Displaying reaults...") will be parsed through the PHP function
         htmlspecialchars() or not. Disable this if you want to use HTML in the
         texts f.e. for graphical "next" and "previous" links.
   
   Default
         1


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.browseBoxWrap
   
   Data type
         stdWrap
   
   Description
         This is the wrap for the complete pagebowser (results and browse
         links).
   
   Default


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.showResultsWrap
   
   Data type
         stdWrap
   
   Description
         This wraps the text "Displaying results...".
   
   Default
         \|<br />


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.browseLinksWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for the browse links.
   
   Default
         \|


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.showResultsNumbersWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for the numbers in the text: "Displaying results 1 to 4 out of 22
         ".
   
   Default


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.disabledLinkWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for disabled links (f.e the "Last >>" link on the last page).
   
   Default
         <span style="color:#bbb;">\|</span>


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.inactiveLinkWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for inactive links (normal links).
   
   Default
         \|


.. container:: table-row

   Property
         pageBrowser.piPageBrowser.activeLinkWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for active links.
   
   Default
         <strong>\|</strong>


.. container:: table-row

   Property
         dontShowOldEvents
   
   Data type
         boolean
   
   Description
         Hide events in the past:0 = no filtering1 = filter everything that has
         start\_time < “now”
         
         2 = filter old events, but keep those from “today”
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.list]

plugin.tx\_cal\_controller.view.list.event <
plugin.tx\_cal\_controller.view.event.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         starttime
   
   Data type
         cObj
   
   Description
         Content object for the event start time
   
   Default
         dataWrap >


.. container:: table-row

   Property
         endtime
   
   Data type
         cObj
   
   Description
         Content object for the event end time
   
   Default
         noTrimWrap = \| - \|\|
         
         required = 1
         
         dataWrap >


.. container:: table-row

   Property
         startdate
   
   Data type
         cObj
   
   Description
         Content object for the event start date
   
   Default
         dataWrap >


.. container:: table-row

   Property
         enddate
   
   Data type
         cObj
   
   Description
         Content object for the event end date
   
   Default
         noTrimWrap = \| - \|\|
         
         required = 1
         
         dataWrap >


.. container:: table-row

   Property
         title
   
   Data type
         cObj
   
   Description
         Content object for the event title
   
   Default
         dataWrap >


.. container:: table-row

   Property
         alldayTitle
   
   Data type
         cObj
   
   Description
         Content object for the event title of all day events.
   
   Default
         dataWrap >


.. container:: table-row

   Property
         noEventFound
   
   Data type
         cObj
   
   Description
         Content object for the no event found text
   
   Default
         wrap = <dt>\|</dt>


.. container:: table-row

   Property
         image
   
   Data type
         cObj
   
   Description
         Content object for the event image
         
         image {
         
         file {
         
         import = {$plugin.tx\_cal\_controller.uploadPath.image}
         
         import {
         
         current = 1
         
         listNum = 0
         
         }
         
         maxW = {$plugin.tx\_cal\_controller.listMaxW}
         
         }
         
         altText {
         
         field = imagealttext
         
         listNum = 0
         
         listNum.splitChar = 10
         
         }
         
         titleText < .altText
         
         titleText.field = imagetitletext
         
         }
   
   Default
         IMAGE


.. container:: table-row

   Property
         description
   
   Data type
         cObj
   
   Description
         Content object for the event description
   
   Default
         crop = 100\|...
         
         dataWrap >
         
         stripHtml = 1


.. container:: table-row

   Property
         teaser
   
   Data type
         cObj
   
   Description
         Content object for the event teaser
   
   Default
         dataWrap >


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.list.event]


ICS
"""

The options described here are related to ics options in other views.
The ics view itself has a separate page type. Therefore it has
received its own part inside this Typoscript Reference: `ICS
<#10.8.4.ICS|outline>`_

plugin.tx\_cal\_controller.view.ics

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         showIcsLinks
   
   Data type
         Boolean
   
   Description
         Turns on ICS/iCal links in the frontend.
         
         also: FlexForm
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.ics.showIcsLinks}


.. container:: table-row

   Property
         link\_wrap
   
   Data type
         String
   
   Description
         Wraps the ics link
   
   Default
         <div class="ics\_link">%s</div>


.. container:: table-row

   Property
         icsViewLink
   
   Data type
         cObj
   
   Description
         The ics view link content object
         
         See: plugin.tx\_cal\_controller.view.defaultLinkSetup
         
         icsViewLink {
         
         typolink.title.override.override {
         
         stdWrap {
         
         required = 1
         
         field = title
         
         wrap = \|\_
         
         }
         
         dataWrap = \|{LLL:EXT:cal/controller/locallang.xml:l\_ics\_view}
         
         }
         
         outerWrap.field = link\_wrap
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         icsViewCalendarLink
   
   Data type
         cObj
   
   Description
         The ics view link content object for the ics list of a whole calendar
         
         icsViewCalendarLink.typolink.parameter.wrap =
         \|,{$plugin.tx\_cal\_controller.view.ics.typeNum}
   
   Default
         .icsViewLink


.. container:: table-row

   Property
         icsViewCategoryLink
   
   Data type
         cObj
   
   Description
         The ics view link content object for the ics list of a whole category.
         
         See: plugin.tx\_cal\_controller.view.ics.icsViewCalendarLink
   
   Default
         .icsViewCalendarLink


.. container:: table-row

   Property
         icsTemplate
   
   Data type
         String / Path
   
   Description
         Template for ICS list view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.ics.icsListTemplate}


.. container:: table-row

   Property
         eventUidPrefix
   
   Data type
         String
   
   Description
         Prefix for used with event UID to create a GUID. Site URL is a good
         choice here to ensure uniqueness.
   
   Default
         {$plugin.tx\_cal\_controller.view.ics.eventUidPrefix}


.. container:: table-row

   Property
         categoryLink\_splitChar
   
   Data type
         cObj
   
   Description
         for more than one category this is the separator
         
         categoryLink\_splitChar {
         
         value = ,
         
         noTrimWrap= \|\| \|
         
         }
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.ics]

plugin.tx\_cal\_controller.view.ics.event <
plugin.tx\_cal\_controller.view.list.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         category
   
   Data type
         cObj
   
   Description
         Content object for the event category
         
         category {
         
         required = 1
         
         current = 1
         
         dataWrap = CATEGORIES:\|
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         location
   
   Data type
         cObj
   
   Description
         Content object for the event location
         
         location {
         
         required = 1
         
         current = 1
         
         dataWrap = LOCATION:\|
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         organizer
   
   Data type
         cObj
   
   Description
         Content object for the event organizer
         
         organizer {
         
         required = 1
         
         current = 1
         
         dataWrap = ORGANIZER;CN=\|:
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         title
   
   Data type
         cObj
   
   Description
         Content object for the event title
         
         title {
         
         required = 1
         
         current = 1
         
         dataWrap = SUMMARY:\|
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         description
   
   Data type
         cObj
   
   Description
         Content object for the event description
         
         description {
         
         dataWrap = DESCRIPTION:\|
         
         current = 1
         
         crop >
         
         prefixComment >
         
         parseFunc >
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         attachment\_url
   
   Data type
         cObj
   
   Description
         Content object for event attachment in ics view
         
         attachment\_url {
         
         current = 1
         
         current = 1
         
         required = 1
         
         wrap = ATTACH:\|;
         
         }
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.ics.event]


Single\_ics
"""""""""""

plugin.tx\_cal\_controller.view.single\_ics.event <
plugin.tx\_cal\_controller.view.list.event


Admin
"""""

plugin.tx\_cal\_controller.view.admin

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         adminTemplate
   
   Data type
         String / Path
   
   Description
         Template for frontend administrative view.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.admin.adminTemplate}


.. container:: table-row

   Property
         link\_wrap
   
   Data type
         String
   
   Description
         Wraps the admin link
   
   Default
         <div class="admin\_link">%s</div>


.. container:: table-row

   Property
         linkText
   
   Data type
         String
   
   Description
         The admin link content
   
   Default
         <img src="###IMG\_PATH###/config\_calendar.gif" border="0"
         style="margin:2px;"/>


.. container:: table-row

   Property
         adminViewLink
   
   Data type
         cObj
   
   Description
         The admin link content
         
         See: plugin.tx\_cal\_controller.view.defaultLinkSetup
         
         adminViewLink {
         
         typolink.title.override.override.dataWrap =
         {LLL:EXT:cal/controller/locallang.xml:l\_administration\_view}
         
         outerWrap.field = link\_wrap
         
         }
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.admin]


Free- & Busy
""""""""""""

plugin.tx\_cal\_controller.view.freeAndBusy

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         enable
   
   Data type
         Boolean
   
   Description
         Enables a free & busy view of the calendar.
   
   Default
         0


.. container:: table-row

   Property
         headerStyle
   
   Data type
         String
   
   Description
         Class applied to free/busy header.
   
   Default
         fnb\_header


.. container:: table-row

   Property
         bodyStyle
   
   Data type
         String
   
   Description
         Class applied to free/busy body.
   
   Default
         fnb\_body


.. container:: table-row

   Property
         eventTitle
   
   Data type
         String
   
   Description
         Generic even title when viewed in free/busy view.
   
   Default
         Busy


.. container:: table-row

   Property
         defaultCalendarUid
   
   Data type
         Integer / UID
   
   Description
         Default calendar to display in Free/Busy view.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.freeAndBusy]


Other
"""""

plugin.tx\_cal\_controller.view.other

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         showLogin
   
   Data type
         Boolean
   
   Description
         Displays a login box with the calendar. Requires the newloginbox
         extension.
         
         also: FlexForm
         
         see: `http://typo3.org/extensions/repository/search/newloginbox/
         <http://typo3.org/extensions/repository/search/newloginbox/>`_
   
   Default
         0


.. container:: table-row

   Property
         loginPageId
   
   Data type
         Integer / PID
   
   Description
         Page to perform login on. If login fails, redirect to this page.
   
   Default


.. container:: table-row

   Property
         userFolderId
   
   Data type
         Integer / PID
   
   Description
         Page where frontend users are stored.
   
   Default


.. container:: table-row

   Property
         showSearch
   
   Data type
         Boolean
   
   Description
         Show the search box.
         
         also: FlexForm
   
   Default
         0


.. container:: table-row

   Property
         showGoto
   
   Data type
         Boolean
   
   Description
         Show the goto box.
         
         also: FlexForm
   
   Default
         0


.. container:: table-row

   Property
         showCategorySelection
   
   Data type
         Boolean
   
   Description
         Enables category filtering.
         
         also: FlexForm
   
   Default
         0


.. container:: table-row

   Property
         categorySelectorSubmit
   
   Data type
         String
   
   Description
         Submit button for the legend category selector.
   
   Default
         <input type="image" class="refresh\_calendar"
         src="###IMG\_PATH###/refresh.gif" alt="###REFRESH\_LABEL###"
         title="###REFRESH\_LABEL###">


.. container:: table-row

   Property
         showCalendarSelection
   
   Data type
         Boolean
   
   Description
         Enables calendar filtering.
   
   Default
         0


.. container:: table-row

   Property
         optionString
   
   Data type
         String
   
   Description
         Template for option tag used in category and calendar selector.
   
   Default
         <option value="%s">%s</option>


.. container:: table-row

   Property
         showTomorrowEvents
   
   Data type
         Boolean
   
   Description
         Adds a sidebar box with tomorrow's events.
         
         also: FlexForm
   
   Default
         0


.. container:: table-row

   Property
         tomorrowsEvents
   
   Data type
         cObj
   
   Description
         Wrap for tomorrow's events shown in the sidebar.
         
         tomorrowsEvents {
         
         current = 1
         
         wrap = \|<br />
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         legend\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Wrap for the legend shown in the sidebar.
   
   Default
         wrap = \|<div style="text-align:right;margin-left:10px;"></div>


.. container:: table-row

   Property
         showTodos
   
   Data type
         Boolean
   
   Description
         Not currently implemented.
   
   Default
         0


.. container:: table-row

   Property
         showJumps
   
   Data type
         Boolean
   
   Description
         Enables calendar navigation from the sidebar.
         
         also: FlexForm
   
   Default
         0


.. container:: table-row

   Property
         dateFormatWeekJump
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for the dropdown list of weeks in the sidebar.
   
   Default
         %b %d


.. container:: table-row

   Property
         listWeeks\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Option tag for weeks used to build the dropdown list in the sidebar.
   
   Default
         wrap = <option value="\|" >###WEEK1### - ###WEEK2###</option>


.. container:: table-row

   Property
         listWeeksSelected\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Option tag for selected weeks used to build the dropdown list in the
         sidebar.
   
   Default
         <option value="\|" selected="selected">###WEEK1### -
         ###WEEK2###</option>


.. container:: table-row

   Property
         listWeek\_onlyShowCurrentYear
   
   Data type
         Boolean
   
   Description
         Only show the current year in the list of weeks.
   
   Default
         0


.. container:: table-row

   Property
         listWeek\_totalWeekCount
   
   Data type
         Integer
   
   Description
         Total number of weeks to show in the list.
   
   Default
         20


.. container:: table-row

   Property
         listWeek\_previousWeekCount
   
   Data type
         Integer
   
   Description
         Show this many weeks before the current week. Must be less than
         listWeek\_totalWeekCount.
   
   Default
         5


.. container:: table-row

   Property
         dateFormatMonthJump
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for the dropdown list of months in the sidebar.
   
   Default
         %B %Y


.. container:: table-row

   Property
         listMonth\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Option tag for months used to build the dropdown list in the sidebar.
   
   Default
         <option value="\|" >###MONTH###</option>


.. container:: table-row

   Property
         listMonthSelected\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Option tag for selected months used to build the dropdown list in the
         sidebar.
   
   Default
         <option value="\|" selected="selected">###MONTH###</option>


.. container:: table-row

   Property
         listMonth\_onlyShowCurrentYear
   
   Data type
         Boolean
   
   Description
         Only show the current year in the list of months.
   
   Default
         0


.. container:: table-row

   Property
         listMonth\_totalMonthCount
   
   Data type
         Integer
   
   Description
         Total number of months to show in the list.
   
   Default
         12


.. container:: table-row

   Property
         listMonth\_previousMonthCount
   
   Data type
         Integer
   
   Description
         Show this many months before the current month. Must be less than
         listMonth\_totalMonthCount.
   
   Default
         3


.. container:: table-row

   Property
         dateFormatYearJump
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format for the dropdown list of years in the sidebar.
   
   Default
         %Y


.. container:: table-row

   Property
         listYear\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Option tag for selected years used in the dropdown list of years in
         the sidebar.
   
   Default
         <option value="\|" selected="selected">###YEAR###</option>


.. container:: table-row

   Property
         listYear\_totalYearCount
   
   Data type
         Integer
   
   Description
         Total number of years to show in the list.
   
   Default
         3


.. container:: table-row

   Property
         listYear\_previousYearCount
   
   Data type
         Integer
   
   Description
         Show this many years before the current year. Must be less than
         listYear\_totalYearCount.
   
   Default
         1


.. container:: table-row

   Property
         sidebarTemplate
   
   Data type
         String / Path
   
   Description
         Template for the sidebar.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.other.sidebarTemplate}


.. container:: table-row

   Property
         searchBoxTemplate
   
   Data type
         String / Path
   
   Description
         Template for the search box.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.other.searchBoxTemplate}


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.other]

plugin.tx\_cal\_controller.view.other.monthMenu

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         monthStart
   
   Data type
         Integer (1 - 12)
   
   Description
         A static month to start the menu with
   
   Default
         1


.. container:: table-row

   Property
         yearStart
   
   Data type
         Integer
   
   Description
         A static year to start the menu with
   
   Default
         2007


.. container:: table-row

   Property
         monthStart.thisMonth
   
   Data type
         Boolean
   
   Description
         Defines to take the current month and year shall be taken instead of
         what is defined at monthStart and yearStart
   
   Default
         1


.. container:: table-row

   Property
         count
   
   Data type
         Integer
   
   Description
         Number of items in the menu
   
   Default
         5


.. container:: table-row

   Property
         format
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         The format to display the links in
   
   Default
         %b %Y


.. container:: table-row

   Property
         month\_stdWrap
   
   Data type
         stdWrap
   
   Description
         StandardWrap for each month link
   
   Default
         wrap = <span>\|</span>


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.other.monthMenu]


Search
""""""

plugin.tx\_cal\_controller.view.search

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         searchResultAllTemplate
   
   Data type
         String / Path
   
   Description
         Template for search results.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.search.searchResultAllTemplate}


.. container:: table-row

   Property
         searchResultEventTemplate
   
   Data type
         String / Path
   
   Description
         Template for search results within an event.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.search.searchResultEventTemplate}


.. container:: table-row

   Property
         searchResultLocationTemplate
   
   Data type
         String / Path
   
   Description
         Template for search results within a location.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.search.searchResultLocationTemplate}


.. container:: table-row

   Property
         searchResultOrganizerTemplate
   
   Data type
         String / Path
   
   Description
         Template for search results within an organizer.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.search.searchResultOrganizerTemplate
         }


.. container:: table-row

   Property
         searchEventFieldList
   
   Data type
         String / CSV
   
   Description
         Fields to search within an event.
   
   Default
         title,organizer,location,description


.. container:: table-row

   Property
         searchLocationFieldList
   
   Data type
         String / CSV
   
   Description
         Fields to search within a location.
   
   Default
         name


.. container:: table-row

   Property
         searchOrganizerFieldList
   
   Data type
         String / CSV
   
   Description
         Fields to search within an organizer.
   
   Default
         name


.. container:: table-row

   Property
         searchUserFieldList
   
   Data type
         String / CSV
   
   Description
         Fields to search within a user.
   
   Default
         name


.. container:: table-row

   Property
         searchGroupFieldList
   
   Data type
         String / CSV
   
   Description
         Fields to search within a group.
   
   Default
         name


.. container:: table-row

   Property
         startRange
   
   Data type
         Integer
   
   Description
         Start Range for search.
   
   Default
         19700102


.. container:: table-row

   Property
         endRange
   
   Data type
         Integer
   
   Description
         End Range for Search.
   
   Default
         20300101


.. container:: table-row

   Property
         searchAllLink
   
   Data type
         cObj
   
   Description
         The search-all view link content object
         
         See: plugin.tx\_cal\_controller.view.defaultLinkSetup
         
         searchAllLink.typolink.title.override.override.dataWrap =
         \|{LLL:EXT:cal/controller/locallang.xml:l\_search}
   
   Default
         =< plugin.tx\_cal\_controller.view.defaultLinkSetup


.. container:: table-row

   Property
         startSearchAfterSubmit
   
   Data type
         Boolean
   
   Description
         Enables the search view to wait for the user to submit the form. If
         set to 0, the defaultValues will be taken to perform an initial search
         before any interaction has taken place, resulting in a initial search
         result list.
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search]

plugin.tx\_cal\_controller.view.search.defaultValues

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         query
   
   Data type
         String
   
   Description
         Default search term
   
   Default


.. container:: table-row

   Property
         start\_day
   
   Data type
         String
   
   Description
         Default start day
   
   Default
         now


.. container:: table-row

   Property
         end\_day
   
   Data type
         String
   
   Description
         Default end day
   
   Default
         +1 month


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search.defaultValues]


Search\_event
"""""""""""""

plugin.tx\_cal\_controller.view.search\_event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         strftimeHeadingStartFormat
   
   Data type
         String
   
   Description
         Formats the list title
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         strftimeHeadingEndFormat
   
   Data type
         String
   
   Description
         Formats the list title
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.dateFormat}


.. container:: table-row

   Property
         heading
   
   Data type
         cObj
   
   Description
         Formats the Headline
         
         heading {
         
         1 = TEXT
         
         1 {
         
         data = register:cal\_list\_starttime
         
         strftime = {$plugin.tx\_cal\_controller.view.dateFormat}
         
         }
         
         2 = TEXT
         
         2 {
         
         data = register:cal\_list\_endtime
         
         wrap = &nbsp;-&nbsp;\|
         
         strftime = {$plugin.tx\_cal\_controller.view.dateFormat}
         
         }
         
         }
   
   Default
         COA


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search\_event]

plugin.tx\_cal\_controller.view.search\_event.event <
plugin.tx\_cal\_controller.view.list.event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search\_event.event]

plugin.tx\_cal\_controller.view.search\_event.location <
plugin.tx\_cal\_controller.view.location.location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         includeEventsInResult
   
   Data type
         Boolean
   
   Description
         Disable the search for events, since we already have the event as the
         root for the location
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search\_event.location]

plugin.tx\_cal\_controller.view.search\_event.organizer <
plugin.tx\_cal\_controller.view.location.organizer

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         includeEventsInResult
   
   Data type
         Boolean
   
   Description
         Disable the search for events, since we already have the event as the
         root for the organizer
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search\_event.organizer]


search\_location
""""""""""""""""

plugin.tx\_cal\_controller.view.search\_location.location <
plugin.tx\_cal\_controller.view.location.location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         name
   
   Data type
         cObj
   
   Description
         dataWrap >
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search\_location.location]


search\_organizer
"""""""""""""""""

plugin.tx\_cal\_controller.view.search\_organizer.organizer <
plugin.tx\_cal\_controller.view.organizer.organizer

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         name
   
   Data type
         cObj
   
   Description
         dataWrap >
   
   Default
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.search\_organizer.organizer]


create\_event
"""""""""""""

plugin.tx\_cal\_controller.view.create\_event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for creation of an event in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.createEventTemplate}


.. container:: table-row

   Property
         redirectAfterCreateToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the event has been saved. The
         parameter 'getdate' will get passed along.
         
         also: redirectAfterCreateToView
   
   Default


.. container:: table-row

   Property
         redirectAfterCreateToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the event has been saved. The parameter
         'getdate' will get passed along.
         
         also: redirectAfterCreateToPid
   
   Default


.. container:: table-row

   Property
         calendar\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the calendar field
         
         dataWrap = <div><label for="calendar">{LLL:EXT:cal/controller/locallan
         g.xml:l\_calendar}:</label><select
         name="tx\_cal\_controller[switch\_calendar]" size="1"
         onchange="submit();" id="calendar\_selector">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label><input type="checkbox" \|
         name="tx\_cal\_controller[hidden]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         category\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the category field
         
         dataWrap = <div><label for="category">{LLL:EXT:cal/controller/locallan
         g.xml:l\_category}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         allday\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the allday field
         
         dataWrap = <div><label for="allday">{LLL:EXT:cal/controller/locallang.
         xml:l\_event\_allday}:</label><input type="checkbox" \|
         name="tx\_cal\_controller[allday]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         startdate\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the startdate field
         
         dataWrap = <div><label for="startdate">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_edit\_startdate}:</label><input type="text"
         value="\|" name="tx\_cal\_controller[event\_start\_day]"
         id="event\_start\_day" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         enddate\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the enddate field
         
         dataWrap = <div><label for="enddate">{LLL:EXT:cal/controller/locallang
         .xml:l\_event\_edit\_enddate}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[event\_end\_day]" id="event\_end\_day"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         starthour\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the starthour field
         
         dataWrap = <div><label for="starttime">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_edit\_starttime}:</label><select
         name="tx\_cal\_controller[event\_start\_hour]" id="event\_start\_hour"
         size="1">\|</select>
   
   Default
         See Description


.. container:: table-row

   Property
         startminutes\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the startminutes field
         
         dataWrap = :<select name="tx\_cal\_controller[event\_start\_minutes]"
         id="event\_start\_minutes" size="1">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         endhour\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the endhour field
         
         dataWrap = <div><label for="endtime">{LLL:EXT:cal/controller/locallang
         .xml:l\_event\_edit\_endtime}:</label><select
         name="tx\_cal\_controller[event\_end\_hour]" id="event\_end\_hour"
         size="1">\|</select>
   
   Default
         See Description


.. container:: table-row

   Property
         endminutes\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the endminutes field
         
         dataWrap = :<select name="tx\_cal\_controller[event\_end\_minutes]"
         id="event\_end\_minutes" size="1">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         title\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the title field
         
         dataWrap = <div><label for="title">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_title}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[title]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         cal\_organizer\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the cal\_organizer field
         
         dataWrap = <div><label for="cal\_organizer">{LLL:EXT:cal/controller/lo
         callang.xml:l\_event\_cal\_organizer}:</label><select
         name="tx\_cal\_controller[cal\_organizer]" size="1">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         organizer\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the organizer field
         
         dataWrap = <div><label for="organizer">{LLL:EXT:cal/controller/localla
         ng.xml:l\_organizer}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[organizer]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         cal\_location\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the cal\_location field
         
         dataWrap = <div><label for="cal\_location">{LLL:EXT:cal/controller/loc
         allang.xml:l\_location}:</label><select
         name="tx\_cal\_controller[cal\_location]" size="1">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         location\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the location field
         
         dataWrap = <div><label for="location">{LLL:EXT:cal/controller/locallan
         g.xml:l\_location}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[location]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         teaser\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the teaser field
         
         dataWrap = <div><label for="teaser">{LLL:EXT:cal/controller/locallang.
         xml:l\_event\_teaser}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         description\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the description field
         
         dataWrap = <div><label for="description">{LLL:EXT:cal/controller/local
         lang.xml:l\_event\_description}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         image\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the image field
         
         imageCount=2
         
         file.maxW = 150
         
         file.maxH = 150
         
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_image}:</label><input type="file" value="\|"
         name="tx\_cal\_controller[image][]" />###IMAGE\_PREVIEW###</div>
   
   Default
         See Description


.. container:: table-row

   Property
         imageUpload\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the imageUpload field
         
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_image\_upload}:</label><input type="file" value="\|"
         name="tx\_cal\_controller[image][]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         frequency\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the frequency field
         
         dataWrap = <div><label for="frequency">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_frequency}:</label><select
         name="tx\_cal\_controller[frequency\_id]" size="1">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         byDay\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the byDay field
         
         split {
         
         \# replace ###SPLITTER### with the split option
         
         token = ###SPLITTER###
         
         \# the order is beeing defined in the normal optionSplit style
         
         cObjNum = 1 \|\| 2 \|\| 3 \|\| 4 \|\| 5 \|\| 6 \|\| 7
         
         \# define the wraps on every position
         
         1.current = 1
         
         1.wrap = <input type="checkbox" value="mo"
         name="tx\_cal\_controller[by\_day]" \|
         
         2.current = 1
         
         2.wrap = <input type="checkbox" value="tu"
         name="tx\_cal\_controller[by\_day]" \|
         
         3.current = 1
         
         3.wrap = <input type="checkbox" value="we"
         name="tx\_cal\_controller[by\_day]" \|
         
         4.current = 1
         
         4.wrap = <input type="checkbox" value="th"
         name="tx\_cal\_controller[by\_day]" \|
         
         5.current = 1
         
         5.wrap = <input type="checkbox" value="fr"
         name="tx\_cal\_controller[by\_day]" \|
         
         6.current = 1
         
         6.wrap = <input type="checkbox" value="sa"
         name="tx\_cal\_controller[by\_day]" \|
         
         7.current = 1
         
         7.wrap = <input type="checkbox" value="su"
         name="tx\_cal\_controller[by\_day]" \|
         
         }
         
         dataWrap = <div><label for="bymonthday">{LLL:EXT:cal/controller/locall
         ang.xml:l\_event\_edit\_byday}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         byMonthday\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the byMonthday field
         
         dataWrap = <div><label for="bymonthday">{LLL:EXT:cal/controller/locall
         ang.xml:l\_event\_edit\_bymonthday}:</label><input type="text"
         value="\|" name="tx\_cal\_controller[by\_monthday]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         byMonth\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the byMonth field
         
         dataWrap = <div><label for="bymonth">{LLL:EXT:cal/controller/locallang
         .xml:l\_event\_edit\_bymonth}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[by\_month]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         until\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the until field
         
         dataWrap = <span id="until"><label for="until">{LLL:EXT:cal/controller
         /locallang.xml:l\_until}:</label><input type="text" value="\|"
         id="until\_value" name="tx\_cal\_controller[until]"
         /></span>
   
   Default
         See Description


.. container:: table-row

   Property
         count\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the count field
         
         dataWrap = <div><label for="count">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_count}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[count]"/></div>
   
   Default
         See Description


.. container:: table-row

   Property
         interval\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the interval field
         
         dataWrap = <div><label for="interval">{LLL:EXT:cal/controller/locallan
         g.xml:l\_event\_interval}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[interval]"/></div>
   
   Default
         See Description


.. container:: table-row

   Property
         notify\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the notify field
         
         dataWrap = <div><label for="notify">{LLL:EXT:cal/controller/locallang.
         xml:l\_event\_notify}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         exception\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the exception field
         
         dataWrap = <div><label for="exception">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_exception}:</label>\|</div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_event]

plugin.tx\_cal\_controller.view.create\_event.tree

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         calendar
   
   Data type
         String / CSV
   
   Description
         Defines the calendars shown in the tree (csv of ids)
   
   Default


.. container:: table-row

   Property
         category
   
   Data type
         String / CSV
   
   Description
         Defines the categories shown in the tree (csv of ids)
   
   Default


.. container:: table-row

   Property
         calendarTitle
   
   Data type
         cObj
   
   Description
         Content object to render the calendar title
         
         calendarTitle {
         
         if.equals = ###
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         rootElement
   
   Data type
         cObj
   
   Description
         Content object to render each root element of the tree
         
         rootElement {
         
         wrap = <table class="treelevel0"><tr><td>\|</td></tr></table>
         
         }
   
   Default
         TEXT


.. container:: table-row

   Property
         selector
   
   Data type
         cObj
   
   Description
         Content object to render the selector
         
         wrap = <input type="checkbox" name="tx\_cal\_controller[category][]"
         value="###UID###" \| />
   
   Default
         TEXT


.. container:: table-row

   Property
         element
   
   Data type
         String
   
   Description
         Defines a root node of the tree
   
   Default
         <span class="###HEADERSTYLE###\_bullet
         ###HEADERSTYLE###\_legend\_bullet" >&bull;</span><span
         class="###HEADERSTYLE###\_text">###TITLE###</span>


.. container:: table-row

   Property
         emptyElement
   
   Data type
         String
   
   Description
         Defines an element if the tree has no nodes
   
   Default
         <br/><br/>


.. container:: table-row

   Property
         subElement
   
   Data type
         String
   
   Description
         Defines a sub node of the tree
   
   Default
         <br /><table class="treelevel###LEVEL###" id="treelevel###UID###">


.. container:: table-row

   Property
         subElement\_wrap
   
   Data type
         String
   
   Description
         Defines a wrap for sub node of the tree
   
   Default
         <tr><td>\|</td></tr>


.. container:: table-row

   Property
         subElement\_pre
   
   Data type
         String
   
   Description
         Defines the trailer for a branch level
   
   Default
         </table>


.. container:: table-row

   Property
         categorySelectorSubmit
   
   Data type
         String
   
   Description
         Defines the submit button
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_event.tree]

plugin.tx\_cal\_controller.view.create\_event.location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         excludeEventsInResult
   
   Data type
         Boolean
   
   Description
         Disable the search for events, since we already have the event as the
         root for the location
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_event.location]

plugin.tx\_cal\_controller.view.create\_event.organizer

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         excludeEventsInResult
   
   Data type
         Boolean
   
   Description
         Disable the search for events, since we already have the event as the
         root for the organizer
   
   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_event.organizer]

plugin.tx\_cal\_controller.view.create\_event.rte

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         width
   
   Data type
         Integer
   
   Description
         custom width for RTE
   
   Default


.. container:: table-row

   Property
         height
   
   Data type
         Integer
   
   Description
         custom height for RTE
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_event.rte]


edit\_event
"""""""""""

plugin.tx\_cal\_controller.view.edit\_event <
plugin.tx\_cal\_controller.view.create\_event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         editEventViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing an event. If this is not configured,
         then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the event has been updated. The
         parameter 'getdate' will get passed along.
         
         also: redirectAfterEditToView
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the event has been updated. The parameter
         'getdate' will get passed along.
         
         also: redirectAfterEditToPid
   
   Default


.. container:: table-row

   Property
         image\_stdWrap
   
   Data type
         stdWrap
   
   Description
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_image}:</label><input type="hidden"
         value="###IMAGE\_VALUE###" name="tx\_cal\_controller[image][]" />###IM
         AGE\_PREVIEW###<br/>{LLL:EXT:cal/controller/locallang.xml:l\_delete\_i
         mage}:<input type="checkbox" name="tx\_cal\_controller[removeImage][]"
         value="###IMAGE\_VALUE###"</div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.edit\_event]


confirm\_event
""""""""""""""

plugin.tx\_cal\_controller.view.confirm\_event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for confirmation of an event created in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.confirmEventTemplate}


.. container:: table-row

   Property
         calendar\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the calendar field
         
         dataWrap = <div><label for="calendar">{LLL:EXT:cal/controller/locallan
         g.xml:l\_calendar}:</label>\|<input type="hidden"
         name="tx\_cal\_controller[calendar\_id]"
         value="###CALENDAR\_VALUE###"></div>
   
   Default
         See Description


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hide">{LLL:EXT:cal/controller/locallang.xm
         l:l\_hidden}:</label>\|<input type="hidden"
         value="###HIDDEN\_VALUE###" name="tx\_cal\_controller[hidden]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         category\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the category field
         
         dataWrap = <div><label for="category">{LLL:EXT:cal/controller/locallan
         g.xml:l\_category}:</label>\|<input type="hidden"
         value="###CATEGORY\_VALUE###"
         name="tx\_cal\_controller[category\_ids]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         allday\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the allday field
         
         dataWrap = <div><label for"allday">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_allday}:</label>\|<input type="hidden"
         value="###ALLDAY\_VALUE###" name="tx\_cal\_controller[allday]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         startdate\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the startdate field
         
         dataWrap = <div><label for="startdate">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_edit\_startdate}:</label>\|<input type="hidden"
         value="###STARTDATE\_VALUE###"
         name="tx\_cal\_controller[event\_start\_day]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         enddate\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the enddate field
         
         dataWrap = <div><label for="enddate">{LLL:EXT:cal/controller/locallang
         .xml:l\_event\_edit\_enddate}:</label>\|<input type="hidden"
         value="###ENDDATE\_VALUE###"
         name="tx\_cal\_controller[event\_end\_day]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         starttime\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the starttime field
         
         dataWrap = <div><label for="startdate">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_edit\_starttime}:</label>\|<input type="hidden"
         value="###STARTTIME\_VALUE###"
         name="tx\_cal\_controller[event\_start\_time]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         endtime\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the endtime field
         
         dataWrap = <div><label for="enddate">{LLL:EXT:cal/controller/locallang
         .xml:l\_event\_edit\_endtime}:</label>\|<input type="hidden"
         value="###ENDTIME\_VALUE###"
         name="tx\_cal\_controller[event\_end\_time]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         title\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the title field
         
         dataWrap = <div><label for="title">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_title}:</label>\|<input type="hidden"
         value="###TITLE\_VALUE###" name="tx\_cal\_controller[title]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         cal\_organizer\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the cal\_organizer field
         
         dataWrap = <div><label for="cal\_organizer">{LLL:EXT:cal/controller/lo
         callang.xml:l\_event\_cal\_organizer}:</label>\|<input type="hidden"
         value="###CAL\_ORGANIZER\_VALUE###"
         name="tx\_cal\_controller[cal\_organizer]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         organizer\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the organizer field
         
         dataWrap = <div><label for="organizer">{LLL:EXT:cal/controller/localla
         ng.xml:l\_organizer}:</label>\|<input type="hidden"
         value="###ORGANIZER\_VALUE###" name="tx\_cal\_controller[organizer]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         cal\_location\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the cal\_location field
         
         dataWrap = <div><label for="cal\_location">{LLL:EXT:cal/controller/loc
         allang.xml:l\_event\_cal\_location}:</label>\|<input type="hidden"
         value="###CAL\_LOCATION\_VALUE###"
         name="tx\_cal\_controller[cal\_location]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         location\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the location field
         
         dataWrap = <div><label for="location">{LLL:EXT:cal/controller/locallan
         g.xml:l\_location}:</label>\|<input type="hidden"
         value="###LOCATION\_VALUE###" name="tx\_cal\_controller[location]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         teaser\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the teaser field
         
         dataWrap = <div><label for="teaser">{LLL:EXT:cal/controller/locallang.
         xml:l\_event\_teaser}:</label>\|<input type="hidden"
         value="###TEASER\_VALUE###" name="tx\_cal\_controller[teaser]"/></div>
   
   Default
         See Description


.. container:: table-row

   Property
         description\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the description field
         
         dataWrap = <div><label for="description">{LLL:EXT:cal/controller/local
         lang.xml:l\_event\_description}:</label>\|<input type="hidden"
         value="###DESCRIPTION\_VALUE###"
         name="tx\_cal\_controller[description]"/></div>
   
   Default
         See Description


.. container:: table-row

   Property
         image\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the image field
         
         imageCount=2
         
         file.maxW = 150
         
         file.maxH = 150
         
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_image}:</label>\|<input type="hidden"
         value="###IMAGE\_VALUE###" name="tx\_cal\_controller[image][]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         frequency\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the frequency field
         
         dataWrap = <div><label for="frequency">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_frequency}:</label>\|<input type="hidden"
         value="###FREQUENCY\_VALUE###"
         name="tx\_cal\_controller[frequency\_id]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         byDay\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the byDay field
         
         dataWrap = <div><label for="bymonthday">{LLL:EXT:cal/controller/locall
         ang.xml:l\_event\_edit\_byday}:</label>\|<input type="hidden"
         value="###BY\_DAY\_VALUE###" name="tx\_cal\_controller[by\_monthday]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         byMonthday\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the byMonthday field
         
         dataWrap = <div><label for="bymonthday">{LLL:EXT:cal/controller/locall
         ang.xml:l\_event\_edit\_bymonthday}:</label>\|<input type="hidden"
         value="###BY\_MONTHDAY\_VALUE###"
         name="tx\_cal\_controller[by\_monthday]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         byMonth\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the byMonth field
         
         dataWrap = <div><label for="bymonth">{LLL:EXT:cal/controller/locallang
         .xml:l\_event\_edit\_bymonth}:</label>\|<input type="hidden"
         value="###BY\_MONTH\_VALUE###" name="tx\_cal\_controller[by\_month]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         until\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the until field
         
         dataWrap = <div><label for="until">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_edit\_until}:</label>\|<input type="hidden"
         value="###UNTIL\_VALUE###" name="tx\_cal\_controller[until]"
         id="until" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         count\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the count field
         
         dataWrap = <div><label for="count">{LLL:EXT:cal/controller/locallang.x
         ml:l\_event\_count}:</label>\|<input type="hidden"
         value="###COUNT\_VALUE###" name="tx\_cal\_controller[count]"/></div>
   
   Default
         See Description


.. container:: table-row

   Property
         interval\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the interval field
         
         dataWrap = <div><label for="interval">{LLL:EXT:cal/controller/locallan
         g.xml:l\_event\_interval}:</label>\|<input type="hidden"
         value="###INTERVAL\_VALUE###"
         name="tx\_cal\_controller[interval]"/></div>
   
   Default
         See Description


.. container:: table-row

   Property
         notify\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the notify field
         
         dataWrap = <div><label for="notify">{LLL:EXT:cal/controller/locallang.
         xml:l\_event\_notify}:</label>\|<input type="hidden"
         value="###NOTIFY\_VALUE###"
         name="tx\_cal\_controller[notify\_ids]"/></div>
   
   Default
         See Description


.. container:: table-row

   Property
         exception\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the exception field
         
         dataWrap = <div><label for="exception">{LLL:EXT:cal/controller/localla
         ng.xml:l\_event\_exception}:</label>\|<input type="hidden"
         value="###EXCEPTION\_SINGLE\_VALUE###"
         name="tx\_cal\_controller[exception\_single\_ids]"/><input
         type="hidden" value="###EXCEPTION\_GROUP\_VALUE###"
         name="tx\_cal\_controller[exception\_group\_ids]"/></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.confirm\_event]


delete\_event
"""""""""""""

plugin.tx\_cal\_controller.view.delete\_event <
plugin.tx\_cal\_controller.view.confirm\_event

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         deleteEventViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting an event. If this is not configured,
         then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the event has been deleted. The
         parameter 'getdate' will get passed along.
         
         also: redirectAfterDeleteToView
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the event has been deleted. The parameter
         'getdate' will get passed along.
         
         also: redirectAfterDeleteToPid
   
   Default


.. container:: table-row

   Property
         deleteEventTemplate
   
   Data type
         String / Path
   
   Description
         Template for deleting an event in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.event.deleteEventTemplate}


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.delete\_event]


create\_calendar
""""""""""""""""

plugin.tx\_cal\_controller.view.create\_calendar

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for creating a new calendar in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.calendar.createCalendarTemplate}


.. container:: table-row

   Property
         createCalendarViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating an calendar. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterCreateToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the calendar has been saved.
         
         also: redirectAfterCreateToView
   
   Default


.. container:: table-row

   Property
         redirectAfterCreateToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the calendar has been saved.
         
         also: redirectAfterCreateToPid
   
   Default


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label><input type="checkbox" \|
         name="tx\_cal\_controller[hidden]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         title\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the title field
         
         dataWrap = <div><label for="title">{LLL:EXT:cal/controller/locallang.x
         ml:l\_calendar\_title}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[title]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         owner\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the owner field
         
         dataWrap = <div><label for="owner">{LLL:EXT:cal/controller/locallang.x
         ml:l\_calendar\_owner}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         activateFreeAndBusy\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the activateFreeAndBusy field
         
         dataWrap = <div><label for="activateFreeAndBusy">{LLL:EXT:cal/controll
         er/locallang.xml:l\_calendar\_activateFreeAndBusy}:</label><input
         type="checkbox" \| name="tx\_cal\_controller[activateFreeAndBusy]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         freeAndBusyUser\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the freeAndBusyUser field
         
         dataWrap = <div><label for="freeAndBusyUser">{LLL:EXT:cal/controller/l
         ocallang.xml:l\_calendar\_freeAndBusyUser}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         calendarType\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the calendarType field
         
         dataWrap = <div><label for="calendarType">{LLL:EXT:cal/controller/loca
         llang.xml:l\_calendar\_type}:</label><select
         name="tx\_cal\_controller[calendarType]" size="1"
         onchange="javascript: typeChanged(this);"
         id="calendarType">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         exturl\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the exturl field
         
         dataWrap = <div id="exturl"><label for="exturl">{LLL:EXT:cal/controlle
         r/locallang.xml:l\_calendar\_exturl}:</label><input type="text"
         value="\|" name="tx\_cal\_controller[exturl]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         icsfile\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the icsfile field
         
         dataWrap = <div id="icsfile"><label for="icsfile">{LLL:EXT:cal/control
         ler/locallang.xml:l\_calendar\_icsfile}:</label><input type="file"
         value="\|" name="tx\_cal\_controller[icsfile]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         refresh\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the refresh field
         
         dataWrap = <div id="refresh"><label for="refresh">{LLL:EXT:cal/control
         ler/locallang.xml:l\_calendar\_refresh}:</label><input type="text"
         value="\|" name="tx\_cal\_controller[refresh]" /></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_calendar]


edit\_calendar
""""""""""""""

plugin.tx\_cal\_controller.view.edit\_calendar <
plugin.tx\_cal\_controller.view.create\_calendar

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         editCalendarViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing a calendar. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the calendar has been updated.
         
         also: redirectAfterEditToView
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the calendar has been updated.
         
         also: redirectAfterEditToPid
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.edit\_calendar]


confirm\_calendar
"""""""""""""""""

plugin.tx\_cal\_controller.view.confirm\_calendar

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for creating/editing a (new) calendar in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.calendar.confirmCalendarTemplate}


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label>\|<input type="hidden"
         value="###HIDDEN\_VALUE###" name="tx\_cal\_controller[hidden]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         title\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the title field
         
         dataWrap = <div><label for="title">{LLL:EXT:cal/controller/locallang.x
         ml:l\_calendar\_title}:</label>\|<input type="hidden"
         value="###TITLE\_VALUE###" name="tx\_cal\_controller[title]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         owner\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the owner field
         
         dataWrap = <div><label for="owner">{LLL:EXT:cal/controller/locallang.x
         ml:l\_calendar\_owner}:</label>\|<input type="hidden"
         value="###OWNER\_SINGLE\_VALUE###"
         name="tx\_cal\_controller[owner\_single]" /><input type="hidden"
         value="###OWNER\_GROUP\_VALUE###"
         name="tx\_cal\_controller[owner\_group]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         activateFreeAndBusy\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the activateFreeAndBusy field
         
         dataWrap = <div><label for="activateFreeAndBusy">{LLL:EXT:cal/controll
         er/locallang.xml:l\_calendar\_activateFreeAndBusy}:</label>\|<input
         type="hidden" value="###ACTIVATE\_FREEANDBUSY\_VALUE###"
         name="tx\_cal\_controller[activateFreeAndBusy]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         freeAndBusyUser\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the freeAndBusyUser field
         
         dataWrap = <div><label for="freeAndBusyUser">{LLL:EXT:cal/controller/l
         ocallang.xml:l\_calendar\_freeAndBusyUser}:</label>\|<input
         type="hidden" value="###FREEANDBUSYUSER\_SINGLE\_VALUE###"
         name="tx\_cal\_controller[freeAndBusyUser\_single]" /><input
         type="hidden" value="###FREEANDBUSYUSER\_GROUP\_VALUE###"
         name="tx\_cal\_controller[freeAndBusyUser\_group]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         calendarType\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the calendarType field
         
         dataWrap = <div><label for="calendarType">{LLL:EXT:cal/controller/loca
         llang.xml:l\_calendar\_type}:</label>\|<input type="hidden"
         value="###CALENDARTYPE\_VALUE###"
         name="tx\_cal\_controller[calendarType]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         exturl\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the exturl field
         
         dataWrap = <div id="exturl"><label for="exturl">{LLL:EXT:cal/controlle
         r/locallang.xml:l\_calendar\_exturl}:</label>>\|<input type="hidden"
         value="###EXTURL\_VALUE###" name="tx\_cal\_controller[exturl]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         icsfile\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the icsfile field
         
         dataWrap = <div id="icsfile"><label for="icsfile">{LLL:EXT:cal/control
         ler/locallang.xml:l\_calendar\_icsfile}:</label>\|<input type="hidden"
         value="###ICSFILE\_VALUE###" name="tx\_cal\_controller[icsfile]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         refresh\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the refresh field
         
         dataWrap = <div id="refresh"><label for="refresh">{LLL:EXT:cal/control
         ler/locallang.xml:l\_calendar\_refresh}:</label>\|<input type="hidden"
         value="###REFRESH\_VALUE###" name="tx\_cal\_controller[refresh]"
         /></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.confirm\_calendar]


delete\_calendar
""""""""""""""""

plugin.tx\_cal\_controller.view.delete\_calendar <
plugin.tx\_cal\_controller.view.confirm\_calendar

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for deleting a calendar in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.calendar.deleteCalendarTemplate}


.. container:: table-row

   Property
         deleteCalendarViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting a calendar. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the calendar has been deleted.
         
         also: redirectAfterDeleteToView
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the calendar has been deleted.
         
         also: redirectAfterDeleteToPid
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.delete\_calendar]


create\_category
""""""""""""""""

plugin.tx\_cal\_controller.view.create\_category

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for creating a category in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.category.createCategoryTemplate}


.. container:: table-row

   Property
         createCategoryViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating a category. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterCreateToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the category has been saved.
         
         also: redirectAfterCreateToView
   
   Default


.. container:: table-row

   Property
         redirectAfterCreateToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the category has been saved.
         
         also: redirectAfterCreateToPid
   
   Default


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label><input type="checkbox" \|
         name="tx\_cal\_controller[hidden]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         title\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the title field
         
         dataWrap = <div><label for="title">{LLL:EXT:cal/controller/locallang.x
         ml:l\_category\_title}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[title]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         calendar\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the calendar field
         
         dataWrap = <div><label for="calendar">{LLL:EXT:cal/controller/locallan
         g.xml:l\_category\_calendar}:</label><select
         name="tx\_cal\_controller[switch\_calendar]" size="1"
         onchange="submit();">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         headerStyle\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the headerStyle field
         
         dataWrap = <div><label for="headerStyle">{LLL:EXT:cal/controller/local
         lang.xml:l\_category\_headerstyle}:</label><select
         name="tx\_cal\_controller[headerstyle]" size="1"
         onchange="getNewStyle(this);"
         id="tx\_cal\_controller\_headerstyle">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         bodyStyle\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the bodyStyle field
         
         dataWrap = <div><label for="bodyStyle">{LLL:EXT:cal/controller/localla
         ng.xml:l\_category\_bodystyle}:</label><select
         name="tx\_cal\_controller[bodystyle]" size="1"
         onchange="getNewStyle(this);"
         id="tx\_cal\_controller\_bodystyle">\|</select></div>
   
   Default
         See Description


.. container:: table-row

   Property
         parentCategory\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the parentCategory field
         
         dataWrap = <div><label for="parentCategory">{LLL:EXT:cal/controller/lo
         callang.xml:l\_category\_parent\_category}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         sharedUserAllowed\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the sharedUserAllowed field
         
         dataWrap = <div><label for="sharedUserAllowed">{LLL:EXT:cal/controller
         /locallang.xml:l\_category\_shared\_user\_allowed}:</label><input
         type="checkbox" \| name="tx\_cal\_controller[shared\_user\_allowed]"
         /></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

plugin.tx\_cal\_controller.view.create\_category.tree <
plugin.tx\_cal\_controller.view.create\_event.tree

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   a
         **Property:**
   
   b
         **Data type:**
   
   c
         **Description:**
   
   d
         **Default:**


.. container:: table-row

   a
         selector
   
   b
         cObj
   
   c
         Content object to render the selector
         
         wrap = <input type="radio"
         name="tx\_cal\_controller[parent\_category][]" value="###UID###" \| />
   
   d
         TEXT


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_category]


edit\_category
""""""""""""""

plugin.tx\_cal\_controller.view.edit\_category <
plugin.tx\_cal\_controller.view.create\_category

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for confirming creation of a new category in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.category.confirmCategoryTemplate}


.. container:: table-row

   Property
         editCategoryViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing a category. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the category has been updated.
         
         also: redirectAfterEditToView
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the category has been updated.
         
         also: redirectAfterEditToPid
   
   Default


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label>\|<input type="hidden"
         value="###HIDDEN\_VALUE###" name="tx\_cal\_controller[hidden]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         title\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the title field
         
         dataWrap = <div><label for="title">{LLL:EXT:cal/controller/locallang.x
         ml:l\_category\_title}:</label>\|<input type="hidden"
         value="###TITLE\_VALUE###" name="tx\_cal\_controller[title]" /></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.edit\_category]


confirm\_category
"""""""""""""""""

plugin.tx\_cal\_controller.view.confirm\_category

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for confirming creation of a new category in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.category.confirmCategoryTemplate}


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label>\|<input type="hidden"
         value="###HIDDEN\_VALUE###" name="tx\_cal\_controller[hidden]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         title\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the title field
         
         dataWrap = <div><label for="title">{LLL:EXT:cal/controller/locallang.x
         ml:l\_category\_title}:</label>\|<input type="hidden"
         value="###TITLE\_VALUE###" name="tx\_cal\_controller[title]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         calendar\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the calendar field
         
         dataWrap = <div><label for="calendar">{LLL:EXT:cal/controller/locallan
         g.xml:l\_category\_calendar}:</label>\|<input type="hidden"
         value="###CALENDAR\_VALUE###"
         name="tx\_cal\_controller[switch\_calendar]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         headerStyle\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the headerStyle field
         
         dataWrap = <div><label for="headerStyle">{LLL:EXT:cal/controller/local
         lang.xml:l\_category\_headerstyle}:</label>\|<input type="hidden"
         value="###HEADERSTYLE\_VALUE###"
         name="tx\_cal\_controller[headerstyle]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         bodyStyle\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the bodyStyle field
         
         dataWrap = <div><label for="bodyStyle">{LLL:EXT:cal/controller/localla
         ng.xml:l\_category\_bodystyle}:</label>\|<input type="hidden"
         value="###BODYSTYLE\_VALUE###" name="tx\_cal\_controller[bodystyle]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         parentCategory\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the parentCategory field
         
         dataWrap = <div><label for="parentCategory">{LLL:EXT:cal/controller/lo
         callang.xml:l\_category\_parent\_category}:</label>\|<input
         type="hidden" value="###PARENT\_CATEGORY\_VALUE###"
         name="tx\_cal\_controller[parent\_category]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         sharedUserAllowed\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the sharedUserAllowed field
         
         dataWrap = <div><label for="sharedUserAllowed">{LLL:EXT:cal/controller
         /locallang.xml:l\_category\_shared\_user\_allowed}:</label>\|<input
         type="hidden" value="###SHARED\_USER\_ALLOWED\_VALUE###"
         name="tx\_cal\_controller[shared\_user\_allowed]" /></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.confirm\_category]


delete\_category
""""""""""""""""

plugin.tx\_cal\_controller.view.delete\_category <
plugin.tx\_cal\_controller.view.confirm\_category

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for deletion of a category in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.category.deleteCategoryTemplate}


.. container:: table-row

   Property
         deleteCategoryViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting a category. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the category has been deleted.
         
         also: redirectAfterDeleteToView
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the category has been deleted.
         
         also: redirectAfterDeleteToPid
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.delete\_category]


create\_location
""""""""""""""""

plugin.tx\_cal\_controller.view.create\_location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for creating a location in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.location.createLocationTemplate}


.. container:: table-row

   Property
         createLocationViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating a location. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterCreateToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the location has been created.
         
         also: redirectAfterCreateToView
   
   Default


.. container:: table-row

   Property
         redirectAfterCreateToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the location has been created.
         
         also: redirectAfterCreateToPid
   
   Default


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label><input type="checkbox" \|
         name="tx\_cal\_controller[hidden]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         name\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the name field
         
         dataWrap = <div><label for="name">{LLL:EXT:cal/controller/locallang.xm
         l:l\_location\_name}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[name]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         description\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the description field
         
         dataWrap = <div><label for="description">{LLL:EXT:cal/controller/local
         lang.xml:l\_location\_description}:</label><input type="text"
         value="\|" name="tx\_cal\_controller[description]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         street\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the street field
         
         dataWrap = <div><label for="street">{LLL:EXT:cal/controller/locallang.
         xml:l\_location\_street}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[street]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         zip\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the zip field
         
         dataWrap = <div><label for="zip">{LLL:EXT:cal/controller/locallang.xml
         :l\_location\_zip}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[zip]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         city\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the city field
         
         dataWrap = <div><label for="city">{LLL:EXT:cal/controller/locallang.xm
         l:l\_location\_city}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[city]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         phone\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the phone field
         
         dataWrap = <div><label for="phone">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_phone}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[phone]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         email\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the email field
         
         dataWrap = <div><label for="email">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_email}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[email]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         image\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the image field
         
         file.maxW = 150
         
         file.maxH = 150
         
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_image}:</label><input type="file" value="\|"
         name="tx\_cal\_controller[image][]" />###IMAGE\_PREVIEW###</div>
   
   Default
         See Description


.. container:: table-row

   Property
         imageUpload\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the imageUpload field
         
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_image\_upload}:</label><input type="file" value="\|"
         name="tx\_cal\_controller[image][]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         country\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the country field
         
         dataWrap = <div><label for="country">{LLL:EXT:cal/controller/locallang
         .xml:l\_location\_country}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[country]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         country\_static\_info\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the country\_static\_info field
         
         dataWrap = <div><label for="country">{LLL:EXT:cal/controller/locallang
         .xml:l\_location\_country}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         countryzone\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the countryzone field
         
         dataWrap = <div><label for="countryzone">{LLL:EXT:cal/controller/local
         lang.xml:l\_location\_countryzone}:</label><input type="text"
         value="\|" name="tx\_cal\_controller[countryzone]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         countryzone\_static\_info\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the countryzone\_static\_info field
         
         dataWrap = <div><label for="countryzone">{LLL:EXT:cal/controller/local
         lang.xml:l\_location\_countryzone}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         link\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the link field
         
         dataWrap = <div><label for="email">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_link}:</label><input type="text" value="\|"
         name="tx\_cal\_controller[link]" /></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_location]


edit\_location
""""""""""""""

plugin.tx\_cal\_controller.view.edit\_location <
plugin.tx\_cal\_controller.view.create\_location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         editLocationViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing a location. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the location has been edited.
         
         also: redirectAfterEditToView
   
   Default


.. container:: table-row

   Property
         redirectAfterEditToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the location has been edited.
         
         also: redirectAfterEditToPid
   
   Default


.. container:: table-row

   Property
         image\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the image field
         
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_image}:</label><input type="hidden"
         value="###IMAGE\_VALUE###" name="tx\_cal\_controller[image][]" />###IM
         AGE\_PREVIEW###<br/>{LLL:EXT:cal/controller/locallang.xml:l\_delete\_i
         mage}:<input type="checkbox" name="tx\_cal\_controller[removeImage][]"
         value="###IMAGE\_VALUE###"</div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.edit\_location]


confirm\_location
"""""""""""""""""

plugin.tx\_cal\_controller.view.confirm\_location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for confirming a location created or edited in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.location.confirmLocationTemplate}


.. container:: table-row

   Property
         hidden\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the hidden field
         
         dataWrap = <div><label for="hidden">{LLL:EXT:cal/controller/locallang.
         xml:l\_hidden}:</label>\|<input type="hidden" ###HIDDEN\_VALUE###
         name="tx\_cal\_controller[hidden]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         name\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the name field
         
         dataWrap = <div><label for="name">{LLL:EXT:cal/controller/locallang.xm
         l:l\_location\_name}:</label>\|<input type="hidden"
         value="###NAME\_VALUE###" name="tx\_cal\_controller[name]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         description\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the description field
         
         dataWrap = <div><label for="description">{LLL:EXT:cal/controller/local
         lang.xml:l\_location\_description}:</label>\|<input type="hidden"
         value="###DESCRIPTION\_VALUE###"
         name="tx\_cal\_controller[description]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         street\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the street field
         
         dataWrap = <div><label for="street">{LLL:EXT:cal/controller/locallang.
         xml:l\_location\_street}:</label>\|<input type="hidden"
         value="###STREET\_VALUE###" name="tx\_cal\_controller[street]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         zip\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the zip field
         
         dataWrap = <div><label for="zip">{LLL:EXT:cal/controller/locallang.xml
         :l\_location\_zip}:</label>\|<input type="hidden"
         value="###ZIP\_VALUE###" name="tx\_cal\_controller[zip]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         city\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the city field
         
         dataWrap = <div><label for="city">{LLL:EXT:cal/controller/locallang.xm
         l:l\_location\_city}:</label>\|<input type="hidden"
         value="###CITY\_VALUE###" name="tx\_cal\_controller[city]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         phone\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the phone field
         
         dataWrap = <div><label for="phone">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_phone}:</label>\|<input type="hidden"
         value="###PHONE\_VALUE###" name="tx\_cal\_controller[phone]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         email\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the email field
         
         dataWrap = <div><label for="email">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_email}:</label>\|<input type="hidden"
         value="###EMAIL\_VALUE###" name="tx\_cal\_controller[email]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         image\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the image field
         
         file.maxW = 150
         
         file.maxH = 150
         
         dataWrap = <div><label for="image">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_image}:</label>\|<input type="hidden"
         value="###IMAGE\_VALUE###" name="tx\_cal\_controller[image][]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         imageUpload\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the imageUpload field
         
         dataWrap = <div><label for="country">{LLL:EXT:cal/controller/locallang
         .xml:l\_location\_country}:</label>\|<input type="hidden"
         value="###COUNTRY\_VALUE###" name="tx\_cal\_controller[country]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         country\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the country field
         
         dataWrap = <div><label for="country">{LLL:EXT:cal/controller/locallang
         .xml:l\_location\_country}:</label>\|<input type="hidden"
         value="###COUNTRY\_VALUE###" name="tx\_cal\_controller[country]"
         /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         country\_static\_info\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the country\_static\_info field
         
         dataWrap = <div><label for="country">{LLL:EXT:cal/controller/locallang
         .xml:l\_location\_country}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         countryzone\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the countryzone field
         
         dataWrap = <div><label for="countryzone">{LLL:EXT:cal/controller/local
         lang.xml:l\_location\_countryzone}:</label>\|<input type="hidden"
         value="###COUNTRYZONE\_VALUE###"
         name="tx\_cal\_controller[countryzone]" /></div>
   
   Default
         See Description


.. container:: table-row

   Property
         countryzone\_static\_info\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the countryzone\_static\_info field
         
         dataWrap = <div><label for="countryzone">{LLL:EXT:cal/controller/local
         lang.xml:l\_location\_countryzone}:</label>\|</div>
   
   Default
         See Description


.. container:: table-row

   Property
         link\_stdWrap
   
   Data type
         stdWrap
   
   Description
         Standard wrap for the link field
         
         dataWrap = <div><label for="email">{LLL:EXT:cal/controller/locallang.x
         ml:l\_location\_link}:</label>\|<input type="hidden"
         value="###LINK\_VALUE###" name="tx\_cal\_controller[link]" /></div>
   
   Default
         See Description


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.confirm\_location]


delete\_location
""""""""""""""""

plugin.tx\_cal\_controller.view.delete\_location <
plugin.tx\_cal\_controller.view.confirm\_location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         template
   
   Data type
         String / Path
   
   Description
         Template for deleting a location in the frontend.
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.view.location.deleteLocationTemplate}


.. container:: table-row

   Property
         deleteLocationViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting a location. If this is not
         configured, then the current page will be used instead.
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToPid
   
   Data type
         Integer / PID
   
   Description
         Redirects to another page after the location has been edited.
         
         also: redirectAfterDeleteToView
   
   Default


.. container:: table-row

   Property
         redirectAfterDeleteToView
   
   Data type
         String / View
   
   Description
         Redirects to this view after the location has been edited.
         
         also: redirectAfterDeleteToPid
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.delete\_location]


create\_organizer
"""""""""""""""""

plugin.tx\_cal\_controller.view.create\_organizer <
plugin.tx\_cal\_controller.view.create\_location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         createOrganizerViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for creating an organizer. If this is not
         configured, then the current page will be used instead.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.create\_organizer]


edit\_organizer
"""""""""""""""

plugin.tx\_cal\_controller.view.edit\_organizer <
plugin.tx\_cal\_controller.view.edit\_location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         editOrganizerViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for editing an organizer. If this is not
         configured, then the current page will be used instead.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.edit\_organizer]


confirm\_organizer
""""""""""""""""""

plugin.tx\_cal\_controller.view.confirm\_organizer <
plugin.tx\_cal\_controller.view.confirm\_location


delete\_organizer
"""""""""""""""""

plugin.tx\_cal\_controller.view.delete\_organizer <
plugin.tx\_cal\_controller.view.confirm\_location

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         deleteOrganizerViewPid
   
   Data type
         Integer / PID
   
   Description
         Page view configured for deleting an organizer. If this is not
         configured, then the current page will be used instead.
   
   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.delete\_organizer]


translation
"""""""""""

plugin.tx\_cal\_controller.view.translation

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:
   
   Data type
         Data type:
   
   Description
         Description:
   
   Default
         Default:


.. container:: table-row

   Property
         languageMenu
   
   Data type
         cObj
   
   Description
         Create links to the different available languages. Insert them in
         ascending order of the language uid
         
         1 = IMAGE
         
         1 {
         
         file = media/flags/flag\_de.gif
         
         offset = 2,2
         
         }
         
         2 = IMAGE
         
         2 {
         
         file = media/flags/flag\_uk.gif
         
         offset = 2,2
         
         }
   
   Default
         COA


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.translation]

