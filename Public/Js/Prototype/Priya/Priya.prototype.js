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
    this.collection = {};
    this.parent = this;
};

priya.prototype.run = function (data){
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
            this.addClass('display-block overflow-auto');
            result =  this.offsetWidth;
            this.removeClass('display-block overflow-auto');
            return result;
        break;
        case 'height':
            this.addClass('display-block overflow-auto');
            result =  this.offsetHeight;
            this.removeClass('display-block overflow-auto');
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
    } else {
        console.log('node.next() isnt available yet');
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
    } else {
        console.log('node.next() isnt available yet');
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
            } else {
                console.log('error in this');
                console.log(this);
            }
        }
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
    //classname classname = &&
    //classname && classname = &&
    //classname || classname = ||
    //add later () sets
    console.log('dom.hasClass: ' + className);
}

priya.prototype.css = function(attribute, value){
    //write and read to .style.property
    if(this.is_nodeList(this)){
        var index;
        for(index=0; index < this.length; index++){
            var node = this[index];
            node.style[attribute] = value;
        }
    } else {
        this.style[attribute] = value;
    }
    console.log('priya.css: ' + attribute + ' -> ' + value);
}

priya.prototype.val = function (value){
    if(this.isset('value')){
        this.value = value
        return this.value;
    } else {
        return false;
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
    }

    else {
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
        }

    }

}

priya.prototype.request = function (url, data, script){
    if(typeof url == 'object'){
        data = url;
        url = '';
    }
    //add script here to execute script
    if(this.empty(url)){
        url = this.data('request');
    }
    if(this.empty(url)){
        return;
        //error cannot request
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
        console.log(data);
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
}

priya.prototype.exception = function (data){
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
    console.log(exception);
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
    if(!this.isset(temp[1])){
        return;
    }
    var text = this.trim(temp[1]);
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

/**
 * @todo
 * - wrap
 * - unwrap
 */
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
                console.log(node);
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
                console.log('unknown method ('+ method +') in content');
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
            console.log('unknown method ('+ method +') in content');
        }
    }
}

priya.prototype.append = function (node, html){
    console.log('hree');
    console.log(this);
    console.log(node);
//	this.insertAdjacentHTML('beforeend', html);
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
        /*
        var attr = document.createAttribute(attribute);
        attr.value = value;                           // Set the value of the class attribute
        if(typeof this.setAttributeNode == 'function'){
            this.setAttributeNode(attr);
        } else {
//			console.log('attribute: ' + attribute + ' not set to value: ' + value + ' this is probably a list');
        }
        */
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
    console.log('priya.off event:' + event);
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
    console.log('dom.trigger: ' + event);
}

/*
dom.prototype.bind = function (node) {
      var obj = Object.create(this.prototype);
      this.apply(node);
      return obj;
    };
*/

priya.prototype.attach = function (element){
    if(element === null){
        return false;
    }
    if(typeof element != 'object'){
        return false;
    }
    if(typeof element['Priya'] == 'object'){
        return element;
        //make a nice error
        //make instance run log
        var message = 'Priya in:' + element.tagName;
        if(typeof element.id !== 'undefined'){
            message += ' id: ' + element.id;
        }
        if(typeof element.className != 'undefined'){
            message += ' class: ' + element.className;
        }
        console.log(message);
        console.log(element.Priya);
        return element;
    }
    var dom;
    if(this.isDom === true){
        dom = this;
    }
    else if(typeof this['Priya'] == 'undefined'){
        dom = this;
//		console.log(dom);
    }
    else if(typeof this['Priya']['dom'] == 'object'){
        dom = this['Priya']['dom'];
    } else {
        console.log('unexpected dom in attach');
        console.log(this);
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
            "mTime": this.microtime(true),
            "dom" : dom
    };
    element.data('mTime', element['Priya']['mTime']);
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
    console.log('dom init');
    console.log(data);
    return data;
}

priya.prototype.empty = function (mixed_var){
    var key;
     if (mixed_var === "" || mixed_var === 0 || mixed_var === "0" || mixed_var === null || mixed_var === false || typeof mixed_var === 'undefined') {
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

priya.prototype.is_numeric = function (mixed_var){
    var whitespace =
        " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (
        typeof mixed_var === 'number' ||
            (typeof mixed_var === 'string' &&
                    whitespace.indexOf(mixed_var.slice(-1)) === -1)
            ) &&
        mixed_var !== '' && !isNaN(mixed_var);
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
        var result = 'object';
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

priya.prototype.is_nodeList = function (nodes){
    var stringRepr = Object.prototype.toString.call(nodes);

    return typeof nodes === 'object' &&
        /^\[object (HTMLCollection|NodeList|Object)\]$/.test(stringRepr) &&
        (typeof nodes.length === 'number') &&
        (nodes.length === 0 || (typeof nodes[0] === "object" && nodes[0].nodeType > 0));
}
