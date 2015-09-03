function ExtUrlUI (containerID, storageID, rowClass, rowHTML) {
	this.containerID = containerID;
	this.storageID = storageID;
	this.rowClass = rowClass;
	this.rowHTML = rowHTML;
};

ExtUrlUI.prototype = {
						
	addUrl: function(defaultNote, defaultUrl){
		new Insertion.Bottom(this.containerID, this.rowHTML);
		
		if(defaultUrl) {
			$(this.containerID).getElementsBySelector('input[type="text"]').last().value = defaultUrl;
		}
		if(defaultNote) {
			$(this.containerID).getElementsBySelector('input[type="text"]').last().previous().value = defaultNote;
		}
		
		this.save();
	},
	
	removeUrl: function(icon) {
		$(icon).up().remove();
		this.save();
	},
	
	save: function() {
		storage = $(this.storageID);
		storage.clear();
		
		storageNotes = $(this.storageID.substr(0,this.storageID.length-1)+"_notes]");
		storageNotes.clear();
		
		//@todo  Figure out how to differentiate selector based forms from element based forms
		$(this.containerID).getElementsBySelector('div.' + this.rowClass).each( function(div) {
			div.getElementsBySelector('input[type="text"]').each( function(input) {
				if(input.className=="exturl") {
					if(storage.value) {
						storage.value += '\n';
					}
					storage.value += input.value;
				}
				if(input.className=="exturlnote") {
					if(storageNotes.value) {
						storageNotes.value += '\n';
					}
					storageNotes.value += input.value;
				}
			});
		});
	},
	
	load: function() {
		initialUrlValue = $(this.storageID).value;
		urlArray = initialUrlValue.split('\n');
		initialNoteValue = $(this.storageID.substr(0,this.storageID.length-1)+"_notes]").value;
		noteArray = initialNoteValue.split('\n');
		var obj = this;
		
		for(var i=0; i<urlArray.length; i++){
			obj.addUrl(noteArray[i],urlArray[i]);
		}
	},
	
	storageToHash: function(url) {
		urlValue = url;
		return $H({ url: urlValue });
	}
};

function ExtUrlInstanceUI (containerID, storageID, rowClass, rowHTML) {
	ExtUrlUI.call(this, containerID, storageID, rowClass, rowHTML);
};
ExtUrlInstanceUI.prototype = Object.create(ExtUrlUI.prototype, {
});