priya.css = function(attribute, value){
    if(empty(value)){
        if(typeof this.style == 'undefined'){
            return '';
        }
        return this.computedStyle(attribute);
    }
    if(typeof this.style == 'undefined'){
        return '';
    }
    if(attribute == 'has'){
        return !!this.style[value];
    }
    else if(attribute == 'delete'){
        this.style[value] = '';
    }
    if(this.is_nodeList(this)){
        var index;
        for(index=0; index < this.length; index++){
            var node = this[index];
            value = node.computeStyle(attribute, value);
            node.style[attribute] = value;
        }
    } else {
        value = this.computeStyle(attribute, value);
        this.style[attribute] = value;
    }
}

priya.computeStyle = function(attribute, value){
    if(attribute == 'top' && value=='middle'){
        var height = parseInt(this.css('height'));
        value = 'calc(50% - ' + (height /2) + 'px)';
    }
    else if(attribute == 'left' && value=='center'){
        var width = parseInt(this.css('width'));
        value = 'calc(50% - ' + (width /2) + 'px)';
    }
    return value;
}

priya.computedStyle = function(attribute){
    if(!this.Priya.style){
        this.Priya.style = window.getComputedStyle(this);
    }
    if(attribute){
        return this.Priya.style[attribute];
    } else {
        return this.Priya.style;
    }
}
