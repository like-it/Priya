_('prototype').attach = function(element){
    if(element.tagName == 'PRIYA-NODE'){
        console.log(element);
    }

    if(element === null){
        return false;
    }
    if(typeof element != 'object'){
        return false;
    }
    if(typeof element['Priya'] == 'object'){
        return element;
    }
    var dom;
    if(this.isDom === true){
        dom = this;
    }
    else if(typeof this['Priya'] == 'undefined'){
        dom = this;
    }
    else if(typeof this['Priya']['dom'] == 'object'){
        dom = this['Priya']['dom'];
    } else {
        dom = this;
    }
    for(property in dom){
        if(typeof dom[property] != 'function'){
            continue;
        }
        if(property == 'parentNode'){
            continue;
        }
        if(property == 'closest'){
            console.log('bind closest');
        }
        element[property] = dom[property].bind(element);
    }
    element['parent'] = dom['parentNode'].bind(element);
    element['Priya'] = {
            "version": '0.0.1',
            "dom" : dom
    };
    if(typeof element.tagName != 'undefined'){
        if(typeof this.microtime == 'function' && typeof element.data == 'function'){
            element.data('mtime', this.microtime(true));
        }
    }
    return element;
}

priya.attach = _('prototype').attach;