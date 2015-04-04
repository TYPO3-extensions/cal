.. _Rss:

=============
RSS
=============

.. include:: ../../../Includes.txt

RSS is a special view and has its own page:

::

   calRSS = PAGE
   calRSS {
           typeNum = 151
           10 < plugin.tx_cal_controller

calRSS.10

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
         Comma separated list of pids to get the information (records) for the
         calendar from
   
   Default
         {$plugin.tx\_cal\_controller.pidList}


.. container:: table-row

   Property
         recursive
   
   Data type
         Integer +
   
   Description
         Include X levels underneath the defined pids
   
   Default
         1


.. container:: table-row

   Property
         view.allowedViews
   
   Data type
         String / CSV
   
   Description
         The rss view only allows the rss views
   
   Default
         Rss, event


.. container:: table-row

   Property
         view.event.eventViewPid
   
   Data type
         Integer / PID
   
   Description
         Page ID where the single event view is located
   
   Default
         {$plugin.tx\_cal\_controller.view.event.eventViewPid}


.. container:: table-row

   Property
         \_CSS\_DEFAULT\_STYLE
   
   Data type
         String
   
   Description
         Disabling all styles
   
   Default
         \_CSS\_DEFAULT\_STYLE >


.. container:: table-row

   Property
         view
   
   Data type
         Array
   
   Description
         Clearing not needed typoscript objects, to reduce the overall memory
         consumption of the typoscript
   
   Default
         day >
         
         week >
         
         month >
         
         year >
         
         event >
         
         calendar >
         
         category >
         
         list >
         
         freeAndBusy >
         
         other >
         
         search >
         
         search\_event >
         
         search\_location >
         
         search\_organizer >
         
         admin >
         
         location >
         
         organizer >
         
         single\_ics >
         
         ics >
         
         create\_event >
         
         edit\_event >
         
         confirm\_event >
         
         delete\_event >
         
         create\_calendar >
         
         edit\_calendar >
         
         confirm\_calendar >
         
         delete\_calendar >
         
         create\_category >
         
         edit\_category >
         
         confirm\_category >
         
         delete\_category >
         
         create\_location >
         
         edit\_location >
         
         confirm\_location >
         
         delete\_location >
         
         create\_organizer >
         
         edit\_organizer >
         
         confirm\_organizer >
         
         delete\_organizer >
         
         translation >


.. container:: table-row

   Property
         rights
   
   Data type
         Array
   
   Description
         Clearing not needed typoscript objects, to reduce the overall memory
         consumption of the typoscript
   
   Default
         create >
         
         edit >
         
         delete >


.. ###### END~OF~TABLE ######

[tsref:calRSS.10]

calRSS.10.view.event < plugin.tx\_cal\_controller.view.event

calRSS.10.config

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
         disableAllHeaderCode
   
   Data type
         Boolean
   
   Description
         See TSRef
   
   Default
         1


.. container:: table-row

   Property
         additionalHeaders
   
   Data type
         String
   
   Description
         See TSRef
   
   Default
         Content-type:application/xml


.. container:: table-row

   Property
         xhtml\_cleaning
   
   Data type
         Boolean
   
   Description
         See TSRef
   
   Default
         0


.. container:: table-row

   Property
         admPanel
   
   Data type
         Boolean
   
   Description
         See TSRef
   
   Default
         0


.. ###### END~OF~TABLE ######

[tsref:calRSS.10.config]

calRSS.10.view.rss

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
         rss091\_tmplFile
   
   Data type
         String / Path
   
   Description
         RSS-News rss v2 Template File: XML template for RSS 2.0 feed
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.rss091\_tmplFile}


.. container:: table-row

   Property
         rss2\_tmplFile
   
   Data type
         String / Path
   
   Description
         RSS-News rss v0.91 Template File: XML template for RSS 0.91 feed.
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.rss2\_tmplFile}


.. container:: table-row

   Property
         rdf\_tmplFile
   
   Data type
         String / Path
   
   Description
         RDF-News RDF Template File: XML template for RDF feed.
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.rdf\_tmplFile}


.. container:: table-row

   Property
         {$plugin.tx\_cal\_controller.view.rss.rdf\_tmplFile}
   
   Data type
         String / Path
   
   Description
         Atom-News Atom v0.3 Template File: XML template for Atom 0.3 feed.
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.atom03\_tmplFile}


.. container:: table-row

   Property
         atom1\_tmplFile
   
   Data type
         String / Path
   
   Description
         Atom-News Atom v1.0 Template File: XML template for Atom 1.0 feed.
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.atom1\_tmplFile}


.. container:: table-row

   Property
         xmlFormat
   
   Data type
         String
   
   Description
         News-Feed XML-Format: Defines the format of the news feed.
         
         Possible values are: 'rss091', 'rss2' 'rdf', 'atom1' and 'atom03'
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.xmlFormat}


.. container:: table-row

   Property
         xmlTitle
   
   Data type
         String
   
   Description
         Event-Feed XML-Title: The title of your news feed. (required for
         rss091, rss2, rdf and atom03)
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.xmlTitle}


.. container:: table-row

   Property
         xmlLink
   
   Data type
         String / URL
   
   Description
         Event-Feed XML-Link: The link to your hompage. (required for rss091,
         rss2, rdf and atom03)
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.xmlLink}


.. container:: table-row

   Property
         xmlDesc
   
   Data type
         String
   
   Description
         Event-Feed XML-Description: The description of your news feed.
         (required for rss091, rss2 and rdf. optional for atom03)
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.xmlDesc}


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
         {$plugin.tx\_cal\_controller.view.rss.xmlLang}


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
         {$plugin.tx\_cal\_controller.view.rss.xmlIcon}


.. container:: table-row

   Property
         xmlLimit
   
   Data type
         Integer
   
   Description
         Event-Feed XML-Limit: max events items in RSS feeds.
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.xmlLimit}


.. container:: table-row

   Property
         xmlCaching
   
   Data type
         Boolean
   
   Description
         Event-Feed XML-Caching: Allow caching for the RSS feed
   
   Default
         {$plugin.tx\_cal\_controller.view.rss.xmlCaching}


.. container:: table-row

   Property
         xmlLastBuildDate
   
   Data type
         Boolean
   
   Description
         Enables the lastBuildDate tag
   
   Default
         1


.. container:: table-row

   Property
         timeFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Date format
         
         also: Constants
   
   Default
         %I:%M %p


.. container:: table-row

   Property
         dateFormat
   
   Data type
         String / `PEAR Date format <#10.8.Pear%20Date%20format|outline>`_
   
   Description
         Time format
         
         also: Constants
   
   Default
         %Y-%m-%d


.. container:: table-row

   Property
         range
   
   Data type
         Integer
   
   Description
         A value in days, the rss feed should show days ahead
         
         also: Constants
   
   Default
         {$plugin.tx\_cal\_controller.rss.range}


.. ###### END~OF~TABLE ######

[tsref:calRSS.10.view.rss]

calRSS.10.view.rss.event

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
         dataWrap >
         
         htmlSpecialChars = 1
         
         htmlSpecialChars.preserveEntities = 1
   
   Default
         See description


.. container:: table-row

   Property
         startdate
   
   Data type
         cObj
   
   Description
         dataWrap = \|<br/>
   
   Default
         See description


.. container:: table-row

   Property
         starttime
   
   Data type
         cObj
   
   Description
         dataWrap = \|-
   
   Default
         See description


.. container:: table-row

   Property
         endtime
   
   Data type
         cObj
   
   Description
         dataWrap = \|<br/>
   
   Default
         See description


.. container:: table-row

   Property
         description
   
   Data type
         cObj
   
   Description
         crop = 100\|..
         
         dataWrap = \|<br/>
   
   Default
         See description


.. container:: table-row

   Property
         location
   
   Data type
         cObj
   
   Description
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_location}:
         \|</div><br/>
   
   Default
         See description


.. container:: table-row

   Property
         category
   
   Data type
         cObj
   
   Description
         dataWrap = <div>{LLL:EXT:cal/controller/locallang.xml:l\_category}:
         \|</div><br/>
   
   Default
         See description


.. ###### END~OF~TABLE ######

[tsref:calRSS.10.view.rss.event]

