/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: home_drag.js 32655 2013-02-28 04:08:00Z zhengqingpeng $
*/

var Drags       = [];
var nDrags      = 1;

var mouseOffset = null;
var iMouseDown  = false;
var lMouseState = false;
var dragObject  = null;

var DragDrops   = [];
var curTarget   = null;
var lastTarget  = null;
var dragHelper  = null;
var tempDiv     = null;
var rootParent  = null;
var rootSibling = null;

var D1Target    = null;

Number.prototype.NaN0=function(){return isNaN(this)?0:this;};

function CreateDragContainer(){
	var cDrag        = DragDrops.length;
	DragDrops[cDrag] = [];

	for(var i=0; i<arguments.length; i++){
		var cObj = arguments[i];
		DragDrops[cDrag].push(cObj);
		cObj.setAttribute('DropObj', cDrag);

		for(var j=0; j<cObj.childNodes.length; j++){

			if(cObj.childNodes[j].nodeName=='#text') continue;

			cObj.childNodes[j].setAttribute('DragObj', cDrag);
		}
	}

}

function getPosition(e){
	var left = 0;
	var top  = 0;
	while (e.offsetParent){
		left += e.offsetLeft + (e.currentStyle?(parseInt(e.currentStyle.borderLeftWidth)).NaN0():0);
		top  += e.offsetTop  + (e.currentStyle?(parseInt(e.currentStyle.borderTopWidth)).NaN0():0);
		e     = e.offsetParent;
	}


	left += e.offsetLeft + (e.currentStyle?(parseInt(e.currentStyle.borderLeftWidth)).NaN0():0);
	top  += e.offsetTop  + (e.currentStyle?(parseInt(e.currentStyle.borderTopWidth)).NaN0():0);

	return {x:left, y:top};

}

function mouseCoords(ev){
	if(ev.pageX || ev.pageY){
		return {x:ev.pageX, y:ev.pageY};
	}
	return {
		x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
		y:ev.clientY + document.body.scrollTop  - document.body.clientTop
	};
}

function writeHistory(object, message){
	if(!object || !object.parentNode || typeof object.parentNode.getAttribute == 'unknown' || !object.parentNode.getAttribute) return;
	var historyDiv = object.parentNode.getAttribute('history');
	if(historyDiv){
		historyDiv = document.getElementById(historyDiv);
		historyDiv.appendChild(document.createTextNode(object.id+': '+message));
		historyDiv.appendChild(document.createElement('BR'));

		historyDiv.scrollTop += 50;
	}
}

function getMouseOffset(target, ev){
	ev = ev || window.event;

	var docPos    = getPosition(target);
	var mousePos  = mouseCoords(ev);
	return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
}

function mouseMove(ev){
	ev         = ev || window.event;

	var target   = ev.target || ev.srcElement;
	var mousePos = mouseCoords(ev);
	if(Drags[0]){
		if(lastTarget && (target!==lastTarget)){
			writeHistory(lastTarget, 'Mouse Out Fired');
			var origClass = lastTarget.getAttribute('origClass');
			if(origClass) lastTarget.className = origClass;
		}

		var dragObj = target.getAttribute('DragObj');

		if(dragObj!=null){

			if(target!=lastTarget){
				writeHistory(target, 'Mouse Over Fired');

				var oClass = target.getAttribute('overClass');
				if(oClass){
					target.setAttribute('origClass', target.className);
					target.className = oClass;
				}
			}


			if(iMouseDown && !lMouseState){
				writeHistory(target, 'Start Dragging');

				curTarget     = target;

				rootParent    = curTarget.parentNode;
				rootSibling   = curTarget.nextSibling;
				mouseOffset   = getMouseOffset(target, ev);
				for(var i=0; i<dragHelper.childNodes.length; i++) dragHelper.removeChild(dragHelper.childNodes[i]);

				dragHelper.appendChild(curTarget.cloneNode(true));
				dragHelper.style.display = 'block';

				var dragClass = curTarget.getAttribute('dragClass');
				if(dragClass){
					dragHelper.firstChild.className = dragClass;
				}

				dragHelper.firstChild.removeAttribute('DragObj');

				var dragConts = DragDrops[dragObj];


				curTarget.setAttribute('startWidth',  parseInt(curTarget.offsetWidth));
				curTarget.setAttribute('startHeight', parseInt(curTarget.offsetHeight));
				curTarget.style.display  = 'none';

				for(var i=0; i<dragConts.length; i++){
					with(dragConts[i]){
						var pos = getPosition(dragConts[i]);

						setAttribute('startWidth',  parseInt(offsetWidth));
						setAttribute('startHeight', parseInt(offsetHeight));
						setAttribute('startLeft',   pos.x);
						setAttribute('startTop',    pos.y);
					}

					for(var j=0; j<dragConts[i].childNodes.length; j++){
						with(dragConts[i].childNodes[j]){
							if((nodeName=='#text') || (dragConts[i].childNodes[j]==curTarget)) continue;

							var pos = getPosition(dragConts[i].childNodes[j]);

							setAttribute('startWidth',  parseInt(offsetWidth));
							setAttribute('startHeight', parseInt(offsetHeight));
							setAttribute('startLeft',   pos.x);
							setAttribute('startTop',    pos.y);
						}
					}
				}
			}
		}

		if(curTarget){
			dragHelper.style.top  = (mousePos.y - mouseOffset.y)+"px";
			dragHelper.style.left = (mousePos.x - mouseOffset.x)+"px";
			var dragConts  = DragDrops[curTarget.getAttribute('DragObj')];
			var activeCont = null;

			var xPos = mousePos.x - mouseOffset.x + (parseInt(curTarget.getAttribute('startWidth')) /2);
			var yPos = mousePos.y - mouseOffset.y + (parseInt(curTarget.getAttribute('startHeight'))/2);

			for(var i=0; i<dragConts.length; i++){
				with(dragConts[i]){
					if((parseInt(getAttribute('startLeft'))                                           < xPos) &&
						(parseInt(getAttribute('startTop'))                                            < yPos) &&
						((parseInt(getAttribute('startLeft')) + parseInt(getAttribute('startWidth')))  > xPos) &&
						((parseInt(getAttribute('startTop'))  + parseInt(getAttribute('startHeight'))) > yPos)){

							activeCont = dragConts[i];

							break;
					}
				}
			}

			if(activeCont){
				if(activeCont!=curTarget.parentNode){
					writeHistory(curTarget, 'Moved into '+activeCont.id);
				}

				var beforeNode = null;

				for(var i=activeCont.childNodes.length-1; i>=0; i--){
					with(activeCont.childNodes[i]){
						if(nodeName=='#text') continue;

						if(curTarget != activeCont.childNodes[i]                                                  &&
							((parseInt(getAttribute('startLeft')) + parseInt(getAttribute('startWidth')))  > xPos) &&
							((parseInt(getAttribute('startTop'))  + parseInt(getAttribute('startHeight'))) > yPos)){
								beforeNode = activeCont.childNodes[i];
						}
					}
				}

				if(beforeNode){
					if(beforeNode!=curTarget.nextSibling){
						writeHistory(curTarget, 'Inserted Before '+beforeNode.id);

						activeCont.insertBefore(curTarget, beforeNode);
					}

				} else {
					if((curTarget.nextSibling) || (curTarget.parentNode!=activeCont)){
						writeHistory(curTarget, 'Inserted at end of '+activeCont.id);

						activeCont.appendChild(curTarget);
					}
				}

				setTimeout(function(){
				var contPos = getPosition(activeCont);
				activeCont.setAttribute('startWidth',  parseInt(activeCont.offsetWidth));
				activeCont.setAttribute('startHeight', parseInt(activeCont.offsetHeight));
				activeCont.setAttribute('startLeft',   contPos.x);
				activeCont.setAttribute('startTop',    contPos.y);}, 5);

				if(curTarget.style.display!=''){
					writeHistory(curTarget, 'Made Visible');
					curTarget.style.display    = '';
					curTarget.style.visibility = 'hidden';
				}
			} else {

				if(curTarget.style.display!='none'){
					writeHistory(curTarget, 'Hidden');
					curTarget.style.display  = 'none';
				}
			}
		}

		lMouseState = iMouseDown;

		lastTarget  = target;
	}

	if(dragObject){
		dragObject.style.position = 'absolute';
		dragObject.style.top      = mousePos.y - mouseOffset.y;
		dragObject.style.left     = mousePos.x - mouseOffset.x;
	}

	lMouseState = iMouseDown;

	if(curTarget || dragObject) return false;
}

function mouseUp(ev){
	if(Drags[0]){
		if(curTarget){
			writeHistory(curTarget, 'Mouse Up Fired');

			dragHelper.style.display = 'none';
			if(curTarget.style.display == 'none'){
				if(rootSibling){
					rootParent.insertBefore(curTarget, rootSibling);
				} else {
					rootParent.appendChild(curTarget);
				}
			}
			curTarget.style.display    = '';
			curTarget.style.visibility = 'visible';
		}
		curTarget  = null;
	}

	dragObject = null;
	iMouseDown = false;
}

function mouseDown(ev){
	mousedown(ev);
	ev         = ev || window.event;
	var target = ev.target || ev.srcElement;
	iMouseDown = true;
	if(Drags[0]){
		if(lastTarget){
			writeHistory(lastTarget, 'Mouse Down Fired');
		}
	}
	if(target.onmousedown || target.getAttribute('DragObj')){
		return false;
	}
}

function makeDraggable(item){
	if(!item) return;
	item.onmousedown = function(ev){
		dragObject  = this;
		mouseOffset = getMouseOffset(this, ev);
		return false;
	}
}

function init_drag2(){

	document.onmousemove = mouseMove;
	document.onmousedown = mouseDown;
	document.onmouseup   = mouseUp;

	Drags[0] = $('Drags0');
	if(Drags[0]){
		CreateDragContainer($('DragContainer0'));
	}
	if(Drags[0]){
		var cObj = $('applistcontent');
		dragHelper = document.createElement('div');
		dragHelper.style.cssName = "apps dragable";
		dragHelper.style.cssText = 'position:absolute;display:none;width:777px;';
		cObj.parentNode.insertBefore(dragHelper, cObj);
	}
}
function mousedown(evnt){
}