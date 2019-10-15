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

var priya = function (collection){
    this.version = '0.3.4';
    this.collect = {};
    this.parent = this;
    this.load = 0;
    this.hit = 0;
    this.collect.url = collection.url;
    this.collect.web = {};
    this.collect.web.root = collection.url;
    this.collect.web.bin = collection.bin;
    this.collect.web.priya = collection.priya;
    this.get(this.collect.web.bin + 'Bootstrap.json?' + this.version, function(url, data){
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
        /* require needs this */
        priya.expose();
        priya.collect.data.loaded = 1;
        priya.collect.require.loaded--;
        priya.collect.data.file = [];
        priya.collect.data.file.push(url);

        if(data.require.core){
            var load = [];
            var index;
            for(index=0; index < data.require.core.length; index++){
            	load.push(priya.collect.web.priya + data.require.core[index]);
            }
            require(load, function(){
                priya.expose('window');
            });
        }
    });
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
         file.push(this.getAttribute('src'));
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
                if(priya.collect.require.loaded == priya.collect.require.toLoad){
                    closure();
                    return true;

                }
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
            "dom" : dom
    };
    if(typeof element.tagName != 'undefined'){
        if(typeof this.microtime == 'function' && typeof element.data == 'function'){
            element.data('mtime', this.microtime(true));
        }
    }
    return element;
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
