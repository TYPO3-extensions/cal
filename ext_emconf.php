<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "cal".
 *
 * Auto generated 01-05-2013 10:23
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Calendar Base',
	'description' => 'A calendar combining all the functions of the existing calendar extensions plus adding some new features. It is based on the ical standard',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.5.4',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_cal/pics,uploads/tx_cal/ics,uploads/tx_cal/media',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Mario Matzulla, Jeff Segars, Franz Koch, Thomas Kowtsch',
	'author_email' => 'mario@matzullas.de, jeff@webempoweredchurch.org, franz.koch@elements-net.de, typo3@thomas-kowtsch.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-6.2.99',
		),
	),
	'_md5_values_when_last_written' => 'a:534:{s:20:"class.ext_update.php";s:4:"1bfe";s:11:"credits.txt";s:4:"69d7";s:16:"ext_autoload.php";s:4:"cf55";s:21:"ext_conf_template.txt";s:4:"2aed";s:12:"ext_icon.gif";s:4:"0532";s:17:"ext_localconf.php";s:4:"5937";s:14:"ext_tables.php";s:4:"7ff3";s:14:"ext_tables.sql";s:4:"2d78";s:13:"locallang.xml";s:4:"d24e";s:16:"locallang_db.xml";s:4:"d1e9";s:17:"locallang_tca.xml";s:4:"01f8";s:7:"tca.php";s:4:"906f";s:43:"Classes/ViewHelpers/TempStoreViewHelper.php";s:4:"2f18";s:21:"controller/ce_wiz.gif";s:4:"0532";s:31:"controller/class.tx_cal_api.php";s:4:"7e34";s:43:"controller/class.tx_cal_base_controller.php";s:4:"9ba7";s:36:"controller/class.tx_cal_calendar.php";s:4:"8af3";s:38:"controller/class.tx_cal_controller.php";s:4:"98f7";s:38:"controller/class.tx_cal_dateParser.php";s:4:"cc7c";s:45:"controller/class.tx_cal_event_linkHandler.php";s:4:"7f83";s:37:"controller/class.tx_cal_functions.php";s:4:"123f";s:43:"controller/class.tx_cal_modelcontroller.php";s:4:"99d7";s:36:"controller/class.tx_cal_registry.php";s:4:"45aa";s:32:"controller/class.tx_cal_tsfe.php";s:4:"931e";s:38:"controller/class.tx_cal_uriHandler.php";s:4:"304c";s:42:"controller/class.tx_cal_viewcontroller.php";s:4:"2995";s:35:"controller/class.tx_cal_wizicon.php";s:4:"102c";s:24:"controller/locallang.xml";s:4:"e469";s:35:"cron/class.tx_cal_calendar_cron.php";s:4:"c856";s:40:"cron/class.tx_cal_calendar_scheduler.php";s:4:"e3c6";s:35:"cron/class.tx_cal_reminder_cron.php";s:4:"274e";s:40:"cron/class.tx_cal_reminder_scheduler.php";s:4:"78c9";s:14:"doc/manual.sxw";s:4:"ee53";s:29:"hooks/class.tx_cal_befunc.php";s:4:"8ae4";s:31:"hooks/class.tx_cal_dateeval.php";s:4:"df01";s:45:"hooks/class.tx_cal_logoff_post_processing.php";s:4:"fa76";s:30:"hooks/class.tx_cal_realurl.php";s:4:"dcf1";s:45:"hooks/class.tx_cal_tceforms_getmainfields.php";s:4:"e4cc";s:44:"hooks/class.tx_cal_tcemain_processcmdmap.php";s:4:"8d4e";s:45:"hooks/class.tx_cal_tcemain_processdatamap.php";s:4:"3484";s:29:"hooks/class.tx_cal_wecmap.php";s:4:"ba1b";s:26:"lib/class.tx_cal_cache.php";s:4:"41a4";s:32:"lib/class.tx_cal_tsparserext.php";s:4:"9542";s:29:"misc/class.module_example.php";s:4:"3830";s:36:"misc/class.module_locationloader.php";s:4:"73aa";s:37:"misc/class.module_organizerloader.php";s:4:"d273";s:16:"misc/locales.php";s:4:"1555";s:24:"misc/realurl_example.txt";s:4:"955c";s:22:"misc/user_htmlCrop.php";s:4:"bfc8";s:42:"mod1/class.tx_cal_recurrence_generator.php";s:4:"031b";s:40:"mod1/class.tx_cal_template_generator.php";s:4:"0ba3";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"3383";s:14:"mod1/index.php";s:4:"7517";s:18:"mod1/locallang.xml";s:4:"cc05";s:22:"mod1/locallang_mod.xml";s:4:"7fd8";s:19:"mod1/moduleicon.gif";s:4:"5b00";s:37:"model/class.tx_cal_abstract_model.php";s:4:"8fad";s:37:"model/class.tx_cal_attendee_model.php";s:4:"f31f";s:33:"model/class.tx_cal_base_model.php";s:4:"07cf";s:37:"model/class.tx_cal_calendar_model.php";s:4:"5bb1";s:37:"model/class.tx_cal_category_model.php";s:4:"455d";s:27:"model/class.tx_cal_date.php";s:4:"896b";s:31:"model/class.tx_cal_location.php";s:4:"4642";s:39:"model/class.tx_cal_location_address.php";s:4:"0b5a";s:37:"model/class.tx_cal_location_model.php";s:4:"bf9a";s:39:"model/class.tx_cal_location_partner.php";s:4:"ed28";s:28:"model/class.tx_cal_model.php";s:4:"8f63";s:32:"model/class.tx_cal_organizer.php";s:4:"c9b2";s:40:"model/class.tx_cal_organizer_address.php";s:4:"b336";s:39:"model/class.tx_cal_organizer_feuser.php";s:4:"515b";s:40:"model/class.tx_cal_organizer_partner.php";s:4:"3d55";s:41:"model/class.tx_cal_phpicalendar_model.php";s:4:"c4c5";s:55:"model/class.tx_cal_phpicalendar_rec_deviation_model.php";s:4:"404c";s:45:"model/class.tx_cal_phpicalendar_rec_model.php";s:4:"04e5";s:33:"model/class.tx_cal_todo_model.php";s:4:"aff6";s:37:"model/class.tx_cal_todo_rec_model.php";s:4:"b382";s:34:"model/class.tx_model_iCalendar.php";s:4:"af1e";s:47:"model/iCalendar/class.tx_iCalendar_daylight.php";s:4:"e055";s:47:"model/iCalendar/class.tx_iCalendar_standard.php";s:4:"22d3";s:45:"model/iCalendar/class.tx_iCalendar_valarm.php";s:4:"e68d";s:44:"model/iCalendar/class.tx_iCalendar_vcard.php";s:4:"5b02";s:45:"model/iCalendar/class.tx_iCalendar_vevent.php";s:4:"52ae";s:48:"model/iCalendar/class.tx_iCalendar_vfreebusy.php";s:4:"0e91";s:47:"model/iCalendar/class.tx_iCalendar_vjournal.php";s:4:"20ad";s:44:"model/iCalendar/class.tx_iCalendar_vnote.php";s:4:"ea40";s:48:"model/iCalendar/class.tx_iCalendar_vtimezone.php";s:4:"09ef";s:44:"model/iCalendar/class.tx_iCalendar_vtodo.php";s:4:"bccf";s:30:"res/class.tx_cal_customtca.php";s:4:"3dac";s:46:"res/class.tx_cal_isCalNotAllowedToBeCached.php";s:4:"685d";s:34:"res/class.tx_cal_itemsProcFunc.php";s:4:"9faa";s:27:"res/class.tx_cal_labels.php";s:4:"8289";s:29:"res/class.tx_cal_treeview.php";s:4:"846b";s:39:"res/class.user_staticinfotables_div.php";s:4:"c326";s:20:"res/flexform1_ds.xml";s:4:"8686";s:18:"res/pearLoader.php";s:4:"abac";s:14:"res/recurui.js";s:4:"606d";s:17:"res/timezones.php";s:4:"bf80";s:10:"res/url.js";s:4:"be3c";s:17:"res/PEAR/Date.php";s:4:"d6b6";s:22:"res/PEAR/Date/Calc.php";s:4:"645b";s:23:"res/PEAR/Date/Human.php";s:4:"8b37";s:22:"res/PEAR/Date/Span.php";s:4:"f46e";s:26:"res/PEAR/Date/TimeZone.php";s:4:"2d1e";s:35:"res/help/locallang_csh_flexform.xml";s:4:"baa6";s:35:"res/help/locallang_csh_txcalcal.xml";s:4:"df57";s:35:"res/help/locallang_csh_txcalcat.xml";s:4:"7baa";s:37:"res/help/locallang_csh_txcalevent.xml";s:4:"fd79";s:46:"res/help/locallang_csh_txcalexceptionevent.xml";s:4:"f303";s:51:"res/help/locallang_csh_txcalexceptioneventgroup.xml";s:4:"5809";s:40:"res/help/locallang_csh_txcallocation.xml";s:4:"47c3";s:41:"res/help/locallang_csh_txcalorganizer.xml";s:4:"e63c";s:34:"res/icons/icon_tx_cal_attendee.gif";s:4:"b7ab";s:34:"res/icons/icon_tx_cal_calendar.gif";s:4:"8aec";s:37:"res/icons/icon_tx_cal_calendar__h.gif";s:4:"f03c";s:41:"res/icons/icon_tx_cal_calendar_exturl.gif";s:4:"69c1";s:44:"res/icons/icon_tx_cal_calendar_exturl__h.gif";s:4:"6941";s:39:"res/icons/icon_tx_cal_calendar_feed.gif";s:4:"2e50";s:38:"res/icons/icon_tx_cal_calendar_ics.gif";s:4:"df51";s:41:"res/icons/icon_tx_cal_calendar_ics__h.gif";s:4:"1045";s:34:"res/icons/icon_tx_cal_category.gif";s:4:"d6c6";s:37:"res/icons/icon_tx_cal_category__h.gif";s:4:"28a8";s:35:"res/icons/icon_tx_cal_deviation.gif";s:4:"7019";s:32:"res/icons/icon_tx_cal_events.gif";s:4:"7019";s:35:"res/icons/icon_tx_cal_events__h.gif";s:4:"0b0e";s:39:"res/icons/icon_tx_cal_events_exturl.gif";s:4:"1fec";s:42:"res/icons/icon_tx_cal_events_exturl__h.gif";s:4:"1e56";s:39:"res/icons/icon_tx_cal_events_intlnk.gif";s:4:"6481";s:42:"res/icons/icon_tx_cal_events_intlnk__h.gif";s:4:"66c8";s:40:"res/icons/icon_tx_cal_events_meeting.gif";s:4:"763a";s:43:"res/icons/icon_tx_cal_events_meeting__h.gif";s:4:"0bf9";s:37:"res/icons/icon_tx_cal_events_todo.gif";s:4:"2e55";s:40:"res/icons/icon_tx_cal_events_todo__h.gif";s:4:"8539";s:41:"res/icons/icon_tx_cal_exception_event.gif";s:4:"35ac";s:44:"res/icons/icon_tx_cal_exception_event__h.gif";s:4:"e69b";s:47:"res/icons/icon_tx_cal_exception_event_group.gif";s:4:"2d30";s:50:"res/icons/icon_tx_cal_exception_event_group__h.gif";s:4:"587b";s:50:"res/icons/icon_tx_cal_fe_user_event_monitor_mm.gif";s:4:"6943";s:34:"res/icons/icon_tx_cal_location.gif";s:4:"ebbd";s:37:"res/icons/icon_tx_cal_location__h.gif";s:4:"6e9c";s:35:"res/icons/icon_tx_cal_organizer.gif";s:4:"e0ba";s:38:"res/icons/icon_tx_cal_organizer__h.gif";s:4:"47e7";s:39:"res/icons/icon_tx_cal_unknown_users.gif";s:4:"4e4a";s:19:"res/icons/Thumbs.db";s:4:"d2ff";s:16:"service/ajax.php";s:4:"e01c";s:41:"service/class.tx_cal_attendee_service.php";s:4:"3ce0";s:37:"service/class.tx_cal_base_service.php";s:4:"d0a5";s:41:"service/class.tx_cal_calendar_service.php";s:4:"e01e";s:41:"service/class.tx_cal_category_service.php";s:4:"8767";s:38:"service/class.tx_cal_event_service.php";s:4:"4659";s:41:"service/class.tx_cal_fnbevent_service.php";s:4:"8224";s:42:"service/class.tx_cal_icalendar_service.php";s:4:"6492";s:49:"service/class.tx_cal_location_address_service.php";s:4:"129d";s:49:"service/class.tx_cal_location_partner_service.php";s:4:"d9ae";s:41:"service/class.tx_cal_location_service.php";s:4:"fcbd";s:44:"service/class.tx_cal_nearbyevent_service.php";s:4:"63ab";s:50:"service/class.tx_cal_organizer_address_service.php";s:4:"5e9d";s:49:"service/class.tx_cal_organizer_feuser_service.php";s:4:"6f96";s:50:"service/class.tx_cal_organizer_partner_service.php";s:4:"ab91";s:42:"service/class.tx_cal_organizer_service.php";s:4:"67d0";s:39:"service/class.tx_cal_rights_service.php";s:4:"84dd";s:37:"service/class.tx_cal_todo_service.php";s:4:"9a97";s:28:"standard_template/admin.tmpl";s:4:"edb6";s:31:"standard_template/atom_0_3.tmpl";s:4:"e4f7";s:31:"standard_template/atom_1_0.tmpl";s:4:"7788";s:35:"standard_template/calendar_nav.tmpl";s:4:"9897";s:39:"standard_template/confirm_calendar.tmpl";s:4:"aa8d";s:39:"standard_template/confirm_category.tmpl";s:4:"e8fb";s:36:"standard_template/confirm_event.tmpl";s:4:"6843";s:39:"standard_template/confirm_location.tmpl";s:4:"8352";s:38:"standard_template/create_calendar.tmpl";s:4:"06a2";s:38:"standard_template/create_category.tmpl";s:4:"bb92";s:35:"standard_template/create_event.tmpl";s:4:"017e";s:40:"standard_template/create_event_ajax.tmpl";s:4:"d043";s:38:"standard_template/create_location.tmpl";s:4:"692a";s:26:"standard_template/day.tmpl";s:4:"eb8f";s:38:"standard_template/delete_calendar.tmpl";s:4:"0faa";s:38:"standard_template/delete_category.tmpl";s:4:"8f0f";s:35:"standard_template/delete_event.tmpl";s:4:"06e3";s:38:"standard_template/delete_location.tmpl";s:4:"9eab";s:28:"standard_template/event.tmpl";s:4:"aa5e";s:34:"standard_template/event_model.tmpl";s:4:"9986";s:33:"standard_template/fe_editing.tmpl";s:4:"fec9";s:26:"standard_template/ics.tmpl";s:4:"65d4";s:30:"standard_template/icslist.tmpl";s:4:"1775";s:29:"standard_template/invite.tmpl";s:4:"ef63";s:37:"standard_template/inviteOnChange.tmpl";s:4:"f739";s:27:"standard_template/list.html";s:4:"c063";s:27:"standard_template/list.tmpl";s:4:"e20c";s:36:"standard_template/list_w_teaser.tmpl";s:4:"51ce";s:31:"standard_template/location.tmpl";s:4:"6683";s:39:"standard_template/location_address.tmpl";s:4:"dc34";s:45:"standard_template/location_address_model.tmpl";s:4:"0ed2";s:37:"standard_template/location_model.tmpl";s:4:"a40f";s:39:"standard_template/location_partner.tmpl";s:4:"eaed";s:37:"standard_template/meetingManager.tmpl";s:4:"c46f";s:44:"standard_template/module_locationloader.tmpl";s:4:"b0c7";s:45:"standard_template/module_organizerloader.tmpl";s:4:"473b";s:28:"standard_template/month.tmpl";s:4:"31e8";s:34:"standard_template/month_large.tmpl";s:4:"df58";s:35:"standard_template/month_medium.tmpl";s:4:"eb13";s:34:"standard_template/month_small.tmpl";s:4:"99c1";s:32:"standard_template/monthMini.tmpl";s:4:"b983";s:29:"standard_template/newDay.tmpl";s:4:"c6c4";s:36:"standard_template/newLargeMonth.tmpl";s:4:"74a6";s:37:"standard_template/newMediumMonth.tmpl";s:4:"2c7a";s:36:"standard_template/newSmallMonth.tmpl";s:4:"0aca";s:30:"standard_template/newWeek.tmpl";s:4:"25c4";s:36:"standard_template/notifyConfirm.tmpl";s:4:"985a";s:37:"standard_template/notifyOnChange.tmpl";s:4:"77a0";s:37:"standard_template/notifyOnCreate.tmpl";s:4:"03d1";s:37:"standard_template/notifyOnDelete.tmpl";s:4:"c1cd";s:47:"standard_template/notifyUnsubscribeConfirm.tmpl";s:4:"c7d0";s:32:"standard_template/organizer.tmpl";s:4:"02c7";s:40:"standard_template/organizer_address.tmpl";s:4:"1f33";s:46:"standard_template/organizer_address_model.tmpl";s:4:"dfb3";s:39:"standard_template/organizer_feuser.tmpl";s:4:"6668";s:38:"standard_template/organizer_model.tmpl";s:4:"7885";s:40:"standard_template/organizer_partner.tmpl";s:4:"b147";s:41:"standard_template/phpicalendar_event.tmpl";s:4:"cb2b";s:26:"standard_template/rdf.tmpl";s:4:"4546";s:29:"standard_template/remind.tmpl";s:4:"517e";s:31:"standard_template/rss_0_91.tmpl";s:4:"2864";s:28:"standard_template/rss_2.tmpl";s:4:"7b22";s:33:"standard_template/search_all.tmpl";s:4:"f9de";s:33:"standard_template/search_box.tmpl";s:4:"6ae7";s:35:"standard_template/search_event.tmpl";s:4:"512e";s:38:"standard_template/search_location.tmpl";s:4:"c5fa";s:33:"standard_template/search_old.tmpl";s:4:"ed53";s:39:"standard_template/search_organizer.tmpl";s:4:"75a3";s:30:"standard_template/sidebar.tmpl";s:4:"4bf2";s:43:"standard_template/subscription_manager.tmpl";s:4:"8e2f";s:40:"standard_template/todo_inline_model.tmpl";s:4:"2a46";s:27:"standard_template/week.tmpl";s:4:"852c";s:27:"standard_template/year.tmpl";s:4:"c90f";s:42:"standard_template/Partials/List/event.html";s:4:"b0dd";s:27:"standard_template/img/0.png";s:4:"c381";s:34:"standard_template/img/ACCEPTED.png";s:4:"c9b5";s:29:"standard_template/img/add.gif";s:4:"9dbe";s:29:"standard_template/img/add.png";s:4:"1988";s:35:"standard_template/img/add_small.png";s:4:"970f";s:34:"standard_template/img/allday_1.gif";s:4:"35c4";s:34:"standard_template/img/allday_2.gif";s:4:"886b";s:34:"standard_template/img/allday_3.gif";s:4:"8a90";s:34:"standard_template/img/allday_4.gif";s:4:"bb0d";s:34:"standard_template/img/allday_5.gif";s:4:"a7c2";s:34:"standard_template/img/allday_6.gif";s:4:"3407";s:34:"standard_template/img/allday_7.gif";s:4:"7b18";s:35:"standard_template/img/allday_bg.gif";s:4:"f2c3";s:36:"standard_template/img/allday_dot.gif";s:4:"27b7";s:30:"standard_template/img/back.gif";s:4:"ec99";s:40:"standard_template/img/bg_searchInput.gif";s:4:"0fdf";s:42:"standard_template/img/calendar-icon_bg.png";s:4:"c224";s:40:"standard_template/img/calendar-share.png";s:4:"c5ad";s:32:"standard_template/img/cancel.gif";s:4:"62d3";s:35:"standard_template/img/cancelled.gif";s:4:"fa43";s:31:"standard_template/img/CHAIR.png";s:4:"c8ca";s:37:"standard_template/img/clock-small.png";s:4:"61ff";s:31:"standard_template/img/color.gif";s:4:"66f9";s:35:"standard_template/img/completed.gif";s:4:"6669";s:32:"standard_template/img/config.png";s:4:"16d9";s:41:"standard_template/img/config_calendar.gif";s:4:"d73f";s:35:"standard_template/img/confirmed.gif";s:4:"9c8e";s:37:"standard_template/img/control-180.png";s:4:"e99c";s:33:"standard_template/img/control.png";s:4:"0284";s:32:"standard_template/img/create.gif";s:4:"57e0";s:41:"standard_template/img/create_calendar.gif";s:4:"4179";s:37:"standard_template/img/cross-small.png";s:4:"6ba8";s:32:"standard_template/img/day_on.gif";s:4:"3286";s:35:"standard_template/img/day_title.gif";s:4:"c273";s:33:"standard_template/img/DECLINE.png";s:4:"4249";s:32:"standard_template/img/delete.gif";s:4:"90c6";s:32:"standard_template/img/delete.png";s:4:"c292";s:33:"standard_template/img/details.gif";s:4:"3501";s:40:"standard_template/img/download_arrow.gif";s:4:"5f99";s:30:"standard_template/img/edit.gif";s:4:"e0b9";s:35:"standard_template/img/event_dot.gif";s:4:"3de5";s:29:"standard_template/img/ics.gif";s:4:"48aa";s:35:"standard_template/img/important.gif";s:4:"08a5";s:37:"standard_template/img/left_arrows.gif";s:4:"6a98";s:34:"standard_template/img/left_day.gif";s:4:"abd7";s:33:"standard_template/img/list_on.gif";s:4:"d3d9";s:34:"standard_template/img/month_on.gif";s:4:"81c2";s:36:"standard_template/img/monthdot_1.gif";s:4:"5ac8";s:36:"standard_template/img/monthdot_2.gif";s:4:"f880";s:36:"standard_template/img/monthdot_3.gif";s:4:"669e";s:36:"standard_template/img/monthdot_4.gif";s:4:"f1c7";s:36:"standard_template/img/monthdot_5.gif";s:4:"98bb";s:36:"standard_template/img/monthdot_6.gif";s:4:"efe9";s:36:"standard_template/img/monthdot_7.gif";s:4:"c15b";s:38:"standard_template/img/NEEDS-ACTION.png";s:4:"c381";s:39:"standard_template/img/not_completed.gif";s:4:"be19";s:38:"standard_template/img/pencil-small.png";s:4:"1f0b";s:38:"standard_template/img/phpical-logo.gif";s:4:"63f9";s:36:"standard_template/img/plus-small.png";s:4:"5cc3";s:33:"standard_template/img/printer.gif";s:4:"fc80";s:35:"standard_template/img/recurring.gif";s:4:"c370";s:33:"standard_template/img/refresh.gif";s:4:"3b3b";s:40:"standard_template/img/resultset_last.png";s:4:"1705";s:40:"standard_template/img/resultset_next.png";s:4:"03a3";s:44:"standard_template/img/resultset_previous.png";s:4:"e122";s:38:"standard_template/img/right_arrows.gif";s:4:"114f";s:35:"standard_template/img/right_day.gif";s:4:"7ed7";s:30:"standard_template/img/save.gif";s:4:"c8b4";s:32:"standard_template/img/search.gif";s:4:"7b43";s:34:"standard_template/img/shadow_l.gif";s:4:"3190";s:34:"standard_template/img/shadow_m.gif";s:4:"4606";s:34:"standard_template/img/shadow_r.gif";s:4:"d224";s:33:"standard_template/img/side_bg.gif";s:4:"8a2f";s:35:"standard_template/img/smallicon.gif";s:4:"f3f0";s:32:"standard_template/img/spacer.gif";s:4:"a0db";s:35:"standard_template/img/tentative.gif";s:4:"2c7a";s:30:"standard_template/img/tick.png";s:4:"c9b5";s:33:"standard_template/img/time_bg.gif";s:4:"2171";s:35:"standard_template/img/valid-rss.png";s:4:"3727";s:33:"standard_template/img/week_on.gif";s:4:"64df";s:44:"standard_template/img/wrench-screwdriver.png";s:4:"8bf0";s:33:"standard_template/img/year_on.gif";s:4:"9224";s:36:"standard_template/js/dateformater.js";s:4:"700f";s:34:"standard_template/js/fe-editing.js";s:4:"f900";s:21:"static/ajax/setup.txt";s:4:"bd3f";s:20:"static/css/setup.txt";s:4:"8be3";s:29:"static/css_standard/setup.txt";s:4:"3de3";s:31:"static/fe-editing/constants.txt";s:4:"98b5";s:27:"static/fe-editing/setup.txt";s:4:"d56e";s:24:"static/ics/constants.txt";s:4:"98ca";s:20:"static/ics/setup.txt";s:4:"d1ec";s:29:"static/rss_feed/constants.txt";s:4:"1e0c";s:25:"static/rss_feed/setup.txt";s:4:"08a2";s:23:"static/ts/constants.txt";s:4:"7e1f";s:19:"static/ts/setup.txt";s:4:"60ff";s:32:"static/ts_standard/constants.txt";s:4:"e6dc";s:28:"static/ts_standard/setup.txt";s:4:"db4b";s:19:"template/admin.tmpl";s:4:"1b4e";s:22:"template/atom_0_3.tmpl";s:4:"e4f7";s:22:"template/atom_1_0.tmpl";s:4:"7788";s:26:"template/calendar_nav.tmpl";s:4:"8853";s:30:"template/confirm_calendar.tmpl";s:4:"39db";s:30:"template/confirm_category.tmpl";s:4:"e8fb";s:27:"template/confirm_event.tmpl";s:4:"9166";s:30:"template/confirm_location.tmpl";s:4:"bac9";s:29:"template/create_calendar.tmpl";s:4:"e66b";s:29:"template/create_category.tmpl";s:4:"3b01";s:26:"template/create_event.tmpl";s:4:"7687";s:31:"template/create_event_ajax.tmpl";s:4:"ddff";s:29:"template/create_location.tmpl";s:4:"0d79";s:17:"template/day.tmpl";s:4:"6303";s:29:"template/delete_calendar.tmpl";s:4:"0faa";s:29:"template/delete_category.tmpl";s:4:"8f0f";s:26:"template/delete_event.tmpl";s:4:"706a";s:29:"template/delete_location.tmpl";s:4:"b863";s:19:"template/event.tmpl";s:4:"8197";s:25:"template/event_model.tmpl";s:4:"a9b8";s:24:"template/fe_editing.tmpl";s:4:"4776";s:17:"template/ics.tmpl";s:4:"35fd";s:21:"template/icslist.tmpl";s:4:"1775";s:20:"template/invite.tmpl";s:4:"ef63";s:28:"template/inviteOnChange.tmpl";s:4:"f739";s:18:"template/list.tmpl";s:4:"51ce";s:27:"template/list_w_teaser.tmpl";s:4:"51ce";s:22:"template/location.tmpl";s:4:"6683";s:30:"template/location_address.tmpl";s:4:"dc34";s:36:"template/location_address_model.tmpl";s:4:"9279";s:28:"template/location_model.tmpl";s:4:"7af7";s:30:"template/location_partner.tmpl";s:4:"eaed";s:36:"template/location_partner_model.tmpl";s:4:"8567";s:28:"template/meetingManager.tmpl";s:4:"c46f";s:35:"template/module_locationloader.tmpl";s:4:"473b";s:36:"template/module_organizerloader.tmpl";s:4:"473b";s:19:"template/month.tmpl";s:4:"9e4f";s:24:"template/month_ajax.tmpl";s:4:"29e9";s:25:"template/month_large.tmpl";s:4:"431d";s:26:"template/month_medium.tmpl";s:4:"a5d1";s:25:"template/month_small.tmpl";s:4:"3cc8";s:27:"template/notifyConfirm.tmpl";s:4:"985a";s:28:"template/notifyOnChange.tmpl";s:4:"53e8";s:28:"template/notifyOnCreate.tmpl";s:4:"8b30";s:28:"template/notifyOnDelete.tmpl";s:4:"2b9b";s:38:"template/notifyUnsubscribeConfirm.tmpl";s:4:"0925";s:23:"template/organizer.tmpl";s:4:"1fad";s:31:"template/organizer_address.tmpl";s:4:"1f33";s:37:"template/organizer_address_model.tmpl";s:4:"3d5f";s:30:"template/organizer_feuser.tmpl";s:4:"6668";s:36:"template/organizer_feuser_model.tmpl";s:4:"d61a";s:29:"template/organizer_model.tmpl";s:4:"17f1";s:31:"template/organizer_partner.tmpl";s:4:"b147";s:37:"template/organizer_partner_model.tmpl";s:4:"65de";s:17:"template/rdf.tmpl";s:4:"4546";s:20:"template/remind.tmpl";s:4:"6c40";s:22:"template/rss_0_91.tmpl";s:4:"2864";s:19:"template/rss_2.tmpl";s:4:"7b22";s:24:"template/search_all.tmpl";s:4:"eb9a";s:24:"template/search_box.tmpl";s:4:"98d9";s:26:"template/search_event.tmpl";s:4:"df6a";s:29:"template/search_location.tmpl";s:4:"b5a9";s:24:"template/search_old.tmpl";s:4:"ed53";s:30:"template/search_organizer.tmpl";s:4:"60f1";s:21:"template/sidebar.tmpl";s:4:"d6d6";s:34:"template/subscription_manager.tmpl";s:4:"78f2";s:31:"template/todo_inline_model.tmpl";s:4:"c1a1";s:33:"template/todo_separate_model.tmpl";s:4:"e4c3";s:18:"template/week.tmpl";s:4:"048f";s:18:"template/year.tmpl";s:4:"96c6";s:21:"template/css/ajax.css";s:4:"bfa3";s:18:"template/img/0.gif";s:4:"a0db";s:18:"template/img/0.png";s:4:"c381";s:25:"template/img/ACCEPTED.png";s:4:"c9b5";s:20:"template/img/add.gif";s:4:"9dbe";s:20:"template/img/add.png";s:4:"1988";s:26:"template/img/add_small.png";s:4:"970f";s:25:"template/img/allday_1.gif";s:4:"35c4";s:25:"template/img/allday_2.gif";s:4:"886b";s:25:"template/img/allday_3.gif";s:4:"8a90";s:25:"template/img/allday_4.gif";s:4:"bb0d";s:25:"template/img/allday_5.gif";s:4:"a7c2";s:25:"template/img/allday_6.gif";s:4:"3407";s:25:"template/img/allday_7.gif";s:4:"7b18";s:26:"template/img/allday_bg.gif";s:4:"f2c3";s:27:"template/img/allday_dot.gif";s:4:"27b7";s:21:"template/img/back.gif";s:4:"ec99";s:30:"template/img/blue_gradient.gif";s:4:"532c";s:30:"template/img/blue_gradient.png";s:4:"0dd9";s:23:"template/img/cancel.gif";s:4:"62d3";s:26:"template/img/cancelled.gif";s:4:"28a8";s:26:"template/img/cancelled.png";s:4:"4249";s:22:"template/img/CHAIR.png";s:4:"c8ca";s:20:"template/img/cog.png";s:4:"30a1";s:22:"template/img/color.gif";s:4:"66f9";s:26:"template/img/completed.gif";s:4:"518a";s:23:"template/img/config.png";s:4:"16d9";s:32:"template/img/config_calendar.gif";s:4:"d73f";s:26:"template/img/confirmed.gif";s:4:"9c8e";s:23:"template/img/create.gif";s:4:"57e0";s:32:"template/img/create_calendar.gif";s:4:"4179";s:20:"template/img/day.png";s:4:"0c71";s:23:"template/img/day_on.gif";s:4:"3286";s:26:"template/img/day_title.gif";s:4:"c273";s:24:"template/img/DECLINE.png";s:4:"4249";s:23:"template/img/delete.gif";s:4:"90c6";s:23:"template/img/delete.png";s:4:"c292";s:24:"template/img/details.gif";s:4:"3501";s:31:"template/img/download_arrow.gif";s:4:"5f99";s:21:"template/img/edit.gif";s:4:"e0b9";s:26:"template/img/event_dot.gif";s:4:"3de5";s:24:"template/img/garbage.gif";s:4:"90c6";s:31:"template/img/green_gradient.png";s:4:"4a85";s:30:"template/img/grey_gradient.png";s:4:"33c2";s:20:"template/img/ics.gif";s:4:"48aa";s:26:"template/img/important.gif";s:4:"08a5";s:28:"template/img/in-progress.gif";s:4:"9801";s:28:"template/img/left_arrows.gif";s:4:"6a98";s:25:"template/img/left_day.gif";s:4:"abd7";s:24:"template/img/list_on.gif";s:4:"d3d9";s:25:"template/img/month_on.gif";s:4:"81c2";s:27:"template/img/monthdot_1.gif";s:4:"5ac8";s:27:"template/img/monthdot_2.gif";s:4:"f880";s:27:"template/img/monthdot_3.gif";s:4:"669e";s:27:"template/img/monthdot_4.gif";s:4:"f1c7";s:27:"template/img/monthdot_5.gif";s:4:"98bb";s:27:"template/img/monthdot_6.gif";s:4:"efe9";s:27:"template/img/monthdot_7.gif";s:4:"c15b";s:29:"template/img/needs-action.gif";s:4:"9d96";s:29:"template/img/NEEDS-ACTION.png";s:4:"c381";s:23:"template/img/new_el.gif";s:4:"591c";s:30:"template/img/not_completed.gif";s:4:"be19";s:32:"template/img/orange_gradient.png";s:4:"5020";s:28:"template/img/other_month.png";s:4:"fb03";s:29:"template/img/phpical-logo.gif";s:4:"63f9";s:30:"template/img/pink_gradient.png";s:4:"e612";s:24:"template/img/printer.gif";s:4:"fc80";s:26:"template/img/recurring.gif";s:4:"c370";s:29:"template/img/red_gradient.png";s:4:"ae8a";s:24:"template/img/refresh.gif";s:4:"3b3b";s:29:"template/img/right_arrows.gif";s:4:"114f";s:26:"template/img/right_day.gif";s:4:"7ed7";s:21:"template/img/save.gif";s:4:"c8b4";s:23:"template/img/search.gif";s:4:"7b43";s:25:"template/img/shadow_l.gif";s:4:"3190";s:25:"template/img/shadow_m.gif";s:4:"4606";s:25:"template/img/shadow_r.gif";s:4:"d224";s:24:"template/img/side_bg.gif";s:4:"8a2f";s:26:"template/img/smallicon.gif";s:4:"f3f0";s:23:"template/img/spacer.gif";s:4:"a0db";s:26:"template/img/tentative.gif";s:4:"2c7a";s:21:"template/img/tick.png";s:4:"c9b5";s:24:"template/img/time_bg.gif";s:4:"2171";s:26:"template/img/valid-rss.png";s:4:"3727";s:24:"template/img/week_on.gif";s:4:"64df";s:24:"template/img/year_on.gif";s:4:"9224";s:32:"template/img/yellow_gradient.png";s:4:"ff44";s:23:"template/js/calendar.js";s:4:"0c33";s:27:"template/js/dateformater.js";s:4:"2622";s:17:"template/js/dd.js";s:4:"2191";s:25:"template/js/fe-editing.js";s:4:"9aa4";s:27:"template/js/jquery-1.3.2.js";s:4:"7b7e";s:30:"template/js/jquery.typo3cal.js";s:4:"6f6c";s:24:"template/js/js_helper.js";s:4:"91e8";s:19:"template/js/move.js";s:4:"68fe";s:28:"tests/DayByWeek_testcase.php";s:4:"cb65";s:31:"view/class.tx_cal_adminview.php";s:4:"095e";s:31:"view/class.tx_cal_base_view.php";s:4:"ba7d";s:43:"view/class.tx_cal_confirm_calendar_view.php";s:4:"7f5e";s:43:"view/class.tx_cal_confirm_category_view.php";s:4:"3f50";s:40:"view/class.tx_cal_confirm_event_view.php";s:4:"bdae";s:53:"view/class.tx_cal_confirm_location_organizer_view.php";s:4:"138c";s:42:"view/class.tx_cal_create_calendar_view.php";s:4:"fe9b";s:42:"view/class.tx_cal_create_category_view.php";s:4:"c05d";s:39:"view/class.tx_cal_create_event_view.php";s:4:"0790";s:52:"view/class.tx_cal_create_location_organizer_view.php";s:4:"fb96";s:29:"view/class.tx_cal_dayview.php";s:4:"25e9";s:42:"view/class.tx_cal_delete_calendar_view.php";s:4:"dc4a";s:42:"view/class.tx_cal_delete_category_view.php";s:4:"b09d";s:39:"view/class.tx_cal_delete_event_view.php";s:4:"abbe";s:52:"view/class.tx_cal_delete_location_organizer_view.php";s:4:"fcfb";s:31:"view/class.tx_cal_eventview.php";s:4:"7dd3";s:42:"view/class.tx_cal_fe_editing_base_view.php";s:4:"6263";s:29:"view/class.tx_cal_icsview.php";s:4:"1e2d";s:30:"view/class.tx_cal_listview.php";s:4:"5b20";s:34:"view/class.tx_cal_locationview.php";s:4:"176f";s:42:"view/class.tx_cal_meeting_manager_view.php";s:4:"09c0";s:31:"view/class.tx_cal_monthview.php";s:4:"9b5a";s:33:"view/class.tx_cal_new_dayview.php";s:4:"bc57";s:35:"view/class.tx_cal_new_monthview.php";s:4:"aaf8";s:34:"view/class.tx_cal_new_timeview.php";s:4:"af28";s:34:"view/class.tx_cal_new_weekview.php";s:4:"9f3b";s:39:"view/class.tx_cal_notification_view.php";s:4:"15e0";s:35:"view/class.tx_cal_organizerview.php";s:4:"bdf2";s:35:"view/class.tx_cal_reminder_view.php";s:4:"8afe";s:29:"view/class.tx_cal_rssview.php";s:4:"d086";s:33:"view/class.tx_cal_searchviews.php";s:4:"f01b";s:47:"view/class.tx_cal_subscription_manager_view.php";s:4:"b334";s:30:"view/class.tx_cal_weekview.php";s:4:"1fac";s:30:"view/class.tx_cal_yearview.php";s:4:"a0df";s:31:"xclass/class.ux_wizard_edit.php";s:4:"9c6e";}',
	'suggests' => array(
	),
);

?>