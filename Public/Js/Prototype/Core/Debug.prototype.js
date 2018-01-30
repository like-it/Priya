_('prototype').debug = function (data){
    var string = 'Loading Debug...';
    var core = priya.collect.web.core;
    require([
        core + 'AddClass.prototype.js',
        core + 'On.prototype.js',
        core + 'Closest.prototype.js',
        core + 'Select.prototype.js',
        core + 'Content.prototype.js',
        core + 'Run.prototype.js'
    ], function(){
        var node = run('.debug');
        if(!node){
            var node = priya.create('div', 'dialog no-select debug');
            node.html('<div class="head"><i class="icon icon-bug"></i><h2>Debug</h2></div><div class="menu"><ul class="tab-head"><li class="tab-debug selected"><p>Debug</p></li><li class="tab-collection"><p>Collection</p></li><li class="tab-session"><p>Session</p></li></ul></div><div class="body"><div class="tab tab-body tab-debug selected"></div><div class="tab tab-body tab-collection"></div><div class="tab tab-body tab-session"></div></div><div class="footer"><button type="button" class="button-default button-close">Close</button><button type="button" class="button-default button-debug-clear"><i class="icon-trash"></i></button></div></div>');
            priya.select('body').append(node);

            node.on('open', function(){
                node.select('div.head').closest('.debug').addClass('has-head');
                node.select('div.menu').closest('.debug').addClass('has-menu');
                node.select('div.icon').closest('.debug').addClass('has-icon');
                node.select('div.footer').closest('.debug').addClass('has-footer');
                priya.select('.debug').addClass('display-block');
                node.loader('remove');
            });
            node.on('close', function(){
                priya.select('.debug').removeClass('display-block');
            });
            node.on('debug', function(){
                priya.select('.debug .tab-head li').removeClass('selected');
                priya.select('.debug .tab-body').removeClass('selected');
                var node = priya.select('.debug .tab-body.tab-debug');
                node.addClass('selected');
                //wrong syntax
                //var scrollable = node.closest('has', 'scrollbar', 'vertical');
                //scrollable.scrollbar('to', {'x': 0, 'y': scrollable.scrollbar('height')});
            });
            node.on('debug-clear', function(){
                var debug = run('.debug .tab-body.tab-debug');
                debug.html('');
            });
            node.on('collection', function(){
                priya.select('.debug .tab-head li').removeClass('selected');
                priya.select('.debug .tab-body').removeClass('selected');
                var node = priya.select('.debug .tab-body.tab-collection');
                node.addClass('selected');
                var collection = priya.collection();
                if (typeof JSON.decycle == "function") {
                    collection = JSON.decycle(collection);
                }
                collection = JSON.stringify(collection, null, 2);
                node.html('<pre>' + collection + '</pre>');
            });
            node.on('session', function(){
                priya.select('.debug .tab-head li').removeClass('selected');
                priya.select('.debug .tab-body').removeClass('selected');
                var node = priya.select('.debug .tab-body.tab-session');
                node.addClass('selected');

                var request = {};
                request.method = 'replace';
                request.target = '.tab-body.tab-session';

                priya.request(priya.collection('url') + 'Priya.System.Session', request);

                node.html('<pre>Retrieving session...</pre>');
            });

            node.select('.button-close').on('click', function(){
                node.trigger('close');
            });
            node.select('.button-debug-clear').on('click', function(){
                node.trigger('debug-clear');
            });
            node.select('.tab-head .tab-collection').on('click', function(){
                node.trigger('collection');
                this.addClass('selected');
            });
            node.select('.tab-head .tab-debug').on('click', function(){
                node.trigger('debug');
                this.addClass('selected');
            });
            node.select('.tab-head .tab-session').on('click', function(){
                node.trigger('session');
                this.addClass('selected');
            });
        }

        var debug = select('.debug .tab-body.tab-debug');
        if(typeof data == 'string'){
            if(data == 'run'){
                data = string;
            }
            var item = priya.create('pre', '');
            item.html(data);
            debug.append(item);
            //wrong syntax
            //var scrollable = debug.closest('has', 'scrollbar', 'vertical');
            //scrollable.scrollbar('to', {'x': 0, 'y': scrollable.scrollbar('height')});
            node.trigger('open');
            if(data == string){
                setTimeout(function(){
                    item.remove();
                }, 1500);
            }
        }
        else if(typeof data == 'object'){
            var remove = priya.collection('debug');
            if(remove){
                var index;
                for(index in remove){
                    priya.debug(index);
                    delete data.index;
                }
            }
            if (typeof JSON.decycle == "function") {
                data = JSON.decycle(data);
            }
            data = JSON.stringify(data, null, 2);
            var item = priya.create('pre', '');
            item.html(data);
            debug.append(item);
            var scrollable = debug.closest('has', 'scrollbar', 'vertical');
            scrollable.scrollbar('to', {'x': 0, 'y': scrollable.scrollbar('height')});
            node.trigger('open');
        } else {
            node.trigger('open');
            //node.loader('remove');
        }
    });
}

priya.debug = _('prototype').debug;