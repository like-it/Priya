/**
 * @todo
 * key up key down (select first etc)
 * alphabetic sort & select
 */

ready(function(){
    var node = new dropdown();
    node.run();
});

var dropdown = function(){
}

dropdown.prototype.run = function(){
    var node = priya.run('.dropdown');
    if (typeof node == 'undefined'){
        return;
    }
    if(node.is_nodeList()){;
        for(index = 0; index < node.length; index++){
            var item = node[index];
            this.item(item);
        }
    } else {
        this.item(node);
    }
}

dropdown.prototype.item = function(element){
    var first = element.dom('ul li:first-child');
    element.dom('p').html(first.dom('span').html());
    var div = element.dom('div');
    var ul = element.dom('ul');
    var i = element.dom('i');
    ul.addClass('toggle');
    //ul.calculate('offset');
    if(parseInt(element.css('width')) >= (parseFloat(ul.css('width')) + parseFloat(i.css('width')))){
        ul.css('width', element.css('width'));
    } else {
        var width = parseFloat(ul.css('width')) + parseFloat(i.css('width'));
        element.css('width', width + 'px');
        ul.css('width', width + 'px');
    }
    ul.removeClass('toggle');
    div.on('mouseover', function(){
        this.dom('i').addClass('toggle');
    });
    div.on('mouseout', function(){
       this.dom('i').removeClass('toggle');
    });
    div.on('keyup', this.key);
    div.on('click', this.enable);
    ul.dom('li').on('mouseover', function(){
        this.parent().parent().dom('i').addClass('toggle');
    });
    ul.dom('li').on('mouseout', function(){
        this.parent().parent().dom('i').removeClass('toggle');
    });
    ul.dom('li').on('mousedown', function(){ //mousedown before blur, click after...
        this.parent().parent().dom('input').val(this.data('value'));
        this.parent().removeClass('toggle');
        this.parent().parent().dom('i').removeClass('toggle');
        var p = this.parent().parent().dom('div p');
        var span = this.dom('span');
        p.html(span.html());
    });
    div.on('blur', this.disable);
}

dropdown.prototype.key = function(event){
    if(event.keyCode == '32'){
        if(this.attribute('tabindex')){
            var node = new dropdown();
            node.enable(this);
            event.preventDefault();
        }
    }
    if(event.keyCode == '27'){
        if(this.attribute('tabindex')){
            var node = new dropdown();
            node.disable(this);
            event.preventDefault();
        }
    }
}

dropdown.prototype.enable = function(element){
    if(!element){
        element = this;
    }
    else if (element.target){
        //dont want the click or blur element
        element = this;
    }
    element.focus();
    priya.dom('.dropdown ul').removeClass('toggle');
    var ul = element.parent().dom('ul');
    element.parent().calculate('offset');
    element.calculate('offset');
    ul.css('delete', 'top');
    ul.toggleClass('toggle');
    ul.calculate('offset');
    var offsetParent = priya.dom(ul.data('offset-parent'));
    if(offsetParent){
        var scrollable = element.parent().closest('has', 'scrollbar', 'vertical');
        scrollable.on('scroll', function(){
            this.dom('ul').removeClass('toggle');
        });
        ul.css('z-index', parseInt(offsetParent.css('z-index')) + 1);
        if(parseInt(ul.css('bottom')) < 0){
            ul.css('top', (parseFloat(element.data('offset-top')) - parseFloat(scrollable.scrollbar('y')) - parseFloat(ul.css('height'))) + 'px');
        }
    }
    console.log(ul.dom('li:first-child'));
    ul.dom('li:first-child').addClass('.selected');
}

dropdown.prototype.disable = function(element){
    if(!element){
        element = this;
    }
    else if (element.target){
        element = this;
    }
    element.parent().dom('ul').removeClass('toggle');
}
