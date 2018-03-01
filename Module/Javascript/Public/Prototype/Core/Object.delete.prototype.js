_('prototype').object_delete = function(attributeList, object, parent, key){
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
            if(this.isset(object[index])){
                return this.object_delete(attribute, object[index], object, index);
            } else {
                return false;
            }
        }
    } else {
        delete parent[key];
        return true;
    }
}

priya.object_delete = _('prototype').object_delete;