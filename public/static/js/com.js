/**
 * Created by smallseven on 2019/2/19.
 */


var w = window.screen.width;
var h = window.screen.height;
var timer = null;

function msg(msg,time){
    timer = clearTimeout(timer);
    if(!time){
        time = 1500;
    }
    lay(msg);
    cloak();
    timer = setTimeout(elastic,time);
}



function elastic(){

    var d = document.querySelector('div#lays');
    var e = document.querySelector('div#cloak');

    d.style.display = "none";
    e.style.display = "none";
}

function lay(msg){
    var d = document.querySelector('div#lays');
    if(d){
        document.body.removeChild(d);
    }
    if(!msg){
        msg = "错误";
    }

    var dh = 60,dw=w*0.4;

    var t = (h-dh)/2;
    var l = (w-(dw))/2;

    var div = document.createElement('div');
    var span = document.createElement('span');
    var notes = document.createTextNode(msg);
    span.appendChild(notes);
    div.appendChild(span);


    document.body.appendChild(div);

    div.setAttribute('id','lays');
    div.style.position = "fixed";
    div.style.textAlign = "center";
    div.style.color = "#000FFF";
    div.style.borderRadius = "8px";
    div.style.background = "white";
    div.style.width = dw+'px';
    div.style.lineHeight = dh+'px';
    div.style.top = t+'px';
    div.style.left = l+'px';
    div.style.zIndex = '9999';

}

function cloak(){
    var d = document.querySelector('div#cloak');
    if(d){
        document.body.removeChild(d);
    }

    var div = document.createElement('div');

    document.body.appendChild(div);

    div.setAttribute('id','cloak');
    div.style.position = "fixed";
    div.style.background = "#eee";
    div.style.opacity = "0.5";
    div.style.width = w+'px';
    div.style.height = h+'px';
    div.style.top = 0;
    div.style.left = 0;
    div.style.zIndex = '999';
}