/**
 * @see https://www.phpied.com/sleep-in-javascript/
 */

_('prototype').usleep = function (msec){
    console.log('USLEEP__________________________________________________' + msec);
    var start = new Date().getTime();
    var current = new Date().getTime() - start;
    while(current < msec) {
        current = new Date().getTime() - start;
    }
}

priya.usleep = _('prototype').usleep;

//_('prototype').expose('priya', 'usleep');