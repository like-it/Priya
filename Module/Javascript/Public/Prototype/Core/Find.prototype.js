_('prototype').find = function(selector, attach) {
    if (!this.id) {
        this.id = this.attribute('id', 'priya-find-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999) + '-' + this.rand(1000, 9999));
        var removeId = true;
    }
    if(typeof selector == 'object'){
        console.log(selector);
    }
    selector = '#' + this.id + ' ' + selector;
    var list = document.querySelectorAll(selector);
    if (removeId) {
        this.attribute('remove', 'id');
    }
    if(attach){
        if(list.length == 0){
            var priya = this.attach(this.create('element', selector));
            priya.data('selector', selector);
            /*add to document for retries? */
            return priya;
        }
        else if(list.length == 1){
            return this.attach(list[0]);
        } else {
            for(item in list){
                list[item] = this.attach(list[item]);
            }
            return this.attach(list);
        }
    }
    return list;
};

priya.find = _('prototype').find;