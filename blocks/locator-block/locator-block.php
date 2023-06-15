<?php
/**
 * Plugin Name:       Locator Block
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       locator-block
 *
 * @package           create-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_locator_block_block_init() {
	register_block_type( __DIR__ . '/build', array(
		'render_callback'	=> 'render_locator_block_cb'
	) );
}
add_action( 'init', 'create_block_locator_block_block_init' );

function render_locator_block_cb( $block_attributes, $content ){

	$content = '<div class="store-locator" data-key="'. get_field('google_maps_api_key', 'options') . '" data-lat="' . get_field('default_latitude', 'options') . '" data-lng="' . get_field('default_longitude', 'options') .'" data-zoom="' . get_field('default_zoom', 'options') . '">';
	$content .= '<div class="location-section">';
	$content .= '<label>Postal Code, City, State, or Country</label>';
	$content .= '<div class="location-search-section">';
	$content .= '<form onSubmit="return doSearchLocation()" method="post">';
	$content .= '<input type="text" name="location" class="location-input form-control"/>';
	$content .= '<button type="submit" class="location-search-button"><i class="fa-solid fa-magnifying-glass"></i></button>';
	$content .= '</form>';
	$content .= '</div>';
	$content .= '<button type="button" class="current-location-button" onClick="getCurrentLocation()">Use My Current Location</button>';
	$content .= '<div class="loading-indicator"><i class="fa-solid fa-spinner fa-spin fa-2x"></i></div>';
	$content .= '</div>';
	$content .= '<div class="store-locator--map" id="storeLocatorMap"></div>';
	$content .= '<div class="store-locator--list-section" id="storeLocatorListSection">';
	$content .= '<ul class="list stores-list"></ul>';
	$content .= '<ul class="pagination"></ul>';
	$content .= '</div>';
	$content .= '</div>';
	return $content;
}
