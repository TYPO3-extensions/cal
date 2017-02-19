function ExtUrlUI (containerID, storageID, rowClass, rowHTML) {
	this.containerID = containerID;
	this.storageID = storageID;
	this.rowClass = rowClass;
	this.rowHTML = rowHTML;
};

ExtUrlUI.prototype = {
						
	addUrl: function(defaultNote, defaultUrl){
		var container = $("#"+escapeRegExp(this.containerID));
		
		container.append(this.rowHTML);
		
		if(defaultUrl) {
			$("#"+escapeRegExp(this.containerID) + ' input[type="text"]').last().val(defaultUrl);
		}
		if(defaultNote) {
			$("#"+escapeRegExp(this.containerID) + ' input[type="text"]').last().prev().val(defaultNote);
		}
		
		this.save();
	},
	
	removeUrl: function(icon) {
		$(icon).parent().remove();
		this.save();
	},
	
	save: function() {
		storage = $("#"+escapeRegExp(this.storageID));
		storage.val('');
		
		storageNotes = $("#"+escapeRegExp(this.storageID.substr(0,this.storageID.length-1)+"_notes]"));
		storageNotes.val('');
		
		var container = $("#"+escapeRegExp(this.containerID));
		container.find('div.' + this.rowClass).each( function(index, div) {
			$(div).find('input[type="text"]').each( function(index, input) {
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
		initialUrlValue = $("#"+escapeRegExp(this.storageID)).val();
		urlArray = initialUrlValue.split('\n');
		initialNoteValue = $("#"+escapeRegExp(this.storageID.substr(0,this.storageID.length-1)+"_notes]")).val();
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
