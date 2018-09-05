<?php
require_once( 'search-form.php' );

// search form 
add_shortcode( 'search-form', function( $atts ) {
	$atts = shortcode_atts(
		array( 'type' => 'simple' ),
		$atts
	);

	if ( in_array( $atts['type'], array( "simple" ) ) )
		call_user_func( $atts['type']."_search_form" );	
});

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'slick', plugins_url( 'assets/slick/slick.css', __FILE__ ) );
	wp_enqueue_style( 'slick-theme', plugins_url( 'assets/slick/slick-theme.css', __FILE__ ) );

	wp_enqueue_script( 'slick', plugins_url( 'assets/slick/slick.min.js', __FILE__ ), array( 'jquery' ), '1.8.1', true );

	wp_enqueue_style( 'style', plugins_url( 'assets/css/style.css', __FILE__ ) );

	wp_enqueue_script( 'scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array( 'slick' ), '1.0.0', true );

	wp_enqueue_script( 'global-scripts', plugins_url( 'assets/js/global.js', __FILE__ ), array( 'jquery' ), '1.0.0' );
});

// add single property page in front end
add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ) {
	$wp_rewrite->rules = array_merge(
		[
			'single-property/(\d+)/?$' => 'index.php?spType=single&listingID=$matches[1]',
			'search-results/([a-zA-Z0-9 ,_]+)/?$' => 'index.php?spType=search&keyword=$matches[1]'
		],
		$wp_rewrite->rules
	);

	return $wp_rewrite->rules;
} );

// add queries to the url
add_filter( 'query_vars', function( $query_vars ) {
	array_push( $query_vars, 'listingID', 'keyword', 'spType');

	return $query_vars;
} );

// template redirect hook
add_action( 'template_redirect', function() {
	$spType = get_query_var('spType');

	if ($spType) {
		get_header();

		switch ( $spType ) {
			case 'single':
				$listingID = intval( get_query_var( 'listingID' ) );

				$property = SI()->getDataBylistingID( $listingID );

				if ( $property)
					include "single-property.php";
				else
					echo "<h1>Invalid Listing ID</h1>";
				break;
			
			case 'search':
				$keywords = explode( ' ', str_replace( ',', ' ', get_query_var('keyword') ) );

				$properties = SI()->getPropertiesByKeyWord( $keywords, 'property' );

				if ( count( $properties ) )
					include "search-results.php";
				else
					echo "<h1>No Results</h1>";
				break;
		}

		get_footer();

		die;
	}
} );

// add template to page editor page
add_filter( 'theme_page_templates', function( $templates ) {

	$templates['properties-by-location.php'] = 'Properties by Location';

	return $templates;
} );

add_filter( 'page_template', function( $template ) {
	if ( get_page_template_slug() == 'properties-by-location.php' )
		$template = SR_PLUGIN_PATH . 'templates/properties-by-location.php';

	return $template;
} );