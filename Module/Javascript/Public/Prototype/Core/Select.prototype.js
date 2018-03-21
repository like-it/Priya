_('prototype').select = function(selector){
    if(this.nodeName){
    }
    if(Object.prototype.toString.call(priya) == '[object Function]'){
    } else {
        if(Object.prototype.toString.call(this) == '[object Window]'){
            object = window.priya;
        } else {
            if(Object.prototype.toString.call(this) == '[object HTMLElement]'){
                if(isset(this.nodeName) && this.nodeName == 'PRIYA-NODE'){
                    return false;
                }
            }
            object = this;
        }
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
            console.log(object);
            console.log(selector);
            console.log(call);
            console.log('error, cannot attach ??? with priya.attach(object)');
            var object =  object.attach(call);
            console.log(object);
            return object;
        }
    }
}

priya.select = _('prototype').select;