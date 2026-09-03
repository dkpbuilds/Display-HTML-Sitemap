<?php
/*
Plugin Name: Display HTML Sitemap
Version: 1.2.0
Description: Display HTML Sitemap generates a clean hierarchical HTML sitemap. Supports Pages, Posts and all Custom Post Types with full admin control.
Author: Dipak Kumar Pusti
Author URI: https://profiles.wordpress.org/dkpbuilds/
Text Domain: display-html-sitemap
Requires PHP: 7.4
Requires at least: 5.0
Tested up to: 7.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Define Variable 
define( 'DHSWP_FILE', __FILE__ );
define( 'DHSWP_PATH', plugin_dir_path( DHSWP_FILE ) );
define( 'DHSWP_BASE', plugin_basename( DHSWP_FILE ) );

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
final class Display_Html_Sitemap {

	/**
	 * Instance of this class.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Whether textdomain has been loaded.
	 *
	 * @var bool
	 */
	private $textdomain_loaded = false;

	/**
	 * Returns the single instance of this class.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'display_sitemap_menu' ) );
		add_action( 'admin_init', array( $this, 'dhswp_set_default_option' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'plugin_action_links_' . DHSWP_BASE, array( $this, 'dhswp_plugin_actions_links' ) );

		// Saving the settings page
		add_action( 'wp_loaded', array( $this, 'dhswp_save_options' ) );

		// Generating Shortcode for Plugin
		add_shortcode( 'display-html-sitemap', array( $this, 'shortcode_dhswp_sitemap' ) );
	}

	/**
 	* Initialize and load the plugin textdomain
 	*
 	* @since 1.0.0
 	*/
	function load_textdomain() {
		
		if($this->textdomain_loaded) {
			return;
		}

		load_plugin_textdomain('dhswp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
		$this->textdomain_loaded = true;
	}

	/**
 	* Set up plugin default settings and save them to options
 	*
 	* @since 1.0.0
 	*/
	function dhswp_set_default_option() {

		// Loading default post types
		$post_types = $this->dhswp_post_types();
		
		// Generating new list of saved posttypes
		$list = array();
		foreach ( $post_types  as $post_type ){
			
			$list[] = $post_type->name;

			// Adding active post types to options
			add_option( 'dhswp_active_'.$post_type->name , 'active' );
		}
		$list = implode( ',', $list );

		// Adding default sorting of post types to options
		add_option( 'dhswp_sortorder', $list );
	}

	/**
 	* Create menu page for Html Sitemap
 	*
 	* @since 1.0.0
 	*/
	function display_sitemap_menu() {

		add_options_page( 
			__( 'HTML Sitemap', 'dhswp' ), 
			__( 'HTML Sitemap', 'dhswp' ), 
			'manage_options', 
			'html-sitemap', 
			array( $this, 'html_sitemap_options_page' )
		);
	}

	/**
     * Settings page for HTML Sitemap
     *
     * @since 1.0.0
     */
    public function html_sitemap_options_page() {
    
        // check user capabilities
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        require DHSWP_PATH . 'templates/settings-html-sitemap.php';
    }

	/**
 	* Enqueue and load the plugin stuff
 	*
 	* @since 1.0.0
 	*/
	function enqueue_scripts() {

		// Enqueue CSS
		wp_enqueue_style( 'dhswp-custom-css' ,  
			plugins_url( 'assets/css/display-html-sitemap.css', __FILE__ ) );
		
		// Enqueue JS
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'dhswp-custom-js',  
			plugins_url( 'assets/js/display-html-sitemap.js', __FILE__ ) );
		
	}

	/**
 	* Save settings and save them to options
 	*
 	* @since 1.0.1
 	*/
	function dhswp_save_options() {

		if ( isset( $_POST['dhswp-update'] ) && 'Save Changes' === $_POST['dhswp-update'] ) {

			if ( check_admin_referer( 'save_sitemap_option', 'save_sitemap_option' ) && current_user_can( 'manage_options' ) ) {

				// Sanitize all inputs
				update_option( 'dhswp_sortorder', sanitize_text_field( wp_unslash( $_POST['dhswp-sortorder'] ?? '' ) ) );
				update_option( 'dhswp_exclude', sanitize_text_field( wp_unslash( $_POST['dhswp-exclude'] ?? '' ) ) );

				$post_types = $this->dhswp_post_types();

				if ( count( $post_types ) > 0 ) {
					foreach ( $post_types as $post_type ) {
						$active_key   = 'dhswp_active_' . $post_type->name;
						$newname_key  = 'dhswp_newname_' . $post_type->name;

						$active = ( isset( $_POST[ $active_key ] ) && 'on' === $_POST[ $active_key ] ) ? 'active' : 'deactive';
						update_option( $active_key, $active );

						// Sanitize new name
						$newname = isset( $_POST[ $newname_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $newname_key ] ) ) : '';
						update_option( $newname_key, $newname );
					}
				}

				wp_redirect( admin_url( 'options-general.php?page=html-sitemap&message=1' ) );
				exit;

			} else {

				wp_redirect( admin_url( 'options-general.php?page=html-sitemap&message=2' ) );
				exit;
			}
		}
	}

	/**
 	* Get default post types and send them to default option function
 	* http://codex.wordpress.org/Function_Reference/get_post_types
 	*
 	* @since 1.0.0
 	*/
	function dhswp_post_types() {
		
		$args = array(
			'public'   => true
		);
		
		// names or objects, note names is the default
		$output = 'objects';

		// 'and' or 'or'
		$operator = 'and';

		// Get post types
		$post_types = get_post_types( $args, $output, $operator ); 
		
		// Removing Attachment Post Type
		unset( $post_types["attachment"] );
		
		return $post_types;
	}

    /**
     * Set setting menu link for plugin HTML Sitemap
     *
     * @since 1.0.0
     */
    public function dhswp_plugin_actions_links( $links ) {
        $dhswp_settings_page = array(
            '<a href="' . admin_url( 'options-general.php?page=html-sitemap' ) . '">'.__( 'Settings', 'dhswp' ).'</a>',
        );
        return array_merge( $links, $dhswp_settings_page );
    }

	

	/**
 	* Initialize and load sortable html to be
 	* viewd in admin panel of settings page
 	*
 	* @since 1.0.0
 	*/
	function dhswp_sortable_list( $post_types ) {

		$html = '';

		foreach( $post_types as $post_type ) {
			
			$checked = '';

			if($post_type->dhswp_active == 'yes' ){
				$checked = ' checked="checked" ';
			}
			
			$newname = $post_type->labels->name;

			if( isset( $post_type->newname ) ) {
				$newname = $post_type->newname;
			}
			
			$html .= '<li class="dhswp-ui-state-default" id="' . $post_type->name . '">
				<div class="dhswp-cpt">
					<div class="dhswp-dragable-handler"></div>
					<div class="dhswp-dragable-checkbox"><input name="dhswp_active_'.$post_type->name.'" id="dhswp_active_'.$post_type->name.'" type="checkbox" ' . $checked . ' /></div>
					<div class="dhswp-cpt-name">
						<span class="dhswp-cpt-name-title">' . $newname . '</span>
						&nbsp; <span class="dhswp_changename">(<a href="#" title="'.__( 'Update post type name to be displayed on sitemap page', 'dhswp' ).'">'.__( 'Change', 'dhswp' ).'</a>)</span>
						<div class="dhswp-newname"><input type="text" name="dhswp_newname_'.$post_type->name.'" value="'.$newname.'" /> <a class="dhswp-save-newname" href="#">'.__( 'Ok', 'dhswp' ).'</a> &nbsp; <a class="dhswp-cancel-newname" href="#">'.__( 'Cancel', 'dhswp' ).'</a></div>
					</div>
					<div class="dhswp-cpt-slug">' . $post_type->name . '</div>
					<span style="display:none;" class="dhswp-originalname">'.$post_type->labels->name.'</span>
					<div class="clr"></div>
				</div>
			</li>';
		}
		return $html;
	}

	/**
 	* Creating post types list for html sorting component
 	*
 	* @since 1.0.0
 	*/
	function dhswp_posts_list() {
		
		$post_types        	= $this->dhswp_post_types();
		$dhswp_sortorder 	= get_option('dhswp_sortorder');
		
		// Creating array from current order
		$dhswp_sortorder_array = explode( ',', $dhswp_sortorder );

		// Save to new array to return
		$allposttypes = array();
		$allposttypes = $this->sortArrayByArray( $post_types, $dhswp_sortorder_array );

		return $allposttypes;
	}

	/**
 	* Creating nice and sorted array from given input
 	*
 	* @since 1.0.0
 	*/
	function sortArrayByArray( $array, $orderArray ) {

		$ordered = array();
		foreach( $orderArray as $key ) {
			
			if( array_key_exists( $key, $array ) ) {
				
				if( get_option( 'dhswp_active_' . $key ) == 'active' ) {
					$array[$key]->dhswp_active = 'yes';
				} else {
					$array[$key]->dhswp_active = 'no';
				}
				
				// New Name
				if( get_option( 'dhswp_newname_' . $key ) != '' ) {
					$array[$key]->newname = get_option( 'dhswp_newname_' . $key );
				} else {
					$array[$key]->newname = $array[$key]->label;
				}
				
				$ordered[$key] = $array[$key];
				unset($array[$key]);
			}
		}
		return $ordered + $array;
	}

	/**
 	* Generating shortcode for html sitemap
 	* that will be used in pages or widgets
 	*
 	* @since 1.0.0
 	* @param array $atts Shortcode attributes.
 	* @return string
 	*/
	function shortcode_dhswp_sitemap( $atts ) {

		$atts = shortcode_atts(
			array(
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'posts_per_page' => -1,
				'number'         => -1,        // alias
				'exclude'        => '',
				'hierarchical'   => 1,
			),
			$atts,
			'display-html-sitemap'
		);

		// Support both 'posts_per_page' and legacy 'number'
		$posts_per_page = ( -1 !== (int) $atts['posts_per_page'] ) ? (int) $atts['posts_per_page'] : (int) $atts['number'];

		$return = '<div class="dhswp-html-sitemap-wrapper">';

		// Cache frequently used data
		$post_types            = $this->dhswp_post_types();
		$dhswp_sortorder       = get_option( 'dhswp_sortorder', '' );
		$dhswp_sortorder_array = array_filter( explode( ',', $dhswp_sortorder ) );

		foreach ( $dhswp_sortorder_array as $post_type ) {
			$post_type = trim( $post_type );
			if ( empty( $post_type ) || get_option( 'dhswp_active_' . $post_type ) !== 'active' ) {
				continue;
			}

			// Ensure new name is always defined
			$newname = isset( $post_types[ $post_type ] ) ? $post_types[ $post_type ]->labels->name : $post_type;

			if ( get_option( 'dhswp_newname_' . $post_type ) !== '' ) {
				$newname = get_option( 'dhswp_newname_' . $post_type );
			}

			$return .= $this->dhswp_get_post_by_post_type(
				$post_type,
				$newname,
				$atts['orderby'],
				$atts['order'],
				$posts_per_page,
				$atts['exclude'],
				(bool) $atts['hierarchical']
			);
		}

		$return .= '</div> <!-- .dhswp-html-sitemap-wrapper -->';

		return $return;
	}

	/**
 	* Get posts for a specific post type and build heading + list.
 	* Updated in 1.1.0 to support shortcode parameters.
 	*
 	* @since 1.0.0
 	* @param string $postype Post type name.
 	* @param string $title Display title.
 	* @param string $orderby Query orderby.
 	* @param string $order Query order.
 	* @param int    $posts_per_page Number of posts.
 	* @param string $shortcode_exclude Additional exclude IDs from shortcode.
 	* @param bool   $hierarchical Whether to show nested structure.
 	* @return string
 	*/
	function dhswp_get_post_by_post_type( $postype, $title, $orderby = 'menu_order', $order = 'ASC', $posts_per_page = -1, $shortcode_exclude = '', $hierarchical = true ) {

		global $post;

		$return       = '';
		$curr_page_id = isset( $post->ID ) ? absint( $post->ID ) : 0;

		$args = array(
			'post_type'      => $postype,
			'posts_per_page' => $posts_per_page,
			'orderby'        => $orderby,
			'order'          => strtoupper( $order ),
		);

		if ( 'page' === $postype ) {
			$args['post__not_in'] = array( $curr_page_id );
		}

		$loop = new WP_Query( $args );
		wp_reset_postdata();

		$posts = $loop->posts;
		$return = '<h2 class="dhswp-html-sitemap-post-title dhswp-' . esc_attr( $loop->query_vars['post_type'] ) . '-title">' . esc_html( $title ) . '</h2>';

		if ( count( $posts ) > 0 ) {

			// Combine global exclude with shortcode exclude
			$global_exclude  = get_option( 'dhswp_exclude' );
			$global_ids      = $global_exclude ? array_map( 'absint', explode( ',', $global_exclude ) ) : array();
			$shortcode_ids   = $shortcode_exclude ? array_map( 'absint', explode( ',', $shortcode_exclude ) ) : array();
			$exclude_ids     = array_unique( array_merge( $global_ids, $shortcode_ids ) );

			$return .= '<ul class="dhswp-html-sitemap-post-list dhswp-' . esc_attr( $loop->query_vars['post_type'] ) . '-list">';
			$parent_id = 0;
			$return   .= $this->dhswp_get_subpost( $posts, $parent_id, false, $exclude_ids, (bool) $hierarchical );
			$return   .= '</ul>';
		}

		return $return;
	}

	/**
 	* Recursively build hierarchical or flat list.
 	* Updated in 1.1.0 to accept shortcode-level exclude and hierarchical flag.
 	*
 	* @since 1.0.0
 	*/
	function dhswp_get_subpost( $posts, $parent_id, $display_ul = false, $exclude_ids = array(), $hierarchical = true ) {

		$return = '';

		if ( ! empty( $posts ) ) {

			foreach ( $posts as $post ) {

				if ( (int) $post->post_parent === (int) $parent_id ) {

					if ( ! in_array( (int) $post->ID, $exclude_ids, true ) ) {

						$return .= '<li>';
						$return .= '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( $post->post_title ) . '</a>';

						if ( $hierarchical ) {
							$return .= $this->dhswp_get_subpost( $posts, $post->ID, true, $exclude_ids, true );
						}
						$return .= '</li>';
					}
				}
			}

			if ( '' !== $return && $display_ul ) {
				$return = '<ul>' . $return . '</ul>';
			}
		}

		return $return;
	}
}

Display_Html_Sitemap::get_instance();