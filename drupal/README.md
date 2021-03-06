oEmbedder
==========

Drupal7 oEmbed plugin for programmatically customization export nodes by oEmbed protocol

## Install

Load oembed module on server.

Enabled module. 

Write owner module.

OEmbed endpoint is $base_url/oembedder

## Joomla!

Install joomla plugin

## Write Drupal7 module for oEmbedder

### hook_oembedder

You must implement hook_oembedder like this:

```php
<?php
/**
 * $module = array("url","node","oembed")
 * $module["url"] - standart object url with absolute url and relative $url->absolute, $url->relative. Can parsed modules.
 * $module["node"] - loaded $node object (optional) if $module[url]->relative == "node/\d+"
 * $module["oembed"] - returned array like oembed specification.
 * $module["oembed"]["html"] - html code with iframe code. This output for user.
 * hook_oembedder implementation
 */
function hook_oembedder($module=array(),$width=0) {
  global $base_url;
  if (!isset($module['node'])) return $module;
  $node = $module['node'];		// It full loaded $node object 
  if ($node->type != "myCustomType") return $module;    // work only for owner document types
  $width = 640;		//default 
  $height = 480;	//default
  $koef = $width / $height;
  if (isset($_GET['maxwidth']) && (int) $_GET['maxwidth'] > 0 && $width > $_GET['maxwidth']) {
    $width = (int) $_GET['maxwidth'];
    $height = (int) ($width / $koef) + 1;
  }
  $oembed = array();		// this array we export to $module['oembed']
  // We can embed by iframe.
  $oembed['html'] = '<iframe src="' . $base_url . '/YOURMODULE/' . $node->nid . '"';  // by this source your module print embed code.
  $oembed['html'] .= 'marginheight=0 marginwidth=0 frameborder=no width=' . $width . ' height=' . $height . ' allowfullscreen=1 mozallowfullscreen=1 webkitallowfullscreen=1 scrolling="none"></iframe>';
  $module['oembed'] = $oembed;
  return $module;
}
?>
```

### iframe code

You Drupal code you can added script for send into Joomla height of iframe. Iframe usage event object {height,src}

This script calculate vertical size and send to iframe.parent by postMessage:

```javascript
<script>
var oldHeight;
setInterval(function(event){
  var iframeHeight = document.getElementsByTagName("body")[0].clientHeight;
  if (oldHeight != iframeHeight) {
    oldHeight = iframeHeight;
    window.parent.postMessage(JSON.stringify({height:iframeHeight,src: window.location.href}),'*');
  }
},300);
</script>
```
