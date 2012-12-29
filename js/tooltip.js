
var offsetxpoint=-60 //Customize x offset of tooltip
var offsetypoint=20 //Customize y offset of tooltip
var ie = document.all;
var ns6 = document.getElementById && !document.all;
var enabletip = false;
var tipobj= document.getElementById("bff_tooltip");

function ietruebody() {
    return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function sctip(thetext, thecolor, thewidth) {
    if (ns6||ie){
        if(!tipobj) tipobj= document.getElementById("bff_tooltip");
	    if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px";
	    if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor = thecolor;
	    tipobj.innerHTML = thetext;
	    enabletip = true;
	    return false;
    }
}

function positiontip(e) {
	if (enabletip){
		var curX=(ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;
		var curY=(ns6)?e.pageY : event.clientY+ietruebody().scrollTop;
		//Find out how close the mouse is to the corner of the window
		var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
		var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20
		
		var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000
		tipobj.style.left=curX+offsetxpoint+"px"
		tipobj.style.top=curY+offsetypoint+"px"
		tipobj.style.visibility="visible"
		tipobj.style.display="block"
	}
}

function hidesctip() {
    if (ns6||ie){
	    enabletip = false
	    tipobj.style.visibility="hidden"
	    tipobj.style.display="none"
	    tipobj.style.left="-1000px"
	    tipobj.style.backgroundColor=''
	    tipobj.style.width=''
    }
}

document.onmousemove = positiontip;

