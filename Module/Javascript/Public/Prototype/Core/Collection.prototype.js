_('prototype').collection = function (attribute, value){
    if(typeof attribute != 'undefined'){
        if(typeof value != 'undefined'){
            if(attribute == 'delete' || attribute == 'remove'){
                return this.deleteCollection(value);
            } else {
                this.object_delete(attribute, this.collection());
                this.object_set(attribute, value, this.collection());
                return this.object_get(attribute, this.collection());
            }
        } else {
            if(typeof attribute == 'string'){
                return this.object_get(attribute, this.collection());
            } else {
                this.setCollection(attribute);
                return this.getCollection();
            }
        }
    }
    return this.getCollection();
}

_('prototype').getCollection = function (attribute){
    if(typeof attribute == 'undefined'){
        if(typeof this.collect == 'undefined'){
            this.collect = {};
        }
        return this.collect;
    }
    if(this.isset(this.collect[attribute])){
        return this.collect[attribute];
    } else {
        return false;
    }
}

_('prototype').setCollection = function (attribute, value){
    if(typeof attribute == 'object'){
        if(typeof this.collect == 'object'){
            var key;
            for (key in attribute){
                this.collect[key] = attribute[key];
            }
        } else {
            this.collect = attribute;
        }
    } else {
        if(typeof this.collect == 'object'){
            this.collect[attribute] = value;
        } else {
            this.collect = {};
            this.collect[attribute] = value;
        }
    }
    this.collect = collection;
}

_('prototype').deleteCollection = function(attribute){
    return this.object_delete(attribute, this.collect);
}

priya.collection = _('prototype').collection;
priya.setCollection = _('prototype').setCollection;
priya.getCollection = _('prototype').getCollection;
priya.deleteCollection = _('prototype').deleteCollection;
