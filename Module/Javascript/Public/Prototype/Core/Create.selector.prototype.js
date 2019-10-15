_('prototype').createSelector = function(element){
    if(this.empty(element)){
        return '';
    }
    selector = element.tagName;
    if(typeof selector == 'undefined' && element instanceof HTMLDocument){
        /*
        var priya = this.attach(this.create('element', selector));
        priya.data('selector', selector);
        */
        return element;
    }
    selector = selector.toLowerCase();
    if(element.id){
        selector += ' #' + element.id;
    }
    if(element.className){
        selector += ' .' + this.str_replace(' ','.',element.className);
    }
    return selector;
}

priya.createSelector = _('prototype').createSelector;