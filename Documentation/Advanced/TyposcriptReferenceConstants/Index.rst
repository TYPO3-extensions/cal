.. _TypoScriptReferenceConstants:

================================
TypoScript Reference: constants
================================

.. include:: ../../Includes.txt

plugin.tx\_cal\_controller

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
         pidList

   Data type
         String / CSV

   Description
         Page ID's where events are stored. Required for ICS and XML output.

   Default


.. container:: table-row

   Property


   Data type


   Description


   Default


.. container:: table-row

   Property
         singleMaxW

   Data type
         Integer

   Description
         Event Image Max Width: Max width for an image displayed in Event view.

   Default
         240


.. container:: table-row

   Property
         singleMaxH

   Data type
         Integer

   Description
         Event Image Max Height: Max height for an image displayed in Event
         view.

   Default
         180


.. container:: table-row

   Property
         listMaxW

   Data type
         Integer

   Description
         List Image Max Width: Max width for an image displayed in List view.

   Default
         120


.. container:: table-row

   Property
         listMaxH

   Data type
         Integer

   Description
         List Image Max Height: Max height for an image displayed in List view.

   Default
         90


.. container:: table-row

   Property
         uploadPath.image

   Data type
         String

   Description
         Image Upload Path: Path where image files get uploaded. Normally this
         has not to be changed unless you modify the settings in TCA.

   Default
         uploads/tx\_cal/pics/


.. container:: table-row

   Property
         uploadPath.media

   Data type
         String

   Description
         Media Upload Path: Path where media files (f.e. attachments) get
         uploaded. Normally this has not to be changed unless you modify the
         settings in TCA.

   Default
         uploads/tx\_cal/media/


.. container:: table-row

   Property
         rights.edit

   Data type
         Boolean

   Description
         Enable Frontend Editing: This setting turns frontend editing on.
         Without it, the other Typoscript options for frontend editing are
         ignored.

   Default
         0


.. container:: table-row

   Property
         rights.admin.user

   Data type
         String / CSV

   Description
         Frontend Calendar Admin Users: Comma seperated list of frontend user
         IDs.

   Default


.. container:: table-row

   Property
         rights.admin.group

   Data type
         String / CSV

   Description
         Frontend Calendar Admin Groups: Comma separated list of frontend group
         IDs.

   Default


.. container:: table-row

   Property
         rights.defaultSavePid

   Data type
         Integer / PID

   Description
         Page ID that frontend-created records should be saved on.

   Default


.. container:: table-row

   Property
         emailAddress

   Data type
         String / Email

   Description
         Email Address: Address used for notifications and reminder emails.

   Default


.. container:: table-row

   Property
         emailName

   Data type
         String

   Description
         Email Name: Name used for notification and reminder emails.

   Default


.. container:: table-row

   Property
         emailOrganisation

   Data type
         String

   Description
         Email Organization: Organization used for notification and reminder
         emails.

   Default


.. container:: table-row

   Property
         view.event.remind.time

   Data type
         Integer

   Description
         Reminder Time: Remind users about subscribed events this many minutes
         in advance. Requires the scheduler extension.

   Default
         30


.. container:: table-row

   Property
         view.event.notify.subscriptionViewPid

   Data type
         Integer / PID

   Description
         Subscription Page: Page ID where the subscription view is allowed.

   Default


.. container:: table-row

   Property
         view.event.meeting.statusViewPid

   Data type
         Integer / PID

   Description
         Meeting status Page: Page ID where the meeting-status view is allowed.

   Default


.. container:: table-row

   Property
         view.location.showMap

   Data type
         Boolean

   Description
         Enable the google map (wec\_map needed)

   Default
         0


.. container:: table-row

   Property
         view.dateFormat

   Data type


   Description
         General date format

   Default
         %B %d


.. container:: table-row

   Property
         view.timeFormat

   Data type


   Description
         General time format

   Default
         %I:%M %p


.. container:: table-row

   Property
         view.imagePath

   Data type
         String / Path

   Description
         Images (Path): Path of the calendar images.

   Default
         EXT:cal/template/img


.. container:: table-row

   Property
         view.javascriptPath

   Data type
         String / Path

   Description
         Javascripts (Path): Path of the calendar javascripts.

   Default
         EXT:cal/template/js


.. container:: table-row

   Property
         view.calendar.createCalendarTemplate

   Data type
         String / Path

   Description
         Calendar Create Template: Marker-based template for calendar creation.

   Default
         EXT:cal/template/create\_calendar.tmpl


.. container:: table-row

   Property
         view.calendar.confirmCalendarTemplate

   Data type
         String / Path

   Description
         Calendar Confirm Template: Marker-based template for calendar
         confirmation.

   Default
         EXT:cal/template/confirm\_calendar.tmpl


.. container:: table-row

   Property
         view.calendar.deleteCalendarTemplate

   Data type
         String / Path

   Description
         Calendar Delete Template: Marker-based template for calendar deletion.

   Default
         EXT:cal/template/delete\_calendar.tmpl


.. container:: table-row

   Property
         view.category.createCategoryTemplate

   Data type
         String / Path

   Description
         Category Create Template: Marker-based template for category creation.

   Default
         EXT:cal/template/create\_category.tmpl


.. container:: table-row

   Property
         view.category.confirmCategoryTemplate

   Data type
         String / Path

   Description
         Category Confirm Template: Marker-based template for category
         confirmation.

   Default
         EXT:cal/template/confirm\_category.tmpl


.. container:: table-row

   Property
         view.category.deleteCategoryTemplate

   Data type
         String / Path

   Description
         Category Delete Template: Marker-based template for category deletion.

   Default
         EXT:cal/template/delete\_category.tmpl


.. container:: table-row

   Property
         view.day.dayTemplate

   Data type
         String / Path

   Description
         Day View Template: Marker-based template for day view.

   Default
         EXT:cal/template/day.tmpl


.. container:: table-row

   Property
         view.week.weekTemplate

   Data type
         String / Path

   Description
         Week View Template: Marker-based template for week view.

   Default
         EXT:cal/template/week.tmpl


.. container:: table-row

   Property
         view.month.monthTemplate

   Data type
         String / Path

   Description
         Month View Template: Marker-based template for month view.

   Default
         EXT:cal/template/month.tmpl


.. container:: table-row

   Property
         view.month.monthSmallTemplate

   Data type
         String / Path

   Description
         Month View Template (Small): Marker-based template for small month
         view.

   Default
         EXT:cal/template/month\_small.tmpl


.. container:: table-row

   Property
         view.month.monthMediumTemplate

   Data type
         String / Path

   Description
         Month View Template (Medium): Marker-based template for medium month
         view.

   Default
         EXT:cal/template/month\_medium.tmpl


.. container:: table-row

   Property
         view.month.monthLargeTemplate

   Data type
         String / Path

   Description
         Month View Template (Large): Marker-based template for large month
         view.

   Default
         EXT:cal/template/month\_large.tmpl


.. container:: table-row

   Property
         view.month.horizontalSidebarTemplate

   Data type
         String / Path

   Description
         Calendar Nav Template: Marker-based template for calendar navigation
         view.

   Default
         EXT:cal/template/calendar\_nav.tmpl


.. container:: table-row

   Property
         view.year.yearTemplate

   Data type
         String / Path

   Description
         Year View Template: Marker-based template for year view.

   Default
         EXT:cal/template/year.tmpl


.. container:: table-row

   Property
         view.event.eventTemplate

   Data type
         String / Path

   Description
         Event-wrapper-Template: Marker-based template for event view.

   Default
         EXT:cal/template/event.tmpl


.. container:: table-row

   Property
         view.event.eventModelTemplate

   Data type
         String / Path

   Description
         Event (phpiCalendar) Template: Marker-based template for phpiCalendar
         event view.

   Default
         EXT:cal/template/event\_model.tmpl


.. container:: table-row

   Property
         view.event.createEventTemplate

   Data type
         String / Path

   Description
         Event (Create) Template: Marker-based template for event creation
         view.

   Default
         EXT:cal/template/create\_event.tmpl


.. container:: table-row

   Property
         view.event.confirmEventTemplate

   Data type
         String / Path

   Description
         Event (Confirm) Template: Marker-based template for event confirmation
         view.

   Default
         EXT:cal/template/confirm\_event.tmpl


.. container:: table-row

   Property
         view.event.deleteEventTemplate

   Data type
         String / Path

   Description
         Event (Delete) Template: Marker-based template for event deletion
         view.

   Default
         EXT:cal/template/delete\_event.tmpl


.. container:: table-row

   Property
         view.event.notify.all.onCreateTemplate

   Data type
         String / Path

   Description
         Event email notification Template: Marker-based template for email
         notification on creation.

   Default
         EXT:cal/template/notifyOnCreate.tmpl


.. container:: table-row

   Property
         view.event.notify.all.onChangeTemplate

   Data type
         String / Path

   Description
         Event email notification Template: Marker-based template for email
         notification on change.

   Default
         EXT:cal/template/notifyOnChange.tmpl


.. container:: table-row

   Property
         view.event.notify.all.onDeleteTemplate

   Data type
         String / Path

   Description
         Event email notification Template: Marker-based template for email
         notification on delete.

   Default
         EXT:cal/template/notifyOnDelete.tmpl


.. container:: table-row

   Property
         view.event.remind.all.template

   Data type
         String / Path

   Description
         Event email reminder Template: Marker-based template for email
         reminder.

   Default
         EXT:cal/template/remind.tmpl


.. container:: table-row

   Property
         view.event.subscriptionManagerTemplate

   Data type
         String / Path

   Description
         Subscription Manager Template: Marker-based template for subscription
         manager view.

   Default
         EXT:cal/template/subscription\_manager.tmpl


.. container:: table-row

   Property
         view.event.notify.confirmTemplate

   Data type
         String / Path

   Description
         Confirm Subscription Template: Marker-based template to confirm a
         subscription.

   Default
         EXT:cal/template/notifyConfirm.tmpl


.. container:: table-row

   Property
         view.event.notify.unsubscribeConfirmTemplate

   Data type
         String / Path

   Description
         Confirm Unsubscription Template: Marker-based template to confirm a
         unsubscription.

   Default
         EXT:cal/template/notifyUnsubscribeConfirm.tmpl


.. container:: table-row

   Property
         view.event.meeting.template

   Data type
         String / Path

   Description
         Confirm Unsubscription Template: Marker-based template for meeting
         invitation (email).

   Default
         EXT:cal/template/invite.tmpl


.. container:: table-row

   Property
         view.event.meeting.onChangeTemplate

   Data type
         String / Path

   Description
         Confirm Unsubscription Template: Marker-based template for meeting
         invitation updates (email).

   Default
         EXT:cal/template/inviteOnChange.tmpl


.. container:: table-row

   Property
         view.event.meeting.managerTemplate

   Data type
         String / Path

   Description
         Meeting Manager Template: Marker-based template for meeting manager
         view.

   Default
         EXT:cal/template/meetingManager.tmpl


.. container:: table-row

   Property
         view.location.locationTemplate

   Data type
         String / Path

   Description
         Location-wrapper-Template: Marker-based template for location view.

   Default
         EXT:cal/template/location.tmpl


.. container:: table-row

   Property
         view.location.locationTemplate4Partner

   Data type
         String / Path

   Description
         Location (Partner) Template: Marker-based template for location view
         using the partner framework.

   Default
         EXT:cal/template/location\_partner.tmpl


.. container:: table-row

   Property
         view.location.locationTemplate4Address

   Data type
         String / Path

   Description
         Location (Address) Template: Marker-based template for location view
         using tt\_address.

   Default
         EXT:cal/template/location\_address.tmpl


.. container:: table-row

   Property
         view.location.createLocationTemplate

   Data type
         String / Path

   Description
         Location (Create) Template: Marker-based template for location
         creation view.

   Default
         EXT:cal/template/create\_location.tmpl


.. container:: table-row

   Property
         view.location.confirmLocationTemplate

   Data type
         String / Path

   Description
         Location (Confirm) Template: Marker-based template for location
         confirmation view.

   Default
         EXT:cal/template/confirm\_location.tmpl


.. container:: table-row

   Property
         view.location.deleteLocationTemplate

   Data type
         String / Path

   Description
         Location (Delete) Template: Marker-based template for location
         deletion view.

   Default
         EXT:cal/template/delete\_location.tmpl


.. container:: table-row

   Property
         view.organizer.organizerTemplate

   Data type
         String / Path

   Description
         Organizer-wrapper-Template: Marker-based template for organizer view.

   Default
         EXT:cal/template/organizer.tmpl


.. container:: table-row

   Property
         view.organizer.organizerTemplate4Partner

   Data type
         String / Path

   Description
         Organizer (Partner) Template: Marker-based template for organizer view
         using the partner framework.

   Default
         EXT:cal/template/organizer\_partner.tmpl


.. container:: table-row

   Property
         view.organizer.organizerTemplate4Address

   Data type
         String / Path

   Description
         Organizer (Address) Template: Marker-based template for organizer view
         using tt\_address.

   Default
         EXT:cal/template/organizer\_address.tmpl


.. container:: table-row

   Property
         view.organizer.organizerTemplate4FEUser

   Data type
         String / Path

   Description
         Organizer (Frontend User) Template: Marker-based template for
         organizer using frontend users.

   Default
         EXT:cal/template/organizer\_feuser.tmpl


.. container:: table-row

   Property
         view.list.listTemplate

   Data type
         String / Path

   Description
         List View Template: Marker-based template for list view.

   Default
         EXT:cal/template/list.tmpl


.. container:: table-row

   Property
         view.list.listWithTeaserTemplate

   Data type
         String / Path

   Description
         List View Template: Marker-based template for list view with teaser
         enabled.

   Default
         EXT:cal/template/list\_w\_teaser.tmpl


.. container:: table-row

   Property
         module.locationloader.template

   Data type
         String / Path

   Description
         Locationloader Template: Marker-based template to add location
         information into the event view.

   Default
         EXT:cal/template/module\_locationloader.tmpl


.. container:: table-row

   Property
         view.admin.adminTemplate

   Data type
         String / Path

   Description
         Admin View Template: Marker-based template for admin view.

   Default
         EXT:cal/template/admin.tmpl


.. container:: table-row

   Property
         view.other.sidebarTemplate

   Data type
         String / Path

   Description
         Sidebar Template: Marker-based template for the sidebar.

   Default
         EXT:cal/template/sidebar.tmpl


.. container:: table-row

   Property
         view.other.searchBoxTemplate

   Data type
         String / Path

   Description
         Search Box Template: Marker-based template for the search box.

   Default
         EXT:cal/template/search\_box.tmpl


.. container:: table-row

   Property
         view.search.searchResultAllTemplate

   Data type
         String / Path

   Description
         Search (All) Template: Marker-based template for full search.

   Default
         EXT:cal/template/search\_all.tmpl


.. container:: table-row

   Property
         view.search.searchResultEventTemplate

   Data type
         String / Path

   Description
         Search (Event) Template: Marker-based template for event search.

   Default
         EXT:cal/template/search\_event.tmpl


.. container:: table-row

   Property
         view.search.searchResultLocationTemplate

   Data type
         String / Path

   Description
         Search (Location) Template: Marker-based template for location search.

   Default
         EXT:cal/template/search\_location.tmpl


.. container:: table-row

   Property
         view.search.searchResultOrganizerTemplate

   Data type
         String / Path

   Description
         Search (Organizer) Template: Marker-based template for organizer
         search.

   Default
         EXT:cal/template/search\_organizer.tmpl


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller]

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

   Default
         1


.. container:: table-row

   Property
         icsTemplate

   Data type
         String / Path

   Description
         Template for ICS view.

   Default
         EXT:cal/template/ics.tmpl


.. container:: table-row

   Property
         icsListTemplate

   Data type
         String / Path

   Description
         Template for ICS list view.

   Default
         EXT:cal/template/icslist.tmpl


.. container:: table-row

   Property
         eventUidPrefix

   Data type
         String

   Description
         Prefix used with event UID to create a GUID. Site URL is a good choice
         here to ensure uniqueness.

   Default
         www.mysite.com


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.view.ics]

plugin.tx\_cal\_controller.rss

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
         eventViewPID

   Data type
         Integer / PID

   Description
         Event Page ID: Frontend page for viewing single events from RSS feed.

   Default


.. container:: table-row

   Property
         range

   Data type
         Integer

   Description
         Event Range: Include events from now through this many days in the
         future.

   Default
         10


.. container:: table-row

   Property
         rss2\_tmplFile

   Data type
         String / Path

   Description
         RSS-News rss v2 Template File: XML template for RSS 2.0 feed

   Default
         EXT:cal/template/rss\_2.tmpl


.. container:: table-row

   Property
         rss091\_tmplFile

   Data type
         String / Path

   Description
         RSS-News rss v0.91 Template File: XML template for RSS 0.91 feed.

   Default
         EXT:cal/template/rss\_0\_91.tmpl


.. container:: table-row

   Property
         rdf\_tmplFile

   Data type
         String / Path

   Description
         RDF-News RDF Template File: XML template for RDF feed.

   Default
         EXT:cal/template/rdf.tmpl


.. container:: table-row

   Property
         atom03\_tmplFile

   Data type
         String / Path

   Description
         Atom-News Atom v0.3 Template File: XML template for Atom 0.3 feed.

   Default
         EXT:cal/template/atom\_0\_3.tmpl


.. container:: table-row

   Property
         atom1\_tmplFile

   Data type
         String / Path

   Description
         Atom-News Atom v1.0 Template File: XML template for Atom 1.0 feed.

   Default
         EXT:cal/template/atom\_1\_0.tmpl


.. container:: table-row

   Property
         xmlFormat

   Data type
         String

   Description
         News-Feed XML-Format: Defines the format of the news feed.

         Possible values are: 'rss091', 'rss2' 'rdf', 'atom1' and 'atom03'

   Default
         rss2


.. container:: table-row

   Property
         xmlTitle

   Data type
         String

   Description
         Event-Feed XML-Title: The title of your news feed. (required for
         rss091, rss2, rdf and atom03)

   Default
         your-server.org: Latest Events


.. container:: table-row

   Property
         xmlLink

   Data type
         String / URL

   Description
         Event-Feed XML-Link: The link to your hompage. (required for rss091,
         rss2, rdf and atom03)

   Default
         http://your-server.org/


.. container:: table-row

   Property
         xmlDesc

   Data type
         String

   Description
         Event-Feed XML-Description: The description of your news feed.
         (required for rss091, rss2 and rdf. optional for atom03)

   Default
         Latest events


.. container:: table-row

   Property
         xmlLang

   Data type
         String

   Description
         Event-Feed XML-Language: Your site's language. A list of allowable
         values for <language> in RSS is available at
         http://backend.userland.com/stories/storyReader$16 (equired for
         rss091, optional for rss2, not available for rdf, recommended for
         atom03)

   Default
         en


.. container:: table-row

   Property
         xmlIcon

   Data type
         String / Path

   Description
         Event-Feed XML-Icon: Provide an icon for your news feed with preferred
         size of 16x16 px, can be gif, jpeg or png. (required for rss091,
         optional for rss2 and rdf, not available for atom03)

   Default
         EXT:cal/ext\_icon.gif


.. container:: table-row

   Property
         xmlLimit

   Data type
         Integer

   Description
         Event-Feed XML-Limit: max events items in RSS feeds.

   Default
         10


.. container:: table-row

   Property
         xmlCaching

   Data type
         Boolean

   Description
         Event-Feed XML-Caching: Allow caching for the RSS feed

   Default
         1


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_cal\_controller.rss]


