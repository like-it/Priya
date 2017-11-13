priya.next = function (node){
    if(typeof node == 'undefined'){
        var parent = this.parent();
        var index;
        var found;
        for(index = 0; index < parent.childNodes.length; index++){
            var child = parent.childNodes[index];
            if(child.isEqualNode(this)){
                found = true;
                continue;
            }
            if(!empty(found) && child.tagName == this.tagName){
                found = child;
                break;
            }
        }
        if(found !== true && !empty(found)){
            return this.select(found);
        }
    }
}