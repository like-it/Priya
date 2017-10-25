priya.script = function (data, closure){
    if(typeof data == 'undefined'){
        return;
    }
    if(this.isset(data.src) && this.isset(data.type) && data.type == 'text/javascript'){
        priya.select('head').appendChild(data);
        priya.load++;
        data.addEventListener('load', function(event){
            priya.load--;
        }, false);
        if(closure){
            data.addEventListener('load', function(event){
                closure();
            }, false);
        }
        return data;
    }
    if(typeof attempt == 'undefined'){
        attempt = 0;
    }
    if(parseInt(priya.load) != 0 && attempt < 500){
        setTimeout(function(){
            priya.script(data, ++attempt);
            priya.hit++;
            priya.debug('waiting on load...');
        }, parseFloat(1/30));
        return data;
    }
    if(!this.isset(data.script)){
        return data;
    }
    var index;
    for(index in data.script){
        if(data.script[index].substr(0, 4) == '&lt;'){
            data.script[index] = data.script[index].toString()
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>');
        }
        this.addScriptSrc(data.script[index]);
        this.addScriptText(data.script[index]);
    }
    return this;
}

priya.addScriptSrc = function (data){
    var tag = this.readTag(data);
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

priya.addScriptText = function (data){
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

priya.readTag = function (data){
    var temp = this.explode('>', this.trim(data));
    temp = this.explode(' ', this.trim(temp[0]));
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