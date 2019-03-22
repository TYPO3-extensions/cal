function RecurUI(containerID, storageID, rowClass, rowHTML){
	this.containerID = containerID;
	this.storageID = storageID;
	this.rowClass = rowClass;
	this.rowHTML = rowHTML;	
};

RecurUI.prototype = {
	
	addRecurrence: function(defaultValues) {
		var container = TYPO3.jQuery("#"+escapeRegExp(this.containerID));
		
		container.append(this.rowHTML);
		
		if(defaultValues) {
			TYPO3.jQuery.each (defaultValues, function(index, pair) {
				var element = container.find('select.' + index).last();
				element.val(pair);
			});
		}
		
		this.save();					
	},
	
	setCheckboxes: function(defaultValues) {
		var container = TYPO3.jQuery("#"+escapeRegExp(this.containerID));
		var rowSelector = '.' + this.rowClass;
		if(defaultValues) {
			TYPO3.jQuery.each (defaultValues, function(index, pair) {
				container.find(rowSelector + ' input[value="' + pair +'"]').each( function(index, input) {
					input.checked = "true";
				});
			});
		}
	},
	
	removeRecurrence: function(icon) {
		TYPO3.jQuery(icon).parent().remove();
		this.save();
	},
	
	save: function() {
		var storage = TYPO3.jQuery("#"+escapeRegExp(this.storageID));
		storage.val('');
		
		//@todo  Figure out how to differentiate selector based forms from element based forms
		var container = TYPO3.jQuery("#"+escapeRegExp(this.containerID));
		container.find('div.' + this.rowClass).each( function(index, div) {
			var rowValue = '';
			
			TYPO3.jQuery(div).find("select").each( function(index, select) {
				rowValue += TYPO3.jQuery(select).val();
			});
			
			TYPO3.jQuery(div).find('input[type="checkbox"]:checked').each( function(index, input) {
				if(TYPO3.jQuery(input).val()) {
					rowValue += TYPO3.jQuery(input).val();
				}							
			});
			
			if(rowValue && rowValue.trim().length > 0 ) {

				if(storage.val()) {
					storage.val(storage.val() + ',');
				}
				storage.val(storage.val() + rowValue);
			}
		});
	},
	
	load: function() {
		var initialValue = TYPO3.jQuery("#"+escapeRegExp(this.storageID)).val();
		var recurArray = initialValue.split(",");
		var obj = this;
		
		TYPO3.jQuery.each (recurArray, function(index, recur) {
			var hash = obj.storageToHash(recur);
			if(obj.rowHTML) {
				obj.addRecurrence(hash);
			} else {
				obj.setCheckboxes(hash);
			}
			
		});
	}
}

function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function ByDayUI(containerID, storageID, rowClass, rowHTML){
	RecurUI.call(this, containerID, storageID, rowClass, rowHTML);
};
ByDayUI.prototype = Object.create(RecurUI.prototype, {
	storageToHash: {
		value: function(recur) {
			var splitLocation = 0;
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
										
			return { count: countValue, day: dayValue };
		}
	}
		
});

function ByMonthDayUI(containerID, storageID, rowClass, rowHTML){
	RecurUI.call(this, containerID, storageID, rowClass, rowHTML);
};
ByMonthDayUI.prototype = Object.create(RecurUI.prototype, {
	storageToHash: {
		value: function(recur) {
			var dayValue = recur;
			return { day: dayValue };
		}
	}
});

function ByMonthUI(containerID, storageID, rowClass, rowHTML){
	RecurUI.call(this, containerID, storageID, rowClass, rowHTML);
};
ByMonthUI.prototype = Object.create(RecurUI.prototype, {
	storageToHash: {
		value: function(recur) {
			var monthValue = recur;
			return { month: monthValue };
		}
	}
});