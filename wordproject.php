<?php
/**
 * @package WordProject
 */
/*
Plugin Name: WordProject
Plugin URI: http://www.wordproject.es
Description: Another Project Management plugin that allow you extend its funcionality by plugins. 
Version: 0.0.1
Author: EVM Project Management
Author URI: http://www.evm.net
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Setup some statics, check wordpress version, load text domain
 *
 * @package WordProject
 * @since 3.3
 *
 * wordpress version needs to be 3.3 for some of the new features. mainly wp_editor()
 * use a static for lang makes coding easier
 */
global $wp_version;
if ( version_compare( $wp_version, '3.3', '<' ) ) {
	wp_die( __( 'Wordpress 3.0 or greater is required for this plugin to function.' ) );
}

if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content' );
}

if ( ! defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
}
define ( 'WORDPJT_PLUGIN', __FILE__ );

define ( 'WORDPJT_DIR', dirname(__FILE__).'/' );

define ( 'WORDPJT_URL', plugins_url('/',__FILE__) );

define ( 'WORDPJT_RELATIVE', plugin_basename(__FILE__) );					//to get plugin file

define ( 'WORDPJT_INC', dirname(__FILE__).'/includes/' );

define ( 'WORDPJT_INC_URL', plugins_url('/includes/',__FILE__) ); // for urls instead of DIR

define ( 'WORDPJT_IMG', plugins_url('/images/',__FILE__) );

define ( 'WORDPJT_OPTION', '_WORDPJT_Options' );
define ( 'WORDPJT_LANG', 'WORDPJT_LANG' );

/**
 * Require files for functionality
 *
 * @package WORDPJT
 * @since 3.3
 *
 * all files include classes for specific parts of the plugins operation, classes used further down
 */
//require_once( WORDPJT_DIR.'WORDPJT-Options.php' );

/**
 * Create global $WORDPJT object and load classes
 *
 * @package WORDPJT
 * @since 3.3
 *
 * global not required for anything yet but may in future
 */
global $WORDPJT;
$WORDPJT[] = new WordProject;//core class - adds post type and taxonomy

/**
 * Core Class
 *
 * @package WORDPJT
 * @since 3.3
 *
 * core class - sets up the post type and taxomony, and all associated features
 */
class WordProject{

	/**
	 * Construct core class
	 *
	 * @package WORDPJT
	 * @since 3.3
	 *
	 * add all required actions
	 */
	function __construct(){
		
		load_plugin_textdomain( WORDPJT_LANG, false, basename( dirname( __FILE__ ) ) . '/languages' );//translations
		
		add_action('init', array(&$this,'_register_post_type'));//register post type
		
		/*add_action('init', array(&$this,'_register_project_type'));//register post type types taxonomy
		
		add_action('admin_head', array(&$this, '_post_type_css_head'));//custom icon for post type
		
		add_filter('post_updated_messages', array(&$this, '_set_post_type_messages'));//custom messages for post type
		
		add_filter('manage_edit-projects_columns', array(&$this, '_projects_edit_columns'));//custom headers in list projects table
		
		add_action('manage_posts_custom_column', array(&$this, '_post_type_column_data'));//custom data in list projects table
		
		add_filter( 'manage_edit-projects_sortable_columns', array(&$this, '_post_type_sortable_columns'));//tell wordpress whats sortable
		
		add_filter( 'request', array(&$this, '_post_type_sort_function'));
		
		add_action( 'restrict_manage_posts', array(&$this ,'_filter_by_taxonomy') );//add select box to filer by tax type
		
		add_filter('template_redirect', array(&$this,'_post_type_template_smart'));//load projects template
		
		add_action('wp_enqueue_scripts', array(&$this,'_post_type_css'));//load projects css
		
		if(WPPM_Ultimate_Usage::option('project_comment_uploads_allowed') == 'Enable'){//if comment uploads allowed
		
			add_filter('comment_text', array(&$this, '_insert_comment_formatting'));//format inserts
			
			add_filter( 'comment_form', array(&$this, '_add_iframe_upload_to_comments') );//add the upload form
			
			add_action('wp_ajax_nopriv_wppm_ajax_comment_upload', array(&$this, 'ajax_comment_upload'));//ajax page upload form
			
			add_action('wp_ajax_wppm_ajax_comment_upload', array(&$this, 'ajax_comment_upload'));//ajax page upload form
		
		}//if
		
		if(WPPM_Ultimate_Usage::option('project_css')){//if custom css option
		
			add_action('wp_head', array(&$this, '_custom_css'));//add custom css to head
			
		}//if
		
		*/
		//flush rules if needed
		register_activation_hook( __FILE__, array(&$this,'_activation') );
		register_deactivation_hook( __FILE__, array(&$this,'_deactivation') );
		
		
	}//function

	/**
	 * Registers the 'projects' post type
	 *
	 * @package WORDPJT
	 * @since 3.3
	 *
	 *
	 */
	function _register_post_type(){
		$labels = array(
		'name' => __( 'Projects',WORDPJT_LANG),
		'singular_name' => __( 'Project',WORDPJT_LANG ),
		'add_new' => __('Add New',WORDPJT_LANG),
		'add_new_item' => __('Add New Project',WORDPJT_LANG),
		'edit_item' => __('Edit Project',WORDPJT_LANG),
		'new_item' => __('New Project',WORDPJT_LANG),
		'view_item' => __('View Project',WORDPJT_LANG),
		'search_items' => __('Search Projects',WORDPJT_LANG),
		'not_found' =>  __('No Projects found',WORDPJT_LANG),
		'not_found_in_trash' => __('No Projects found in Trash',WORDPJT_LANG), 
		'parent_item_colon' => ''
	  );
	  
	  $args = array(
		'labels' => $labels,
		'public' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'rewrite' => true, 
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title','editor','comments'),
		'menu_icon' => WPPM_IMG .'16/icon.png'
	  ); 
	  
	  register_post_type( __( 'projects' , WORDPJT_LANG), $args);
		
		
	}//function
	
	function _activation() {
    	$this->_register_post_type();
       	flush_rewrite_rules();
	}
	
	function _deactivation() {
    	flush_rewrite_rules();
	}
	
} // class WORDPJT