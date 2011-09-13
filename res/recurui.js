var RecurUI = Class.create();
RecurUI.prototype = {
	initialize: function(containerID, storageID, rowClass, rowHTML) {
		this.containerID = containerID;
		this.storageID = storageID;
		this.rowClass = rowClass
		this.rowHTML = rowHTML;
	},
					
	addRecurrence: function(defaultValues) {
		new Insertion.Bottom(this.containerID, this.rowHTML);
		
		container = $(this.containerID);
		
		if(defaultValues) {
			defaultValues.each( function(pair) {
				element = container.getElementsByClassName(pair.key).last();
				element.value = pair.value
			});
		}
		
		this.save();					
	},
	
	setCheckboxes: function(defaultValues) {
		
		container = $(this.containerID);
		rowSelector = '.' + this.rowClass;
		
		if(defaultValues) {
			defaultValues.each( function(pair) {
				container.getElementsBySelector(rowSelector + ' input[value="' + pair.value +'"]').each( function(input) {
					input.checked = "true";
				});
			});
		}
	},
	
	removeRecurrence: function(icon) {
		$(icon).up().remove();
		this.save();
	},
	
	save: function() {
		storage = $(this.storageID);
		storage.clear();
		
		//@todo  Figure out how to differentiate selector based forms from element based forms
		$(this.containerID).getElementsByClassName(this.rowClass).each( function(div) {
			var rowValue = '';
			
			div.getElementsBySelector("select").each( function(select) {
				rowValue += $F(select);
			});
			
			div.getElementsBySelector('input[type="checkbox"]').each( function(input) {
				if($F(input)) {
					rowValue += $F(input);
				}							
			});
			
			if(rowValue) {

				if(storage.value) {
					storage.value += ',';
				}
				storage.value += rowValue;
			}
		});
	},
	
	load: function() {
		initialValue = $(this.storageID).value;
		recurArray = initialValue.split(",");
		var obj = this;
		
		recurArray.each ( function(recur) {
			hash = obj.storageToHash(recur);
			
			if(obj.rowHTML) {
				obj.addRecurrence(hash);
			} else {
				obj.setCheckboxes(hash);
			}
			
		});
	}
}


var ByDayUI = Class.create();
Object.extend(ByDayUI.prototype, RecurUI.prototype);
Object.extend(ByDayUI.prototype, {
	storageToHash: function(recur) {
		
		splitLocation = 0;
		if(recur.length > 2) {
			for (i=1; i<recur.length; i++) {
				var character = recur.charAt(i);
				if(((character < "0") || (character > "9")) && (character != '-')) {
					splitLocation = i;
					break;
				}
			}
		}
		
		var countValue = recur.substr(0, splitLocation);
		var dayValue   = recur.substr(splitLocation, recur.length);
									
		return $H({ count: countValue, day: dayValue });
	}
});

var ByMonthDayUI = Class.create();
Object.extend(ByMonthDayUI.prototype, RecurUI.prototype);
Object.extend(ByMonthDayUI.prototype, {
	storageToHash: function(recur) {
		dayValue = recur;
		return $H({ day: dayValue });
	}
});

var ByMonthUI = Class.create();
Object.extend(ByMonthUI.prototype, RecurUI.prototype);
Object.extend(ByMonthUI.prototype, {
	storageToHash: function(recur) {
		monthValue = recur;
		return $H({ month: monthValue });
	}
});