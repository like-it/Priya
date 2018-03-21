priya.calculate = function (calculate){
    var result = null;
    switch(calculate){
        case 'all':
            /*
             * var className = this.className;
             * this.addClass('display-block overflow-auto');
             */
            var rect = this.getBoundingClientRect();
            var result = {};
            result.window = {};
            result.dimension = {};
            result.window.width = window.innerWidth;
            result.window.height = window.innerHeight;
            for (attribute in rect){
                result['dimension'][attribute] = rect[attribute];
            }
            var style = window.getComputedStyle(this);
            result.margin = {
                left: parseInt(style["margin-left"]),
                right: parseInt(style["margin-right"]),
                top: parseInt(style["margin-top"]),
                bottom: parseInt(style["margin-bottom"])
            };
            result.padding = {
                left: parseInt(style["padding-left"]),
                right: parseInt(style["padding-right"]),
                top: parseInt(style["padding-top"]),
                bottom: parseInt(style["padding-bottom"])
            };
            result.border = {
                left: parseInt(style["border-left-width"]),
                right: parseInt(style["border-right-width"]),
                top: parseInt(style["border-top-width"]),
                bottom: parseInt(style["border-bottom-width"])
            };
            result.top = result.dimension.top;
            result.bottom = result.window.height - result.dimension.bottom;
            result.height = result.dimension.height;
            result.width = result.dimension.width;
            result.left = result.dimension.left;
            result.right = result.dimension.right;

            result.offset = {};
            result.offset.parent = this.createSelector(this.offsetParent);
            result.offset.left = this.offsetLeft;
            result.offset.top = this.offsetTop;
            this.data(result);
            return result;
        break;
        case 'offset':
            var result = {};
            result.offset = {};
            result.offset.parent = this.createSelector(this.offsetParent);
            result.offset.left = this.offsetLeft;
            result.offset.top = this.offsetTop;
            this.data(result);
            return result.offset;
        break;
        case 'window-width':
            result =  window.innerWidth;
            return result;
        break;
        case 'window-height':
            result =  window.innerHeight;
            return result;
        break;
        case 'width':
            var className = this.className;
            this.addClass('display-block overflow-auto');
            result =  this.offsetWidth;
            this.className = className;
            return result;
        break;
        case 'height':
            var className = this.className;
            this.addClass('display-block overflow-auto');
            result =  this.offsetHeight;
            this.className = className;
            return result;
        break;
    }
}