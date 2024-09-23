jQuery.noConflict();
var ypFrame = document.createElement("IFRAME");
function widgetTrigger(ypFrame, type, lang) {
	var langg = (lang != '-1' && typeof lang != 'undefined') ? '&lang='+lang : "";
	var uniqID = bcc.uniqID;
	var yp='';
	ypFrame.id = uniqID;
    ypFrame.class = "iframe";
	ypFrame.name = uniqID;
	ypFrame.style = "border:0!important;min-width:170px;min-height:300px";
	ypFrame.width = (type == 'custom') ? bcc.atts.w+"px" : ((type == 'fix') ? "170px" : "100%");
	ypFrame.height = (type == 'custom') ? bcc.atts.h+"px" : ((type == 'fix') ? "300px" : "300px");
	document.getElementById("currency-bcc-"+uniqID).appendChild(ypFrame);
	var ypElem = document.getElementById(uniqID).parentNode.childNodes;
	var l = false;
	var width = (type == 'custom') ? bcc.atts.w : ((type == 'fix') ? 170 : 0);
	var height = (type == 'custom') ? bcc.atts.h : ((type == 'fix') ? 300 : 300);
	for(var i=0;i < ypElem.length;i++) {
		if (ypElem[i].nodeType == 1 
			&& ypElem[i].nodeName == "A" 
			&& ypElem[i].href == "https://www.currency.wiki/" 
			&& !(ypElem[i].rel 
			&& (ypElem[i].rel.indexOf('nofollow') + 1))) {
			var ypTmp = ypElem[i];
			yp=JSON.stringify({
				w:width,
				h:height,
				nodeType:ypElem[i].nodeType,
				nodeName:ypElem[i].nodeName,
				href :ypElem[i].href,
				rel:ypElem[i].rel,
				cd:uniqID,
				f:bcc.atts.f,
				t:bcc.atts.t,
				c:bcc.atts.s,
				fc:bcc.atts.fc
			});
			l=true;
			break;
		}
	}
    if (l && yp) {
		var url = "https://www.currency.wiki/widget/w.php?wd=1&tm="+bcc.time+langg;
		url = url.replace(/\"/g, "");
		ypFrame.setAttribute("src", url);
		var w = window.frames[uniqID];
		ypFrame.onload = function() {
			w.postMessage({"t": yp}, "*");
		}
		ypTmp.parentNode.removeChild(ypTmp);
	}
	else {
		console.log('Something went wrong, please try later.');
	}
}
widgetTrigger(ypFrame, bcc.atts.type, bcc.atts.lang);