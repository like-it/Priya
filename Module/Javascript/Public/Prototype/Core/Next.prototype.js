_('prototype').next = function (tagName){
    if(!tagName){
        tagName = this.tagName;
    }
    var parent = this.parentNode;
    var index;
    var found;
    for(index = 0; index < parent.childNodes.length; index++){
        var child = parent.childNodes[index];
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
}

priya.next = _('prototype').next;