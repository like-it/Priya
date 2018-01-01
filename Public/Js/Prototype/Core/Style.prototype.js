priya.style = function (data, closure){
    if(typeof data == 'undefined'){
        return;
    }
    if(typeof data == 'string'){
        var data = {
            style : [data]
        };
    }
    if(!this.isset(data.style)){
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