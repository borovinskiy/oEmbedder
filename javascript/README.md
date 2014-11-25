oEmbedder.js
===========

OEmbedder javascript file for embed resources from Drupal sites with ombedder module by simple code

https://github.com/borovinskiy/oEmbedder/blob/master/javascript/src/oembedder.js

```
 {{ oembedder:url }}
```

## Install

Just insert javascript into your site 

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
 
