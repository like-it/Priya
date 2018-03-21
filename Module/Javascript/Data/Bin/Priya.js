(function(funcName, baseObj) {
    funcName = funcName || "ready";
    baseObj = baseObj || window;
    var readyList = [];
    var readyFired = false;
    var readyEventHandlersInstalled = false;

    function ready() {
        //need to add .wait or similar for all includes
        if(typeof priya == 'undefined'){
            setTimeout(ready, 1/30);
            return;
        }
        var require = {};
        if(typeof priya.collect != 'undefined' && typeof priya.collect.require == 'object'){
            require = priya.collect.require;
        }
        if(require.toLoad != require.loaded){
            setTimeout(ready, 1/30);
            return;
        }
        if (!readyFired) {
            readyFired = true;
            for (var i = 0; i < readyList.length; i++) {
                readyList[i].fn.call(window, readyList[i].ctx);
            }
            readyList = [];
        }
    }

    function readyStateChange() {
        if ( document.readyState === "complete" ) {
            ready();
        }
    }
    baseObj[funcName] = function(callback, context) {
        if (readyFired) {
            setTimeout(function() {callback(context);}, 1);
            return;
        } else {
            readyList.push({fn: callback, ctx: context});
        }
        if (document.readyState === "complete") {
            setTimeout(ready, 1);
        } else if (!readyEventHandlersInstalled) {
            if (document.addEventListener) {
                document.addEventListener("DOMContentLoaded", ready, false);
                window.addEventListener("load", ready, false);
            } else {
                document.attachEvent("onreadystatechange", readyStateChange);
                window.attachEvent("onload", ready);
            }
            readyEventHandlersInstalled = true;
        }
    }
})("ready", window);

function include (url){
    var element = document.createElement('script');
    element.setAttribute('type', 'text/javascript');
    element.setAttribute('src', url);
    element.setAttribute('defer','defer');
    var result = document.querySelectorAll('head');
    if(result.length==0){
        console.log('Error: no <head> found in document');
        return;
    }
    result[0].appendChild(element);
    return element;
}


var priya;
(function(){
    if(typeof $ != 'undefined'){ //so it also works with jquery...
        $.holdReady(true);
    }
    var parent = this || {};
    parent.status = {};
    var url = window.location.protocol + '//' + window.location.host + '/';

    var scripts = document.getElementsByTagName('script');
    var index;
    var src = '';
    for(index=0; index < scripts.length; index++){
        var script = scripts[index];
        if(script.src.toLowerCase().indexOf('priya') !== -1){
            var src = script.src;
            break;
        }
    }
    src = src.split('/');
    src.pop();
    src = src.join('/') + '/';
    bin = src + 'Bin/';
    var node = include(bin + '/Priya.prototype.js');
    node.addEventListener('load', function(event){
        priya = new priya({
            "url" : url,
            "bin" : bin,
            "priya" : src
        });
    }, false);
})();