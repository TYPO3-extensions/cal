jQuery.noConflict();

function typo3CalRenderer() {
	this.renderList = function(eventArray) {
		jQuery('#eventList').html('');
		for(var i = 0; i < eventArray.length; i++){
			jQuery('#eventList').append('<li>'+eventArray[i].title+'   <span onclick="remove('+eventArray[i].uid+');">delete</span></li>');
		}
	}
}

var calRenderer = new typo3CalRenderer();

function typo3Cal() {
	this.events = [];
	this.todos = [];
	
	this.addEvent = function(calEvent) {
		this.events.push(calEvent);
	};
	
	this.getEvents = function() {
		return this.events;
	};
	
	this.getEvent = function(uid) {
		for(var i=0; i < this.events.length; i++){
			if(this.events[i].uid == uid){
				return this.events[i];
			}
		}
	};
	
	this.removeEvent = function(uid) {
		for(var i=0; i < this.events.length; i++){
			if(this.events[i].uid == uid){
				this.events.splice(i,1);
				return;
			}
		}
	};
	
	this.addTodo = function(calTodo) {
		this.todos.push(calTodo);
	};
	
	this.getTodos = function() {
		return this.todos;
	};
	
	this.getTodo = function(uid) {
		for(var i=0; i < this.todos.length; i++){
			if(this.todos[i].uid == uid){
				return this.todos[i];
			}
		}
	};
	
	this.loadEvents = function() {
		this.events.length = 0;
		jQuery.get(
			url,
			{
				id:6,
				eID:"cal_ajax",
				no_cache:1,
				"tx_cal_controller[view]":"load_events",
				"tx_cal_controller[pid]":pid,
				"tx_cal_controller[start]":viewStart,
				"tx_cal_controller[end]":viewEnd,
				"tx_cal_controller[targetView]":"month",
			},
			function(data, textStatus){
				var dataValue = eval(data);
				for (var i=0;i<dataValue.length;i++){
					var e = new calEvent(dataValue[i]);
					cal.addEvent(e);
				}
				calRenderer.renderList(cal.getEvents());
			}
		);
	};
	
	this.saveEvent = function(eventObject) {
		jQuery.get(
			url,
			{
				id:6,
				eID:"cal_ajax",
				no_cache:1,
				"tx_cal_controller[view]":"save_event",
				"tx_cal_controller[pid]":pid,
				"tx_cal_controller[type]":"tx_cal_phpicalendar",
				"tx_cal_controller[title]":eventObject.title,
				"tx_cal_controller[start_date]":eventObject.startObject.format('yyyymmdd'),
				"tx_cal_controller[end_date]":eventObject.endObject.format('yyyymmdd'),
				"tx_cal_controller[start_time]":eventObject.startObject.getHours()*3600 + eventObject.startObject.getMinutes()*60,
				"tx_cal_controller[end_time]":eventObject.endObject.getHours()*3600 + eventObject.endObject.getMinutes()*60,
				"tx_cal_controller[calendar_id]":4,
			},
			function(data, textStatus){
				cal.addEvent(new calEvent(eval(data)));
				calRenderer.renderList(cal.getEvents());
			}
		);
	};
	
	this.updateEvent = function(eventObject) {
		jQuery.get(
			url,
			{
				id:6,
				eID:"cal_ajax",
				no_cache:1,
				"tx_cal_controller[view]":"save_event",
				"tx_cal_controller[pid]":pid,
				"tx_cal_controller[uid]":eventObject.uid,
				"tx_cal_controller[type]":"tx_cal_phpicalendar",
				"tx_cal_controller[title]":eventObject.title,
			},
			function(data, textStatus){
				eventObject.update(eval(data));
			}
		);
	};
	
	this.deleteEvent = function(eventObject) {
		cal.removeEvent(eventObject.uid);
		calRenderer.renderList(cal.getEvents());
		jQuery.get(
			url,
			{
				id:6,
				eID:"cal_ajax",
				no_cache:1,
				"tx_cal_controller[view]":"remove_event",
				"tx_cal_controller[pid]":pid,
				"tx_cal_controller[uid]":eventObject.uid,
				"tx_cal_controller[type]":"tx_cal_phpicalendar",
			},
			function(data, textStatus){
				
			}
		);
	};
};

var cal = new typo3Cal();

function calEvent(props) {
	this.type = 0;
	this.startObject = new Date();
	this.endObject = new Date();
	
	this.initProps = function(props) {
		for (var prop in props){
			for(var p in props[prop]){
				this[p] = props[prop][p];
			}
		}
	}
	
	this.initStart = function() {
		if(this.start_date){
			var year = parseInt(this.start_date.substr(0,4),10);
			var month = parseInt(this.start_date.substr(4,2),10);
			var day = parseInt(this.start_date.substr(6,2),10);
			this.startObject.setFullYear(year);
			this.startObject.setMonth(month-1);
			this.startObject.setDate(day);
		}
		if(this.start_time){
			var minutes = this.start_time%3600;
			var hours = this.start_time/3600;
			this.startObject.setMinutes(minutes);
			this.startObject.setHours(hours);
			this.startObject.setSeconds(0);
			this.startObject.setMilliseconds(0);
		}
	};
	this.initEnd = function() {
		if(this.end_date){
			var year = parseInt(this.end_date.substr(0,4),10);
			var month = parseInt(this.end_date.substr(4,2),10);
			var day = parseInt(this.end_date.substr(6,2),10);
			this.endObject.setFullYear(year);
			this.endObject.setMonth(month-1);
			this.endObject.setDate(day);
		}
		if(this.end_time){
			var minutes = this.end_time%3600;
			var hours = this.end_time/3600;
			this.endObject.setMinutes(minutes);
			this.endObject.setHours(hours);
			this.endObject.setSeconds(0);
			this.endObject.setMilliseconds(0);
		}
	};
	
	this.update = function(props) {
		this.initProps(props);
		this.initStart();
		this.initEnd();
	}
	
	this.update(props);
}

function calTodo() {
	this.type = 4;
}

calTodo.inherits(calEvent);