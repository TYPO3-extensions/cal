var eventTime = '0700';
var eventDate = '20090617';
var eventUid = 0;
var eventType = 'tx_cal_phpicalendar';	
var dialogWindow = '';
var dialogWindowTitle = '';
var formPanel;
var event_start_day = '';
var event_end_day = '';
var cal_event_title = '';

var EventDialog = function(){
    // everything in this space is private and only accessible in the HelloWorld block
    
    // define some private variables
    var dialog;
    var showBtn;
    var loaded = false;
    
    // return a public interface
    return {
        init : function(){
	    	if(!dialog){
	        	formPanel = new Ext.FormPanel({
		       		url:'index.php',
		       		monitorValid:true,
		       		//standardSubmit:true,
		       		buttons: [{
	                   text: save_label,
	                   formBind: true,
	                   handler: function(){
		       				if(formPanel.getForm().isValid()){
		       					dialog.hide();
		       					//alert('valid');
		       					// alert(formPanel.getForm().items.get(0));
				       			formPanel.getForm().submit({
				       					success:function(form, action){
				       						if(eventUid>0){
				       							var eventId = 'cal_event_'+eventUid;
				       							eventArray[eventId] = null;
				       	    					dragZones[eventId] = null;
				       	    					var eventNode = Ext.get(eventId);
				       	    					eventNode.dom.parentNode.removeChild(eventNode.dom);
				       						}
				       						events = new Array();
				       						eventUid = 0;
				       						eval(action.result.data);
				       					}
				       					
				       				});
		       				}
	                   }},{
	                   text: cancel_label,
	                   handler: function(){
	            	   		dialog.hide();
	                   }
	               }],
		       		items: new Ext.TabPanel({
		                   el: 'my-tabs',
		                   autoTabs: true,
		                   activeTab: 0,
		                   deferredRender: false,
		                   border: false
		               }),
		       });
	           dialog = new Ext.Window({
	        	   	   title:"",
	                   width:600,
	                   height:330,
	                   shadow:true,
	                   proxyDrag: true,
	                   autoScroll:true,
	                   syncHeightBeforeShow:true,
	                   closable : false,
		               keys: [{
		                   key: 27,  // hide on Esc
		                   fn: function(){
		            	   		dialog.hide();
		                   }
		               }],
		               items: formPanel,
		           });
		           dialog.on('minimize', function(){
		        	   dialog.toggleCollapse();
		       });
		       
	
	          var el = Ext.get("my-tabs");
	
				var mgr = el.getUpdater();
				dialog.addListener('beforeshow',this.updateDialog);
				mgr.addListener('update',this.createForm);
			}
        },
 
        showDialog : function(elem){
        	dialog.show();
        	if(elem && elem.dateValue && loaded && event_start_day){
        		event_start_day.setValue(elem.caldate);
        		event_end_day.setValue(elem.caldate);
        		cal_event_title.setValue('');
        	}
        },
        createForm : function(el, success, response, options){
        	try {
        		if(document.getElementById("create_js")){
        			eval(document.getElementById("create_js").innerHTML);
        		}
        		dialog.setTitle(dialogWindowTitle);
        	} catch(e){}
        },
        updateDialog : function() {
        	var el = Ext.get("my-tabs");
			var mgr = el.getUpdater();
			if(eventUid>0){
				mgr.update("index.php","no_cache=1&eID=cal_ajax&tx_cal_controller[view]=edit_event&tx_cal_controller[pid]="+pid+"&id="+pid+"&tx_cal_controller[uid]="+eventUid+"&tx_cal_controller[type]="+eventType);
			}else{
				if(!loaded){
					var additionalParams = '';
					if(eventTime>0){
						additionalParams = '&tx_cal_controller[getdate]='+eventDate+"&tx_cal_controller[gettime]="+eventTime;
					}
					mgr.update({
				        url: "index.php",
				        params: {
				            "no_cache": 1,
				            "eID": "cal_ajax",
				            "tx_cal_controller[view]": "create_event",
				            "tx_cal_controller[pid]": pid,
				            "id": pid,
				            "tx_cal_controller[getdate]": eventDate,
				            "tx_cal_controller[gettime]": eventTime
				        },
				        script: false,
				        timeout: 10,
				        callback: "this.createForm", 
					});
					loaded = true;
				}
			}
        }
    };
}();
// using onDocumentReady instead of window.onload initializes the application
// when the DOM is ready, without waiting for images and other resources to load
Ext.onReady(EventDialog.init, EventDialog, false);
Ext.onReady(function(){
    Ext.get('mb1').on('click', function(e){
        Ext.MessageBox.confirm(delete_label, delete_confirm_label, showResult);
    });
    function showResult(btn){
        if(btn=='yes'){
        	window.location.href = "?no_cache=1&tx_cal_controller[view]=remove_event&id="+pid+"&tx_cal_controller[lastview]="+document.getElementById('event_lastview').value+"&tx_cal_controller[uid]="+eventUid+"&tx_cal_controller[type]="+eventType;
        }
    };
});

// *******************************************************************************
// *******************************************************************************
	var calendarArray;
	var categoryArray;
	var eventCalendarUid;
	var eventCategoryUids;
	var categoryByCalendar = new Array();
	var categoryByParent = new Array();
	var categoryByUid = new Array();
	
	function renderCalendarSelector(){
		var calSelector = document.getElementById('calendar_selector');
		var newOptions = "";
		for(var key in calendarArray){
			if (isNaN(key)) continue;
			if(calendarArray[key]['uid']!=eventCalendarUid){
				newOptions += '<option value="'+calendarArray[key]['uid']+'">'+calendarArray[key]['title']+'</option>';
			}else{
				newOptions += '<option value="'+calendarArray[key]['uid']+'" selected="selected">'+calendarArray[key]['title']+'</option>';
			}
		}
		calSelector.innerHTML=newOptions;
	}
	
	function renderCategorySelector(){
		var catSelector = document.getElementById('category_selector');
		if(categoryByCalendar.length==0){
			for(var i=0;i<categoryArray.length;i++){
				for(var key in categoryArray[i]){
					for(var j=0; j<categoryArray[i][key].length;j++){
						if(!categoryByCalendar[categoryArray[i][key][j]['calendaruid']]){
							categoryByCalendar[categoryArray[i][key][j]['calendaruid']] = new Array();
						}
						categoryByCalendar[categoryArray[i][key][j]['calendaruid']].push(categoryArray[i][key][j]);
						if(!categoryByParent[categoryArray[i][key][j]['parentuid']]){
							categoryByParent[categoryArray[i][key][j]['parentuid']] = new Array();
						}
						categoryByParent[categoryArray[i][key][j]['parentuid']].push(categoryArray[i][key][j]);
						if(!categoryByUid[categoryArray[i][key][j]['uid']]){
							categoryByUid[categoryArray[i][key][j]['uid']] = new Array();
						}
						categoryByUid[categoryArray[i][key][j]['uid']]=categoryArray[i][key][j];
					}
				}
			}
		}


		var catSelector = document.getElementById('category_selector');
		var calSelector = document.getElementById('calendar_selector');
		var selectedCalendar = calSelector.options[calSelector.selectedIndex].value;
		
		var treeHtml = '';
		for(var key in categoryByCalendar){
			if(isNaN(key) || (key>0 && key!=selectedCalendar)){
				continue;
			}
			var calendarUid = key;
			var calendarTitle;
			for(var i=0;i<calendarArray;i++){
				if(calendarArray[i]['uid']==calendarUid){
					calendarTitle = calendarArray[i]['title'];
				}
			}
			
			for(var i=0;i<categoryByCalendar[calendarUid].length;i++){
				var rootCategory = categoryByUid[categoryByCalendar[calendarUid][i]['uid']];
				if(rootCategory['parentuid'] == 0 || !categoryByUid[rootCategory['parentuid']]){
					treeHtml += '<table class="treelevel0"><tr><td>'+addSubCategory(rootCategory, 0)+'</td></tr></table>';
				}
			}
		}

		catSelector.innerHTML=treeHtml;
	}

	
	function addSubCategory(parentCategory, level){
		level++;
		var treeHtml;
		var selectedCategory = false;
		for(var i = 0; i<eventCategoryUids.length;i++){
			if(eventCategoryUids[i]['uid']==parentCategory['uid']){
				selectedCategory = true;
				break;
			}
		}
		if(selectedCategory){
			treeHtml = '<input type="checkbox" name="tx_cal_controller[category][]" value="'+parentCategory['uid']+'" checked="checked"/><span class="'+parentCategory['headerstyle']+'_bullet '+parentCategory['headerstyle']+'_legend_bullet">&bull;</span><span class="'+parentCategory['headerstyle']+'_text">'+parentCategory['title']+'</span>';
		}else{
			treeHtml = '<input type="checkbox" name="tx_cal_controller[category][]" value="'+parentCategory['uid']+'" /><span class="'+parentCategory['headerstyle']+'_bullet '+parentCategory['headerstyle']+'_legend_bullet">&bull;</span><span class="'+parentCategory['headerstyle']+'_text">'+parentCategory['title']+'</span>';
		}
		
		
		var localCategoryArray = categoryByParent[parentCategory['uid']];
		if(localCategoryArray && localCategoryArray.length>0){
			
			var tempHtml = '<br /><table class="treelevel'+level+'" id="treelevel'+parentCategory['uid']+'">';
			
			treeHtml += tempHtml;
			
			for(var categoryKey in localCategoryArray){
				if (isNaN(categoryKey)) continue;
				treeHtml += '<tr><td>'+addSubCategory(localCategoryArray[categoryKey], level)+'</td></tr>';
				
			}
			
			
			treeHtml += '</table>';
		}	
		return treeHtml;
	}
	
	function alldayChanged(checkbox){
		var state = checkbox.checked;
		document.getElementById('event_start_hour').disabled = state;
		document.getElementById('event_start_minutes').disabled = state;
		document.getElementById('event_end_hour').disabled = state;
		document.getElementById('event_end_minutes').disabled = state;
	}
	
	function isArray(obj) {
	   if (obj.constructor.toString().indexOf("Array") == -1)
	      return false;
	   else
	      return true;
	}
	

function checkFrequency(field){
	var value = field.options[field.selectedIndex].value;
	switch(value){
		case 'day':
			document.getElementById('until').style.display = '';
			document.getElementById('count').style.display = '';
			document.getElementById('interval').style.display = '';
			document.getElementById('byday').style.display = 'none';
			document.getElementById('bymonth').style.display = 'none';
			document.getElementById('bymonthday').style.display = 'none';
			break;
		case 'week':
			document.getElementById('until').style.display = '';
			document.getElementById('count').style.display = '';
			document.getElementById('interval').style.display = '';
			document.getElementById('byday').style.display = '';
			document.getElementById('bymonth').style.display = 'none';
			document.getElementById('bymonthday').style.display = 'none';
			break;
		case 'month':
			document.getElementById('until').style.display = '';
			document.getElementById('count').style.display = '';
			document.getElementById('interval').style.display = '';
			document.getElementById('byday').style.display = '';
			document.getElementById('bymonth').style.display = 'none';
			document.getElementById('bymonthday').style.display = '';
			break;
		case 'year':
			document.getElementById('until').style.display = '';
			document.getElementById('count').style.display = '';
			document.getElementById('interval').style.display = '';
			document.getElementById('byday').style.display = '';
			document.getElementById('bymonth').style.display = '';
			document.getElementById('bymonthday').style.display = '';
			break;
		default:
			document.getElementById('until').style.display = 'none';
			document.getElementById('count').style.display = 'none';
			document.getElementById('interval').style.display = 'none';
			document.getElementById('byday').style.display = 'none';
			document.getElementById('bymonth').style.display = 'none';
			document.getElementById('bymonthday').style.display = 'none';
			break;
	}
}

var viewStart = 0;
var viewEnd = 0;

function loadEvents(){
	Ext.Ajax.request
    (
      {
        url: 'index.php',
        params: {
    	  'no_cache': '1',
    	  'eID': 'cal_ajax',
    	  'tx_cal_controller[view]': 'load_events',
    	  'tx_cal_controller[pid]': pid,
    	  'id': pid,
    	  'tx_cal_controller[start]': viewStart,
    	  'tx_cal_controller[end]': viewEnd,
    	  'tx_cal_controller[targetView]': view,
    	},
        method: 'GET',
        success: function (result, request)
                 {
    				try {
                	   eval (result.responseText);
    				}
    				catch(e){}
                 },
        failure: function (result, request)
                 {
                   alert ('Oh..., Fehler :-(  ' + result.responseText);
                 }
      }
    );
}

function deleteEvent(eventId){
	Ext.Ajax.request
    (
      {
        url: 'index.php',
        params: {
    	  'no_cache': '1',
    	  'eID': 'cal_ajax',
    	  'tx_cal_controller[view]': 'remove_event',
    	  'tx_cal_controller[pid]': pid,
    	  'id': pid,
    	  'tx_cal_controller[uid]': eventArray[eventId].uid,
    	  'tx_cal_controller[type]': 'tx_cal_phpicalendar',
    	},
        method: 'GET',
        success: function (result, request)
                 {
    					eventArray[eventId] = null;
    					dragZones[eventId] = null;
    					var eventNode = Ext.get(eventId);
    					eventNode.dom.parentNode.removeChild(eventNode.dom);
                 },
        failure: function (result, request)
                 {
                   alert ('Oh..., Fehler :-(  ' + result.responseText);
                 }
      }
    );
}

function editEvent(eventId){
	eventUid = eventArray[eventId].uid;
	EventDialog.showDialog(null);
}
var EventView = function(){

    // return a public interface
    return {
        init : function(){
        },
 
        showWindow : function(uid, getdate){
        	eventUid = uid;
        	win = new Ext.Window({
     	   	   title:"Event",
     	   	   height:200,
     	   	   width:300,
                shadow:true,
                proxyDrag: true,
                autoScroll:true,
                syncHeightBeforeShow:true,
                closable : true,
                closeAction:'close',
                buttonAlign:'left',
                modal:true,
	               keys: [{
	                   key: 27,  // hide on Esc
	                   fn: function(){
	            	   win.close();
	                   }
	               }],
	               buttons: [{
	                   icon:'typo3conf/ext/cal/template/img/delete.gif',
	                   minWidth: 20,
	                   handler: function(){
	            	   		win.close();
	            	   		deleteEvent('cal_event_'+eventUid);
	                   }
	               },{
	            	   icon:'typo3conf/ext/cal/template/img/edit.gif',
	                   minWidth: 20,
	                   handler: function(){
	            	   		win.close();
	            	   		editEvent('cal_event_'+eventUid);
	                   }
	               }],
	           });
 		win.on('minimize', function(){
 			win.toggleCollapse();
	       });
        	
        	if(uid>0){
        		win.html = renderEvent(eventArray['cal_event_'+uid]);
        		var eve = eventArray['cal_event_'+uid];
        		if(eventArray['cal_event_'+uid]['isallowedtoedit']=='0'){
        			win.buttons[0].disable();
        		}
        		if(eventArray['cal_event_'+uid]['isallowedtodelete']=='0'){
        			win.buttons[1].disable();
        		}
        		win.show();
        	}
        },
        hide:function(){
        	win.close();
        }
    };
}();

Ext.onReady(EventView.init, EventView, false);

function renderEventForMonth(eventObject){
	var startTime = parseTime(eventObject['start_time']).format('HH:MM');
	return '<span class="'+eventObject['headerstyle']+'_text"><a title="'+eventObject['title']+'" href="javascript:EventView.showWindow('+eventObject['uid']+','+eventObject['start_date']+')">'+startTime+' '+eventObject['title']+'</a></span>';
}
function renderEvent(eventObject){
	var startTime = parseTime(eventObject['start_time']).format('HH:MM');
	var endTime = parseTime(eventObject['start_time']).format('HH:MM');
	var startDate = parseDate(eventObject['start_date']).format('mmmm dd');
	return '<div id="calendar-event"><div>Title: '+eventObject['title']+'</div><div>Start date: '+startDate+'</div><div>Start time: '+startTime+'</div><div>End time: '+endTime+'</div></div>';
}
function parseTime(seconds){
	var myTime = new Date();
	myTime.setTime(seconds*1000);
	return myTime;
}

function parseDate(getdate){
	var year = getdate.slice(0, 4);
	var month = getdate.slice(4, 6);
	month = parseInt(month)-1;
	var day = getdate.slice(6, 8);
	var myTime = new Date(year, month, day);
	return myTime;
}









/*
* Date Format 1.2.3
* (c) 2007-2009 Steven Levithan <stevenlevithan.com>
* MIT license
*
* Includes enhancements by Scott Trenda <scott.trenda.net>
* and Kris Kowal <cixar.com/~kris.kowal/>
*
* Accepts a date, a mask, or a date and a mask.
* Returns a formatted version of the given date.
* The date defaults to the current date/time.
* The mask defaults to dateFormat.masks.default.
*/

var dateFormat = function () {
	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function (val, len) {
			val = String(val);
			len = len || 2;
			while (val.length < len) val = "0" + val;
			return val;
		};

	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if (isNaN(date)) throw SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d:    d,
				dd:   pad(d),
				ddd:  dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m:    m + 1,
				mm:   pad(m + 1),
				mmm:  dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy:   String(y).slice(2),
				yyyy: y,
				h:    H % 12 || 12,
				hh:   pad(H % 12 || 12),
				H:    H,
				HH:   pad(H),
				M:    M,
				MM:   pad(M),
				s:    s,
				ss:   pad(s),
				l:    pad(L, 3),
				L:    pad(L > 99 ? Math.round(L / 10) : L),
				t:    H < 12 ? "a"  : "p",
				tt:   H < 12 ? "am" : "pm",
				T:    H < 12 ? "A"  : "P",
				TT:   H < 12 ? "AM" : "PM",
				Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
				S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace(token, function ($0) {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

//Some common format strings
dateFormat.masks = {
	"default":      "ddd mmm dd yyyy HH:MM:ss",
	shortDate:      "m/d/yy",
	mediumDate:     "mmm d, yyyy",
	longDate:       "mmmm d, yyyy",
	fullDate:       "dddd, mmmm d, yyyy",
	shortTime:      "h:MM TT",
	mediumTime:     "h:MM:ss TT",
	longTime:       "h:MM:ss TT Z",
	isoDate:        "yyyy-mm-dd",
	isoTime:        "HH:MM:ss",
	isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

//Internationalization strings
dateFormat.i18n = {
	dayNames: [
		"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
		"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
	],
	monthNames: [
		"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
		"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
	]
};

//For convenience...
Date.prototype.format = function (mask, utc) {
	return dateFormat(this, mask, utc);
};
