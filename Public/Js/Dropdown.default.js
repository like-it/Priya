/**
 * add protector on offset to click away the select same size as offset parent
 */

ready(function(){
    var dropdown = priya.run('.dropdown');
    if(dropdown.is_nodeList()){
        for(index = 0; index < dropdown.length; index++){
            var item = dropdown[index];
            item.dom('div').on('mouseover', function(){
                this.dom('i').addClass('toggle');
            });
            item.dom('div').on('mouseout', function(){
               this.dom('i').removeClass('toggle');
            });
            item.dom('div').on('click', function(){
                priya.dom('.dropdown ul').removeClass('toggle');
//                var dialog = priya.dom('.dialog.{$class}');
                var dialog = this.closest('.dialog');
                var style = window.getComputedStyle(dialog);
                var ul = this.parent().dom('ul');
                ul.toggleClass('toggle');
                ul.calculate('all');
                var node = this.parent();
                node.calculate('all');
                ul.css('z-index', style.zIndex +1);
                if(ul.data('bottom') < 0){
                    var top =
                        ul.data('offset-top') -
                        ul.data('height') -
                        node.data('height')
                    ;
                    ul.css('top', top + 'px');
                }
                ul.dom('li').on('mouseover', function(){
                    this.parent().parent().dom('i').addClass('toggle');
                });
                ul.dom('li').on('mouseout', function(){
                    this.parent().parent().dom('i').removeClass('toggle');
                });
                ul.dom('li').on('click', function(){
                    this.parent().parent().dom('input').val(this.data('value'));
                    this.parent().removeClass('toggle');
                    this.parent().parent().dom('i').removeClass('toggle');
                    var p = this.parent().parent().dom('div p');
                    var span = this.dom('span');
                    p.html(span.html());
                });
            });
        }
    } else {
        dropdown.dom('div').on('mouseover', function(){
             this.dom('i').addClass('toggle');
        });
        dropdown.dom('div').on('mouseout', function(){
            this.dom('i').removeClass('toggle');
        });
        dropdown.dom('div').on('click', function(){
            var i = this.dom('i');
//            i.toggleClass('toggle');
            var ul = this.parent().dom('ul');
            ul.toggleClass('toggle');
            ul.calculate('all');
            //var dialog = priya.dom('.dialog.{$class}');
            var dialog = this.closest('.dialog');
            var style = window.getComputedStyle(dialog);
            ul.css('z-index', style.zIndex +1);
            if(ul.data('bottom') < 0){
                dropdown.calculate('all');
                var top =
                    ul.data('offset-top') -
                    ul.data('height') -
                    dropdown.data('height')
                ;
                ul.css('top', top + 'px');
            }
            ul.dom('li').on('mouseover', function(){
                this.parent().parent().dom('i').addClass('toggle');
            });
            ul.dom('li').on('mouseout', function(){
                this.parent().parent().dom('i').removeClass('toggle');
            });
            ul.dom('li').on('click', function(){
                this.parent().parent().dom('input').val(this.data('value'));
                this.parent().removeClass('toggle');
                this.parent().parent().dom('i').removeClass('toggle');
                var p = this.parent().parent().dom('div p');
                var span = this.dom('span');
                p.html(span.html());
            });
        });
    }
});