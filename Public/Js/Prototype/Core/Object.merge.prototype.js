_('prototype').object_merge = function (main, merge){
    var key;
    if (typeof main == 'undefined'){
        main = {};
    }
    for (key in merge){
        var value = merge[key];
        if(typeof main[key] == 'undefined'){
            main[key] = value;
        } else {
            if(typeof value == 'object' && typeof main[key] == 'object'){
                main[key] = this.object_merge(main[key], value);
            } else {
                main[key] = value;
            }
        }
    }
    return main;
}

priya.object_merge = _('prototype').object_merge;