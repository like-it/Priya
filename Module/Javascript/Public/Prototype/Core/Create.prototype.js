_('prototype').create = function (type, create){
    if(typeof type.toLowerCase != 'function'){
        console.log('no function toLowerCase______________________________________________');
        console.log(type);
        console.log(create);
        console.log(_('prototype').create.caller);
        return;
    }
    switch(type.toLowerCase()){
        case 'id':
            if(typeof create == 'undefined'){
                create = 'priya';
            }
            var data = priya.collection('id.' + create);
            if(this.empty(data)){
                data = [];
            }
            var id = 1;
            var index;
            for(index =0; index < data.length; index++){
                if(index >= id){
                    id = index + 1;
                }
            }
            data[id] = {"id": create + '-' + id};
            priya.collection('id.' + create, data);
            return create + '-' + id;
        break;
        case 'link':
            var element = document.createElement('LINK');
            element.rel = 'stylesheet';
            if(typeof create == 'string'){
                element.href = create;
            } else {
                alert('todo');
            }
            return element;
        break;
        case 'script':
            var element = document.createElement('SCRIPT');
            element.type = 'text/javascript';
            if(typeof create == 'string'){
                element.src = create;
            } else {
                alert('todo');
            }
            return element;
        break;
        case 'element':
            var element = document.createElement('PRIYA-NODE');
            element.className = this.str_replace('.', ' ', create);
            element.className = this.str_replace('#', '', element.className);
            element.className = this.trim(element.className);
            return this.attach(element);
        break;
        case 'nodelist' :
              var fragment = document.createDocumentFragment();
              if(Object.prototype.toString.call(create) === '[object Array]'){
                  var i;
                  for(i=0; i < create.length; i++){
                      fragment.appendChild(create[i]);
                  }
                  fragment.childNodes.item = false;
              }
              else if (typeof create == 'object'){
                  fragment.appendChild(create);
                  fragment.childNodes.item = false;
              }
              else if (typeof create != 'undefined'){
                  console.log('unknown type (' + typeof create + ') in priya.create()');
              }
              return fragment.childNodes;
        break;
        default :
            var element = document.createElement(type.toUpperCase());
            if(create){
                element.className = this.str_replace('.', ' ', create);
                element.className = this.str_replace('#', '', element.className);
                element.className = this.trim(element.className);
            }
            return this.attach(element);
    }
    return false;
}

priya.create = _('prototype').create;