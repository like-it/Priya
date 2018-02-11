_('prototype').request = function (url, data, script){
    var core = priya.collect.web.core;
    var request = this;

    var offset = 1.75; //time in seconds before loading starts

    if(typeof url == 'object' && url !== null){
        data = url;
        console.log(url);
        console.log(typeof null);
        console.log(typeof url);
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
                time = microtime();
                if(time > (start + offset)){
                    priya.loader();
                }

            }
            if (xhttp.readyState == 4 ){
                //status !- 200
                console.log(xhttp);
                //priya.collect.require.loaded = priya.collect.require.loaded ? priya.collect.require.loaded : 0;
                //priya.collect.require.loaded++;
            }
        }
    };
    if(type == 'GET'){
        priya.collection('request.microtime', microtime());
        xhttp.open("GET", url, true);
        xhttp.setRequestHeader("Content-Type", "application/json");

        xhttp.send();
    } else {
        priya.collection('request.microtime', microtime());
        xhttp.open("POST", url, true);
        xhttp.setRequestHeader("Content-Type", "application/json");
        if (typeof JSON.decycle == "function") {
            data = JSON.decycle(data);
        }
        var send = JSON.stringify(data);
        xhttp.send(send);

    }
    /*
     * requires:
     * - data
     * - empty
     * link
     * script,
     * content
     * refresh
     * exception
     * debug
     */
}

priya.request = _('prototype').request;