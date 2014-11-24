<?php

/*
Plugin Name: oEmbedder
Description: Allow embed content from Drupal sites with oEmbedder module
Version: 1.0.0
Author: Arsen Borovinskiy
Author URI: http://arsen-borovinskiy.blogspot.com
*/

class Oembedder {

	protected $custom_site_default = "http://demo.elibsystem.ru";
        protected $defaultWidth = 640;
        protected $custom_site;                 // active embedded site
        protected $allowed_sites = array();       // array of allowed sites for embed by {{ link }}
        protected $embedWidth;
        protected $embedUrl;


	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		if ( is_admin() ) {
			add_action('admin_menu', array($this,'add_admin_pages'));	// add admin pages
		} else {
			$this->add_filter();
		}
	}

	function add_admin_pages() {
		add_options_page('oEmbedder Options','oEmbedder','manage_options',__FILE__,array($this,'options_page'));
	}
	
	/**
	 * oEmbedder admin settings page
	 */
	function options_page() {
		$output = "<h2>oEmbedder Settings</h2>";
		$allowed_sites = get_option('oEmbedder_allowed_sites');
		if (isset($_REQUEST['allowed_sites'])) {
			if (!isset($_REQUEST['security'])) die;
			if (!wp_verify_nonce($_REQUEST['security'],'oembedder_csrf')) die;	
			$allowed_sites = $_REQUEST['allowed_sites'];
			update_option('oEmbedder_allowed_sites',$allowed_sites);
			
		}
		$this->allowed_sites = $allowed_sites;
		$ajax_nonce = wp_create_nonce("oembedder_csrf");
		
		$output .= '<h3>oEmbedder custom sites list</h3>';
		$output .= '<div>Use line-break for many servers</div>';
		$output .= '<form name="allowed" id="form-sites" methos="post" action="' . str_replace('%7E', '~', $_SERVER['REQUEST_URI']) . '">';
		$output .= '<textarea name="allowed_sites" id="allowed_sites_input" rows="5" style="width: 100%" placeholder="http://example.com">' . $allowed_sites . '</textarea>';
		$output .= '<div>Embedding by {{ http://example.com }} work only for this sites.</div>';
		print $output;
		$onclick = 'jQuery.post(jQuery("#form-sites").attr("action"),{allowed_sites:jQuery("#allowed_sites_input").val(),security: "' . $ajax_nonce . '"},function(data) {console.log("sended" )}); return false;';
		submit_button("save","primary","submit",true,array('onclick'=>$onclick));
		print '</form>';
			
	}


	/**
	 * Setup filter with correct priority
	 */
	function add_filter() {
		$priority = 999;
		add_filter( 'the_content', array( $this, 'oembedder_filter' ), $priority );
		add_filter( 'wp_enqueue_scripts', array($this, 'add_oembedder_js'));
	}

	public function add_oembedder_js() {
		wp_register_script( 'oembedder', plugins_url( '/js/oembedder.js', __FILE__ ),array(),false,false );
		wp_enqueue_script( 'oembedder' );
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * replace filter
	 */
	function oembedder_filter( $text ) {
		global $wp_embed;
		
		if (strpos($text,'{{') === false) return $text;

		$enabled = true;		// filter is enabled

		$sites = get_option('oEmbedder_allowed_sites');
		
		// change to 
		$sites  = explode("\n",$sites);
		foreach ($sites as $site) {
			$site = str_replace("\n","",$site);
			$site = str_replace("\r","",$site);
			if (strlen($site)>0)
				$this->allowed_sites[] = $site;
		}
		unset($sites);

		$regex = "#\{\{\s*(.*?)\s*\}\}#s";
		preg_match_all( $regex, $text, $matches );
		for($x=0; $x<count($matches[0]); $x++)          // loop for all {{ * }}		
			{
			$match=$matches[1][$x];
			$temp1 = explode('|', $match);
			$case = count($temp1);
			if ($case==1)           // select {{ * }}
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
				$text = str_replace($matches[0][$x], $output, $text);
		}

		return $text;
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
                        $output = $this->getOembedHtml($url);
                        return $output;
                        }
                return false;
        }

	/**
         * return html code for embeding by url
         */
        function getOembedHtml($url) {
	//	$this->add_oembedder_js();
		//wp_enqueue_script( 'oembedder' );
		$endPoint = $this->custom_site . '/oembedder';
		$uniqueId = "oembed" . (int)(rand() * 1000000);
		$output =  '<div class="oembedder-wrapper" id="' . $uniqueId  . '" style="max-width: ' . $this->embedWidth. 'px;">loading...';
		$output .= '</div>';
		$output .= '<script async>oEmbedder({wrapperId: "' . $uniqueId . '", endPoint: "' . $endPoint . '", url: "' . $url . '", callback: "oEmbedderCallback' . $uniqueId . '"} );</script>';
		return $output;
        }

}

new Oembedder;
