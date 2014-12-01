oEmbedder.js
===========

OEmbedder javascript file for embed resources from Drupal sites with ombedder module by simple code

https://github.com/borovinskiy/oEmbedder/blob/master/javascript/src/oembedder.js

```
 {{ oembedder:url }}
```

## Install

Insert javascript into you site. 

```
<script src="oEmbedder.js" type="text/javascript"></script>
<!-- while list oEmbedder drupal $base_url -->
<script type="text/javascript">oEmbedder.providers = ["http://example.com/drupal7","http://example.org"]</script>
```

## Usage

In footer call oEmbedder.findAndReplace(domElement) for find all {{ oembedder:URL }} into domElement and replace on oEmbed html object from http://example.com/oembedder?format=jsonp&url=URL ...
```
<script>oEmbedder.findAndReplace(document.getElementsByTagName("body")[0]);</script>
```

## Security warning!

Do not use oEmbedder.findAndReplace on public multiuser sites. It execute is not filtered oEmbed url on white list sites!!!

## Usage

```
{{ oembedder:http://example.com/node/123 }}
```

## How it works

Script is subscribe on page load event and scann all page for change code

```
{{ oembedder:http://example.com/node/123 }} 
```

on

```
<iframe src="http://example.com/node/123"></iframe>
```
 
