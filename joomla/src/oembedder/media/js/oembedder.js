/** 
 * load oembed html code and embed to div with id param.wrapperId 
 * param.endPoint - url of oembed endPoint (example: http://example.com/oembedder)
 * param.url = url of oembed (example http://example.com/node/23434)
 */
var oEmbedder = function(param){
	// init
	var wrapperId = param.wrapperId;
	var endPoint = param.endPoint;
	var url = param.url;
	var callback = param.callback;
	
	var embedWrapper = document.getElementById(wrapperId);
	oEmbedder[callback] = function(oembed) {
		//console.log(oembed.html);
		embedWrapper.innerHTML = oembed.html;
	}
	window.addEventListener("message",function(event){
		//console.log(event);
		var data = JSON.parse(event.data);
		var iframes = document.getElementsByTagName("iframe");		// get all iframes
		for (var i=0; i<iframes.length;i++) {
			if (iframes[i].src == data.src) 					// find source event iframe by src attribute
				iframes[i].style.height = data.height + "px";
		}
	},false);
	var wrapperStyle = getComputedStyle(embedWrapper.parentNode,null);
	var frameWidth = wrapperStyle.width;
	var requestUrl = endPoint + "?maxwidth=" + frameWidth + "&format=jsonp&callback=oEmbedder." + callback + "&url=" + encodeURIComponent(url);
	
	
	var s = document.createElement("script");
	s.type = "text/javascript";
	s.src = requestUrl;
	var h = document.getElementsByTagName("script")[0];
	try {
		h.parentNode.insertBefore(s,h);
	} catch (e) {
	
	}	
	
	/*
	var xhr = new XMLHttpRequest();
	xhr.open("GET", requestUrl,true);
	xhr.onreadystatechange = function(){ if (xhr.readyState == 4){
		console.log(xhr);
		eval(xhr.response);
	}};
	
	xhr.send();*/
	//embedWrapper.addEventListener("resize",function(event){ console.log("resize")});	
};
