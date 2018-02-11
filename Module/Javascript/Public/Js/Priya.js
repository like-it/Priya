(function(funcName, baseObj) {
    // The public function name defaults to window.docReady
    // but you can pass in your own object and own function name and those will be used
    // if you want to put them in a different namespace
    funcName = funcName || "ready";
    baseObj = baseObj || window;
    var readyList = [];
    var readyFired = false;
    var readyEventHandlersInstalled = false;

    // call this when the document & priya is ready
    // this function protects itself against being called more than once
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
            // this must be set to true before we start calling callbacks
            readyFired = true;
            for (var i = 0; i < readyList.length; i++) {
                // if a callback here happens to add new ready handlers,
                // the docReady() function will see that it already fired
                // and will schedule the callback to run right after
                // this event loop finishes so all handlers will still execute
                // in order and no new ones will be added to the readyList
                // while we are processing the list
                readyList[i].fn.call(window, readyList[i].ctx);
            }
            // allow any closures held by these functions to free
            readyList = [];
        }
    }

    function readyStateChange() {
        if ( document.readyState === "complete" ) {
            ready();
        }
    }

    // This is the one public interface
    // docReady(fn, context);
    // the context argument is optional - if present, it will be passed
    // as an argument to the callback
    baseObj[funcName] = function(callback, context) {
        // if ready has already fired, then just schedule the callback
        // to fire asynchronously, but right away
        if (readyFired) {
            setTimeout(function() {callback(context);}, 1);
            return;
        } else {
            // add the function and context to the list
            readyList.push({fn: callback, ctx: context});
        }
        // if document already ready to go, schedule the ready function to run
        if (document.readyState === "complete") {
            setTimeout(ready, 1);
        } else if (!readyEventHandlersInstalled) {
            // otherwise if we don't have event handlers installed, install them
            if (document.addEventListener) {
                // first choice is DOMContentLoaded event
                document.addEventListener("DOMContentLoaded", ready, false);
                // backup is window load event
                window.addEventListener("load", ready, false);
            } else {
                // must be IE
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
    if(typeof $ != 'undefined'){ //so it works also with jquery...
        $.holdReady(true);
    }
    var parent = this || {};
    parent.status = {};
    var web = window.location.protocol + '//' + window.location.host + '/';
    var node = include(web + 'Priya/Module/Javascript/Public/Prototype/Priya/Priya.prototype.js');
    node.addEventListener('load', function(event){
        priya = new priya(web);
        //ready();
        /*
        var require = priya.collection('require');
        if(require.toLoad != require.loaded){
            setTimeout(function(){
                priya.wait();
            }, 1/30);
        }
        */
    }, false);
})();