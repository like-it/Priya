_('prototype').off = function (event, action){
    console.log(this['Priya']['eventListener']); //need to remove it from the eventListener
    this.removeEventListener(event, action)
}

priya.off = _('prototype').off;