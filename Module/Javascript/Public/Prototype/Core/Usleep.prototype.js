_('prototype').usleep = function (msec){
    var start = new Date().getTime();
    var current = new Date().getTime() - start;
    while(current < msec) {
        current = new Date().getTime() - start;
    }
}

priya.usleep = _('prototype').usleep;