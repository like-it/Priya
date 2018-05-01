_('prototype').object_horizontal = function (verticalArray, value, result){
    if(this.empty(result)){
        result = 'object';
    }
    if(this.empty(verticalArray)){
        return false;
    }
    var object = {};
    var last = verticalArray.pop();
    var key;
    for(key in verticalArray){
        var attribute = verticalArray[key];
        if(typeof deep == 'undefined'){ //isset...
            object[attribute] = {};
            var deep = object[attribute];
        } else {
            deep[attribute] = {};
            deep = deep[attribute];
        }
    }
    if(typeof deep == 'undefined'){
        object[last] = value;
    } else {
        deep[last] = value;
    }
    return object;
}

priya.object_horizontal = _('prototype').object_horizontal;