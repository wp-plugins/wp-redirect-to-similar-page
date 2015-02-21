<?php
/**
 * Plugin Name: WP Redirect to similar page.
 * Plugin URI: www.librafire.com
 * Description: Redirects a user to a page the is most similar to the url given page.
 * Version: 1.0.0
 * Author: librafire.com
 * Author URI: www.librafire.com
 * Text Domain: librafire_redirect
 * License: GPL2
 */
function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
  	} 
    else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}
function find_a_similar_page() {

	if( is_404() ){

		$pageSlugsRaw = array();
		$cleanSinglePageSlug = array();
		$mixin = array();
		$removeMe = site_url();
		$url = curPageURL();
		$closestValue = 100;
		$closestLink = '';
		$lastUrlQueryString = array_pop( explode( "/", $url ) );

		$args = array(
			'sort_order' => 'ASC',
			'sort_column' => 'post_title',
			'hierarchical' => 1,
			'post_type' => 'page',
			'post_status' => 'publish'
		);
		$pages = get_pages( $args ); 

		foreach ( $pages as $key ) {
			$pageLink = get_page_link( $key->ID );
			array_push( $pageSlugsRaw, $pageLink );
		}

		foreach( $pageSlugsRaw as $pageLink ) {
			$wholeLink = $pageLink;
			$trimedLink = str_replace( $removeMe, '', $pageLink );
			$similarityLevel = levenshtein( $trimedLink, $lastUrlQueryString );

			array_push( $mixin, array( 'similar' => $similarityLevel, 'link' => $wholeLink ) );
		}

		for( $i = 0; $i < count($mixin); $i++ ){
			if( $closestValue > $mixin[$i]['similar'] ){
				$closestValue = $mixin[$i]['similar'];
				$closestLink = $mixin[$i]['link'];
			}
		}

		wp_redirect( $closestLink );
	}

}
add_action( 'template_redirect', 'find_a_similar_page' );
?>