_('prototype').content = function (data){
    if(Object.prototype.toString.call(priya) != '[object Function]'){
        var priya = window.priya;
    }
    if(typeof data == 'undefined'){
        console.log('json.content failed (data)');
        return;
    }
    if(typeof data['method'] == 'undefined'){
        return;
    }
    if(typeof data['target'] == 'undefined'){
        console.log('json.content failed (target)');
        return;
    }
    if(typeof data['html'] == 'undefined' && (data['method'] != 'replace' && data['method'] != 'unwrap')){
        return;
    }
    var target = priya.select(data['target']);
    var method = data['method'];
    if(this.is_nodeList(target)){
        var i = 0;
        for(i =0; i < target.length; i++){
            var node = target[i];
            if(method == 'replace'){
                node.html(data['html']);
            }
            else if (method == 'replace-with'){
                node.html(data['html'], 'outer');
            }
            else if(method == 'replace-or-append-to-body'){
                if(node.nodeName == 'PRIYA-NODE'){
                    var node = priya.select('body');
                    node.insertAdjacentHTML('beforeend',data['html']);
                } else {
                    node.html(data['html']);
                }
            }
            else if(method == 'replace-with-or-append-to-body'){
                if(node.nodeName == 'PRIYA-NODE'){
                    var node = priya.select('body');
                    node.insertAdjacentHTML('beforeend',data['html']);
                } else {
                    node.html(data['html'], 'outer');
                }
            }
            else if(method == 'append' || method == 'beforeend'){
                node.insertAdjacentHTML('beforeend',data['html']);
            }
            else if(method == 'prepend' || method == 'afterbegin'){
                node.insertAdjacentHTML('afterbegin',data['html']);
            }
            else if(method == 'after' || method == 'afterend'){
                node.insertAdjacentHTML('afterend',data['html']);
            }
            else if(method == 'before' || method == 'beforebegin'){
                node.insertAdjacentHTML('beforebegin', data['html']);
            } else {
                this.exception('write', this.dump('unknown method ('+ method +') in content'));
            }
        }
    } else {
        if(method == 'replace'){
            target.html(data['html']);
        }
        else if(method == 'replace-with'){
            target.html(data['html'], 'outer');
        }
        else if(method == 'replace-or-append-to-body'){
            if(target.nodeName == 'PRIYA-NODE'){
                var target = priya.select('body');
                target.insertAdjacentHTML('beforeend',data['html']);
            } else {
                target.html(data['html']);
            }
        }
        else if(method == 'replace-with-or-append-to-body'){
            if(target.nodeName == 'PRIYA-NODE'){
                var target = priya.select('body');
                target.insertAdjacentHTML('beforeend',data['html']);
            } else {
                target.html(data['html'], 'outer');
            }
        }
        else if(method == 'append' || method == 'beforeend'){
            target.insertAdjacentHTML('beforeend',data['html']);
        }
        else if(method == 'prepend' || method == 'afterbegin'){
            target.insertAdjacentHTML('afterbegin',data['html']);
        }
        else if(method == 'after' || method == 'afterend'){
            target.insertAdjacentHTML('afterend',data['html']);
        }
        else if(method == 'before' || method == 'beforebegin'){
            target.insertAdjacentHTML('beforebegin', data['html']);
        } else {
            this.exception('write', this.dump('unknown method ('+ method +') in content'));
        }
    }
    return target;
}