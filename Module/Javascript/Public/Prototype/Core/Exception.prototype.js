priya.exception = function (data, except){
    if(data == 'write' || data == 'replace'){
        this.debug(except);
        /*
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"replace",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
        */
    }
    else if(data == 'append'){
        this.debug(except);
        /*
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
        */
    }
    else {
        var index;
        var found = false;
        for (index in data){
            if(this.stristr(index,'\\exception')){
                found = true;
                data = data[index];
            }
        }
        if(this.empty(found)){
            return;
        }
        this.debug(JSON.stringify(data, null, 2));
        /*
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ JSON.stringify(data, null, 4) +"</pre>"
        }
        exception.content(content);
        */
    }
}

/*
priya.exception = function (data, except){
    if(data == 'write' || data == 'replace'){
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"replace",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
    }
    else if(data == 'append'){
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ except +"</pre>"
        }
        exception.content(content);
    }
    else {
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
        var exception = this.select('.exception');
        var content = {
            "target": ".exception",
            "method":"append",
            "html":"<pre>"+ JSON.stringify(data, null, 4) +"</pre>"
        }
        exception.content(content);
    }
}
*/