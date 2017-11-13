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