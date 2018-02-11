priya.jid = function (list){
    if(typeof list == 'undefined'){
        list = 'priya';
    }
    var data = this.collection(list);
    if(this.empty(data)){
        return "1";
    } else{
        console.log(data);
    }

}