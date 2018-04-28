_('prototype').previous = function (tagName){
    if(!tagName){
        tagName = this.tagName;
    }
    var parent = this.parentNode;
    var index;
    var found;
    var nodeList = parent.childNodes;
    for(index = nodeList.length-1; index >= 0; index--){
        var child = nodeList[index];
        if(child.isEqualNode(this)){
            found = true;
            continue;
        }
        if(!empty(found)){
            if(typeof child.tagName == 'undefined'){
                continue;
            }
            if(child.tagName.toLowerCase() == tagName.toLowerCase()){
                found = child;
                break;
            }
        }
    }
    if(found !== true && !empty(found)){
        return attach(found);
    }
    return null;
}

priya.previous = _('prototype').previous;