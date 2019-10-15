_('prototype').html = function (html, where){
    if(typeof where == 'undefined'){
        where = 'inner';
    }
    if(typeof html == 'undefined'){
        return this.innerHTML;
    } else {
        if(html === true){
            var attribute = this.attribute();
            html =  '<' + this.tagName.toLowerCase();
            for(attr in attribute){
                html += ' ' + attr + '="' + attribute[attr] + '"';
            }
            //fix <img> etc (no </img>)
            html += '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
            return html;
        } else {
            if(where == 'outer'){
                if(this.is_nodeList()){
                     var index;
                     for(index=0; index < this.length; index++){
                         this[index].outerHTML = html;
                     }
                     return html;
                } else {
                    this.outerHTML = html;
                    return this.outerHTML;
                }

            } else {
                if(this.is_nodeList()){
                    var index;
                    for(index=0; index < this.length; index++){
                        this[index].innerHTML = html;
                    }
                    return html;
               } else {
                   this.innerHTML = html;
                   return this.innerHTML;
               }
            }
        }
    }
}

priya.html = _('prototype').html;