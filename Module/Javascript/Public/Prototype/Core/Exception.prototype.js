_('prototype').exception = function (data, except){
    if(data == 'write' || data == 'replace'){
        this.debug(except);
    }
    else if(data == 'append'){
        this.debug(except);
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
    }
}

priya.exception = _('prototype').exception;