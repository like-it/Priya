_('prototype').object_get = function(attributeList, object){
//    console.log(attributeList);
//    console.log(object);
    if(this.empty(object)){
        return object;
    }
    if(typeof attributeList == 'string'){
        attributeList = this.explode_multi(['.', ':', '->'], attributeList);
        var key;
        for(key in attributeList){
            if(this.empty(attributeList[key])){
                delete attributeList[key];
            }
        }
    }
    if(this.is_array(attributeList)){
        attributeList = this.object_horizontal(attributeList);
    }
    if(this.empty(attributeList)){
        return object;
    }
    var key;
    for (key in attributeList){
        if(this.empty(key)){
            continue;
        }
        var attribute = attributeList[key];
        if(this.isset(object[key])){
            return this.object_get(attributeList[key], object[key]);
        }
    }
    return null;
}

priya.object_get = _('prototype').object_get;