priya.val = function (value){
    if(typeof this.value == 'undefined'){
        return false;
    }
    if(typeof value != 'undefined'){
        this.value = value
    }

    return this.value;
}