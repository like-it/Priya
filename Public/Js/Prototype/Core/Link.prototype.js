priya.link = function (data, closure){
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
        data.addEventListener('loadend', function(event){
            priya.load--;
        }, false);
        if(closure){
            data.addEventListener('loadend', function(event){
                console.log('loadend');
                closure();
            }, false);
            data.addEventListener('load', function(event){
                console.log('load');
            }, false);
            data.addEventListener('error', function(event){
                console.log('error');
            }, false);
            data.addEventListener('progress', function(event){
                console.log('progress');
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