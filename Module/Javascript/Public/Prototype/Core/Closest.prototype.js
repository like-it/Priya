_('prototype').closest = function(attribute, node, type){
    var parent;
    if(this.function_exists(node)){
        parent = this.parent();
        if(parent === false){
            var priya = this.attach(this.create('element', attribute));
            priya.data('selector', attribute);
            return priya;
        }
        var bool = parent[node](attribute, type);
        if(bool === false){
            parent = parent.closest(attribute, node, type);
        }
        return parent;
    } else {
        if(typeof node == 'undefined'){
            if(typeof this.parent != 'function'){
                var priya = this.attach(this.create('element', attribute));
                priya.data('selector', attribute);
                return priya;
            } else {
                parent = this.parent();
            }

        } else {
            parent = node.parent();
        }
        if(parent === false){
            var priya = this.attach(this.create('element', attribute));
            priya.data('selector', attribute);
            return priya;
        }
        if(this === parent && parent === node){
            parent = this.attach(this.parentNode);
        }
        if(parent.tagName.toLowerCase() == attribute.toLowerCase()){
            parent = this.attach(parent);
            return parent;
        }

        var select = parent.select(attribute);
        if(typeof select == 'object' && select.tagName == 'PRIYA-NODE'){
            delete select;
            select = parent.closest(attribute, parent);
        }
        if(select === false){
            select = parent.closest(attribute, parent);
        }
        return select;
    }
}

priya.closest = _('prototype').closest;