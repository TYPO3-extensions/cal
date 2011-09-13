/**
  * Advanced_Custom Drag & Drop Tutorial
  * by Jozef Sakalos, aka Saki
  * http://extjs.com/learn/Tutorial:Advanced_Custom_Drag_and_Drop_Part_1
  */
 
// reference local blank image
Ext.BLANK_IMAGE_URL = '../extjs/resources/images/default/s.gif';
 
Ext.namespace('CalEvent', 'CalEvent.dd');
 
// create application
CalEvent.dd = function() {
    // do NOT access DOM from here; elements don't exist yet
 
    // public space
    return {
        // public properties, e.g. strings to translate
 
        // public methods
        init: function() {

        },
    };
}(); // end of app
 
CalEvent.dd.MyDragZone = function(el, config) {
    config = config || {};
    Ext.apply(config, {
        ddel: document.createElement('div'),
    });
    //alert(el);
    CalEvent.dd.MyDragZone.superclass.constructor.call(this, el, config);
};
 
Ext.extend(CalEvent.dd.MyDragZone, Ext.dd.DragZone, {
    getDragData: function(e) {
		//alert('getDragData');
        var target = Ext.get(e.getTarget());
        if(target.hasClass('V9')) {
        	return {ddel:this.ddel, item:target};
        }
        return false;
    },
    onInitDrag: function(e) {
    	//alert('onInitDrag');
        this.ddel.innerHTML = this.dragData.item.dom.innerHTML;
        this.ddel.className = this.dragData.item.dom.className;
        this.ddel.style.width = this.dragData.item.getWidth() + "px";
        this.proxy.update(this.ddel);
    },
    getRepairXY: function(e, data) {
    	//alert('getRepairXY');
        data.item.highlight('#e8edff');
        return data.item.getXY();
    }
});

CalEvent.dd.MyDropTarget = function(el, config) {
    CalEvent.dd.MyDropTarget.superclass.constructor.call(this, el, config);
};
Ext.extend(CalEvent.dd.MyDropTarget, Ext.dd.DropTarget, {
    notifyDrop: function(dd, e, data) {
        this.el.removeClass(this.overClass);
        this.el.appendChild(data.item);
        
        if(parseInt(this.day) != parseInt(dd.start_day)){
        	var dateDiff = parseInt(this.day) - parseInt(dd.start_day);
        	var start = parseInt(dd.start_day) + dateDiff;
        	var end = parseInt(dd.end_day) + dateDiff;
        	if(isNaN(start) || isNaN(end)){
        		alert('Fehler');
        		return;
        	}
        	Ext.Ajax.request
            (
              {
                url: 'index.php',
                params: {
            	  'no_cache': '1',
            	  'eID': 'cal_ajax',
            	  'tx_cal_controller[view]': 'save_event',
            	  'tx_cal_controller[pid]': pid,
            	  'id': pid,
            	  'tx_cal_controller[uid]': dd.uid,
            	  'tx_cal_controller[start_date]': start,
            	  'tx_cal_controller[end_date]': end,
            	  'tx_cal_controller[start_time]': dd.start_time,
            	  'tx_cal_controller[end_time]': dd.end_time,
            	  'tx_cal_controller[type]': dd.eventType,
            	  'tx_cal_controller[option]': 'move'
            	},
                method: 'GET',
                success: function (result, request)
                         {
                           //alert (result.responseText);
                         },
                failure: function (result, request)
                         {
                           //alert ('Oh..., Fehler :-(  ' + result.responseText);
                         }
              }
            );
        	
        	eval("dragZones['dragZone"+dd.uid+"'].start_day = '"+start+"'");
        	eval("dragZones['dragZone"+dd.uid+"'].end_day = '"+end+"'");
        }
        return true;
    }
});

Ext.onReady(CalEvent.dd.init, CalEvent.dd);

var events = new Array();
var dragZones = new Array();
var dropTargets = new Array();
var eventArray = new Array();

function addEvents() {
	var dh = Ext.DomHelper;
	for(var i=0; i<events.length; i++){
		if(Ext.get("large_"+events[i]['start_date']) && !eventArray['cal_event_'+events[i]['uid']]){
			eventArray['cal_event_'+events[i]['uid']] = events[i];
			dh.append("large_"+events[i]['start_date'], [{tag: 'div', id: 'cal_event_'+events[i]['uid'], class: 'V9 '+events[i]['bodystyle']+'_container', html: renderEventForMonth(events[i])}]);
		}
	}
	
}
// end of file