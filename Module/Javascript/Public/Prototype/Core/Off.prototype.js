_('prototype').off = function (event, action){
    console.log(this['Priya']['eventListener']);
    this.removeEventListener(event, action)
}

priya.off = _('prototype').off;