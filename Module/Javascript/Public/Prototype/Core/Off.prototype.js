_('prototype').off = function (event, action){
    console.log(this['Priya']['eventListener']); //need to remove it from the eventListener

    if(typeof event == 'object'){
        var index;
        for (index=0; index < event.length; index++){
            this.off(event[index], action);
        }
    } else {
    	if(this.is_nodeList(this)){
    		var index;
    		for(index=0; index < this.length; index++){
    			var node = this[index];
    			node.removeEventListener(event, action);
    		}
    	} else {
    		this.removeEventListener(event, action);
    	}

    }
    return this;
}

priya.off = _('prototype').off;