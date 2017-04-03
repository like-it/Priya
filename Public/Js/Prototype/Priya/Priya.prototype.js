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
 *  -	all
 */

var priya = function (id){
    this.collect = {};
    this.parent = this;
};

priya.prototype.run = function (data){
    var element = this.dom(data);
    var request = element.data('request');
    if(!this.empty(request)){
        return element.request(request);
    }
    element.data('mtime', this.microtime(true));
    return element;
}

priya.prototype.dom = function(data){
    return this.init(data);
}

priya.prototype.select = function(selector){
    if(typeof selector == 'undefined' || selector === null){
        return false;
    }
    if(typeof this['Priya'] != 'undefined'){
        if(typeof this['Priya']['dom'] != 'undefined'){
            if(typeof this['Priya']['dom']['selected'] == 'undefined'){
                this['Priya']['dom']['selected'] = {};
            }
            var selected;
            if(typeof selector == 'object'){
                selected = selector.tagName;
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
        var oldSelector = this.trim(selector);
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
            var list = this.querySelectorAll(matchSelector);
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
                var list = this.querySelectorAll(selector);
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

priya.prototype.calculate = function (calculate){
    var result = null;
    switch(calculate){
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

priya.prototype.html = function (html, where){
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
                this.outerHTML = html;
                return this.outerHTML;
            } else {
                this.innerHTML = html;
                return this.innerHTML;
            }
        }
    }
}

priya.prototype.closest = function (selector, node){
    if(typeof node === false){
        return false;
    }
    var parent;
    if(typeof node == 'undefined'){
        parent = this.parent();
    } else {
        parent = node.parent();
    }
    var select = parent.select(selector);
    if(typeof select == 'object' && select.tagName == 'PRIYA-NODE'){
        delete select;
        select = parent.closest(selector, parent);
    }
    if(select === false){
        select = parent.closest(selector, parent);
    }
    return select;
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
        case 'element':
            var element = document.createElement('PRIYA-NODE');
            element.id = 'className';
            element.className = this.str_replace('.', ' ', create);
            element.className = this.str_replace('#', '', element.className);
            return element;
        break;
        case 'nodelist' :
              var fragment = document.createDocumentFragment();
              if(Object.prototype.toString.call( create ) === '[object Array]'){
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
            console.log('type (' +  type + ') note defined in priya.create()');
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

priya.prototype.css = function(attribute, value){
    if(this.empty(value)){
        if(typeof this.style == 'undefined'){
            return '';
        }
        if(this.empty(this.style[attribute])){
            return '';
        }
        return this.style[attribute];
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
    if(!this.empty(value)){
        this.value = value
        return this.value;
    } else {
        if(typeof this.value == 'undefined'){
            return false;
        }
        return this.value;
    }
}

priya.prototype.data = function (attribute, value){
    if(attribute == 'remove'){
        return this.attribute('remove','data-' + value);
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
        } else {
            return this.attribute('data-' + attribute, value);
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

priya.prototype.request = function (url, data, script){
    if(typeof url == 'object'){
        data = url;
        url = '';
    }
    if(this.empty(url)){
        url = this.data('request');
    }
    if(this.empty(url)){
        return;
    }
    if(this.empty(data)){
        data = this.data();
    }
    if(this.empty(data)){
        var type = 'GET';
    }
    else {
        var tmpData = data;
        delete tmpData['mtime'];
        delete tmpData['request'];
        if(this.empty(tmpData)){
            var type = 'GET';
        } else {
            var type = 'POST';
        }
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            var data = JSON.parse(xhttp.responseText);
            priya.link(data);
            priya.script(data);
            priya.content(data);
            priya.refresh(data);
            priya.exception(data);
        }
    };
    if(type == 'GET'){
        xhttp.open("GET", url, true);
        xhttp.setRequestHeader("Content-Type", "application/json");
        xhttp.send();
    } else {
        xhttp.open("POST", url, true);
        xhttp.setRequestHeader("Content-Type", "application/json");
        var send = JSON.stringify(data);
        xhttp.send(send);
    }
    priya.script(script);
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

priya.prototype.link = function (data){
    if(typeof data == 'undefined'){
        return;
    }
    if(!this.isset(data.link)){
        return data;
    }
    var index;
    for(index in data.link){
        var link = {
            "method":"append",
            "target":"head",
            "html":data.link[index]
        };
        this.content(link);
    }
    return this;
}

priya.prototype.script = function (data){
    if(typeof data == 'undefined'){
        return;
    }
    if(!this.isset(data.script)){
        return data;
    }
    var index;
    for(index in data.script){
        this.addScriptSrc(data.script[index]);
        this.addScriptText(data.script[index]);
    }
    return this;
}

priya.prototype.exception = function (data, except){
    if(data == 'write' || data == 'replace'){
        var exception = this.dom('.exception');
        var content = {
            "target": ".exception",
            "method":"replace",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
    }
    else if(data == 'append'){
        var exception = this.dom('.exception');
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
        var exception = this.dom('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ JSON.stringify(data, null, 4) +"</pre>"
        }
        exception.content(content);
    }
}

priya.prototype.addScriptSrc = function (data){
    var tag = this.readTag(data)
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
    temp = this.explode(' ', this.trim(data));
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

priya.prototype.content = function (data){
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
    var target = priya.dom(data['target']);
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

priya.prototype.attribute = function (attribute, value){
    if(attribute == 'remove'){
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
            this.setAttribute(attribute, value);
        }
        return value;
    }
}

priya.prototype.on = function (event, action){
    if(typeof this['Priya']['eventListener'] != 'object'){
        this['Priya']['eventListener'] = {};
    }
    if(typeof this['Priya']['eventListener'][event] == 'undefined'){
        this['Priya']['eventListener'][event] = new Array();
    }
    this['Priya']['eventListener'][event].push(action);
    if(this.is_nodeList(this)){
        var index;
        for (index=0; index < this.length; index++){
            var node = this[index];
            node.addEventListener(event, action);
        }
    } else {
        this.addEventListener(event, action);
    }
    return this;
}

priya.prototype.off = function (event, action){
    this.removeEventListener(event, action)
}

priya.prototype.trigger = function (trigger){
    var event = new Event(trigger, {
        'bubbles'    : true, // Whether the event will bubble up through the DOM or not
        'cancelable' : true  // Whether the event may be canceled or not
    });
    event.initEvent(trigger, true, true);
    event.synthetic = true;
    this.dispatchEvent(event, true);
}

priya.prototype.attach = function (element){
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
    for(property in dom){
        if(typeof dom[property] != 'function'){
            continue;
        }
        if(property == 'parentNode'){
            continue;
        }
        element[property] = dom[property].bind(element);
    }
    element['parent'] = dom['parentNode'].bind(element);
    element['Priya'] = {
            "version": '0.0.1',
            "mtime": this.microtime(true),
            "dom" : dom
    };
    element.data('mtime', element['Priya']['mtime']);
    return element;
}

priya.prototype.init = function (data, configuration){
    if(typeof data == 'undefined'){
        return this;
    }
    if(typeof data == 'string'){
        var element = this.select(data);
        return element;
    }
    return data;
}

priya.prototype.collection = function (attribute, value){
    if(typeof attribute != 'undefined'){
        if(typeof value != 'undefined'){
            if(attribute == 'delete'){
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
    return this.object_delete(attribute, this.collection);
}

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
    for (key in merge){
        var value = merge[key];
        if(!this.isset(main[key])){
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

priya.prototype.is_nodeList = function (nodes){
    var stringRepr = Object.prototype.toString.call(nodes);

    return typeof nodes === 'object' &&
        /^\[object (HTMLCollection|NodeList|Object)\]$/.test(stringRepr) &&
        (typeof nodes.length === 'number') &&
        (nodes.length === 0 || (typeof nodes[0] === "object" && nodes[0].nodeType > 0));
}

priya.prototype.dump = function () {
    var output = ''
    var padChar = ' '
    var padVal = 4
    var lgth = 0
    var i = 0
    var _getFuncName = function (fn) {
      var name = (/\W*function\s+([\w$]+)\s*\(/)
        .exec(fn)
      if (!name) {
        return '(Anonymous)'
      }
      return name[1]
    }
    var _repeatChar = function (len, padChar) {
      var str = ''
      for (var i = 0; i < len; i++) {
        str += padChar
      }
      return str
    }
    var _getInnerVal = function (val, thickPad) {
      var ret = ''
      if (val === null) {
        ret = 'NULL'
      } else if (typeof val === 'boolean') {
        ret = 'bool(' + val + ')'
      } else if (typeof val === 'string') {
        ret = 'string(' + val.length + ') "' + val + '"'
      } else if (typeof val === 'number') {
        if (parseFloat(val) === parseInt(val, 10)) {
          ret = 'int(' + val + ')'
        } else {
          ret = 'float(' + val + ')'
        }
      } else if (typeof val === 'undefined') {
        // The remaining are not PHP behavior because these values
        // only exist in this exact form in JavaScript
        ret = 'undefined'
      } else if (typeof val === 'function') {
        var funcLines = val.toString()
          .split('\n')
        ret = ''
        for (var i = 0, fll = funcLines.length; i < fll; i++) {
          ret += (i !== 0 ? '\n' + thickPad : '') + funcLines[i]
        }
      } else if (val instanceof Date) {
        ret = 'Date(' + val + ')'
      } else if (val instanceof RegExp) {
        ret = 'RegExp(' + val + ')'
      } else if (val.nodeName) {
        // Different than PHP's DOMElement
        switch (val.nodeType) {
          case 1:
            if (typeof val.namespaceURI === 'undefined' ||
              val.namespaceURI === 'http://www.w3.org/1999/xhtml') {
            // Undefined namespace could be plain XML, but namespaceURI not widely supported
              ret = 'HTMLElement("' + val.nodeName + '")'
            } else {
              ret = 'XML Element("' + val.nodeName + '")'
            }
            break
          case 2:
            ret = 'ATTRIBUTE_NODE(' + val.nodeName + ')'
            break
          case 3:
            ret = 'TEXT_NODE(' + val.nodeValue + ')'
            break
          case 4:
            ret = 'CDATA_SECTION_NODE(' + val.nodeValue + ')'
            break
          case 5:
            ret = 'ENTITY_REFERENCE_NODE'
            break
          case 6:
            ret = 'ENTITY_NODE'
            break
          case 7:
            ret = 'PROCESSING_INSTRUCTION_NODE(' + val.nodeName + ':' + val.nodeValue + ')'
            break
          case 8:
            ret = 'COMMENT_NODE(' + val.nodeValue + ')'
            break
          case 9:
            ret = 'DOCUMENT_NODE'
            break
          case 10:
            ret = 'DOCUMENT_TYPE_NODE'
            break
          case 11:
            ret = 'DOCUMENT_FRAGMENT_NODE'
            break
          case 12:
            ret = 'NOTATION_NODE'
            break
        }
      }
      return ret
    }
    var _formatArray = function (obj, curDepth, padVal, padChar) {
      if (curDepth > 0) {
        curDepth++
      }
      var basePad = _repeatChar(padVal * (curDepth - 1), padChar)
      var thickPad = _repeatChar(padVal * (curDepth + 1), padChar)
      var str = ''
      var val = ''
      if (typeof obj === 'object' && obj !== null) {
        if (obj.constructor && _getFuncName(obj.constructor) === 'LOCUTUS_Resource') {
          return obj.var_dump()
        }
        lgth = 0
        for (var someProp in obj) {
          if (obj.hasOwnProperty(someProp)) {
            lgth++
          }
        }
        str += 'array(' + lgth + ') {\n'
        for (var key in obj) {
          var objVal = obj[key]
          if (typeof objVal === 'object' &&
            objVal !== null &&
            !(objVal instanceof Date) &&
            !(objVal instanceof RegExp) &&
            !objVal.nodeName) {
            str += thickPad
            str += '['
            str += key
            str += '] =>\n'
            str += thickPad
            str += _formatArray(objVal, curDepth + 1, padVal, padChar)
          } else {
            val = _getInnerVal(objVal, thickPad)
            str += thickPad
            str += '['
            str += key
            str += '] =>\n'
            str += thickPad
            str += val
            str += '\n'
          }
        }
        str += basePad + '}\n'
      } else {
        str = _getInnerVal(obj, thickPad)
      }
      return str
    }
    output = _formatArray(arguments[0], 0, padVal, padChar)
    for (i = 1; i < arguments.length; i++) {
      output += '\n' + _formatArray(arguments[i], 0, padVal, padChar)
    }
    // Not how PHP does it, but helps us test:
    return output
  }
