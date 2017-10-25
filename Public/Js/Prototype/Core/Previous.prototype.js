priya.previous = function (node){
    if(typeof node == 'undefined'){
        var parent = this.parent();
        var index;
        var found;
        var nodeList = parent.childNodes;

        for(index = nodeList.length-1; index > 0; index--){
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