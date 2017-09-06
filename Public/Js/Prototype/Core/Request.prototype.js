_('prototype').request = function (url, data, script){
    var core = priya.collect.dir.core;
    require([
        core + 'Empty.prototype.js',
        core + 'Data.prototype.js',
        core + 'Link.prototype.js',
        core + 'Script.prototype.js',
        core + 'Content.prototype.js',
        core + 'Refresh.prototype.js'
    ], function(){
          //priya.collect = priya.object_merge(priya.collect, data);
    });
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
    console.log('REQUEST !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
    if(typeof url == 'object' && url !== null){
        data = url;
        console.log(url);
        console.log(typeof null);
        console.log(typeof url);
        url = '';
        console.log(data);
        if (typeof data.altKey != "undefined") {//event
            priya.debug('event');
            var event = data;
            url = this.data('request');
            data = this.data();
            delete data.request;
        }
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
    //priya.collect.require.toLoad = priya.collect.require.toLoad ? priya.collect.require.toLoad : 0;
    //priya.collect.require.toLoad++;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            if(xhttp.responseText.substr(0, 1) == '{' && xhttp.responseText.substr(-1) == '}'){
                var data = JSON.parse(xhttp.responseText);
                priya.link(data);
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
            //priya.collect.require.loaded = priya.collect.require.loaded ? priya.collect.require.loaded : 0;
            //priya.collect.require.loaded++;
            //priya.script(script);
        } else {
            if (xhttp.readyState == 4 ){
                console.log(xhttp);
                //priya.collect.require.loaded = priya.collect.require.loaded ? priya.collect.require.loaded : 0;
                //priya.collect.require.loaded++;
            }
        }
    };
    if(type == 'GET'){
        xhttp.open("GET", url, true);
        xhttp.setRequestHeader("Content-Type", "application/json");
        xhttp.send();
    } else {
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