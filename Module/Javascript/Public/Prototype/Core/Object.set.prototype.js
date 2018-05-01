_('prototype').object_set = function(attributeList, value, object, result){
    if(typeof result == 'undefined'){
        result = 'child';
    }
    if(typeof result == 'string' && result != 'child'){
        if(result == 'root'){
            result = object;
        } else {
            result = this.object_get(result, object);
        }
    }
    if(typeof attributeList == 'string'){
        attributeList = this.explode_multi(['.', ':', '->'], attributeList);
    }
    if(this.is_array(attributeList)){
        attributeList = this.object_horizontal(attributeList);
    }
    if(!this.empty(attributeList)){
        var index;
        for(index in attributeList){
            var attribute = attributeList[index];
            if(this.isset(object[index]) && typeof object[index] == 'object'){
                if(this.empty(attribute) && typeof value == 'object'){
                    var key;
                    for(key in value){
                        var value_value = value[key];
                        object[index][key] = value_value;
                    }
                    return object[index];
                }
                return this.object_set(attribute, value, object[index], result);
            }
            else if(typeof attribute == 'object'){
                object[index] = new Object();
                return this.object_set(attribute, value, object[index], result);
            } else {
                object[index] = value;
            }
        }
    }
    if(result == 'child'){
        return value;
    }
    return result;
}

priya.object_set = _('prototype').object_set;