_('prototype').usleep = function (msec){
    console.log('USLEEP__________________________________________________' + msec);
    var start = new Date().getTime();
    var current = new Date().getTime() - start;
    while(current < msec) {
        current = new Date().getTime() - start;
    }
}

if(typeof priya != 'undefined'){
    priya.usleep = _('prototype').usleep;
}