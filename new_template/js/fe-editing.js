var eventTime = 0;
var eventDate = 0;
var eventUid = 0;
var eventType = '';	

var EventDialog = function(){
    // everything in this space is private and only accessible in the HelloWorld block
    
    // define some private variables
    var dialog;
    var showBtn;
    
    // return a public interface
    return {
        init : function(){
             //showBtn = Ext.get('show-dialog-btn');
             // attach to click event
            // showBtn.on('click', this.showDialog, this);
        },
 
        showDialog : function(elem){
            if(!dialog){
               dialog = new Ext.BasicDialog("event_dlg", { 
                       autoTabs:true,
                       width:600,
                       height:330,
                       shadow:true,
                       proxyDrag: true,
                       autoScroll:true,
                       syncHeightBeforeShow:true
               });
               dialog.addKeyListener(27, dialog.hide, dialog);
               dialog.addButton(save_label, EventDialog.submit, dialog);
               dialog.addButton(cancel_label, dialog.hide, dialog);
               
 
	            var el = Ext.get("cal_window_panel");
				var mgr = el.getUpdateManager();
				this.updateDialog;
				dialog.addListener('beforeshow',this.updateDialog);
				mgr.addListener('update',this.createForm);
			}
			dialog.initTabs();
            dialog.show(elem.dom);
        },
        createForm : function(){
        	eval(document.getElementById('create_js').innerHTML);
        },
        submit : function(){
        	dialog.hide;
        	document.getElementById('tx_cal_controller_create_element').submit();
        },
        updateDialog : function() {
        	var el = Ext.get("cal_window_panel");
			var mgr = el.getUpdateManager();
			if(eventUid>0){
				mgr.update("index.php","no_cache=1&eID=cal_ajax&tx_cal_controller[view]=edit_event&tx_cal_controller[pid]="+pid+"&id="+pid+"&tx_cal_controller[uid]="+eventUid+"&tx_cal_controller[type]="+eventType);
			}else{
				var additionalParams = '';
				if(eventTime>0){
					additionalParams = '&tx_cal_controller[getdate]='+eventDate+"&tx_cal_controller[gettime]="+eventTime;
				}
				mgr.update("index.php","no_cache=1&eID=cal_ajax&tx_cal_controller[view]=create_event&tx_cal_controller[pid]="+pid+"&id="+pid+additionalParams);
			}
        }
    };
}();
// using onDocumentReady instead of window.onload initializes the application
// when the DOM is ready, without waiting for images and other resources to load
Ext.onReady(EventDialog.init, EventDialog, true);
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

//*******************************************************************************
//*******************************************************************************
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
	
	/*hidden = 1/0
	calendar_id
	event_start_day = yyyymmdd
	event_start_time = hhmm
	event_end_day = yyyymmdd
	event_end_time = hhmm
	allday = 1/0
	title
	organizer
	organizer_id
	location
	location_id
	teaser
	description
	frequency_id = 'none','day','week','month','year'
	by_day
	by_monthday
	by_month
	until
	count
	interval
	image
	notify_ids
	single_exception_ids
	group_exception_ids
	shared_user_ids
	category_ids*/
	
	
	
	function isArray(obj) {
	   if (obj.constructor.toString().indexOf("Array") == -1)
	      return false;
	   else
	      return true;
	}
	
//***********************************************************************
//***********************************************************************

// Create user extensions namespace (for ux forms)
Ext.namespace('Ext.ux');
Ext.namespace('Ext.ux.form');
Ext.namespace('Ext.ux.form.Action');

/**
 * A reusable error reader class for XML forms
 *
 * @author  Ing. Ido Sebastiaan Bas van Oostveen
 * @version 1.0
 */
Ext.ux.form.XmlErrorReader = function(){
    Ext.ux.form.XmlErrorReader.superclass.constructor.call(this, {
            record : 'field',
            success: '@success'
        }, [
            'id', 'msg'
        ]
    );
};
Ext.extend(Ext.ux.form.XmlErrorReader, Ext.data.XmlReader);

/**
 * A reusable form reader class for XML forms
 *
 * @author  Ing. Ido Sebastiaan Bas van Oostveen
 * @version 1.0
 */
Ext.ux.form.XmlLoadFormReader = function(){
    Ext.ux.form.XmlLoadFormReader.superclass.constructor.call(this, {
	        record : 'field',
	        success: '@success',
		    url    : 'url'
        }, [
            'id', 'type', 'config'
        ]
    );
};
Ext.extend(Ext.ux.form.XmlLoadFormReader, Ext.data.XmlReader, {
    readRecords : function(doc) {
	var root = doc.documentElement || doc;
	o = Ext.ux.form.XmlLoadFormReader.superclass.readRecords.call(this, doc);
	if(this.meta.url){
	    o.url = Ext.DomQuery.selectValue(this.meta.url, root, true);
	} else {
	    o.url = null;
	}
	return o;
    }
});

/**
 * Load server form definition Action class
 *
 * @author  Ing. Ido Sebastiaan Bas van Oostveen
 * @version 1.0
 */
Ext.ux.form.Action.LoadServerForm = function(form, options){
    Ext.ux.form.Action.LoadServerForm.superclass.constructor.call(this, form, options);
};

Ext.extend(Ext.ux.form.Action.LoadServerForm, Ext.form.Action, {
    type : 'loadForm',
    
    run : function(){
	Ext.lib.Ajax.request(
	    'POST',
	    this.form.formUrl,
	    this.createCallback(),
	    this.getParams());
    },
    
    success : function(response){
   	//here comes marios change:
    var theXml = response.responseXML;
    var configObject = new Object();
    configObject.parentObject = Ext.get(this.options.ct);
   	this.form.generateFormFromXML(theXml, configObject);
   	this.form.generateForm(theXml, this.options.ct);
   	return;
    //end
	var result = this.processResponse(response);
	if(!result.success){
	    this.form.afterAction(this, true);
	    return;
	}
	if(result.data){
	    if (result.url) {
		this.form.url = result.url;
	    }
	    this.form.generateForm(result.data, this.options.ct);
	}
	this.form.afterAction(this, false);
    },
    
    handleResponse : function(response){
	var loadFormReader = new Ext.ux.form.XmlLoadFormReader();
	var rs = loadFormReader.read(response);
	var data = [];
	if (rs.records){
	    for(var i=0, len=rs.records.length; i<len; i++) {
		var r = rs.records[i];
		data[i] = r.data;
	    }
	}
	if(data.length<1){
	    data = null;
	}
	return {
	    success: rs.success,
	    data: data,
	    url: rs.url
	};
    }
});

/**
 * ServerForm class
 *
 * @author  Ing. Ido Sebastiaan Bas van Oostveen
 * @version 1.0
 */
Ext.ux.form.ServerForm = function(config){
    Ext.ux.form.ServerForm.superclass.constructor.call(this, config);
    this.errorReader = new Ext.ux.form.XmlErrorReader();
};

Ext.extend(Ext.ux.form.ServerForm, Ext.form.Form, {
    renderServerForm : function(options){
	if (this.formUrl){
	    //this.serverLoadMask = new Ext.LoadMask(Ext.get(options.ct), {msg: "Loading..."});
	    //this.serverLoadMask.enable();
	    Ext.get(options.ct).mask("Loading...");
	    action = new Ext.ux.form.Action.LoadServerForm(this, options);
	    if(this.fireEvent('beforeaction', this, action) !== false){
		this.beforeAction(action);
		action.run.defer(100, action);
	    }
	}
    },
    
    generateFormFromXML : function (xmlNode, configObject){
//		var tabs = new Ext.TabPanel('tabs')
//	    tabs.addTab('extform', 'ExtJS ServerForm');
//	    tabs.addTab('djangoform', 'Django Form');
//	    tabs.addTab('info', 'More Information');
//	    tabs.activate('extform');
	    
//	    return;
	    
	    /*var submit = form.addButton({
		text: 'Submit',
		handler: function(){
		    form.submit({params: {submit:true}, waitMsg:'Saving Data...'});
		}
	    });
	    var reset = form.addButton({
		text: 'Reset form',
		handler: function(){
		    form.reset();
		}
	    });
	    
	    //form.render('form-ct');
	    form.on({
		actioncomplete: function(form, action) {
		    if(action.type=='submit') {
			Ext.MessageBox.show({
			    title: 'Submit',
			    msg: 'Form submitted successfully',
			    buttons: Ext.MessageBox.OK
			});
		    }
		}
	    })*/
	    
//		return;

		switch(xmlNode.nodeName){
			case "tabpanel":
			case "panelitem":
			case "field":
				configObject.id=null;
				configObject.config=null;
				configObject.type=null;
				configObject.activate=null;
				break;
			case "id":
				configObject.id = xmlNode.childNodes[0].nodeValue;
				break;
			case "config":
				configObject.config = eval('('+xmlNode.childNodes[0].nodeValue+')', {});
				break;
			case "type":
				configObject.type = xmlNode.childNodes[0].nodeValue;
				break;
			case "activate":
				configObject.activate = xmlNode.childNodes[0].nodeValue;
				break;
			case "content":
				configObject.content = eval('('+xmlNode.childNodes[0].nodeValue+')',{});
				break;
		}
		if(configObject.id!=null && configObject.config!=null && configObject.type!=null){
			switch (configObject.type){
				case "Ext.TabPanel":
					configObject.parentObject = new Ext.TabPanel(configObject.id);
					if(configObject.activate){
						configObject.parentObject.activate(configObject.activate);
					}
					configObject.config = null;
					configObject.id = null;
					configObject.activate = null
					break;
				case "Ext.TabPanelItem":
					configObject.parentObject.addTab(configObject.id,configObject.config,configObject.content);
					configObject.content = null;
					configObject.config = null;
					configObject.id = null;
					break;
				case "Ext.form.TextField":
					if (!configObject.config.fieldLabel) {
					    configObject.config['fieldLabel'] = configObject.id;
					}
					if (!configObject.config.name) {
						configObject.config['name'] = configObject.id;
					}
		
					configObject.parentObject = configObject.parentObject.add(eval(configObject.type, Ext.form, configObject.config));
					break;
			}
			configObject.id=null;
			configObject.config=null;
			configObject.type=null;
		}else{
			for(var i=0;i<xmlNode.childNodes.length;i++){
				this.generateFormFromXML(xmlNode.childNodes[i],configObject);
			}
		}
		
		
	},
    
    generateForm : function(dataset, ct){
	//if (dataset){
		/*
	    for(var i=0, len=dataset.length; i<len; i++) {
		var map = dataset[i];
		var key = map['id'];
		var type = eval(map['type'], Ext.form);
		var elk = "id_"+key;
		//var tc = eval("("+map['config']+")");
		var tc = eval('('+map['config']+')', {});
		//alert(tc);
		//tc = {};
		
		if (!tc.fieldLabel) {
		    tc['fieldLabel'] = key;
		}
		if (!tc.name) {
	            tc['name'] = key;
		}
		
		if (tc.vtypeFunc) {
		    var vt = Ext.form.VTypes;
		    var fn = eval("("+tc.vtypeFunc+")");
		    vt[tc.vtype] = fn;
		    if (tc.vtypeText) {
			vt[""+tc.vtype+"Text"] = tc.vtypeText;
		    }
		}
		
		//var field = new tm[0](config);
		//field.applyTo(elk);
		
		this.add(new type(tc));
	    }
	    */
	    this.alreadyGenerated = true;
	    //Ext.form.ServerForm.superclass.render(ct);
	    this.render(ct);
	    Ext.get(ct).unmask();
	//}
    },
    /*
    render : function(ct){
	if (!this.alreadyGenerated) {
	    this.loadServerForm({ct:ct});
	}
    }*/
    
    eojs:function(){}
});

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

