/**
  * Advanced_Custom Drag & Drop Tutorial
  * by Jozef Sakalos, aka Saki
  * http://extjs.com/learn/Tutorial:Advanced_Custom_Drag_and_Drop_Part_1
  */
 
// reference local blank image
Ext.BLANK_IMAGE_URL = '../extjs/resources/images/default/s.gif';
 
Ext.namespace('Tutorial', 'Tutorial.dd');
 
// create application
Tutorial.dd = function() {
    // do NOT access DOM from here; elements don't exist yet
 
    // private variables
    var dragZone1, dragZone2;
 
    // private functions
 
    // public space
    return {
        // public properties, e.g. strings to translate
 
        // public methods
        init: function() {
            dragZone1 = new Tutorial.dd.MyDragZone('dd1-ct', {
                ddGroup: 'group',
                scroll: false
            });
            dragZone2 = new Tutorial.dd.MyDragZone('dd2-ct', {
                ddGroup: 'group',
                scroll: false
            });
            dropTarget1 = new Tutorial.dd.MyDropTarget('dd1-ct', {
                ddGroup: 'group',
                overClass: 'dd-over'
            });
            dropTarget2 = new Tutorial.dd.MyDropTarget('dd2-ct', {
                ddGroup: 'group',
                overClass: 'dd-over'
            });
        }
    };
}(); // end of app
 
Tutorial.dd.MyDragZone = function(el, config) {
    config = config || {};
    Ext.apply(config, {
        ddel: document.createElement('div')
    });
    Tutorial.dd.MyDragZone.superclass.constructor.call(this, el, config);
};
 
Ext.extend(Tutorial.dd.MyDragZone, Ext.dd.DragZone, {
    getDragData: function(e) {
        var target = Ext.get(e.getTarget());
        if(target.hasClass('dd-ct')) {
            return false;
        }
        return {ddel:this.ddel, item:target};
    },
    onInitDrag: function(e) {
        this.ddel.innerHTML = this.dragData.item.dom.innerHTML;
        this.ddel.className = this.dragData.item.dom.className;
        this.ddel.style.width = this.dragData.item.getWidth() + "px";
        this.proxy.update(this.ddel);
    },
    getRepairXY: function(e, data) {
        data.item.highlight('#e8edff');
        return data.item.getXY();
    }
});

Tutorial.dd.MyDropTarget = function(el, config) {
    Tutorial.dd.MyDropTarget.superclass.constructor.call(this, el, config);
};
Ext.extend(Tutorial.dd.MyDropTarget, Ext.dd.DropTarget, {
    notifyDrop: function(dd, e, data) {
        this.el.removeClass(this.overClass);
        this.el.appendChild(data.item);
        return true;
    }
});

Ext.onReady(Tutorial.dd.init, Tutorial.dd);

//private variables
var dragZone1, dragZone2, dropTarget1, dropTarget2;


// end of file