priya.init = function (data, configuration){
    console.log('deprecated, use select or run');
    if(typeof data == 'undefined'){
        return this;
    }
    if(typeof data == 'string'){
        var element = this.select(data);
        return element;
    }
    return data;
}