function ExtUrlUI (containerID, storageID, rowClass, rowHTML) {
	this.containerID = containerID;
	this.storageID = storageID;
	this.rowClass = rowClass;
	this.rowHTML = rowHTML;
};

ExtUrlUI.prototype = {
						
	addUrl: function(defaultNote, defaultUrl){
		var container = TYPO3.jQuery("#"+escapeRegExp(this.containerID));
		
		container.append(this.rowHTML);
		
		if(defaultUrl) {
			TYPO3.jQuery("#"+escapeRegExp(this.containerID) + ' input[type="text"]').last().val(defaultUrl);
		}
		if(defaultNote) {
			TYPO3.jQuery("#"+escapeRegExp(this.containerID) + ' input[type="text"]').last().prev().val(defaultNote);
		}
		
		this.save();
	},
	
	removeUrl: function(icon) {
		TYPO3.jQuery(icon).parent().remove();
		this.save();
	},
	
	save: function() {
		storage = TYPO3.jQuery("#"+escapeRegExp(this.storageID));
		storage.val('');
		
		storageNotes = TYPO3.jQuery("#"+escapeRegExp(this.storageID.substr(0,this.storageID.length-1)+"_notes]"));
		storageNotes.val('');
		
		var container = TYPO3.jQuery("#"+escapeRegExp(this.containerID));
		container.find('div.' + this.rowClass).each( function(index, div) {
			TYPO3.jQuery(div).find('input[type="text"]').each( function(index, input) {
				if(input.className=="exturl") {
					if(storage.value) {
						storage.val(storage.val() + '\n');
					}
					storage.val(storage.val() + input.value);
				}
				if(input.className=="exturlnote") {
					if(storageNotes.val()) {
						storageNotes.val(storageNotes.val() + '\n');
					}
					storageNotes.val(storageNotes.val() + input.value);
				}
			});
		});
	},
	
	load: function() {
		initialUrlValue = TYPO3.jQuery("#"+escapeRegExp(this.storageID)).val();
		urlArray = initialUrlValue.split('\n');
		initialNoteValue = TYPO3.jQuery("#"+escapeRegExp(this.storageID.substr(0,this.storageID.length-1)+"_notes]")).val();
		noteArray = initialNoteValue.split('\n');
		var obj = this;
		
		for(var i=0; i<urlArray.length; i++){
			obj.addUrl(noteArray[i],urlArray[i]);
		}
	},
	
};

function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function ExtUrlInstanceUI (containerID, storageID, rowClass, rowHTML) {
	ExtUrlUI.call(this, containerID, storageID, rowClass, rowHTML);
};
ExtUrlInstanceUI.prototype = Object.create(ExtUrlUI.prototype, {
	storageToHash: function(url) {
		urlValue = url;
		return { url: urlValue };
	}
});
