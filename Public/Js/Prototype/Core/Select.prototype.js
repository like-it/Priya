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
            console.log('error, cannot attach ??? with priya.attach(object)');
            return object.attach(call);
        }
    }
}

priya.select = _('prototype').select;

/*
_('prototype').select = function(selector){
    if(typeof selector == 'undefined' || selector === null){
        return false;
    }
    console.log(this);
    if(typeof this['Priya'] != 'undefined'){
        if(typeof this['Priya']['dom'] != 'undefined'){
            if(typeof this['Priya']['dom']['selected'] == 'undefined'){
                this['Priya']['dom']['selected'] = {};
            }
            var selected;
            if(typeof selector == 'object'){
                selected = selector.tagName;
                if(typeof selected == 'undefined' && selector instanceof HTMLDocument){
                    var priya = this.attach(this.create('element', selector));
                    priya.data('selector', selector);
                    //add to document for retries?
                    return priya;
                }
                selected = selected.toLowerCase();
                if(selector.id){
                    selected += ' #' + selector.id;
                }
                if(selector.className){
                    selected += ' .' + this.str_replace(' ','.',selector.className);
                }
            } else {
                selected = selector;
            }
            if(typeof this['Priya']['dom']['selected'][selected] == 'undefined'){
                var counter = 1;
            } else {
                var counter = this['Priya']['dom']['selected'][selected]++;
            }
            this['Priya']['dom']['selected'][selected] = counter;
        }
    }
    var oldSelector;
    var matchSelector;
    if(typeof selector == 'string'){
        var oldSelector = trim(selector);
        var not = this.explode(':not(', selector);
        if(not.length >= 2){
            var index;
            for(index in not){
                var temp = this.explode(')', not[index]);
                if(temp.length >= 2){
                    var subSelector = temp[0];
                    if(subSelector.substr(0,1) == '#'){
                        subSelector = '[id="' + subSelector.substr(1) + '"]' + ')'; // + implode(')', temp);
                    } else if (subSelector.substr(0,1) == '.'){
                        subSelector = '[class="' + subSelector.substr(1) + '"]' + ')'; // + implode(')', temp);
                    } else {
                        subSelector = temp[0] + ')'; //implode(')', temp);
                    }
                    not[index] = subSelector;
                }
            }
            selector = this.implode(':not(', not);
        }
        matchSelector = this.trim(selector);
        selector = this.trim(selector).split(' ');
    }
    if(Object.prototype.toString.call( selector ) === '[object Array]'){
        if(typeof this.querySelectorAll == 'function' && oldSelector != matchSelector){
            var list = this.find(matchSelector);
//            var list = this.querySelectorAll(matchSelector);
        } else if(oldSelector != matchSelector){
            var list = document.querySelectorAll(matchSelector);
        }
        if(typeof list == 'undefined'){
            list = new Array();
        }
        if(list.length == 0 && selector.length > 1){
            var index;
            for(index = 0; index < selector.length; index++){
                if(typeof select == 'undefined'){
                    var select = this.select(selector[index]);
                    continue;
                }
                var select = select.select(selector[index]);

                if(select.tagName == 'PRIYA-NODE'){
                    select.data('selector', matchSelector);
                    //add to document for retries?
                    return select;
                }
            }
            return select;
        } else {
            selector = selector.join(' ');
        }

    }
    if(typeof list == 'undefined' || (typeof list != 'undefined' && list.length == 0)){
        if(typeof selector == 'object'){
            var list = new Array();
            list.push(selector);
        } else {
            if(typeof this.querySelectorAll == 'function'){
                var list = this.find(selector);
            } else {
                var list = document.querySelectorAll(selector);
            }
        }
    }

    if(list.length == 0){
        var priya = this.attach(this.create('element', selector));
        priya.data('selector', selector);
        //add to document for retries?
        return priya;
    }
    else if(list.length == 1){
        return this.attach(list[0]);
    } else {
        for(item in list){
            list[item] = this.attach(list[item]);
        }
        return this.attach(list);
    }
}
*/