priya.loader = function(data){
    if(data == 'remove'){
         priya.select('.priya-gui-loader').addClass('fade-out');
         setTimeout(function(){
             priya.select('.priya-loader').remove();
         }, 10000);
         return;
    }
    var body = run('body');
    var load = priya.create('element', 'priya-loader');
    load.innerHTML = '<div class="priya-gui-loader"></div>';
    body.append(load);
}
