priya.off = function (event, action){
    console.log(this['Priya']['eventListener']);
    this.removeEventListener(event, action)
}