<?php
/**
* Author:       Arsen Borovinskiy
* Email:        borovinskiy@gmail.com
* Website:      http://arsen-borovinskiy.blogspot.com
* Plugin:       oEmbedder - plugin for embed any drupal pages
* Version:      1.0.0
* Date:         22/11/2014
* License:      GNU/GPL http://www.gnu.org/copyleft/gpl.html
* Copyright:Copyright © 2014 Arsen I. Borovinskiy. All rights reserved.
**/
//Note: Plugin is based on Simple Wiki Linker Plugin by Omar Muhammad.

defined('_JEXEC') or die ('Restricted access');
echo "\n<!-- elisEmbed Plugin is activated! -->";
jimport('joomla.plugin.plugin');

class plgContentOembedder extends JPlugin
{
        protected $custom_site_default = "http://demo.elibsystem.ru";

        protected $defaultWidth = 640;

        protected $custom_site;                 // active embedded site

        protected $allowed_sites;       // array of allowed sites for embed by {{ link }}

        protected $embedWidth;

        protected $embedUrl;

        function onContentPrepare( $context, &$row, &$params, $page=0 )
        {
			// start tags is here?
			if (JString::strpos($row->text, '{{') === false)
					{return true;}

			$enabled                = $this->params->get('state');
			$defaultWidth                   = $this->params->get('defaultWidth');
			$sites  = explode("\n",$this->params->get('custom_site'));
			foreach ($sites as $site) {
					$site = str_replace("\n","",$site);
					$site = str_replace("\r","",$site);
					if (strlen($site)>0)
							$this->allowed_sites[] = $site;
			}
			unset($sites);


			$this->custom_site = $custom_site ? $custom_site : $this->custom_site_default;

			$this->embedWidth = $defaultWidth ? (int) $defaultWidth : $this->defaultWidth;

			$output = false;

			$regex = "#\{\{\s*(.*?)\s*\}\}#s";
			preg_match_all( $regex, $row->text, $matches );
			for($x=0; $x<count($matches[0]); $x++)          // loop for all {{ * }}
				{
				$match=$matches[1][$x];

				$temp1 = explode('|', $match);
				$case = count($temp1);
				if ($case==1)           // Caaaii eae {{ * }}
						{
						$this->embedUrl=$temp1[0];              // url {{ http://example.com }}
						$this->custom_site = $this->findAllowedSiteByUrl($this->embedUrl);      // set active site
						if ($this->custom_site === false) {continue;}            // not url of $custom_site 
						$output = $this->getEmbedCodeByUrl($this->embedUrl);
						}
				else                            // execute {{ * | * | * ... }}
						{
						// TODO may add extension params
						//$componentName=$temp1[0];
						//$this->embedUrl=$temp1[1];
						}

				if ($enabled && $output)
						$row->text = str_replace($matches[0][$x], $output, $row->text);
				}
			return true;
        }

		/**
         * return site from $this->allowed_sites if $url is match with start site
         * return false if $url not matched with allowedSites
         */
        function findAllowedSiteByUrl($url) {
                foreach ($this->allowed_sites as $site) {
                        if (strpos($url,$site) === 0) {return $site;}
                }
                return false;
        }

        /**
         * return string without \s, non-breaking spaces, &nbsp;
         */
        function removeSpaces($string) {
                $string = preg_replace("/\&nbsp\;/",'',$string);
                $string = preg_replace("/\xc2\xa0/",'',$string);      // remove non-breaking space
                $string = preg_replace("/\s/",'',$string);
                return $string;
        }

        function getEmbedCodeByUrl($url) {
                $url = $this->removeSpaces($url);

                if ($res = preg_match('/\/(\d*)$/',$url,$matches))
                        {
                        $nid = $matches[1];             // node id
                        if (!$nid) return false;
                        $output = '<iframe src="http://k.psu.ru/library/ebooks/embed/' . $nid . '" marginheight=0 marginwidth=0 frameborder=no width="630" height="1024" allowfullscreen=1 mozallowfullscreen=1 webkitallowfullscreen=1></iframe>';
                        $output = $this->getOembedHtml($url);
                        return $output;
                        }
                return false;
        }

        /**
         * return html code for embeding by url
         */
        function getOembedHtml($url) {
			$doc = JFactory::getDocument();
			$doc->addScript(JURI::base() . "media/plg_oembedder/js/oembedder.js","text/javascript");

			$endPoint = $this->custom_site . '/oembedder';
			$uniqueId = "oembed" . (int)(rand() * 1000000);

			$output =  '<div class="oembedder-wrapper" id="' . $uniqueId  . '" style="max-width: ' . $this->embedWidth. 'px;">loading...';
			$output .= '</div>';
			$output .= '<script async>oEmbedder({wrapperId: "' . $uniqueId . '", endPoint: "' . $endPoint . '", url: "' . $url . '", callback: "oEmbedderCallback' . $uniqueId . '"} );</script>';
			return $output;
        }
}
?>
		