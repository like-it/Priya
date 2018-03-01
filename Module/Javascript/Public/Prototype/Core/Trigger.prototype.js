priya.trigger = function (trigger, bubble, cancel){
    if(this.empty(bubble)){
        bubble = false;
    } else {
        bubble = true;
    }
    if(this.empty(cancel)){
        cancel = false;
    } else {
        cancel = true;
    }
    var event = new Event(trigger, {
        'bubbles'    : bubble, /* Whether the event will bubble up through the DOM or not */
        'cancelable' : cancel  /* Whether the event may be canceled or not */
    });
    /*event.initEvent(trigger, true, true);*/
    event.synthetic = true;
    if(typeof this.dispatchEvent == 'undefined'){
    	if(priya.is_nodelist(this)){
    		var index;
            for(index=0; index < this.length; index++){
                node = this[index];
                node.dispatchEvent(event, true);                
            }    		
    	} else {
    		console.log('dispatch problem');
            console.log(this);	
    	}       
    } else {
        this.dispatchEvent(event, true);
    }
}