oEmbedder
=========

Wordress oEmbed plugin for resources from Drupal sites with ombedder module by simple {{ oembedder : url }}

## Wordpress support

Plugin tested on Wordpress 4.0

## Install

Copy plugin folder from src/oembedder onto Wordpress ./wp-content/plugins.

Enable plugin. 

Goto plugin settings. 

Add same Drupal target sites url:

![oEmbedder wordpress settings](http://www.elibsystem.ru/sites/default/files/docs/oembedder/wordpress/wordpress-oembedder-settings.png "oEmbedder wordpress settings")

## Usage

```
{{ oembedder : http://example.com/node/1 }}
```
