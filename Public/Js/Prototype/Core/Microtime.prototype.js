_('prototype').microtime = function (get_as_float){
    var now = new Date().getTime() / 1000;
    var s = parseInt(now, 10);
    return (get_as_float) ? now : (Math.round((now - s) * 1000) / 1000) + ' ' + s;
}

priya.microtime = _('prototype').microtime;