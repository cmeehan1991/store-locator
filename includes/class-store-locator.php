<?php 
class Store_Locator{

	function __construct(){
		add_action( 'init', array($this, 'store_post_type') );
		add_action( 'init', array($this, 'register_options_pages') );
		add_action( 'wp_enqueue_scripts', array($this, 'resources'));
		add_action( 'rest_api_init', array($this, 'register_rest_routes'));
	}
	
	public function register_options_pages(){
		if(function_exists('acf_add_options_page')){
			acf_add_options_sub_page( array(
				'page_title'    => 'Location Settings',
				'menu_title'	=> 'Settings',
				'parent_slug'   => 'edit.php?post_type=store',
			) );
		}
	}
	
	public function register_rest_routes(){
		register_rest_route( 'cbm-store-locator/v1', 'stores', array( 
			'methods'	=> 'GET', 
			'callback'	=> array($this, 'get_stores'),
			'permission_callback'	=> '__return_true'
		));
	}
	
	/**
	 * 
	 */
	public function get_stores(WP_REST_Request $request){
		$args = array(
			'post_type'			=> 'store', 
			'posts_per_page'	=> -1, 
			'post_status'		=> 'publish',
			'orderby'			=> 'meta_value store_name', 
			'order'				=> 'asc'
		);
		
		$posts = get_posts($args);
		
		$all_stores = array();
		
		if($posts){
			foreach($posts as $post){
				$post_id = $post->ID;
				
				$full_address_html = get_field('street_address', $post_id) . '<br/>';;
				
				if(get_field('secondary_address', $post_id)){
					$full_address_html .= get_field('secondary_address', $post_id);
				}
				
				if(get_field('country', $post_id) == 'United States'){
					$full_address_html .= get_field('city', $post_id) . ', ' . get_field('state', $post_id) . ' ' . get_field('postal_code', $post_id) . '<br/>' . get_field('country', $post_id);
				}else {
					$full_address_html .= get_field('city', $post_id) . ' ' . get_field('postal_code', $post_id) . '<br/>' . get_field('country', $post_id);
				}
				
				$all_stores[] = array(
					'ID' 				=> $post_id,
					'thumbnail'			=> get_the_post_thumbnail_url($post_id),
					'name' 				=> get_field('store_name', $post_id), 
					'streetAddress'		=> get_field('street_address', $post_id),
					'secondaryAddress'	=> get_field('secondary_address', $post_id),
					'country'			=> get_field('country', $post_id),
					'city'				=> get_field('city', $post_id),
					'state'				=> get_field('state', $post_id),
					'postalCode'		=> get_field('postal_code', $post_id),
					'phoneNumberLink'	=> "tel:" . get_field('phone_number', $post_id),
					'phoneNumber'		=> preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3',get_field('phone_number', $post_id)),
					'emailAddressLink'	=> "mailto:" . get_field("email_address", $post_id),
					'emailAddress'		=> get_field('email_address', $post_id),
					'website'			=> get_field('website', $post_id),
					'latitude'			=> doubleval(get_field('latitude', $post_id)),
					'longitude'			=> doubleval(get_field('longitude', $post_id)),
					'fullAddressHTML'	=> $full_address_html
				);
			}
		}
		
		wp_send_json($all_stores);
	}
	
	public function resourrces(){
		wp_enqueue_script('wp-api');
	}
	
	/* 
	 * Register Custom Post Type 
	 */
	public function store_post_type() {
	
		$labels = array(
			'name'                  => _x( 'Stores', 'Post Type General Name', STORE_LOCATOR_TEXTDOMAIN ),
			'singular_name'         => _x( 'Store', 'Post Type Singular Name', STORE_LOCATOR_TEXTDOMAIN ),
			'menu_name'             => __( 'Stores', STORE_LOCATOR_TEXTDOMAIN ),
			'name_admin_bar'        => __( 'Store', STORE_LOCATOR_TEXTDOMAIN ),
			'archives'              => __( 'Store Archives', STORE_LOCATOR_TEXTDOMAIN ),
			'attributes'            => __( 'Store Attributes', STORE_LOCATOR_TEXTDOMAIN ),
			'parent_item_colon'     => __( 'Parent Store:', STORE_LOCATOR_TEXTDOMAIN ),
			'all_items'             => __( 'All Stores', STORE_LOCATOR_TEXTDOMAIN ),
			'add_new_item'          => __( 'Add New Store', STORE_LOCATOR_TEXTDOMAIN ),
			'add_new'               => __( 'Add New', STORE_LOCATOR_TEXTDOMAIN ),
			'new_item'              => __( 'New Store', STORE_LOCATOR_TEXTDOMAIN ),
			'edit_item'             => __( 'Edit Store', STORE_LOCATOR_TEXTDOMAIN ),
			'update_item'           => __( 'Update Store', STORE_LOCATOR_TEXTDOMAIN ),
			'view_item'             => __( 'View Store', STORE_LOCATOR_TEXTDOMAIN ),
			'view_items'            => __( 'View Stores', STORE_LOCATOR_TEXTDOMAIN ),
			'search_items'          => __( 'Search Store', STORE_LOCATOR_TEXTDOMAIN ),
			'not_found'             => __( 'Not found', STORE_LOCATOR_TEXTDOMAIN ),
			'not_found_in_trash'    => __( 'Not found in Trash', STORE_LOCATOR_TEXTDOMAIN ),
			'featured_image'        => __( 'Featured Image', STORE_LOCATOR_TEXTDOMAIN ),
			'set_featured_image'    => __( 'Set featured image', STORE_LOCATOR_TEXTDOMAIN ),
			'remove_featured_image' => __( 'Remove featured image', STORE_LOCATOR_TEXTDOMAIN ),
			'use_featured_image'    => __( 'Use as featured image', STORE_LOCATOR_TEXTDOMAIN ),
			'insert_into_item'      => __( 'Insert into store', STORE_LOCATOR_TEXTDOMAIN ),
			'uploaded_to_this_item' => __( 'Uploaded to this store', STORE_LOCATOR_TEXTDOMAIN ),
			'items_list'            => __( 'Stores list', STORE_LOCATOR_TEXTDOMAIN ),
			'items_list_navigation' => __( 'Stores list navigation', STORE_LOCATOR_TEXTDOMAIN ),
			'filter_items_list'     => __( 'Filter stores list', STORE_LOCATOR_TEXTDOMAIN ),
		);
		$args = array(
			'label'                 => __( 'Store', STORE_LOCATOR_TEXTDOMAIN ),
			'description'           => __( 'Post Type Description', STORE_LOCATOR_TEXTDOMAIN ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes' ),
			'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-store',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'          => true,
		);
		register_post_type( 'store', $args );
	
	}
	
}

new Store_Locator();