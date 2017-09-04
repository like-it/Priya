_('prototype').closest = function(attribute, node, type){
    console.log('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$CLOSEST');
    console.log(this);
    console.log(node);
    console.log(priya);
    console.log(window.priya);
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
        console.log('#####################################################');
        console.log(this);
        console.log(parent);
        console.log(attribute);
        console.log(node);
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

/*
_('prototype').select = function(selector){
    if(Object.prototype.toString.call(priya) == '[object Function]'){
        var object = this;
    } else {
        console.log(Object.prototype.toString.call(this));
        var object = window.priya;
    }
    if(typeof selector == 'undefined' || selector === null){
        var priya = object.attach(object.create('element', selector));
        priya.data('selector', selector);
        return priya;
    }
    var call = Object.prototype.toString.call(selector);
    if(call === '[object HTMLDocument]'){
        var priya = object.attach(object.create('element', selector));
        priya.data('selector', selector);
        return priya;
    }
    else if(call === '[object HTMLBodyElement]'){
        if(typeof object['Priya'] == 'object'){
            return object;
        } else {
            console.log('error, cannot attach ??? with priya.attach(object)');
        }
    }
    else if(call === '[object String]'){
        if(typeof object.querySelectorAll == 'function'){
            var list = object.find(selector);
        } else {
            var list = document.querySelectorAll(selector);
        }
        var index;
        for (index = 0; index < list.length; index++){
            var node = list[index];
            if(typeof node['Priya'] != 'object'){
                node = object.attach(node);
            }
            list[index] = node;
        }
        if (list.length == 0){
            var priya = object.attach(object.create('element', selector));
            priya.data('selector', selector);
            return priya;
        }
        else if(list.length == 1){
           return node;
       } else {
           return object.attach(list);
       }
    } else {
        if(typeof object['Priya'] == 'object'){
            return object;
        }
        else if(typeof selector['Priya'] == 'object'){
            return selector;
        } else {
            console.log('error, cannot attach ??? with priya.attach(object)');
            return object.attach(call);
        }
    }
}
*/