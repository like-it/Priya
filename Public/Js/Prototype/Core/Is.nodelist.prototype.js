_('prototype').is_nodeList = function (nodes){
    if(typeof nodes == 'undefined'){
        nodes = this;
    }
    var stringRepr = Object.prototype.toString.call(nodes);

    return typeof nodes === 'object' &&
        /^\[object (HTMLCollection|NodeList|Object)\]$/.test(stringRepr) &&
        (typeof nodes.length === 'number') &&
        (nodes.length === 0 || (typeof nodes[0] === "object" && nodes[0].nodeType > 0));
}

priya.is_nodeList = _('prototype').is_nodeList;