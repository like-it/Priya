/**
 * @author Remco van der Velde
 * @since 2015-02-18
 * @version 1.0
 *
 * @package priya
 * @subpackage js
 * @category core
 *
 * @description
 *
 * @todo
 *
 *
 * @changeLog
 * 1.0
 *  -    all
 */

var priya = function (url){
    this.collect = {};
    this.parent = this;
    this.load = 0;
    this.hit = 0;
    this.collect.url = url;
    this.get(url + 'Priya/Javascript', function(url, data){
        priya.collect.data = {};
        priya.collect.data.toLoad = 1;
        priya.collect.require.toLoad--;
        if(typeof data != 'object'){
            priya.collect.data.loaded = 0;
            console.log('data malformed in get' + url);
        }
        if(data.collect){
            var index;
            for(index in data.collect){
                priya.collect[index] = data.collect[index];
            }
        }
        //require needs this
        priya.expose();
        priya.collect.data.loaded = 1;
        priya.collect.require.loaded--;
        priya.collect.data.file = [];
        priya.collect.data.file.push(url);

        if(data.require.core){
            require(data.require.core, function(){
                priya.expose('window');
                if(data.require.file){
                    require(data.require.file, function(){
                        priya.usleep(200);
                    });
                }
            });
        }
    });
}

/*
priya.prototype.run = function (data){
    var require = this.collection('require');
    if(require.toLoad == require.loaded){
        var element = this.select(data);
        if(element.tagName == 'PRIYA-NODE' || element === false){
            return;
        }
        var request = element.data('request');
        if(!this.empty(request)){
            return element.request(request);
        }
        if(typeof microtime == 'undefined'){
            priya.expose('prototype');
        }
        element.data('mtime', microtime(true));
        return element;
    } else {
        setTimeout(function(){
            priya.run(data);
        }, 1/30);
    }
}
*/

priya.prototype.dom = function (data){
    console.log('deprecated, use select or run');
    return this.init(data);
}

priya.prototype.expose = function (collection, attribute){
    if(typeof collection == 'undefined'){
        var expose = this.collect.expose;
        var index;
        for(index in expose){
            if(index == 'window'){
                continue;
            }
            var name = expose[index];
            window[name] = this[index].bind(window);
        }
    } else {
        if(typeof attribute == 'undefined'){
            if(collection == 'window'){
                var expose = this.collect.expose[collection];
                for(index in expose){
                    var name = expose[index];
                    window[name] = _('prototype')[index].bind(window);
                }
            }
        } else {
            console.log('expose; collection: ' + collection +' attribute: ' + attribute);
        }
    }
}

priya.prototype.namespace = function (namespace) {
    if(typeof namespace == 'undefined' || namespace == '__proto__'){
        priya.debug('undefined namespace');
        return;
    }
    if(Object.prototype.toString.call(priya) == '[object Function]'){
        var object = this;
    } else {
        var object = priya;
    }

    var tokens = namespace.split('.');
    if(tokens.length == 1){
        tokens = namespace.split('-');
    }
    var token;
    while (tokens.length > 0) {
        token = tokens.shift();
        if (typeof object[token] === 'undefined') {
            object[token] = {};
        }
        object = object[token];
    }
    if(Object.prototype.toString.call(priya) == '[object Function]'){
        object = this.attach(object);
    } else {
        object = priya.attach(object);
    }
    return object;
}

priya.prototype.requireElement= function(url, closure){
    var element = document.createElement('script');
    if(Object.prototype.toString.call(priya) == '[object Function]'){
        element = this.attach(element);
    } else {
        element = priya.attach(element);
    }
    element.setAttribute('defer', 'defer');
    element.setAttribute('data-require-once', true);

    element.addEventListener('load', function(event){
         var file = priya.collect.require.file ? priya.collect.require.file : [];
         file.push(this.attribute('src'));
         if(typeof priya.collect.require == 'undefined'){
             priya.collect.require = {};
         }
         priya.collect.require.file = file;
         var loaded = priya.collect.require.loaded ? priya.collect.require.loaded : 0;
         priya.collect.require.loaded = ++loaded;
         if(priya.collect.require.loaded == priya.collect.require.toLoad){
             closure();
         }
    }, false);
    element.src = url;
    document.getElementsByTagName("head")[0].appendChild(element);
    if(Object.prototype.toString.call(priya) == '[object Function]'){
        if(typeof this.collect.require == 'undefined'){
            this.collect.require = {};
        }
        var toLoad = this.collect.require.toLoad ? this.collect.require.toLoad : 0;
        this.collect.require.toLoad = ++toLoad;
    } else {
        if(typeof priya.collect.require == 'undefined'){
            priya.collect.require = {};
        }
        var toLoad = priya.collect.require.toLoad ? priya.collect.require.toLoad : 0;
        priya.collect.require.toLoad = ++toLoad;
    }
}

priya.prototype.require= function(url, closure){
    var script = document.querySelectorAll('script');
    var call = Object.prototype.toString.call(url);
    console.log('__REQUIRE___________________________________________________');
    if(call === '[object Array]'){
        var i;
        for(i=0; i < url.length; i++){
            var item = url[i];
            var found = false;
            var index;
            for(index = 0; index < script.length; index++){
                if(Object.prototype.toString.call(priya) == '[object Function]'){
                    var node = this.attach(script[index]);
                } else {
                    var node = priya.attach(script[index]);
                }
                if(node.getAttribute('data-require-once') == 'true' && node.src == item){
                    found = true;
                    break;
                }
            }
            if(found === false){
                if(Object.prototype.toString.call(priya) == '[object Function]'){
                    this.requireElement(item, closure);
                } else {
                    priya.requireElement(item, closure);
                }
            } else {
                /**
                 * closure goes wrong, should only execute once !
                 */
                if(priya.collect.require.loaded == priya.collect.require.toLoad){
                    closure();
                    return true;

                }
                /*
                console.log('found true: ' + url);
                closure();
                */
            }
        }
        return true;

    } else {
        var item = url;
        var found = false;
        var index;
        for(index = 0; index < script.length; index++){
            if(Object.prototype.toString.call(priya) == '[object Function]'){
                var node = this.attach(script[index]);
            } else {
                var node = priya.attach(script[index]);
            }
            if(node.getAttribute('data-require-once') == 'true' && node.src == item){
                found = true;
            }
        }
        if(found === false){
            if(Object.prototype.toString.call(priya) == '[object Function]'){
                this.requireElement(item, closure);
            } else {
                priya.requireElement(item, closure);
            }
        } else {
            closure();
            return true;
        }
    }
}

priya.prototype.attach = function (element){
    if(element.tagName == 'PRIYA-NODE'){
        console.log(element);
    }

    if(element === null){
        return false;
    }
    if(typeof element != 'object'){
        return false;
    }
    if(typeof element['Priya'] == 'object'){
        return element;
    }
    var dom;
    if(this.isDom === true){
        dom = this;
    }
    else if(typeof this['Priya'] == 'undefined'){
        dom = this;
    }
    else if(typeof this['Priya']['dom'] == 'object'){
        dom = this['Priya']['dom'];
    } else {
        dom = this;
    }
    if(element.tagName == 'PRIYA-NODE'){
        console.log(element);
        console.log(typeof dom);
        console.log(dom);
    }
    for(property in dom){
        if(typeof dom[property] != 'function'){
            continue;
        }
        if(property == 'parentNode'){
            continue;
        }
        if(property == 'closest'){
            console.log('bind closest');
        }
        element[property] = dom[property].bind(element);
    }
    element['parent'] = dom['parentNode'].bind(element);
    element['Priya'] = {
            "version": '0.0.1',
            "dom" : dom
    };
    if(typeof element.tagName != 'undefined'){
        if(typeof this.microtime == 'function' && typeof element.data == 'function'){
            element.data('mtime', this.microtime(true));
        }
    }
    return element;
}

priya.prototype.find = function(selector, attach) {
    if (!this.id) {
        this.id = this.attribute('id', 'priya-find-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999));
        var removeId = true;
    }
    if(typeof selector == 'object'){
        console.log(selector);
        //return;
    }
    selector = '#' + this.id + ' ' + selector;
    var list = document.querySelectorAll(selector);
    if (removeId) {
        this.attribute('remove', 'id');
    }
    if(attach){
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
    return list;
};

priya.prototype.methods = function (){
    var result = {};
    for(property in this){
        if(typeof this[property] != 'function'){
            continue;
        }
        result[property] = this[property];
    }
    return result;
}

priya.prototype.parentNode = function (parent){
    if(typeof parent == 'undefined'){
        if(typeof this['attach'] != 'function'){
            console.log('cannot attach without an atach method');
            console.log(this);
            if(typeof this['methods'] == 'function'){
                console.log('methods:');
                console.log(this.methods());
            }
            return this.parentNode;
        } else {
            return this.select(this.parentNode);
        }
    } else {
        console.log('wanna change parent here');
        return this.parentNode;
    }
}

priya.prototype.active = function (){
    return document.activeElement;
}

priya.prototype.calculate = function (calculate){
    var result = null;
    switch(calculate){
        case 'all':
            //var className = this.className;
            //this.addClass('display-block overflow-auto');
            var rect = this.getBoundingClientRect();
            var result = {};
            result.window = {};
            result.dimension = {};
            result.window.width = window.innerWidth;
            result.window.height = window.innerHeight;
            for (attribute in rect){
                result['dimension'][attribute] = rect[attribute];
            }
            var style = window.getComputedStyle(this);
            result.margin = {
                left: parseInt(style["margin-left"]),
                right: parseInt(style["margin-right"]),
                top: parseInt(style["margin-top"]),
                bottom: parseInt(style["margin-bottom"])
            };
            result.padding = {
                left: parseInt(style["padding-left"]),
                right: parseInt(style["padding-right"]),
                top: parseInt(style["padding-top"]),
                bottom: parseInt(style["padding-bottom"])
            };
            result.border = {
                left: parseInt(style["border-left-width"]),
                right: parseInt(style["border-right-width"]),
                top: parseInt(style["border-top-width"]),
                bottom: parseInt(style["border-bottom-width"])
            };
            result.top = result.dimension.top;
            result.bottom = result.window.height - result.dimension.bottom;
            result.height = result.dimension.height;
            result.width = result.dimension.width;
            result.left = result.dimension.left;
            result.right = result.dimension.right;

            result.offset = {};
            result.offset.parent = this.createSelector(this.offsetParent);
            result.offset.left = this.offsetLeft;
            result.offset.top = this.offsetTop;
            this.data(result);
            return result;
        break;
        case 'offset':
            var result = {};
            result.offset = {};
            result.offset.parent = this.createSelector(this.offsetParent);
            result.offset.left = this.offsetLeft;
            result.offset.top = this.offsetTop;
            this.data(result);
            return result.offset;
        break;
        case 'window-width':
            result =  window.innerWidth;
            return result;
        break;
        case 'window-height':
            result =  window.innerHeight;
            return result;
        break;
        case 'width':
            var className = this.className;
            this.addClass('display-block overflow-auto');
            result =  this.offsetWidth;
            this.className = className;
            return result;
        break;
        case 'height':
            var className = this.className;
            this.addClass('display-block overflow-auto');
            result =  this.offsetHeight;
            this.className = className;
            return result;
        break;
    }
}

priya.prototype.scrollbar = function(attribute, type){
    if(attribute == 'y' || attribute == 'top'){
        return this.data('scrollbar-y', this.scrollTop);
    }
    if(attribute == 'x' || attribute == 'left'){
        return this.data('scrollbar-x', this.scrollLeft);
    }
    var width = this.offsetWidth - this.clientWidth;
    var height = this.offsetHeight - this.clientHeight;
    if(attribute == 'width'){
        return this.data('scrollbar-width', width);
        //other (width without scrollbars)
        //return this.data('scrollbar-width', this.scrollWidth);
    }
    if(attribute == 'height'){
        return this.data('scrollbar-height', height);
        //other (height without scrollbars)
        //return this.data('scrollbar-height', this.scrollHeight);
    }
    if(attribute == 'all'){
        var scrollbar = {
                'y': this.scrollTop,
                'x': this.scrollLeft,
                'width': width,
                'height': height
        };
        return this.data('scrollbar', scrollbar);
    }
    if(attribute == 'to'){
        this.scrollTo(type.x, type.y);
    }
    if(attribute == 'has'){
        if(type && type == 'horizontal'){
            return this.scrollWidth > this.clientWidth;
        }
        else if (type && type == 'vertical'){
            return this.scrollHeight > this.clientHeight;
        } else {
            var hasHorizontalScrollbar = this.scrollWidth > this.clientWidth;
            var hasVerticalScrollbar = this.scrollHeight > this.clientHeight;
            if(hasHorizontalScrollbar || hasVerticalScrollbar){
                return true;
            }
            return false;
        }
    }
}

priya.prototype.createSelector = function(element){
    if(this.empty(element)){
        return '';
    }
    selector = element.tagName;
    if(typeof selector == 'undefined' && element instanceof HTMLDocument){
        /*
        var priya = this.attach(this.create('element', selector));
        priya.data('selector', selector);
        */
        return element;
    }
    selector = selector.toLowerCase();
    if(element.id){
        selector += ' #' + element.id;
    }
    if(element.className){
        selector += ' .' + this.str_replace(' ','.',element.className);
    }
    return selector;
}

priya.prototype.previous = function (node){
    if(typeof node == 'undefined'){
        var parent = this.parent();//.children();
        var index;
        var found;
        var nodeList = parent.childNodes;

        for(index = nodeList.length-1; index > 0; index--){
            var child = parent.childNodes[index];
            if(child.isEqualNode(this)){
                found = true;
                continue;
            }
            if(!empty(found) && child.tagName == this.tagName){
                found = child;
                break;
            }
        }
        if(found !== true && !empty(found)){
            return this.select(found);
        }
    }
}

priya.prototype.next = function (node){
    if(typeof node == 'undefined'){
        var parent = this.parent();//.children();
        var index;
        var found;
        for(index = 0; index < parent.childNodes.length; index++){
            var child = parent.childNodes[index];
            if(child.isEqualNode(this)){
                found = true;
                continue;
            }
            if(!empty(found) && child.tagName == this.tagName){
                found = child;
                break;
            }
        }
        if(found !== true && !empty(found)){
            return this.select(found);
        }
    }
}

priya.prototype.children = function (index){
    var children;
    if(typeof index == 'undefined'){
        children = this.childNodes;
        var count;
        for(count=0; count < children.length; count++){
            children[count] = this.attach(children[count]);
        }
        return children;
    } else {
        if(index == 'first' || index == ':first'){
            return this.attach(this.childNodes[0]);
        }
        else if(index == 'last' || index == ':last'){
            return this.attach(this.childNodes[this.childNodes.length-1]);
        } else {
            var i;
            for(i=0; i < this.childNodes.length; i++){
                if(index == i){
                    return this.attach(this.childNodes[i]);
                }
            }
        }
    }
    return false;
}

priya.prototype.clone = function (deep){
    var clone  = this.cloneNode(deep);
    clone = this.select(clone);
    if(typeof this['Priya']['eventListener'] != 'undefined'){
        for(event in this['Priya']['eventListener']){
            var list = this['Priya']['eventListener'][event];
            var index;
            for(index = 0; index < list.length; index++){
                var action = list[index];
                clone.on(event, action);
            }
        }
    }
    return clone;
}

priya.prototype.create = function (type, create){
    switch(type.toLowerCase()){
        case 'id':
            if(typeof create == 'undefined'){
                create = 'priya';
            }
            var data = priya.collection('id.' + create);
            if(this.empty(data)){
                data = [];
            }
            var id = 1;
            var index;
            for(index =0; index < data.length; index++){
                if(index >= id){
                    id = index + 1;
                }
            }
            data[id] = {"id": create + '-' + id};
            priya.collection('id.' + create, data);
            return create + '-' + id;
        break;
        case 'link':
            var element = document.createElement('LINK');
            element.rel = 'stylesheet';
            if(typeof create == 'string'){
                element.href = create;
            } else {
                alert('todo');
            }
            return element;
        break;
        case 'script':
            var element = document.createElement('SCRIPT');
            element.type = 'text/javascript';
            if(typeof create == 'string'){
                element.src = create;
            } else {
                alert('todo');
            }
            return element;
        break;
        case 'element':
            var element = document.createElement('PRIYA-NODE');
            element.className = this.str_replace('.', ' ', create);
            element.className = this.str_replace('#', '', element.className);
            element.className = this.trim(element.className);
            return this.attach(element);
        break;
        case 'nodelist' :
              var fragment = document.createDocumentFragment();
              if(Object.prototype.toString.call(create) === '[object Array]'){
                  var i;
                  for(i=0; i < create.length; i++){
                      fragment.appendChild(create[i]);
                  }
                  fragment.childNodes.item = false;
              }
              else if (typeof create == 'object'){
                  fragment.appendChild(create);
                  fragment.childNodes.item = false;
              }
              else if (typeof create != 'undefined'){
                  console.log('unknown type (' + typeof create + ') in priya.create()');
              }
              return fragment.childNodes;
        break;
        default :
            var element = document.createElement(type.toUpperCase());
            if(create){
                element.className = this.str_replace('.', ' ', create);
                element.className = this.str_replace('#', '', element.className);
            }
            return this.attach(element);
    }
    return false;
}

priya.prototype.addClass = function(className){
    var className = this.str_replace('&&', ' ', className);
    var list = className.split(' ');
    var index;
    for(index = 0; index < list.length; index++){
        var name = this.trim(list[index]);
        if(this.empty(name)){
            continue;
        }
        if(this.is_nodeList(this)){
            var i;
            for(i = 0; i < this.length; i++){
                var node = this[i];
                if(this.stristr(node.className, name) === false){
                    node.classList.add(name);
                }
            }
        } else {
            if(this.stristr(this.className, name) === false){
                this.classList.add(name);
            }
        }
    }
    return this;
}

priya.prototype.removeClass = function(className){
    var className = this.str_replace('&&', ' ', className);
    if(typeof this.className == 'undefined'){
        var index;
        for(index=0; index < this.length; index++){
            if(typeof this[index].className != 'undefined' && typeof this[index].Priya != 'undefined'){
                this[index].removeClass(className);
            }
        }
        return this;
    }
    var list = className.split(' ');
    var index;
    for(index = 0; index < list.length; index++){
        var name = this.trim(list[index]);
        if(this.empty(name)){
            continue;
        }
        if(this.stristr(this.className, name) !== false){
            this.classList.remove(name);
        }
    }
    return this;
}

priya.prototype.toggleClass = function(className){
    var className = this.str_replace('&&', ' ', className);
    if(typeof this.className == 'undefined'){
        var index;
        for(index=0; index < this.length; index++){
            if(typeof this[index].className != 'undefined' && typeof this[index].Priya != 'undefined'){
                this[index].toggleClass(className);
            }
        }
        return this;
    }
    var list = className.split(' ');
    var index;
    for(index = 0; index < list.length; index++){
        var name = this.trim(list[index]);
        if(this.empty(name)){
            continue;
        }
        if(this.stristr(this.className, name) !== false){
            this.classList.remove(className);
        } else {
            this.classList.add(className);
        }
    }
    return this;
}

priya.prototype.hasClass = function (className){
    var className = this.str_replace('&&', ' ', className);
    if(typeof this.className == 'undefined'){
        var index;
        var collection = new Array();
        for(index=0; index < this.length; index++){
            if(typeof this[index].className != 'undefined' && typeof this[index].Priya != 'undefined'){
                collection.push(this[index].hasClass(className));
            }
        }
        for(index=0; index < collection.length; index++){
            if(collection[index] === false){
                return false;
            }
        }
        return true;
    }
    var list = className.split(' ');
    var index;
    for(index = 0; index < list.length; index++){
        var name = this.trim(list[index]);
        if(this.empty(name)){
            continue;
        }
        if(this.stristr(this.className, name) !== false){
            return true;
        }
    }
    return false;
}

priya.prototype.computedStyle = function(attribute){
     if(!this.Priya.style){
         this.Priya.style = window.getComputedStyle(this);
     }
     if(attribute){
         return this.Priya.style[attribute];
     } else {
         return this.Priya.style;
     }
}

priya.prototype.css = function(attribute, value){
    if(this.empty(value)){
        if(typeof this.style == 'undefined'){
            return '';
        }
        return this.computedStyle(attribute);
    }
    if(attribute == 'has'){
        return !!this.style[value];
    }
    if(attribute == 'delete'){
        this.style[value] = '';
    }
    if(this.is_nodeList(this)){
        var index;
        for(index=0; index < this.length; index++){
            var node = this[index];
            node.style[attribute] = value;
        }
    } else {
        this.style[attribute] = value;
    }
}

priya.prototype.val = function (value){
    if(typeof this.value == 'undefined'){
        return false;
    }
    if(typeof value != 'undefined'){
        this.value = value
    }

    return this.value;
}

priya.prototype.data = function (attribute, value){
    if(attribute == 'remove'){
        if(this.attribute('has', 'data-' + value)){
            return this.attribute('remove','data-' + value);
        } else {
            var data = this.data(value);
            if(typeof data == 'object'){
                var attr;
                var result = false;
                for(attr in data){
                    this.data('remove', value + '-' + attr);
                    result = true;
                }
                return result;
            } else {
                console.log('error...');
                console.log(this.attribute('has', 'data-' + value));
                console.log(value);
                console.log(data);
                return
                //return this.attribute('remove')
            }
        }
    }
    else if (attribute == 'clear' && value == 'error'){
        if(this.tagName == 'FORM'){
            //clear errors from form
            var input = this.select('input');
            var textarea = this.select('textarea');
            var select = this.select('select');
            var dropdown = this.select('.dropdown');
            var index;
            if(this.is_nodeList(input)){
                for(index=0; index < input.length; index++){
                    var elem = input[index];
                    elem.removeClass('error');
                }
            } else {
                input.removeClass('error');
            }
            if(this.is_nodeList(textarea)){
                 for(index=0; index < textarea.length; index++){
                     var elem = textarea[index];
                     elem.removeClass('error');
                 }
            } else {
                textarea.removeClass('error');
            }
            if(this.is_nodeList(select)){
                for(index=0; index < select.length; index++){
                    var elem = select[index];
                    elem.removeClass('error');
                }
            } else {
                select.removeClass('error');
            }
            if(this.is_nodeList(dropdown)){
                for(index=0; index < dropdown.length; index++){
                    var elem = select[index];
                    elem.removeClass('error');
                }
            } else {
                dropdown.removeClass('error');
            }
        }
    }
    else if (attribute == 'serialize'){
        if(this.tagName == 'FORM'){
            //return all data for form
            var data = this.data();
            var input = this.select('input');
            var textarea = this.select('textarea');
            var select = this.select('select');
            var index;
            value = [];
            for(index in data){
                var object = {};
                object.name = index;
                object.value = data[index];
                value.push(object);
            }
            if(this.is_nodeList(input)){
                var collection = {};
                for(index=0; index < input.length; index++){
                    if(this.empty(input[index].name)){
                        continue;
                    }
                    if(input[index].type == 'checkbox' && input[index].checked !== true){
                        continue;
                    }
                    if(this.stristr(input[index].name, '[]')){
                        if(!this.isset(collection[input[index].name])){
                            collection[input[index].name] = {};
                            collection[input[index].name].name = input[index].name.split('[]').join('');
                            collection[input[index].name].value = [];
                        }
                        collection[input[index].name].value.push(input[index].value);
                    } else {
                        var object = {};
                        object.name = input[index].name;
                        object.value = input[index].value;
                        value.push(object);
                    }
                }
                for(name in collection){
                    value.push(collection[name]);
                }
            } else {
                if(!this.empty(input.name)){
                    var object = {};
                    object.name = input.name.split('[]').join('');
                    object.value = input.value;
                    value.push(object);
                }
            }
            if(this.is_nodeList(textarea)){
                var collection = {};
                for(index=0; index < textarea.length; index++){
                    if(this.empty(textarea[index].name)){
                        continue;
                    }
                    if(this.stristr(textarea[index].name, '[]')){
                        if(!this.isset(collection[textarea[index].name])){
                            collection[textarea[index].name] = {};
                            collection[textarea[index].name].name = textarea[index].name.split('[]').join('');
                            collection[textarea[index].name].value = [];
                        }
                        collection[textarea[index].name].value.push(textarea[index].value);
                    } else {
                        var object = {};
                        object.name = textarea[index].name;
                        object.value = textarea[index].value;
                        value.push(object);
                    }
                }
                for(name in collection){
                    value.push(collection[name]);
                }
            } else {
                if(!this.empty(textarea.name)){
                    var object = {};
                    object.name = textarea.name.split('[]').join('');
                    object.value = textarea.value;
                    value.push(object);
                }

            }
            if(this.is_nodeList(select)){
                var collection = {};
                for(index=0; index < select.length; index++){
                    if(this.empty(select[index].name)){
                        continue;
                    }
                    if(this.stristr(select[index].name, '[]')){
                        if(!this.isset(collection[select[index].name])){
                            collection[select[index].name] = {};
                            collection[select[index].name].name = select[index].name.split('[]').join('');
                            collection[select[index].name].value = [];
                        }
                        collection[select[index].name].value.push(select[index].value);
                    } else {
                        var object = {};
                        object.name = select[index].name;
                        object.value = select[index].value;
                        value.push(object);
                    }
                }
                for(name in collection){
                    value.push(collection[name]);
                }
            } else {
                if(!this.empty(select.name)){
                    var object = {};
                    object.name = select.name.split('[]').join('');
                    object.value = select.value;
                    value.push(object);
                }
            }
            return value;
        }
    } else {
        if(typeof attribute == 'undefined' || attribute == 'ignore' || attribute == 'select'){
            var select = value;
            var attr;
            value = {};
            for (attr in this.attributes){
                if(typeof this.attributes[attr].value == 'undefined'){
                    continue;
                }
                var key = this.stristr(this.attributes[attr].name, 'data-');
                if(key === false){
                    continue;
                }
                key = this.attributes[attr].name.substr(5);
                if(attribute == 'ignore'){
                    if(typeof select == 'string' && key == select){
                        continue;
                    }
                    if(typeof select == 'object' && this.in_array(key, select)){
                        continue;
                    }
                }
                if(attribute == 'select'){
                    if(typeof select == 'string' && key != select){
                        continue;
                    }
                    if(typeof select == 'object' && !this.in_array(key, select)){
                        continue;
                    }
                }
                var split = key.split('.');
                if(split.length == 1){
                    value[key] = this.attributes[attr].value;
                } else {
                    var object = this.object_horizontal(split, this.attributes[attr].value);
                    value = this.object_merge(value, object);
                }

            }
            return value;
        }
        else if(typeof attribute == 'object'){
            for(attr in attribute){
                this.data(attr, attribute[attr]);
            }
        } else {
            var data = this.attribute('data-' + attribute, value);
            if(this.empty(data) && !this.empty(attribute)){
                data = this.data();
                var collection = {};
                for(key in data){
                    if(this.stristr(key, attribute) !== false){
                        collection[this.str_replace(attribute + '-', '', key)] = data[key];
                    }
                }
                if(this.empty(collection)){
                    return null;
                } else {
                    return collection;
                }
            } else {
                return data;
            }
        }
    }
}

priya.prototype.remove = function (){
    if(this.is_nodeList(this)){
        var index;
        for(index=0; index < this.length; index++){
            var node = this[index];
            node.parentNode.removeChild(node);
        }
        return true;
    } else {
        var node = this.parentNode;
        if(node != null){
            return node.removeChild(this);
        } else {
            return false;
        }
    }
}

priya.prototype.get = function (url, script){
    var xhttp = new XMLHttpRequest();
    if(typeof this.collect.require == 'undefined'){
        this.collect.require = {};
    }
    this.collect.require.toLoad = this.collect.require.toLoad ? this.collect.require.toLoad : 0;
    this.collect.require.toLoad++;
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            if(xhttp.responseText.substr(0, 1) == '{' && xhttp.responseText.substr(-1) == '}'){
                var data = JSON.parse(xhttp.responseText);
                priya.collect.require.loaded = priya.collect.require.loaded ? priya.collect.require.loaded : 0;
                priya.collect.require.loaded++;
                script(url, data);
            } else {
                if(typeof priya.debug !== 'undefined' && typeof run !== 'undefined' ){
                    priya.debug(xhttp.responseText);
                } else {
                    console.log(xhttp.responseText);
                }

            }
        }
    };
    xhttp.open("GET", url, true);
    xhttp.setRequestHeader("Content-Type", "application/json");
    xhttp.send();
}

priya.prototype.loader = function(data){
    if(data == 'remove'){
         priya.select('.priya-gui-loader').addClass('fade-out');
         setTimeout(function(){
             priya.select('.priya-loader').remove();
         }, 10000);
         return;
    }
    var body = run('body');
    var load = priya.create('element', 'priya-loader');
    load.innerHTML = '<div class="priya-gui-loader"></div>';
    body.append(load);
}

priya.prototype.refresh = function (data){
    if(typeof data == 'undefined'){
        return;
    }
    if(!this.isset(data.refresh)){
        if(typeof data == 'object'){
            return;
        }
        var data = {"refresh": data};
    }
    window.location.href = data.refresh;
    return data;
}

priya.prototype.link = function (data, closure){
    if(typeof data == 'undefined'){
        return;
    }
    if(typeof data == 'string'){
        var data = {
            link : [data]
        };
    }
    if(this.isset(data.href)){
        priya.select('head').appendChild(data);
        priya.load++;
        data.addEventListener('load', function(event){
            priya.load--;
        }, false);
        if(closure){
            data.addEventListener('load', function(event){
                closure();
            }, false);
        }
        return data;
    } else {
        if(!this.isset(data.link)){
            return data;
        }
        var index;
        for(index in data.link){
            if(data.link[index].substr(0, 4) == '&lt;'){
                data.link[index] = data.link[index].toString()
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>');
            }
            var link = {
                "method":"append",
                "target":"head",
                "html":data.link[index]
            };
            this.content(link);
//            var element =s
        }
        return this;
    }

}

priya.prototype.script = function (data, closure){
    if(typeof data == 'undefined'){
        return;
    }
    if(this.isset(data.src) && this.isset(data.type) && data.type == 'text/javascript'){
        priya.select('head').appendChild(data);
        priya.load++;
        data.addEventListener('load', function(event){
            priya.load--;
        }, false);
        if(closure){
            data.addEventListener('load', function(event){
                closure();
            }, false);
        }
        return data;
    }
    if(typeof attempt == 'undefined'){
        attempt = 0;
    }
    if(parseInt(priya.load) != 0 && attempt < 500){
        setTimeout(function(){
            priya.script(data, ++attempt);
            priya.hit++;
            priya.debug('waiting on load...');
        }, parseFloat(1/30));
        return data;
    }
    if(!this.isset(data.script)){
        return data;
    }
    var index;
    for(index in data.script){
        if(data.script[index].substr(0, 4) == '&lt;'){
            data.script[index] = data.script[index].toString()
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>');
        }
        this.addScriptSrc(data.script[index]);
        this.addScriptText(data.script[index]);
    }
    return this;
}

priya.prototype.exception = function (data, except){
    if(data == 'write' || data == 'replace'){
        this.debug(except);
        /*
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"replace",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
        */
    }
    else if(data == 'append'){
        this.debug(except);
        /*
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
        */
    }
    else {
        var index;
        var found = false;
        for (index in data){
            if(this.stristr(index,'\\exception')){
                found = true;
                data = data[index];
            }
        }
        if(this.empty(found)){
            return;
        }
        this.debug(JSON.stringify(data, null, 2));
        /*
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ JSON.stringify(data, null, 4) +"</pre>"
        }
        exception.content(content);
        */
    }
}

/*
priya.prototype.exception = function (data, except){
    if(data == 'write' || data == 'replace'){
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"replace",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
    }
    else if(data == 'append'){
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
    }
    else {
        var index;
        var found = false;
        for (index in data){
            if(this.stristr(index,'\\exception')){
                found = true;
            }
        }
        if(this.empty(found)){
            return;
        }
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ JSON.stringify(data, null, 4) +"</pre>"
        }
        exception.content(content);
    }
}
*/

priya.prototype.addScriptSrc = function (data){
    var tag = this.readTag(data);
    if(!this.isset(tag['tagName']) || tag['tagName'] != 'script'){
        return;
    }
    if(!this.isset(tag['src'])){
        return;
    }
    var element = document.createElement(tag.tagName);
    var index;
    for(index in tag){
        if(index == 'tagName'){
            continue;
        }
        element.setAttribute(index, tag[index]);
    }
    document.getElementsByTagName("head")[0].appendChild(element);
}

priya.prototype.addScriptText = function (data){
    var tag = this.readTag(data);
    if(!this.isset(tag['tagName']) || tag['tagName'] != 'script'){
        return;
    }
    var temp = this.explode('<'+tag.tagName, data);
    if(!this.isset(temp[1])){
        return;
    }
    temp = this.explode('</' +tag.tagName, temp[1]);
    temp = this.explode('>', temp[0]);
    temp.shift();
    var text = this.trim(this.implode('>', temp));
    delete temp;
    if(this.empty(text)){
        return;
    }
    var element = document.createElement(tag.tagName);
    var index;
    for(index in tag){
        if(index == 'tagName'){
            continue;
        }
        if(this.stristr(index, '[') !== false){
            continue;
        }
        if(this.stristr(index, '\'') !== false){
            continue;
        }
        element.setAttribute(index, tag[index]);
    }
    element.text = text;
    document.getElementsByTagName("head")[0].appendChild(element);
}

priya.prototype.readTag = function (data){
    var temp = this.explode('>', this.trim(data));
    temp = this.explode(' ', this.trim(temp[0]));
    var index;
    var tag = {
        "tagName": temp[0].substr(1)
    };
    for (index in temp){
        var key = this.explode('="', temp[index]);
        var value = this.explode('"',key[1]);
        key = key[0];
        if(this.empty(value)){
            continue;
        }
        value.pop();
        value = this.implode('"', value);
        tag[key] = value;
    }
    return tag;
}

priya.prototype.attribute = function (attribute, value){
    if(attribute == 'has'){
        var attr;
        for (attr in this.attributes){
            if(typeof this.attributes[attr].value == 'undefined'){
                continue;
            }
            if(this.attributes[attr].name == value){
                return true;
            }
        }
        return false;
    }
    else if(attribute == 'remove'){
        this.removeAttribute(value);
        return;
    }
    if(typeof value == 'undefined'){
        if(typeof attribute == 'undefined'){
            var attr;
            value = {};
            for (attr in this.attributes){
                if(typeof this.attributes[attr].value == 'undefined'){
                    continue;
                }
                value[this.attributes[attr].name] = this.attributes[attr].value;
            }
            return value;
        } else {
            var attr;
            value = null;
            for (attr in this.attributes){
                if(this.attributes[attr].name == attribute){
                    value = this.attributes[attr].value;
                }
            }
            return value;
        }
    } else {
        if (typeof this.setAttribute == 'function'){
            if(value === null){
                this.setAttribute(attribute, value);
            }
            else if(typeof value == 'object' && typeof value.nodeType != 'undefined'){
                selector = value.tagName;
                if(typeof selector == 'undefined' && value instanceof HTMLDocument){
                    /*
                    var priya = this.attach(this.create('element', selector));
                    priya.data('selector', selector);
                    */
                    return value;
                }
                selector = selector.toLowerCase();
                if(value.id){
                    selector += ' #' + value.id;
                }
                if(value.className){
                    selector += ' .' + this.str_replace(' ','.',value.className);
                }
                this.setAttribute(attribute, selector);
            }
            else if(typeof value == 'object'){
                for(attr in value){
                    this.attribute(attribute + '-' + attr, value[attr]);
                }
            } else {
                this.setAttribute(attribute, value);
            }

        }
        return value;
    }
}

priya.prototype.on = function (event, action, capture){
    if(typeof this['Priya'] == 'undefined'){
        console.log('Priya undefined');
        console.log(this);
        console.log(event);
        console.log(action);
        return this;
    }
    if(typeof this['Priya']['eventListener'] != 'object'){
        this['Priya']['eventListener'] = {};
    }
    if(typeof event == 'object'){
        var index;
        for (index=0; index < event.length; index++){
            this.on(event[index], action, capture);
        }
        return this;
    } else {
        if(typeof this['Priya']['eventListener'][event] == 'undefined'){
            this['Priya']['eventListener'][event] = new Array();
        }
        if(this.empty(capture)){
            capture = false;
        } else {
            capture = true;
        }
        this['Priya']['eventListener'][event].push(action);
        if(this.is_nodeList(this)){
            var index;
            for (index=0; index < this.length; index++){
                var node = this[index];
                if(typeof action == 'undefined'){
                    console.log('action undefined with event: ' + event);
                }
                node.addEventListener(event, action, capture);
            }
        } else {
            this.addEventListener(event, action, capture);
        }
        return this;
    }
}

priya.prototype.off = function (event, action){
    console.log(this['Priya']['eventListener']);
    this.removeEventListener(event, action)
}

priya.prototype.trigger = function (trigger, bubble, cancel){
    if(this.empty(bubble)){
        bubble = false;
    } else {
        bubble = true;
    }
    if(this.empty(cancel)){
        cancel = false;
    } else {
        cancel = true;
    }
    var event = new Event(trigger, {
        'bubbles'    : bubble, // Whether the event will bubble up through the DOM or not
        'cancelable' : cancel  // Whether the event may be canceled or not
    });
    //event.initEvent(trigger, true, true);
    event.synthetic = true;
    if(typeof this.dispatchEvent == 'undefined'){
        console.log('dispatch problem');
        console.log(this);
    } else {
        this.dispatchEvent(event, true);
    }
}

/*
*/

priya.prototype.init = function (data, configuration){
    console.log('deprecated, use select or run');
    if(typeof data == 'undefined'){
        return this;
    }
    if(typeof data == 'string'){
        var element = this.select(data);
        return element;
    }
    return data;
}

priya.prototype.jid = function (list){
    if(typeof list == 'undefined'){
        list = 'priya';
    }
    var data = this.collection(list);
    if(this.empty(data)){
        return "1";
    } else{
        console.log(data);
    }

}

priya.prototype.collection = function (attribute, value){
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

priya.prototype.getCollection = function (attribute){
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

priya.prototype.setCollection = function (attribute, value){
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

priya.prototype.deleteCollection = function(attribute){
    return this.object_delete(attribute, this.collect);
}

/*
priya.prototype.empty = function (mixed_var){
    var key;
     if (
        mixed_var === "" ||
        mixed_var === 0 ||
        mixed_var === "0" ||
        mixed_var === null ||
        mixed_var === false ||
        typeof mixed_var === 'undefined') {
        return true;
    }
    if (typeof mixed_var == 'object') {
        for (key in mixed_var) {
            return false;
        }
        return true;
    }
    return false;
}


priya.prototype.isset = function (){
    var a = arguments,
        l = a.length,
        i = 0,
        undef;
    if (l === 0) {
        console.log('Empty isset');
        return false;
    }
    while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false;
        }
        i++;
    }
    return true;
}



priya.prototype.microtime = function (get_as_float){
    var now = new Date().getTime() / 1000;
    var s = parseInt(now, 10);
    return (get_as_float) ? now : (Math.round((now - s) * 1000) / 1000) + ' ' + s;
}
*/


priya.prototype.naturalICompare = function (a, b){
    a = a.toLowerCase();
    b = b.toLowerCase();
    return naturalCompare(a, b);
}

priya.prototype.naturalCompare = function (a, b){
    var i, codeA
    , codeB = 1
    , posA = 0
    , posB = 0
    , alphabet = String.alphabet

    function getCode(str, pos, code) {
        if (code) {
            for (i = pos; code = getCode(str, i), code < 76 && code > 65;) ++i;
            return +str.slice(pos - 1, i)
        }
        code = alphabet && alphabet.indexOf(str.charAt(pos))
        return code > -1 ? code + 76 : ((code = str.charCodeAt(pos) || 0), code < 45 || code > 127) ? code
            : code < 46 ? 65               // -
            : code < 48 ? code - 1
            : code < 58 ? code + 18        // 0-9
            : code < 65 ? code - 11
            : code < 91 ? code + 11        // A-Z
            : code < 97 ? code - 37
            : code < 123 ? code + 5        // a-z
            : code - 63
    }


    if ((a+="") != (b+="")) for (;codeB;) {
        codeA = getCode(a, posA++)
        codeB = getCode(b, posB++)

        if (codeA < 76 && codeB < 76 && codeA > 66 && codeB > 66) {
            codeA = getCode(a, posA, posA)
            codeB = getCode(b, posB, posA = i)
            posB = i
        }

        if (codeA != codeB) return (codeA < codeB) ? -1 : 1
    }
    return 0
}

/*
priya.prototype.trim = function (str, charlist){
    var whitespace, l = 0,
    i = 0;
    str += '';
    if (!charlist) {
        // default list
        whitespace =
            ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
    } else {
        // preg_quote custom list
        charlist += '';
        whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
    }
    l = str.length;
    for (i = 0; i < l; i++) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(i);
            break;
        }
    }
    l = str.length;
    for (i = l - 1; i >= 0; i--) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}
*/

priya.prototype.basename = function (path, suffix){
    var b = path;
    var lastChar = b.charAt(b.length - 1);
    if (lastChar === '/' || lastChar === '\\') {
        b = b.slice(0, -1);
    }
    b = b.replace(/^.*[\/\\]/g, '');
    if (typeof suffix === 'string' && b.substr(b.length - suffix.length) == suffix) {
        b = b.substr(0, b.length - suffix.length);
    }
    return b;
}

priya.prototype.function_exists = function (name){
    if (typeof name === 'string'){
        if(typeof this == 'undefined'){
            return false;
        }
        if(typeof this.Priya == 'object'){
            name = this[name];
        } else {
            name = this.window[name];
        }
    }
    return typeof name === 'function';
}

priya.prototype.str_replace = function (search, replace, subject, count){
    var i = 0,
        j = 0,
        temp = '',
        repl = '',
        sl = 0,
        fl = 0,
        f = [].concat(search),
        r = [].concat(replace),
        s = subject,
        ra = Object.prototype.toString.call(r) === '[object Array]',
        sa = Object.prototype.toString.call(s) === '[object Array]';
          s = [].concat(s);
      if (count) {
        this.window[count] = 0;
      }
      for (i = 0, sl = s.length; i < sl; i++) {
        if (s[i] === '') {
              continue;
        }
        for (j = 0, fl = f.length; j < fl; j++) {
              temp = s[i] + '';
              repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
              s[i] = (temp).split(f[j]).join(repl);
              if (count && s[i] !== temp) {
                this.window[count] += (temp.length - s[i].length) / f[j].length;
              }
        }
      }
      return sa ? s : s[0];
}

priya.prototype.stristr = function (haystack, needle, bool){
    var pos = 0;
    haystack += '';
    pos = haystack.toLowerCase().indexOf((needle + '').toLowerCase());
    if (pos == -1) {
        return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}

priya.prototype.explode = function (delimiter, string, limit){
    if (arguments.length < 2 || typeof delimiter === 'undefined' || typeof string === 'undefined'){
        return null;
    }
      if (delimiter === '' || delimiter === false || delimiter === null){
          return false;
      }
      if (typeof delimiter === 'function' || typeof delimiter === 'object' || typeof string === 'function' || typeof string ==='object') {
        return {
              0: ''
        };
      }
      if (delimiter === true){
          delimiter = '1';
      }
    delimiter += '';
    string += '';
      var s = string.split(delimiter);
    if (typeof limit === 'undefined'){
        return s;
    }
    if (limit === 0){
        limit = 1;
    }
    if (limit > 0) {
        if (limit >= s.length){
            return s;
        }
        return s.slice(0, limit - 1)
                  .concat([s.slice(limit - 1)
                .join(delimiter)
          ]);
      }
      if (-limit >= s.length){
          return [];
      }
      s.splice(s.length + limit);
      return s;
}

priya.prototype.explode_multi = function(delimiter, string, limit){
    var result = new Array();
    var index;
    for(index =0; index < delimiter.length; index++){
        var delim = delimiter[index];
        if(typeof limit != 'undefined' && this.isset(limit[index])){
            var tmp = this.explode(delim. string. limit[index]);
        } else {
            var tmp = this.explode(delim, string);
        }
        if(tmp.length == 1){
            continue;
        }
        var i;
        for(i = 0; i < tmp.length; i++){
            var value = tmp[i];
            result.push(value);
        }
    }
    if(this.empty(result)){
        result.push(string);
    }
    return result;
}

priya.prototype.implode = function (glue, pieces){
    var i = '',
        retVal = '',
        tGlue = '';
    if (arguments.length === 1) {
        pieces = glue;
        glue = '';
    }
    if (typeof pieces === 'object') {
        if (Object.prototype.toString.call(pieces) === '[object Array]') {
            return pieces.join(glue);
        }
        for (i in pieces) {
            retVal += tGlue + pieces[i];
            tGlue = glue;
        }
        return retVal;
    }
    return pieces;
}

priya.prototype.rand = function (min, max) {
    var argc = arguments.length;
    if (argc === 0) {
        min = 0;
        max = 2147483647;
    }
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

priya.prototype.is_numeric = function (mixed_var){
    var whitespace =
        " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (
        typeof mixed_var === 'number' ||
        (
            typeof mixed_var === 'string' &&
            whitespace.indexOf(mixed_var.slice(-1)) === -1)
        ) &&
        mixed_var !== '' && !isNaN(mixed_var)
    ;
}

priya.prototype.in_array = function (needle, haystack, strict) {
    var key = ''
    var strict = !!strict
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true
                }
            }
          } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true
            }
        }
    }
    return false
}

priya.prototype.object_horizontal = function (verticalArray, value, result){
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

priya.prototype.object_merge = function (main, merge){
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

priya.prototype.object_get = function(attributeList, object){
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

priya.prototype.object_set = function(attributeList, value, object, result){
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

priya.prototype.object_delete = function(attributeList, object, parent, key){
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

priya.prototype.is_array = function (mixedVar) {
    var _getFuncName = function (fn) {
        var name = (/\W*function\s+([\w$]+)\s*\(/).exec(fn)
        if (!name) {
          return '(Anonymous)';
        }
        return name[1];
    }
    var _isArray = function (mixedVar) {
        if (!mixedVar || typeof mixedVar !== 'object' || typeof mixedVar.length !== 'number') {
            return false;
        }
        var len = mixedVar.length;
        mixedVar[mixedVar.length] = 'bogus';
        if (len !== mixedVar.length) {
            mixedVar.length -= 1;
            return true;
        }
        delete mixedVar[mixedVar.length];
        return false;
    }
    if (!mixedVar || typeof mixedVar !== 'object') {
        return false;
    }
    var isArray = _isArray(mixedVar);
    if (isArray) {
        return true;
    }
    return false;
}

