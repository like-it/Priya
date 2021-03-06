/** *
 * (c) 2012 - 2018 https://priya.software
 * @author 			Remco van der Velde
 * @version			0.3.4
 * @see				https://priya.software/licence
 * @built				2018-05-31 21:48:30
 *
**//*
 * @name: Active
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Active.prototype.js
 */
_('prototype').active = function (){
    return document.activeElement;
}

priya.active = _('prototype').active;

/*
 * @name: Append
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Append.prototype.js
 */
_('prototype').append = function(node){
    this.appendChild(node);
    return this;
}

priya.append = _('prototype').append;

/*
 * @name: Attach
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Attach.prototype.js
 */
/**
 * required & written in Priya.prototype.js
 */

/*
 * @name: Attribute
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Attribute.prototype.js
 */
_('prototype').attribute = function (attribute, value){
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

priya.attribute = _('prototype').attribute;

/*
 * @name: Basename
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Basename.prototype.js
 */
_('prototype').basename = function (path, suffix){
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

priya.basename = _('prototype').basename;

/*
 * @name: Calculate
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Calculate.prototype.js
 */
_('prototype').calculate = function (calculate){
    var result = null;
    switch(calculate){
        case 'all':
            /*
             * var className = this.className;
             * this.addClass('display-block overflow-auto');
             */
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

priya.calculate = _('prototype').calculate;

/*
 * @name: Children
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Children.prototype.js
 */
_('prototype').children = function (index){
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

priya.children = _('prototype').children;

/*
 * @name: Class
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Class.prototype.js
 */
_('prototype').addClass = function(className){
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
                if(typeof this.classList == 'object'){
                    this.classList.add(name);
                } else {
                    this.debug('error in classlist with ' + this.classname + ' ' + name);
                    console.log(this);
                }
            }
        }
    }
    return this;
}

_('prototype').removeClass = function(className){
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

_('prototype').toggleClass = function(className){
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

_('prototype').hasClass = function (className){
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

priya.addClass = _('prototype').addClass;
priya.removeClass = _('prototype').removeClass;
priya.toggleClass = _('prototype').toggleClass;
priya.hasClass = _('prototype').hasClass;

/*
 * @name: Clone
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Clone.prototype.js
 */
_('prototype').clone = function (deep){
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

priya.clone = _('prototype').clone;

/*
 * @name: Closest
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Closest.prototype.js
 */
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

/*
 * @name: Collection
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Collection.prototype.js
 */
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

/*
 * @name: Content
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Content.prototype.js
 */
_('prototype').content = function (data){
    if(Object.prototype.toString.call(priya) != '[object Function]'){
        var priya = window.priya;
    }
    if(typeof data == 'undefined'){
        console.log('json.content failed (data)');
        return;
    }
    if(typeof data['method'] == 'undefined'){
        return;
    }
    if(typeof data['target'] == 'undefined'){
        console.log('json.content failed (target)');
        return;
    }
    if(typeof data['html'] == 'undefined' && (data['method'] != 'replace' && data['method'] != 'unwrap')){
        return;
    }
    /*
    if(data['html'] === false){
        priya.debug('html is false in data, see:');
        priya.debug(data);
        return;
    }
    */
    var target = priya.select(data['target']);
    var method = data['method'];
    if(this.is_nodeList(target)){
        var i = 0;
        for(i =0; i < target.length; i++){
            var node = target[i];
            if(method == 'replace'){
                node.html(data['html']);
            }
            else if (method == 'replace-with'){
                node.html(data['html'], 'outer');
            }
            else if(method == 'replace-or-append-to-body'){
                if(node.nodeName == 'PRIYA-NODE'){
                    var node = priya.select('body');
                    node.insertAdjacentHTML('beforeend',data['html']);
                } else {
                    node.html(data['html']);
                }
            }
            else if(method == 'replace-with-or-append-to-body'){
                if(node.nodeName == 'PRIYA-NODE'){
                    var node = priya.select('body');
                    node.insertAdjacentHTML('beforeend',data['html']);
                } else {
                    node.html(data['html'], 'outer');
                }
            }
            else if(method == 'append' || method == 'beforeend'){
                node.insertAdjacentHTML('beforeend',data['html']);
            }
            else if(method == 'prepend' || method == 'afterbegin'){
                node.insertAdjacentHTML('afterbegin',data['html']);
            }
            else if(method == 'after' || method == 'afterend'){
                node.insertAdjacentHTML('afterend',data['html']);
            }
            else if(method == 'before' || method == 'beforebegin'){
                node.insertAdjacentHTML('beforebegin', data['html']);
            } else {
                this.exception('write', this.dump('unknown method ('+ method +') in content'));
            }
        }
    } else {
        if(method == 'replace'){
            target.html(data['html']);
        }
        else if(method == 'replace-with'){
            target.html(data['html'], 'outer');
        }
        else if(method == 'replace-or-append-to-body'){
            if(target.nodeName == 'PRIYA-NODE'){
                var target = priya.select('body');
                target.insertAdjacentHTML('beforeend',data['html']);
            } else {
                target.html(data['html']);
            }
        }
        else if(method == 'replace-with-or-append-to-body'){
            if(target.nodeName == 'PRIYA-NODE'){
                var target = priya.select('body');
                target.insertAdjacentHTML('beforeend',data['html']);
            } else {
                target.html(data['html'], 'outer');
            }
        }
        else if(method == 'append' || method == 'beforeend'){
            target.insertAdjacentHTML('beforeend',data['html']);
        }
        else if(method == 'prepend' || method == 'afterbegin'){
            target.insertAdjacentHTML('afterbegin',data['html']);
        }
        else if(method == 'after' || method == 'afterend'){
            target.insertAdjacentHTML('afterend',data['html']);
        }
        else if(method == 'before' || method == 'beforebegin'){
            target.insertAdjacentHTML('beforebegin', data['html']);
        } else {
            this.exception('write', this.dump('unknown method ('+ method +') in content'));
        }
    }
    return target;
}

priya.content = _('prototype').content;

/*
 * @name: Create
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Create.prototype.js
 */
_('prototype').create = function (type, create){
    if(typeof type.toLowerCase != 'function'){
        return;
    }
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
                element.className = this.trim(element.className);
            }
            return this.attach(element);
    }
    return false;
}

priya.create = _('prototype').create;

/*
 * @name: Create.selector
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Create.selector.prototype.js
 */
_('prototype').createSelector = function(element){
    if(this.empty(element)){
        return '';
    }
    selector = element.tagName;
    if(typeof selector == 'undefined' && element instanceof HTMLDocument){
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

priya.createSelector = _('prototype').createSelector;

/*
 * @name: Css
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Css.prototype.js
 */
_('prototype').css = function(attribute, value){
    if(empty(value) && value !== 0 && value !== '0'){
        if(typeof this.style == 'undefined'){
            return '';
        }
        return this.computedStyle(attribute);
    }
    if(typeof this.style == 'undefined'){
        return '';
    }
    if(attribute == 'has'){
        return !!this.style[value];
    }
    else if(attribute == 'delete'){
        this.style[value] = '';
    }
    if(this.is_nodeList(this)){
        var index;
        for(index=0; index < this.length; index++){
            var node = this[index];
            value = node.computeStyle(attribute, value);
            node.style[attribute] = value;
        }
    } else {
        value = this.computeStyle(attribute, value);
        this.style[attribute] = value;
    }
}

_('prototype').computeStyle = function(attribute, value){
    if(attribute == 'top' && value=='middle'){
        var height = parseInt(this.css('height'));
        value = 'calc(50% - ' + (height /2) + 'px)';
    }
    else if(attribute == 'left' && value=='center'){
        var width = parseInt(this.css('width'));
        value = 'calc(50% - ' + (width /2) + 'px)';
    }
    return value;
}

_('prototype').computedStyle = function(attribute){
    if(!this.Priya.style){
        this.Priya.style = window.getComputedStyle(this);
    }
    if(attribute){
        return this.Priya.style[attribute];
    } else {
        return this.Priya.style;
    }
}

priya.css = _('prototype').css;
priya.computeStyle = _('prototype').computeStyle;
priya.computedStyle = _('prototype').computedStyle;

/*
 * @name: Debug
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Debug.prototype.js
 */
_('prototype').debug = function (data){
    var string = 'Loading Debug...';
    var node = run('.debug');
    if(!node){
        var node = priya.create('div', 'dialog no-select debug');
        node.html('<div class="head"><i class="icon icon-bug"></i><h2>Debug</h2></div><div class="menu"><ul class="tab-head"><li class="tab-debug selected"><p>Debug</p></li><li class="tab-collection"><p>Collection</p></li><li class="tab-session"><p>Session</p></li></ul></div><div class="body"><div class="tab tab-body tab-debug selected"></div><div class="tab tab-body tab-collection"></div><div class="tab tab-body tab-session"></div></div><div class="footer"><button type="button" class="button-default button-close">Close</button><button type="button" class="button-default button-debug-clear"><i class="icon-trash"></i></button></div></div>');
        priya.select('body').append(node);

        node.on('open', function(){
            node.select('div.head').closest('.debug').addClass('has-head');
            node.select('div.menu').closest('.debug').addClass('has-menu');
            node.select('div.icon').closest('.debug').addClass('has-icon');
            node.select('div.footer').closest('.debug').addClass('has-footer');
            priya.select('.debug').addClass('display-block');
            node.loader('remove');
        });
        node.on('close', function(){
            priya.select('.debug').removeClass('display-block');
        });
        node.on('debug', function(){
            priya.select('.debug .tab-head li').removeClass('selected');
            priya.select('.debug .tab-body').removeClass('selected');
            var node = priya.select('.debug .tab-body.tab-debug');
            node.addClass('selected');
            //wrong syntax
            //var scrollable = node.closest('has', 'scrollbar', 'vertical');
            //scrollable.scrollbar('to', {'x': 0, 'y': scrollable.scrollbar('height')});
        });
        node.on('debug-clear', function(){
            var debug = run('.debug .tab-body.tab-debug');
            debug.html('');
        });
        node.on('collection', function(){
            priya.select('.debug .tab-head li').removeClass('selected');
            priya.select('.debug .tab-body').removeClass('selected');
            var node = priya.select('.debug .tab-body.tab-collection');
            node.addClass('selected');
            var collection = priya.collection();
            if (typeof JSON.decycle == "function") {
                collection = JSON.decycle(collection);
            }
            collection = JSON.stringify(collection, null, 2);
            node.html('<pre>' + collection + '</pre>');
        });
        node.on('session', function(){
            priya.select('.debug .tab-head li').removeClass('selected');
            priya.select('.debug .tab-body').removeClass('selected');
            var node = priya.select('.debug .tab-body.tab-session');
            node.addClass('selected');

            var request = {};
            request.method = 'replace';
            request.target = '.tab-body.tab-session';

            priya.request(priya.collection('url') + 'Priya.System.Session', request);

            node.html('<pre>Retrieving session...</pre>');
        });

        node.select('.button-close').on('click', function(){
            node.trigger('close');
        });
        node.select('.button-debug-clear').on('click', function(){
            node.trigger('debug-clear');
        });
        node.select('.tab-head .tab-collection').on('click', function(){
            node.trigger('collection');
            this.addClass('selected');
        });
        node.select('.tab-head .tab-debug').on('click', function(){
            node.trigger('debug');
            this.addClass('selected');
        });
        node.select('.tab-head .tab-session').on('click', function(){
            node.trigger('session');
            this.addClass('selected');
        });
    }

    var debug = select('.debug .tab-body.tab-debug');
    if(typeof data == 'string'){
        if(data == 'run'){
            data = string;
        }
        var item = priya.create('pre', '');
        item.html(data);
        debug.append(item);
        //wrong syntax
        //var scrollable = debug.closest('has', 'scrollbar', 'vertical');
        //scrollable.scrollbar('to', {'x': 0, 'y': scrollable.scrollbar('height')});
        node.trigger('open');
        if(data == string){
            setTimeout(function(){
                item.remove();
            }, 1500);
        }
    }
    else if(typeof data == 'object'){
        var remove = priya.collection('debug');
        if(remove){
            var index;
            for(index in remove){
                priya.debug(index);
                delete data.index;
            }
        }
        if (typeof JSON.decycle == "function") {
            data = JSON.decycle(data);
        }
        data = JSON.stringify(data, null, 2);
        var item = priya.create('pre', '');
        item.html(data);
        debug.append(item);
        var scrollable = debug.closest('has', 'scrollbar', 'vertical');
        scrollable.scrollbar('to', {'x': 0, 'y': scrollable.scrollbar('height')});
        node.trigger('open');
    } else {
        node.trigger('open');
        //node.loader('remove');
    }
}

priya.debug = _('prototype').debug;

/*
 * @name: Empty
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Empty.prototype.js
 */
_('prototype').empty = function (mixed_var){
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

if(typeof priya != 'undefined'){
    priya.empty = _('prototype').empty;
}

/*
 * @name: Exception
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Exception.prototype.js
 */
_('prototype').exception = function (data, except){
    if(data == 'write' || data == 'replace'){
        this.debug(except);
    }
    else if(data == 'append'){
        this.debug(except);
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
    }
}

priya.exception = _('prototype').exception;

/*
 * @name: Explode.multi
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Explode.multi.prototype.js
 */
_('prototype').explode_multi = function(delimiter, string, limit){
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

priya.explode_multi = _('prototype').explode_multi;

/*
 * @name: Explode
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Explode.prototype.js
 */
_('prototype').explode = function (delimiter, string, limit){
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

priya.explode = _('prototype').explode;

/*
 * @name: Extended
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Extended.prototype.js
 */
priya.init = function (data, configuration){
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

/*
 * @name: Find
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Find.prototype.js
 */
_('prototype').find = function(selector, attach) {
    if (!this.id) {
        this.id = this.attribute('id', 'priya-find-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999));
        var removeId = true;
    }
    if(typeof selector == 'object'){
        console.log(selector);
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
            /*add to document for retries? */
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

priya.find = _('prototype').find;

/*
 * @name: Function.exists
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Function.exists.prototype.js
 */
_('prototype').function_exists = function (name){
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

priya.function_exists = _('prototype').function_exists;

/*
 * @name: Html
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Html.prototype.js
 */
_('prototype').html = function (html, where){
    if(typeof where == 'undefined'){
        where = 'inner';
    }
    if(typeof html == 'undefined'){
        return this.innerHTML;
    } else {
        if(html === true){
            var attribute = this.attribute();
            html =  '<' + this.tagName.toLowerCase();
            for(attr in attribute){
                html += ' ' + attr + '="' + attribute[attr] + '"';
            }
            //fix <img> etc (no </img>)
            html += '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
            return html;
        } else {
            if(where == 'outer'){
                if(this.is_nodeList()){
                     var index;
                     for(index=0; index < this.length; index++){
                         this[index].outerHTML = html;
                     }
                     return html;
                } else {
                    this.outerHTML = html;
                    return this.outerHTML;
                }

            } else {
                if(this.is_nodeList()){
                    var index;
                    for(index=0; index < this.length; index++){
                        this[index].innerHTML = html;
                    }
                    return html;
               } else {
                   this.innerHTML = html;
                   return this.innerHTML;
               }
            }
        }
    }
}

priya.html = _('prototype').html;

/*
 * @name: Implode
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Implode.prototype.js
 */
_('prototype').implode = function (glue, pieces){
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

priya.implode = _('prototype').implode;

/*
 * @name: In.array
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/In.array.prototype.js
 */
_('prototype').in_array = function (needle, haystack, strict) {
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

priya.in_array = _('prototype').in_array;

/*
 * @name: Is.array
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Is.array.prototype.js
 */
_('prototype').is_array = function (mixedVar) {
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

priya.is_array = _('prototype').is_array;

/*
 * @name: Is.nodelist
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Is.nodelist.prototype.js
 */
_('prototype').is_nodeList = function (nodes){
    if(typeof nodes == 'undefined'){
        nodes = this;
    }
    var stringRepr = Object.prototype.toString.call(nodes);

    return typeof nodes === 'object' &&
        /^\[object (HTMLCollection|NodeList|Object)\]$/.test(stringRepr) &&
        (typeof nodes.length === 'number') &&
        (nodes.length === 0 || (typeof nodes[0] === "object" && nodes[0].nodeType > 0));
}

priya.is_nodeList = _('prototype').is_nodeList;

/*
 * @name: Is.numeric
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Is.numeric.prototype.js
 */
_('prototype').is_numeric = function (mixed_var){
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

priya.is_numeric = _('prototype').is_numeric;

/*
 * @name: Isset
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Isset.prototype.js
 */
_('prototype').isset = function (){
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

priya.isset = _('prototype').isset;

/*
 * @name: Jid
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Jid.prototype.js
 */
_('prototype').jid = function (list){
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

priya.jid = _('prototype').jid;

/*
 * @name: Link
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Link.prototype.js
 */
_('prototype').link = function (data, closure){
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
            data.addEventListener('error', function(event){
                console.log('error');
                closure();
            }, false);
        }
        return data;
    } else {
        if(!this.isset(data.link)){
            console.log('no data link');
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
        }
        return this;
    }
}

priya.link = _('prototype').link;

/*
 * @name: Loader
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Loader.prototype.js
 */
_('prototype').loader = function(data){
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

priya.loader = _('prototype').loader;

/*
 * @name: Methods
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Methods.prototype.js
 */
_('prototype').methods = function (){
    var result = {};
    for(property in this){
        if(typeof this[property] != 'function'){
            continue;
        }
        result[property] = this[property];
    }
    return result;
}

priya.methods = _('prototype').methods;

/*
 * @name: Microtime
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Microtime.prototype.js
 */
_('prototype').microtime = function (get_as_float){
    var now = new Date().getTime() / 1000;
    var s = parseInt(now, 10);
    return (get_as_float) ? now : (Math.round((now - s) * 1000) / 1000) + ' ' + s;
}

priya.microtime = _('prototype').microtime;

/*
 * @name: Namespace
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Namespace.prototype.js
 */
/**
 * required & written in Priya.prototype.js
 */

/*
 * @name: Compare.natural
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Compare.natural.prototype.js
 */
_('prototype').naturalICompare = function (a, b){
    a = a.toLowerCase();
    b = b.toLowerCase();
    return naturalCompare(a, b);
}

_('prototype').naturalCompare = function (a, b){
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
            : code < 46 ? 65               /* - */
            : code < 48 ? code - 1
            : code < 58 ? code + 18        /* 0-9 */
            : code < 65 ? code - 11
            : code < 91 ? code + 11        /* A-Z */
            : code < 97 ? code - 37
            : code < 123 ? code + 5        /* a-z */
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

priya.naturalICompare = _('prototype').naturalICompare;
priya.naturalCompare = _('prototype').naturalCompare;

/*
 * @name: Next
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Next.prototype.js
 */
_('prototype').next = function (tagName){
    if(!tagName){
        tagName = this.tagName;
    }
    var parent = this.parentNode;
    var index;
    var found;
    for(index = 0; index < parent.childNodes.length; index++){
        var child = parent.childNodes[index];
        if(child.isEqualNode(this)){
            found = true;
            continue;
        }
        if(!empty(found)){
            if(typeof child.tagName == 'undefined'){
                continue;
            }
            if(child.tagName.toLowerCase() == tagName.toLowerCase()){
                found = child;
                break;
            }
        }
    }
    if(found !== true && !empty(found)){
        return attach(found);
    }
}

priya.next = _('prototype').next;

/*
 * @name: Object.delete
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Object.delete.prototype.js
 */
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

/*
 * @name: Object.get
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Object.get.prototype.js
 */
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

/*
 * @name: Object.horizontal
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Object.horizontal.prototype.js
 */
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

/*
 * @name: Object.merge
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Object.merge.prototype.js
 */
_('prototype').object_merge = function (main, merge){
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

priya.object_merge = _('prototype').object_merge;

/*
 * @name: Object.set
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Object.set.prototype.js
 */
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

/*
 * @name: On
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/On.prototype.js
 */
_('prototype').on = function (event, action, capture){
    if(typeof this['Priya'] == 'undefined'){
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

priya.on = _('prototype').on;

/*
 * @name: Previous
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Previous.prototype.js
 */
_('prototype').previous = function (tagName){
    if(!tagName){
        tagName = this.tagName;
    }
    var parent = this.parentNode;
    var index;
    var found;
    var nodeList = parent.childNodes;
    for(index = nodeList.length-1; index >= 0; index--){
        var child = nodeList[index];
        if(child.isEqualNode(this)){
            found = true;
            continue;
        }
        if(!empty(found)){
            if(typeof child.tagName == 'undefined'){
                continue;
            }
            if(child.tagName.toLowerCase() == tagName.toLowerCase()){
                found = child;
                break;
            }
        }
    }
    if(found !== true && !empty(found)){
        return attach(found);
    }
    return null;
}

priya.previous = _('prototype').previous;

/*
 * @name: Rand
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Rand.prototype.js
 */
_('prototype').rand = function (min, max) {
    var argc = arguments.length;
    if (argc === 0) {
        min = 0;
        max = 2147483647;
    }
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

priya.rand = _('prototype').rand;

/*
 * @name: Refresh
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Refresh.prototype.js
 */
_('prototype').refresh = function (data){
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

priya.refresh = _('prototype').refresh;

/*
 * @name: Remove
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Remove.prototype.js
 */
_('prototype').remove = function (){
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

priya.remove = _('prototype').remove;

/*
 * @name: Request
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Request.prototype.js
 */
_('prototype').request = function (url, data, script){
    var core = priya.collect.web.core;
    var request = this;

    var offset = 1.75; //time in seconds before loading starts

    if(typeof url == 'object' && url !== null){
        data = url;
        url = '';
        if (typeof data.altKey != "undefined") {//event
            priya.debug('event');
            var event = data;
            url = request.data('request');
            data = request.data();
            delete data.request;
        }
    }
    if(empty(url)){
        url = request.data('request');
    }
    if(empty(url)){
        return;
    }
    if(empty(data)){
        if(!empty(request.tagName) && request.tagName == 'FORM'){
            data = request.data('serialize');
        } else {
            data = request.data();
        }
    }
    if(empty(data)){
        var type = 'GET';
    }
    else {
        var tmpData = data;
        delete tmpData['mtime'];
        delete tmpData['request'];
        if(empty(tmpData)){
            var type = 'GET';
        } else {
            var type = 'POST';
        }
    }
    //priya.collect.require.toLoad = priya.collect.require.toLoad ? priya.collect.require.toLoad : 0;
    //priya.collect.require.toLoad++;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            if(xhttp.responseText.substr(0, 1) == '{' && xhttp.responseText.substr(-1) == '}'){
                var data = JSON.parse(xhttp.responseText);
                priya.link(data);
                priya.styler(data);
                priya.script(data);
                priya.content(data);
                priya.refresh(data);
                priya.exception(data);
                if(typeof script == 'function'){
                    script(url, data);
                }
            } else {
                priya.debug(xhttp.responseText);
            }
            setTimeout(function(){
                priya.loader('remove');
            }, 500);
            //priya.collect.require.loaded = priya.collect.require.loaded ? priya.collect.require.loaded : 0;
            //priya.collect.require.loaded++;
            //priya.script(script);
        } else {
            if(xhttp.readyState == 0){
                //UNSENT
            }
            else if (xhttp.readyState == 1){
//                    OPENED
            }
            else if(xhttp.readyState == 2){
//                    HEADERS_RECEIVED
            }
            if(xhttp.readyState == 3){
//                  loading
                var start = priya.collection('request.microtime');
                time = microtime(true);
                if(time > (start + offset)){
//                    priya.loader(); //buggy
                }
            }
            if (xhttp.readyState == 4 ){
                //status !- 200
                console.log(xhttp);
            }
        }
    };
    if(type == 'GET'){
        priya.collection('request.microtime', microtime(true));
        xhttp.open("GET", url, true);
        xhttp.setRequestHeader("Content-Type", "application/json");
        xhttp.send();
    } else {
        priya.collection('request.microtime', microtime(true));
        xhttp.open("POST", url, true);
        xhttp.setRequestHeader("Content-Type", "application/json");
        if (typeof JSON.decycle == "function") {
            data = JSON.decycle(data);
        }
        var send = JSON.stringify(data);
        xhttp.send(send);
    }
}

priya.request = _('prototype').request;

/*
 * @name: Round
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Round.prototype.js
 */
_('prototype').round = function (value, precision, mode) {
    var m, f, isHalf, sgn;
    // making sure precision is integer
    precision |= 0
    m = Math.pow(10, precision)
    value *= m
    // sign of the number
    sgn = (value > 0) | -(value < 0)
    isHalf = value % 1 === 0.5 * sgn
    f = Math.floor(value)
    if (isHalf) {
        switch (mode) {
            case 'PHP_ROUND_HALF_DOWN':
                // rounds .5 toward zero
                value = f + (sgn < 0)
            break
            case 'PHP_ROUND_HALF_EVEN':
                // rouds .5 towards the next even integer
                value = f + (f % 2 * sgn)
            break
            case 'PHP_ROUND_HALF_ODD':
                // rounds .5 towards the next odd integer
                value = f + !(f % 2)
                break
            default:
                // rounds .5 away from zero
                value = f + (sgn > 0)
        }
    }
    return (isHalf ? value : Math.round(value)) / m
}

priya.round = _('prototype').round;

/*
 * @name: Run
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Run.prototype.js
 */
_('prototype').run = function (data){
    if(Object.prototype.toString.call(priya) == '[object Function]'){
        var object = this;
    } else {
        var object = priya;
    }
    var require = object.collection('require');
    if(require.toLoad == require.loaded){
        var element = select(data);
        if(element.tagName == 'PRIYA-NODE' || element === false){
            return;
        }
        var request = element.data('request');
        if(!empty(request)){
            return element.request(request);
        }
        if(typeof microtime == 'undefined'){
            priya.expose('prototype');
        }
        element.data('mtime', microtime(true));
        return element;
    } else {
        setTimeout(function(){
            _('prototype').run(data);
        }, 1/30);
    }
}

priya.run = _('prototype').run;

/*
 * @name: Script
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Script.prototype.js
 */
_('prototype').script = function (data, closure){
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

_('prototype').addScriptSrc = function (data){
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

_('prototype').addScriptText = function (data){
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

_('prototype').readTag = function (data){
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

priya.script = _('prototype').script;
priya.addScriptSrc = _('prototype').addScriptSrc;
priya.addScriptText = _('prototype').addScriptText;
priya.readTag = _('prototype').readTag;

/*
 * @name: Scrollbar
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Scrollbar.prototype.js
 */
_('prototype').scrollbar = function(attribute, type){
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
        /*
        other (width without scrollbars)
        return this.data('scrollbar-width', this.scrollWidth);
        */
    }
    if(attribute == 'height'){
        return this.data('scrollbar-height', height);
        /*
        other (height without scrollbars)
        return this.data('scrollbar-height', this.scrollHeight);
        */
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

priya.scrollbar = _('prototype').scrollbar;

/*
 * @name: Select
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Select.prototype.js
 */
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
            var object =  object.attach(call);
            return object;
        }
    }
}

priya.select = _('prototype').select;

/*
 * @name: Str.replace
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Str.replace.prototype.js
 */
_('prototype').str_replace = function (search, replace, subject, count){
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

priya.str_replace = _('prototype').str_replace;

/*
 * @name: Str.stri
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Str.stri.prototype.js
 */
_('prototype').stristr = function (haystack, needle, bool){
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

priya.stristr = _('prototype').stristr;

/*
 * @name: Styler
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Styler.prototype.js
 */
_('prototype').styler = function (data, closure){
    if(typeof data == 'undefined'){
        return;
    }
    if(typeof data == 'string'){
        var data = {
            style : [data]
        };
    }
    if(!isset(data.style)){
            return data;
    }
    var index;
    for(index in data.style){
        if(data.style[index].substr(0, 4) == '&lt;'){
            data.style[index] = data.style[index].toString()
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>');
        }
        var style = {
            "method":"append",
            "target":"head",
            "html":data.style[index]
        };
        this.content(style);
    }
    return this;
}

priya.styler = _('prototype').styler;

/*
 * @name: Trigger
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Trigger.prototype.js
 */
_('prototype').trigger = function (trigger, bubble, cancel){
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
        'bubbles'    : bubble, /* Whether the event will bubble up through the DOM or not */
        'cancelable' : cancel  /* Whether the event may be canceled or not */
    });
    /*event.initEvent(trigger, true, true);*/
    event.synthetic = true;
    if(typeof this.dispatchEvent == 'undefined'){
        if(priya.is_nodelist(this)){
            var index;
            for(index=0; index < this.length; index++){
                node = this[index];
                node.dispatchEvent(event, true);
            }
        } else {
            console.log('dispatch problem');
            console.log(this);
        }
    } else {
        this.dispatchEvent(event, true);
    }
}

priya.trigger = _('prototype').trigger;

/*
 * @name: Trim
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Trim.prototype.js
 */
_('prototype').trim = function(str, charlist){
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

priya.trim = _('prototype').trim;

/*
 * @name: Usleep
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Usleep.prototype.js
 */
_('prototype').usleep = function (msec){
    var start = new Date().getTime();
    var current = new Date().getTime() - start;
    while(current < msec) {
        current = new Date().getTime() - start;
    }
}

priya.usleep = _('prototype').usleep;

/*
 * @name: Val
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Val.prototype.js
 */
_('prototype').val = function (value){
    if(typeof this.value == 'undefined'){
        return false;
    }
    if(typeof value != 'undefined'){
        this.value = value
    }

    return this.value;
}

priya.val = _('prototype').val;

/*
 * @name: Week
 * @url: /mnt/c/Library/Server/Vendor/Priya/Module/Javascript/Public/Prototype/Core/Week.prototype.js
 */
_('prototype').week = function (date) {
    if(typeof date == 'undefined'){
        date = new Date();
    }
    var tdt = new Date(date.valueOf());
    var dayn = (date.getDay() + 6) % 7;
    tdt.setDate(tdt.getDate() - dayn + 3);
    var firstThursday = tdt.valueOf();
    tdt.setMonth(0, 1);
    if (tdt.getDay() !== 4) {
        tdt.setMonth(0, 1 + ((4 - tdt.getDay()) + 7) % 7);
    }
    return 1 + Math.ceil((firstThursday - tdt) / 604800000);
}

priya.week = _('prototype').week;

