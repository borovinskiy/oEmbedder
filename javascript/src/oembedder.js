/* 
 * @author Arsen I. Borovinskiy
 * borovinskiy at gmail com
 * oEmbedder.js - parse page on {{ oembedder:http://example.com/resource }}
 * and embed iframe by oEmbed protocol 
 * http://example.com/oembedder?format=jsonp&url=http://example.com/resource
 * Typically iframe is automatically post message about change height.
 * Site can change height iframe!
 */


/** 
 * load oembed html code and embed to div with id param.wrapperId 
 * param.endPoint - url of oembed endPoint (example: http://example.com/oembedder)
 * param.url = url of oembed (example: http://example.com/node/23434)
 * @version 0.1
 */
var oEmbedder = {
  
  providers: [],    // array of white list providers. example ["http://example.com", "http://example.org/drupal"]
  
  replace: function(param){
    var wrapperId = param.wrapperId;
    var endPoint = param.endPoint;
    var url = param.url;
    var callback = param.callback;

    var embedWrapper = document.getElementById(wrapperId);
    if (embedWrapper === null) return;  // finded {{ oembedder:* }} in comment. 
    oEmbedder[callback] = function(oembed) {          
      document.getElementById(wrapperId).innerHTML = oembed.html; //embedWrapper уже отцеплен от DOM, нам надо заново его найти
    }
    window.addEventListener("message",function(event){
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
  },



  // find all {{ oembedder: url }} and replace on iframe
  findAndReplace: function(rootElement) {

    var all = document.getElementsByTagName("*");
    var regexpGlobal = /{{\s?(&nbsp;)?oembedder\s?(&nbsp;)?\:\s?(&nbsp;)?(.*?)\s?(&nbsp;)?}}/g;
    var regexp = /{{\s?(&nbsp;)?oembedder\s?(&nbsp;)?\:\s?(&nbsp;)?(.*?)\s?(&nbsp;)?}}/;

    var embedWidth = 640;

    //console.log(all);

    /**
     * Обходит oEmbedder.providers в поисках совпадения провайдера по url.
     * Возвращает первого попавшегося провайдера или ничего
     * @param  {String} resourceUrl = url from {{oembedder:url}}
     * @returns first match {provider|String} from white list oEmbedder.providers
     */
    var getAllowedProviderFromUrl = function(resourceUrl) {
      var allowedProvider = false;
      oEmbedder.providers.forEach(function(provider){
        if (allowedProvider) return;
        console.log(provider + " " + resourceUrl + " " + resourceUrl.indexOf(provider));
        if (resourceUrl.indexOf(provider) == 0) {           
          allowedProvider = provider;
        }
      });
      return allowedProvider;
    }

    /**
     * Выделяет из url адрес oembedder сайта
     * @param {type} elem
     * @returns {undefined}
     */
    var findOEmbedderUrl = function (url) {
      var matches = url.match(/(https?\:\/\/)([^\/]*)/);
      if (matches[2]) {
        //console.log(matches);
        return matches[1] + matches[2] + "/oembedder";
      }
    };

    /**
     * Replace {{ oembedder: http://example.com }} on iframe and script
     * @param {type} elem
     * @returns {undefined}
     */
    var replaceRegexp = function(elem)  {

      var executeReplace = function(oembedderUrl,resourceUrl,elem) {
        
        var provider = getAllowedProviderFromUrl(resourceUrl);
        if (!provider) {    // embedding is not allowed
          console.log("Embedding is not allowed for url " + resourceUrl + " " + provider);
          console.log("Add whitelist oEmbedder.providers = ['http://example.com/drupal','http://example.org',...]");          
          elem.innerHTML = elem.innerHTML.replace(regexp,"Embed resource error: security resstriction");
          return;
        }
        
        var uniqueId = "oembedder" + Math.round(Math.random() * 10000000);      
        var replacedString = '<div class="oembedder-wrapper" id="' + uniqueId + '" style="max-width: ' + embedWidth + 'px;">loading...</div>';
        
        oembedderUrl = provider + "/oembedder";   // rewrite oembedderUrl
        
        elem.innerHTML = elem.innerHTML.replace(regexp,replacedString);
        var param = {wrapperId: uniqueId, endPoint: oembedderUrl, url: resourceUrl, callback: "oEmbedderCallback" + uniqueId };

        try {
          oEmbedder.replace(param);
          //console.log("embed with param"); console.log(param);
        } catch (e) {
          console.log(e);
        }
      }

      var found = elem.innerHTML.match(regexpGlobal);
      if (!found) return; // Это вышестоящая нода и в ней уже все заменили
      found.forEach(function(str,index){    // В одном <div> может быть несколько совпадений на одном уровне типа <div>{{ * }} {{ * }}</div> и надо их всех обойти
       var found2 = str.match(regexp);
       var url = found2[4];
       executeReplace(findOEmbedderUrl(url),url,elem);
       //console.log("found str: " + index + " " + str);

      });

    }

    /**
     * Test element by regexp
     * @param {type} elem
     * @returns {undefined}
     */
    var testOEmbedderElement = function(elem) {
      if (elem && elem.innerHTML && elem.innerHTML.toString().search(regexp) >=0) {
        var childIncludeRegexp = false;
        if (elem.childNodes) {
          for (var j=0; j<elem.childNodes.length; j++) {
            var child = elem.childNodes[j]; 
            //console.log('test' + child);
            if (testOEmbedderElement(child)) {    // Рекурсивно вызываем тест на потомках
              childIncludeRegexp = true;
            }
          }
        }  

        if (!childIncludeRegexp) {
          //console.log("fixed element " + elem.innerHTML);
          replaceRegexp(elem);
        } else {
          //console.log("element have children regexp");
        }
      }
    }
    testOEmbedderElement(rootElement);
  },

};
