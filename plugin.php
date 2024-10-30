<?php
/*
Plugin Name: Community Jar
Plugin URI: http://ChurchMediaDesign.tv/community-jar
Description: Have a need or Help a need? The Community Jar makes it easy for anyone to submit a service project or volunteer to help meet a need.
Version: 1.1.2
Author: Brad Zimmerman
Author URI: http://ChurchMediaDesign.tv/
Author Email: communityjar@churchmediadesign.tv
License:

  Copyright 2013 Brad Zimmerman (communityjar@churchmediadesign.tv)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

if( ! defined( 'COMMUNITY_JAR_VERSION' ) ) {
	define( 'COMMUNITY_JAR_VERSION', '1.1.2' );
} // end if

/**
 * Defines the plug-in directory url
 * <code>url:http://mysite.com/wp-content/plugins/community-jar</code>
 */
define('COMMUNITYJAR_PATH',plugin_dir_path(__FILE__ ));
define('COMMUNITYJAR_DIR',plugin_dir_url(__FILE__ ));

// Dependencies for managing volunteer data, generating project short links, and replacing unique tokens
require_once( COMMUNITYJAR_PATH . 'lib/Community_Jar_Volunteer_Manager.class.php' );
require_once( COMMUNITYJAR_PATH . 'lib/Community_Jar_Tokenizer.class.php' );
include_once( COMMUNITYJAR_PATH . 'lib/Community_Jar_Notifications.class.php' );
require_once( COMMUNITYJAR_PATH . 'lib/Community_Jar_Functions.php' );
require_once( COMMUNITYJAR_PATH . 'lib/Community_Jar_Admin_Page.php' );
require_once( COMMUNITYJAR_PATH . 'lib/Community_Jar_Archive_Loop.php' );
require_once( COMMUNITYJAR_PATH . 'lib/Community_Jar_Admin.php' );



class CommunityJar {

	/*--------------------------------------------*
	 * Attributes
	 *--------------------------------------------*/
	private $volunteers;
	
	private $notices;
	
	private $tokenizer;

	private $completed_nonce = 'project_is_completed_nonce';
	
	private $owner_nonce = 'project_owner_nonce';
	
	private $date_nonce = 'project_date_nonce';
	
	private $volunteer_nonce = 'project_volunteer_nonce';
	
	private $subject_nonce = 'email_subject_nonce';
	
	private $admin_email;
	
	public $cj_options;
	private $cj_options_admin_email;
	private $cj_options_from_name;
	private $cj_options_from_email;

	
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 *
	 * @version		1.0
     * @since 		1.0
	 */
	function __construct( $volunteers, $tokenizer, $notices ) {
		
		// Set the dependencies coming in via the constructor
		$this->volunteers = $volunteers;
		$this->tokenizer = $tokenizer;
		$this->notices = $notices;
		$this->cj_options = get_option('cj_settings');
		$this->cj_options_from_email = sanitize_text_field($this->cj_options['from_email']);
		$this->cj_options_from_name = sanitize_text_field($this->cj_options['from_name']);
		$this->admin_email = sanitize_text_field($this->cj_options['admin_email']);
		
		// sets the email headers
		$this->cj_email_headers = 'From: '.$this->cj_options['from_name'].' <'.$this->cj_options_from_email .'>' . "\r\n";
		
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );
		
		// Set Up Notifications  // Removing in favour of new update routine.
		//register_activation_hook( __FILE__, array( $this->notices, 'activate' ) );
        //register_deactivation_hook( __FILE__, array( $this->notices, 'deactivate' ) );
		//if( false == get_option( 'cj_notification' ) ) {
           // add_action( 'admin_notices', array( $this->notices, 'display_admin_notice' ) );
        //} // end if
		// add_action( 'wp_ajax_hide_admin_notification', array( $this->notices, 'hide_admin_notification' ) );
		
		// Setup the project detection
		//add_action( 'init', array( $this, 'detect_project' ) );
		
		// Setup the Project and Email post types
		add_action( 'init', array( $this, 'register_post_types' ) );
		
		// Flush Rewrite rules so all pretty permalinks work on plugin activation
		register_activation_hook( __FILE__, array( $this, 'communityjar_plugin_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'communityjar_plugin_deactivation' ) );
	
		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this,'register_plugin_styles' ));
		add_action( 'wp_enqueue_scripts', array( $this,'register_plugin_scripts'));

		// Setup the Project Owner metabox
		add_action( 'add_meta_boxes', array( $this, 'add_project_owner_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_project_owner' ) );
		
		// Setup the Email Template metabox
		add_action( 'add_meta_boxes', array( $this, 'add_email_subject_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_email_subject' ) );

		// Setup the Project Date metabox
		add_action( 'add_meta_boxes', array( $this, 'add_project_info_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_email_submit_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_email_shortcodes_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_project_info' ) );
		
		// Setup the Project Volunteers metabox
		add_action( 'add_meta_boxes', array( $this, 'add_project_volunteer_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_project_volunteers' ) );
		
		// Notify the Project Owner that the Project is live
		add_action( 'save_post', array( $this, 'notify_project_owner_of_publish' ) );

		// Register the plugin template files on plugin activation
		register_activation_hook( __FILE__, array( $this, 'register_project_template' ) );
		
		// Register the plugin template files when the theme is switched
		add_action( 'after_theme_swich', array( $this, 'register_project_template' ) );
		
		// Programmatically Register the Email Templates on plugin activation
		register_activation_hook( __FILE__, array( $this, 'create_project_pages' ) );
		register_activation_hook( __FILE__, array( $this, 'create_email_templates' ) );
		
		// Delete the Project Submission Page: don't think this a good idea or right hook, disabled till I find the Correct way.
		//register_deactivation_hook( __FILE__, array( $this, 'delete_project_submission_page' ) );
		
		//If WordPress couldn't find proper 'event' templates use plug-in instead:
		add_filter('template_include', array( $this,'communityjar_set_template'));

		// Remove secret keys as soon as a post is trashed
		add_action( 'transition_post_status', array( $this,'project_trashed'), 10, 3 );
		add_action( 'get_header',  array( $this, 'single_project_header_hook' ));
		// Updates
		add_action('admin_init', array( $this,'cj_upgradecheck'));
	} // end constructor

	/*------------------------------------------------------------*
	 * Localization, JavaScripts, Stylesheets, Rewrite Rules, etc.
	 *-------------------------------------------------------------*/

	/**
	 * Loads the plugin text domain for translation
	 *
	 * @version		1.0
     * @since 		1.0
	 */
	public function plugin_textdomain() {
		load_plugin_textdomain( 'community-jar', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	} // end plugin_textdomain

	/**
	 * If the project ID is set in the query string, look it up for the current post
	 *
	 * @version	1.0
	 * @since	1.0
	 */
	public function detect_project($project_key) {

			// Next, we need to look up the project by this meta value
			global $wpdb;
			$project_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %s AND meta_key = 'project_hash'", $project_key ) );
			if (isset($project_id) && !empty($project_id)){
				if('cj_project' === get_post_type($project_id) && 'publish' == get_post_status ( $project_id )){
					return $project_id;
				}else{
					return NULL;
				}
			}else{
				return NULL;
			}
			
				
		
		
	} // end detect_project
	
	/**
	 * Registers and enqueues plugin-specific styles.
	 *
	 * @version		1.0
     * @since 		1.0
	 */
	public function register_plugin_styles() {
	
		// If the current page includes the 'Project Submission' template, then add the Redactor stylesheet
		if( $this->using_project_submission_template() ) {
		
			wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'community-jar-redactor', COMMUNITYJAR_DIR . 'css/lib/redactor.css');	
			wp_enqueue_style( 'community-jar',  COMMUNITYJAR_DIR . 'css/community-jar-project.css');	
			
		} // end if
		
		// If we're on the 'Read Only' page for projects, then we need to enqueue the styles
		if( 'cj_project' == get_post_type() || is_post_type_archive( 'cj_project' ) ) {
			wp_enqueue_style( 'community-jar-project', COMMUNITYJAR_DIR . 'css/community-jar.css');	 
		} // end if
		
	} // end register_plugin_styles
	

	
	
	/**
	 * Registers and enqueues plugin-specific scripts.
	 *
	 * @version		1.0
     * @since 		1.0
	 */
	public function register_plugin_scripts() {
		
		// If the current page includes the 'Project Submission' template, then add the Redactor JavaScript
		if( $this->using_project_submission_template() ) {
		
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'community-jar-redactor-plugin',  COMMUNITYJAR_DIR . 'js/lib/redactor.js' , array( 'jquery' ),  COMMUNITY_JAR_VERSION );	
			wp_enqueue_script( 'community-jar-redactor',  COMMUNITYJAR_DIR . 'js/community-jar-project.min.js', array( 'community-jar-redactor-plugin' ),  COMMUNITY_JAR_VERSION );	
			
		} // end if
		
		// If we're on the 'Read Only' page for projects, then we need to enqueue the JavaScript
		if( 'cj_project' == get_post_type() ) {
			wp_enqueue_script( 'community-jar-project',  COMMUNITYJAR_DIR .'js/single-project.min.js', array( 'jquery' ), COMMUNITY_JAR_VERSION );	 
		} // end if
		
	} // end register_plugin_scripts
	
	

	/**
	 * Programmatically creates a project based on the content of the form submission.
	 *
	 * @version		1.0
	 * @since		1.0
	 */
	public function register_new_project() {

		// Initialize success to false
		$success = false;
		//die;
		// if the $_POST array isn't empty, then we need to create a project
		if( ! empty( $_POST ) ) {
		
			// If the project is valid...
			if ( $this->validate_project( $_POST ) ) {

				// ...read the post values
				$title = strip_tags( stripslashes( trim( $_POST['project-title'] ) ) );
				$desc = $_POST['project-description'];
				$name = strip_tags( stripslashes( trim( $_POST['project-owner-name'] ) ) );
				$phone = strip_tags( stripslashes( trim( $_POST['project-owner-phone'] ) ) );
				$owner = strip_tags( stripslashes( trim( $_POST['project-owner'] ) ) );
				if (isset($_POST['is-anonymous']) && !empty($_POST['is-anonymous'])){
					$anonymous = 1 == $_POST['is-anonymous'] ? 'anonymous' : 'public';
				}
				$date = date("Y-m-d", strtotime(strip_tags( trim( $_POST['project-date'] ) ) ) );

				// Next, let's grab the user (or create the user)
				$user = $this->generate_project_user( $owner );
				
				// After that, set the user information
				$this->set_user_info( $user->data->ID, $name, $phone, $owner );

				// If the Post ID is 0, then we've had a problem
				if( ( $project = $this->create_project( $user, $title, $desc ) ) ) {

					add_post_meta( $project, 'project_owner', $user->data->user_email );
					add_post_meta( $project, 'owner_visibility', $anonymous );
					add_post_meta( $project, 'project_date', $date );

					// Now, get the 'Project Submitted' email
					$page = get_page_by_title( '[Admin] Project Submitted', OBJECT, 'cj_email' );
					$subject = get_post_meta( $page->ID, 'email_subject', true );
					$searchArray = array('&#8217;', '&quot;');
					$replaceArray = array("'", '"');
					$subject = str_ireplace( $searchArray, $replaceArray, $this->replace_tags( $subject, $user->data->ID, $project ) );
					
					
					// Make sure new lines are replaced with page breaks
					$message = str_ireplace( "\n", "<br />", $page->post_content );
					$message = $this->replace_tags( $message, $user->data->ID, $project );
					
					// Email the site admin that a new project has been created
					add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );
					
					if( wp_mail( $this->admin_email, $subject, $message, $this->cj_email_headers ) ) {
						$success = 1;
					} // end if

				} // end if

			} // end if/else

			// And redirect with the extra query string variable so we can give a heads up that something is wrong.
			wp_redirect( add_query_arg( array( 'success' => $success ), $_SERVER['REQUEST_URI'] ) );
			exit;

		} // end if

	} // end register_new_project
	
	/**
	 * Programmatically updates a project based on the content of the form submission.
	 *
	 * @version		1.0
	 * @since		1.0
	 */
	public function update_existing_project( $project_id ) {
		
		// Initialize success to false
		$success = false;
		
		// if the $_POST array isn't empty, then we need to create a project
		if( ! empty( $_POST ) ) {

			// If the project is valid...
			if ( $this->validate_project( $_POST ) && 'cj_project' === get_post_type( $project_id ) ) {

				// ...read the post values				
				$title = strip_tags( stripslashes( trim( $_POST['project-title'] ) ) );
				$desc = $_POST['project-description'];
				$name = strip_tags( stripslashes( trim( $_POST['project-owner-name'] ) ) );
				$phone = strip_tags( stripslashes( trim( $_POST['project-owner-phone'] ) ) );
				$owner = strip_tags( stripslashes( trim( $_POST['project-owner'] ) ) );
				$anonymous = 1 == $_POST['is-anonymous'] ? 'anonymous' : 'public';
				$date = date("Y-m-d", strtotime(strip_tags( trim( $_POST['project-date'] ) ) ) ); //Remove tags then format date to the XXXX-XX-XX format
				$is_complete = $_POST['project_is_complete'];
				
				// Next, let's grab the user (or create the user)
				$user = $this->generate_project_user( $owner );
				
				// After that, set the user information
				$this->set_user_info( $user->data->ID, $name, $phone, $owner );
				
				// If the Post ID is 0, then we've had a problem
				if( ( $project = $this->update_project( $project_id, $user, $title, $desc ) ) ) {

					update_post_meta( $project, 'project_owner', $user->data->user_email );
					update_post_meta( $project, 'owner_visibility', $anonymous );
					update_post_meta( $project, 'project_date', $date );
					
					if( isset( $is_complete ) && '1' == $is_complete ) {

						update_post_meta( $project, 'project_is_complete', $is_complete);	
						
					} else {
						
						delete_post_meta( $project, 'project_is_complete' );	
						
					}
					
					
					// Email the admin if the project is completed
					if( '1' == $is_complete ) {
						$this->send_completed_email( $project_id );
					} else {

						
							// Now get the project owner
							$project_owner_email = get_post_meta( $project_id, 'project_owner', true );
		
							// Now, get the 'Project Updated' email
							$page = get_page_by_title( '[Project Owner] Project Updated', OBJECT, 'cj_email' );
							$subject = get_post_meta( $page->ID, 'email_subject', true );
							$searchArray = array('&#8217;', '&quot;');
							$replaceArray = array("'", '"');
							$subject = str_ireplace( $searchArray, $replaceArray, $this->replace_tags( $subject, $user->data->ID, $project ) );
							
							// Make sure new lines are replaced with page breaks
							$message = str_ireplace( "\n", "<br />", $page->post_content );
							$message = $this->replace_tags( $message, $user->data->ID, $project );

							// Email the Project Owner that a new user has been created
							add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );
							
							if ( wp_mail( $project_owner_email, $subject, $message, $this->cj_email_headers ) ) {
								$success = true;
							} // end if
						
						
					
					} // end if/else

				} // end if

			} // end if/else

			// And redirect with the extra query string variable so we can give a heads up that something is wrong.
			wp_redirect( add_query_arg( array( 'success' => $success ), $_SERVER['REQUEST_URI'] ) );
			exit;

		} // end if
		
		return $success;
		
	} // end update_existing_project
	
	/**
	 * Programmatically creates a volunteer based on the content of the form submission.
	 *
	 * @version		1.0
	 * @since		1.0
	 */
	public function register_new_volunteer() {
		
		// Initialize success to false
		$success = false;

		// if the $_POST array isn't empty, then we need to create a new Volunteer
		if( ! empty( $_POST ) && isset( $_POST ) ) {

			// If the Volunteer data is valid
			if ( $this->validate_volunteer( $_POST ) ) {

				// ...read the post values
				$name = $_POST['volunteer-name'];
				$phone = $_POST['volunteer-phone'];
				$email = $_POST['volunteer-email'];
				$original_comment = $_POST['volunteer-comments'];
				$project_id = $_POST['project-id'];

				// Next, let's grab the user (or create the user).
				$user = $this->generate_project_user( $email );
				
				// After that, set the user information
				$this->set_user_info( $user->data->ID, $name, $phone, $email );

				// Now we need to set this user as pending for this project
				update_user_meta( $user->data->ID, $project_id, 'pending' );
				
				// Associate the comments for this project with the user
				// First, we build the user's comment:
				$project_title = get_the_title( $project_id );
				$project_title = '[' . $project_title . ']&nbsp;';
				$comment = $project_title . $original_comment . '<br />';
				
				$comment_args = array(
					'comment_post_ID'		=>	$project_id,
					'comment_author'		=>	$name,
					'comment_author_email'	=>	$email,
					'comment_content'		=>	$original_comment,
					'user_id'				=>	$user->data->ID,
					'comment_date'			=>	current_time('mysql'),
					'comment_type'			=>  'volunteer',
					'comment_approved'		=>	1
				);
				$comment_id = wp_insert_comment( $comment_args );
				update_user_meta( $user->data->ID, 'project-comment-' . $project_id, $comment_id );
				
				// Email the admin that a new volunteer has been submitted for the project
				$page = get_page_by_title( '[Admin] Volunteer Submitted', OBJECT, 'cj_email' );
				$subject = get_post_meta( $page->ID, 'email_subject', true );
				$searchArray = array('&#8217;', '&quot;');
				$replaceArray = array("'", '"');
				$subject = str_ireplace( $searchArray, $replaceArray, $this->replace_tags( $subject, $user->data->ID, $project_id ) );


				// Make sure new lines are replaced with page breaks
				$message = str_ireplace( "\n", "<br />", $page->post_content );
				$message = $this->replace_tags( $message, $user->data->ID, $project_id );
				
				// Finally, email the site admin
				add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );
				
				if ( wp_mail( $this->admin_email, $subject, $message, $this->cj_email_headers ) ) {
					$success = true;
				} // end if

			} // end if/else

			// And redirect with the extra query string variable so we can give a heads up that something is wrong.
			wp_redirect( add_query_arg( array( 'success' => $success ), $_SERVER['REQUEST_URI'] ) );
			exit;

		} // end if
		
	} // end register_new_volunteer
	
 	/*---------------------------------------------*
	 * Custom Filter
	 *---------------------------------------------*/
	
	 /**
	  * Loops through all of the various tags that are supported and replaces them with the actual values for the project.
	  *
	  * @param	string	$content				The content containing the tags
	  * @param	int		$id						The ID for the user, volunteer, or project owner.
	  * @param	int		$project_id	optional	The ID of the project for which we'll need to retrieve data.
	  * @return	string							The message with the content replaced.
	  */
	 private function replace_tags( $content, $id, $project_id = -1 ) {
		
		 // Here's the list of the accepted keys
		 $keys = array( '[project-title]', '[project-owner-name]', '[project-owner-email]', '[project-owner-phone]', '[project-date]', '[project-description]', '[project-edit-url]', '[project-url]', '[volunteer-name]', '[volunteer-email]', '[volunteer-phone]', '[volunteer-comment]', '[admin-login-url]', '[admin-email]', '[admin-project-url]' );
		 
		 // Now iterate through each of the keys...
		 for( $i = 0; $i < count( $keys ); $i++ ) {
			 
			 // Get the current key
			 $current_key = $keys[ $i ];
			 
			 // Now pass the information to the tokenizer
			 $content = $this->tokenizer->get_value( $id, $current_key, $content, $project_id );
			 
		 } // end for
		 
		 return $content;
		 
	 } // end replace_tags
	
	/*---------------------------------------------*
	 * Project-based Functions
	 *
	 * 1. Post Types
	 * 2. Email Subject Meta Box
	 * 3. Project Owner Meta Box
	 * 4. Project Date Meta 
	 * 5. Project Owner Notification
	 * 6. Project Volunteers
	 * 7. Project Template Registration
	 * 8. Programmatically Create Pages and  Email Templates
	 *---------------------------------------------*/
  
 	/*---------------------------------------------*
	 * 1. Post Types
	 *---------------------------------------------*/
  
	 /**
	  * Adds the Project and Email post types to the WordPress dashboard.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	  
			

	 public function register_post_types() {
		
		$args = array(
			'labels'	=>	array(
								'name'					=> 	__( 'Projects', 'community-jar' ),
								'all_items'				=> 	__( 'Projects', 'community-jar' ),
								'menu_name'				=>	__( 'Community Jar', 'community-jar' ),
								'singular_name'			=>	__( 'Project', 'community-jar' ),
							 	'edit_item'				=>	__( 'Edit Project', 'community-jar' ),
								'add_new_item'			=>	__( 'Add New Project', 'community-jar' ),
							 	'new_item'				=>	__( 'New Project', 'community-jar' ),
							 	'view_item'				=>	__( 'View Project', 'community-jar' ),
							 	'items_archive'			=>	__( 'Project Archive', 'community-jar' ),
							 	'search_items'			=>	__( 'Search Projects', 	'community-jar' ),
							 	'not_found'				=>	__( 'No projects found.', 'community-jar'),
							 	'not_found_in_trash'	=>	__( 'No projects found in trash.', 'community-jar' )	
							),
			'supports'		=>	array( 'title', 'editor', 'author', 'revisions' ),				
			'menu_position'	=>	5,
			'public'		=>	true,
			'has_archive'	=> true,
			'menu_icon'		=>  '',
			'rewrite' 	 	=> array( 'slug' => 'projects', 'with_front' => false )
		);
		register_post_type( 'cj_project', $args );
		
		$args = array(
			'labels'	=>	array(
								'name'					=> 	__( 'Email Templates', 'community-jar' ),
								'all_items'				=> 	__( 'Email Templates', 'community-jar' ),
								'menu_name'				=>	__( 'Email Templates', 'community-jar' ),
								'singular_name'			=>	__( 'Email Template', 'community-jar' ),
							 	'edit_item'				=>	__( 'Edit Template', 'community-jar' ),
							 	'new_item'				=>	__( 'New Template', 'community-jar' ),
							 	'view_item'				=>	__( 'View Template', 'community-jar' ),
							 	'items_archive'			=>	__( 'Template Archive', 'community-jar' ),
							 	'search_items'			=>	__( 'Search Templates', 	'community-jar' ),
							 	'not_found'				=>	__( 'No templates found.', 'community-jar'),
							 	'not_found_in_trash'	=>	__( 'No templates found in trash.', 'community-jar' )	
							),
			'supports'		=>	array( 'title', 'editor', 'revisions' ),
			'show_in_menu'	=>	'edit.php?post_type=cj_project',
			'menu_position'	=>	6,
			'public'		=>	false,
			'show_ui' => true
		);
		register_post_type( 'cj_email', $args );
		
	 } // end register_post_types

	 
	 
	 
	 
 	/*---------------------------------------------*
	 * 2. Email Subject Meta Box
	 *---------------------------------------------*/
	
	 /**
	  * Registers the meta box for displaying the 'Email Subject' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function add_email_subject_meta_box() {
		 
		 add_meta_box(
		 	'email_subject',
		 	__( 'Email Subject', 'community-jar' ),
		 	array( $this, 'email_subject_display' ),
		 	'cj_email',
		 	'normal',
		 	'low'
		 );
		 
	 } // end add_email_subject_meta_box
	 
	 /**
	  * Renders the user interface for providing a subject line for the email
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function email_subject_display( $post ) {
  
		 wp_nonce_field( plugin_basename( __FILE__ ), $this->subject_nonce );
		 $html = "";
		 // Display the date for the project
		 $html .= '<p id="community-jar-email-subject">';
			 $html .= '<input type="text" id="email_subject" name="email_subject" value="' . get_post_meta( $post->ID, 'email_subject', true ) . '" />';
		 $html .= '</p>';
		 
		 echo $html;
  
	 } // end email_subject_display

	 /**
	  * Saves the email template subject for the incoming post ID.
	  *
	  * @param		int		The current Post ID.
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function save_email_subject( $post_id ) {
		 
		 // If the user has permission to save the meta data...
		 if( $this->user_can_save( $post_id, $this->subject_nonce ) ) { 
		 
		 	// Delete any existing meta data for the email subject
			if( get_post_meta( $post_id, 'email_subject' ) ) {
				delete_post_meta( $post_id, 'email_subject' );
			} // end if
			update_post_meta( $post_id, 'email_subject', esc_js( $_POST[ 'email_subject' ] ) );

		 } // end if
		 
	 } // end save_email_subject 
	 	 
 	/*---------------------------------------------*
	 * 3. Project Owner Metabox
	 *---------------------------------------------*/
	 
	 /**
	  * Registers the meta box for displaying the 'Project Completion' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function add_project_owner_meta_box() {
		 
		 add_meta_box(
		 	'project_owner',
		 	__( 'Project Owner', 'community-jar' ),
		 	array( $this, 'project_owner_display' ),
		 	'cj_project',
		 	'side',
		 	'low'
		 );
		 
	 } // end add_project_owner_meta_box
	 
	 /**
	  * Renders the user interface for completing the project in its associated meta box.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function project_owner_display( $post ) {
  
		 wp_nonce_field( plugin_basename( __FILE__ ), $this->owner_nonce );
		 
		 // First, list the users
		 $html = '<fieldset>';
		 	$html .= '<legend>' . __( 'Project Owner', 'community-jar' ) . '</legend>';
			$html .= __( 'The project owner: ', 'community-jar' );
			$html .= '<select name="project_owners" id="project_owners" class="project_select">';
				foreach( get_users() as $user ) {
					$html .= '<option value="' . $user->user_email . '" ' . selected( $user->user_email, get_post_meta( $post->ID, 'project_owner', true ), false ) . '>' . $user->display_name . '</option>';
				} // end foreach
			$html .= '</select>';
		 $html .= '</fieldset>';
		 
		 // Next, introduce an option to mark the user as anonymous
		 $html .= '<fieldset>';
		 	$html .= '<legend>' . __( 'Owner Visibility', 'community-jar' ) . '</legend>';
			$html .= __( 'The the owner is', 'community-jar' );
			$html .= '<select name="owner_visibility" id="owner_visibility" class="project_select">';
				$html .= '<option value="public" ' . selected( 'public', get_post_meta( $post->ID, 'owner_visibility', true ), false ) . '>' . __( 'Public', 'community-jar' ) . '</option>';
			 	$html .= '<option value="anonymous" ' . selected( 'anonymous', get_post_meta( $post->ID, 'owner_visibility', true ), false ) . '>' . __( 'Anonymous', 'community-jar' ) . '</option>';
			$html .= '</select>';
		 $html .= '</fieldset>';
		 
		 echo $html;
  
	 } // end completed_project_display

	 /**
	  * Saves the project completion data for the incoming post ID.
	  *
	  * @param		int		The current Post ID.
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function save_project_owner( $post_id ) {
		 
		 // If the user has permission to save the meta data...
		 if( $this->user_can_save( $post_id, $this->owner_nonce ) ) { 
		 
		 	// Delete any existing meta data for the owner
			if( get_post_meta( $post_id, 'project_owner' ) ) {
				delete_post_meta( $post_id, 'project_owner' );
			} // end if
			update_post_meta( $post_id, 'project_owner', strip_tags( stripslashes( $_POST[ 'project_owners' ] ) ) );
			
		 	// Delete any existing meta data for the owner
			if( get_post_meta( $post_id, 'owner_visibility' ) ) {
				delete_post_meta( $post_id, 'owner_visibility' );
			} // end if
			update_post_meta( $post_id, 'owner_visibility', $_POST[ 'owner_visibility' ] );			
			 
		 } // end if
		 
	 } // end save_meta_data 
	 
 	/*---------------------------------------------*
	 * 4. Project Date
	 *---------------------------------------------*/
	 
	 /**
	  * Registers the meta box for displaying the 'Project Date' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function add_project_info_meta_box() {
		 
		 add_meta_box(
		 	'project_information',
		 	__( 'Project Information', 'community-jar' ),
		 	array( $this, 'project_info_display' ),
		 	'cj_project',
		 	'side',
		 	'low'
		 );
		 
	 } // end add_project_info_meta_box
	 
	 /**
	  * Renders the user interface for completing the project in its associated meta box.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function project_info_display( $post ) {
  
		 wp_nonce_field( plugin_basename( __FILE__ ), $this->date_nonce );
		 $html = "";
		 // Display the date for the project
		 $html .= '<p id="community-jar-date">';
			 $html = '<img src="' .  COMMUNITYJAR_DIR .'images/calendar.png'. '" id="calendar-icon" />';
			 $html .= '<input type="text" id="datepicker" name="project_date" value="' . $this->get_project_date_formatted($post->ID). '" />';
		 $html .= '</p>';
		 
		 // If the post is published... 
		 if( 'publish' == get_post_status( $post->ID ) ) {
		 
		 	// Display the Short URL
		 	$html .= '<p>';
		 		$html .= '<a href="' . $this->create_project_edit_url($post->ID). '" target="_blank">Public Project Edit Page</a>';
			$html .= '</p>';
			 
			// Display the 'Complete Project' option
			$html .= '<label for="project_is_complete">';
				$html .= '<input type="checkbox" name="project_is_complete" id="project_is_complete" value="1"' . checked( 1, get_post_meta( $post->ID, 'project_is_complete', true ), false ) . '" />&nbsp;';
				$html .= __( 'This project is complete.', 'community-jar' );
			$html .= '</label>';
		 	
		 } // end if
		 
		 echo $html;
  
	 } // end project_info_display

	 /**
	  * Saves the project completion date and short URL for the incoming post ID.
	  *
	  * @param		int		The current Post ID.
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function save_project_info( $post_id ) {
		 
		 // If the user has permission to save the meta data...
		 if( $this->user_can_save( $post_id, $this->date_nonce ) ) { 
		 
		 	// Delete any existing meta data for the owner
			if( get_post_meta( $post_id, 'project_date' ) ) {
				delete_post_meta( $post_id, 'project_date' );
			} // end if
			update_post_meta( $post_id, 'project_date', date("Y-m-d", strtotime($_POST[ 'project_date' ] )));
			
			// If there's no short URL, then save it
			if( '' == get_post_meta( $post_id, 'project_hash', true ) ) {
				update_post_meta( $post_id, 'project_hash', sha1(wp_salt('auth').$post_id));
			} // end if
			
			// If the 'Complete Project' option has been checked, then we need to take it
			if( isset( $_POST['project_is_complete'] ) && '1' == $_POST['project_is_complete'] ) {

				update_post_meta( $post_id, 'project_is_complete', $_POST['project_is_complete'] );	
				
				// And email the site administrator
				$this->send_completed_email( $post_id );
				
			} else {
				
				delete_post_meta( $post_id, 'project_is_complete' );	
				
			} // end if/else
			
		 } // end if
		 
	 } // end save_project_date 
	 
	 /*---------------------------------------------*
	 * 4. Project Date
	 *---------------------------------------------*/
	 
	 /**
	  * Creates a new Publish Meta Box for email template pages.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function add_email_submit_meta_box() {
		 
		 add_meta_box(
		 	'publish',
		 	__( 'Update Template', 'community-jar' ),
		 	array( $this, 'email_submit_display' ),
		 	'cj_email',
		 	'side',
		 	'high'
		 );
		 
	 } // end add_project_info_meta_box
	 
	 /**
	  * Renders the user interface for the new publish meta box with only a save button.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function email_submit_display($post) {
  
		echo '<div class="submitbox" id ="submitpost"><div id="major-publishing-actions"><div id="publishing-action">'.get_submit_button($text = 'Save', $type = 'primary', $name = 'submit', $wrap = false, $other_attributes = NULL).'</div><div class="clear"></div></div></div>';
  
	 } // end project_info_display
	 
	  /**
	  * Creates a new Publish Meta Box for email template pages.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function add_email_shortcodes_meta_box() {
		 
		 add_meta_box(
		 	'email_shortcodes',
		 	__( 'Available Shortcodes', 'community-jar' ),
		 	array( $this, 'email_shortcodes_display' ),
		 	'cj_email',
		 	'side',
		 	'low'
		 );
		 
	 } // end add_project_info_meta_box
	 
	 /**
	  * Renders the user interface for the new publish meta box with only a save button.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function email_shortcodes_display( $post ) {
		?>
		
		<h2>Project</h2>
		<ul>
			<li>[project-title]</li>
			<li>[project-owner-name]</li>
			<li>[project-owner-email]</li>
			<li>[project-owner-phone]</li>
			<li>[project-date]</li>
			<li>[project-description]</li>
			<li>[project-edit-url]</li>
			<li>[project-url]</li>
		</ul>
		<h2>Volunteer</h2>
		<ul>
			<li>[volunteer-name]</li>
			<li>[volunteer-email]</li>
			<li>[volunteer-phone]</li>
			<li>[volunteer-comment]</li>
		</ul>
		<h2>Administrative</h2>
		<ul>
			<li>[admin-email]</li>
			<li>[admin-project-url]</li>
			<li>[admin-login-url]</li>
		</ul>
		<?php
		
 

  
	 } // end project_info_display
	 
	 
 	/*---------------------------------------------*
	 * 5. Project Notification
	 *---------------------------------------------*/ 
	 
	/** 
	 * Emails the project owner and the administrator that the project has just been published.
	 * 
	 * @param	int	$post_id	The ID of the post that's just been published.
	 * @since	1.0
	 * @version	1.0
	 */ 
	public function notify_project_owner_of_publish( $post_id ) {
		
		// If the post is published, send the notification email
		if( $this->post_is_published( $post_id ) ) {
			$this->send_published_email( $post_id );
		} // end if
		
	} // end notify_project_owner
	 
 	/*---------------------------------------------*
	 * 4. Project Volunteers
	 *---------------------------------------------*/ 
	 
	 /**
	  * Registers the meta box for displaying the 'Project Date' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function add_project_volunteer_meta_box() {
		 
		 add_meta_box(
		 	'project_volunteers',
		 	__( 'Project Volunteers', 'community-jar' ),
		 	array( $this, 'project_volunteer_display' ),
		 	'cj_project',
		 	'normal',
		 	'low'
		 );
		 
	 } // end add_project_volunteer_meta_box
	 
	 /**
	  * Renders the user interface for completing the project in its associated meta box.
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function project_volunteer_display( $post ) {
  
		 wp_nonce_field( plugin_basename( __FILE__ ), $this->volunteer_nonce );
		 
		 // First, we need to get all of the users who are volunteers
		 $volunteers = $this->get_pending_volunteers_for( $post->ID );
		 
		 // Next, build the select box for all of the volunteers in the system. Those active will be removed in admin.js
		 $html = '<div class="volunteers">';
			 $html .= '<fieldset class="all-volunteers">';
			 	$html .= '<legend>' . __( 'Pending Volunteers', 'community-jar' ) . '</legend>';
				 $html .= '<select id="pending_volunteers" name="pending_volunteers[]" multiple>';
				 	foreach( $volunteers as $volunteer ) {
				 		if( '' != trim( $volunteer->ID ) ) {
							$comment_id = get_user_meta( $volunteer->ID, 'project-comment-' . $post->ID, true ); 
							$comment = get_comment( $comment_id );
							$phone_number = get_user_meta( $volunteer->ID, 'phone-number', true );
							if($comment->comment_content){
								$the_comment = $comment->comment_content;
							}else{
								$the_comment = 'No Comment';
							}
						 	$html .= '<option value="' . $volunteer->ID . '" title="'. $comment->comment_author_email .'&#10;'.$phone_number.'&#10;'.$the_comment.'">' . $volunteer->display_name . '</option>';
					 	} // end if
				 	} // end foreach
				 $html .= '</select>';
				 $html .= '<a class="button" href="javascript:;" id="add-volunteers">' . __( 'Add Volunteers', 'community-jar' ) . '</a>';
			 $html .= '</fieldset>';
			 
			 // After that, we get all of the volunteers for this project
			 $active_volunteers = $this->get_volunteers_for( $post->ID, true );
			 
			 // Finally, build the select box for the volunteers on the Project.
			 $html .= '<fieldset class="active-volunteers">';
			 	$html .= '<legend>' . __( 'Project Volunteers', 'community-jar' ) . '</legend>';
				 $html .= '<select id="active_volunteers" name="active_volunteers[]" multiple>';
				 	foreach( $active_volunteers as $volunteer ) {
				 		if( '' != trim( $volunteer->ID ) ) {
						 	$comment_id = get_user_meta( $volunteer->ID, 'project-comment-' . $post->ID, true ); 
							$comment = get_comment( $comment_id );
							$phone_number = get_user_meta( $volunteer->ID, 'phone-number', true );
							if($comment->comment_content){
								$the_comment = $comment->comment_content;
							}else{
								$the_comment = 'No Comment';
							}
						 	$html .= '<option value="' . $volunteer->ID . '" title="'. $comment->comment_author_email .'&#10;'.$phone_number.'&#10;'.$the_comment.'">' . $volunteer->display_name . '</option>';
					 	} // end if
				 	} // end foreach
				 $html .= '</select>';
				 $html .= '<a class="button" href="javascript:;" id="remove-volunteers">' . __( 'Remove Volunteers', 'community-jar' ) . '</a>';
			 $html .= '</fieldset>';
		 $html .= '</div><!-- /.volunteers -->';
		 
		 $html .= '<div class="approved-notification">';
		 	$html .= '<p>';
		 		$html .= __( 'Please remember to SAVE this project after you have approved volunteers.', 'community-jar' );
		 	$html .= '</p>';
		 $html .= '</div><!-- /.approved-notification -->';
		 
		 echo $html;
  
	 } // end project_volunteer_display

	 /**
	  * Saves the project completion data for the incoming post ID.
	  *
	  * @param		int		The current Post ID.
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function save_project_volunteers( $project_id ) {
		 
		 // If the user has permission to save the meta data...
		 if( $this->user_can_save( $project_id, $this->volunteer_nonce ) ) { 
	 
			 // Grab the collection of volunteers and the ID of the project
			 $active_volunteers = $_POST['active_volunteers'];
			 $all_volunteers = $_POST['pending_volunteers'];
			 
			 // If there are volunteers who are set
			 if( isset( $active_volunteers ) && 0 < count( $active_volunteers ) ) {
			 
			 	// First, go through each of the active volunteers for this project
				foreach( $active_volunteers as $volunteer_id ) {

					// Activate the volunteer
					update_user_meta( $volunteer_id, $project_id, 'project' );
					
					/* --- Email the Volunteer -------- */
					
					// Get the email page
					$page = get_page_by_title( '[Volunteer] Volunteer Approved', OBJECT, 'cj_email' );
					
					// Get the email's subject
					$subject = get_post_meta( $page->ID, 'email_subject', true );
					$searchArray = array('&#8217;', '&quot;');
					$replaceArray = array("'", '"');
					$subject = str_ireplace( $searchArray, $replaceArray, $this->replace_tags( $subject, $volunteer_id, $project_id ) );
					
					
					// Make sure new lines are replaced with page breaks
					$email = get_post( $page->ID );
					$message = str_ireplace( "\n", "<br />", $email->post_content );
					$message = $this->replace_tags( $message, $volunteer_id, $project_id );
					
					// Now get the volunteer
					$volunteer = get_userdata( $volunteer_id );
					
					// Finally, email the volunteer
					add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );
					
					wp_mail( $volunteer->user_email, $subject, $message, $this->cj_email_headers );
					
					/* --- Email the Project Owner --- */
					
					// And now email the Project Owner
					$project = get_post( $project_id );
					$project_owner_id = $project->post_author;
					$project_owner = get_the_author_meta( 'user_email', $project_owner_id );
					
					// Get the email page
					$page = get_page_by_title( '[Project Owner] Volunteer Approved', OBJECT, 'cj_email' );
					
					// Get the email's subject
					$subject = get_post_meta( $page->ID, 'email_subject', true ); 
					$searchArray = array('&#8217;', '&quot;');
					$replaceArray = array("'", '"');
					$subject = str_ireplace( $searchArray, $replaceArray, $this->replace_tags( $subject, $volunteer_id, $project_id ) );

					// Make sure new lines are replaced with page breaks
					$email = get_post( $page->ID );
					$message = str_ireplace( "\n", "<br />", $email->post_content );
					$message = $this->replace_tags( $message, $volunteer_id, $project_id );
					
					// Now get the project owner's email address
					$project_owner_email = get_post_meta( $project_id, 'project_owner', true );

					// And email the project owner
					add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );
					
					wp_mail( $project_owner_email, $subject, $message, $this->cj_email_headers );
					
				} // end foreach
				
			 } // end if/else
			 
			 // If 'All Volunteers' are set, loop through them and delete meta data if it exists
			 if( isset( $all_volunteers ) && 0 < count( $all_volunteers ) ) {
								 
				 foreach( $all_volunteers as $volunteer_id ) {
					 if( 'project' == get_user_meta( $volunteer_id, $project_id, true ) ) {
						 update_user_meta( $volunteer_id, $project_id, 'pending' );
					 } // end if
				 } // end foreach
				 
			 } // end if
	 
		 } // end if
		 
		 // Finally, if there are approved volunteers, we need to send an email
		 if( null != $this->get_volunteers_for( $project_id, true ) ) {
			 
			// Get the project owner
			$project_owner_email = get_user_meta( $project_id, 'project_owner', true );
			
			// Notify them that new volunteers have been approved
			
			wp_mail( $project_owner_email, 'When does this get Sent?', 'New volunteer(s) have been approved for your project!', $this->cj_email_headers );
			 
		 } // end if
		 
	 } // end save_project_volunteers 
	 
 	/*---------------------------------------------*
	 * 7. Project Template Registration
	 *---------------------------------------------*/
	 
	/**
	 * Copies the template from the `views/templates` directory to the root of the active theme
	 * directory so that it can be applied to pages.
	 *
	 * @verison	1.0
	 * @since	1.0
	 */
	public static function register_project_template() {
		
		// First, locate the active theme directory
		$theme_dir = get_template_directory();
		$template_destination = $theme_dir . '/community-jar-project-submission.php';
		
		// Next, grab a reference to the template file in the `templates` directory
		$template_source = dirname( __FILE__ ) . '/views/templates/community-jar-project-submission.php';
		
		// After that, check to see if the template already exists. If so don't copy it; otherwise, copy if
		if( ! file_exists( $template_destination ) ) {
			
			// Create the file
			touch( $template_destination );
			
			// Read the source file
			if( null != ( $template_handle = @fopen( $template_source, 'r' ) ) ) {
			
				// Now read the contents of the file into a string
				if( null != ( $template_content = fread( $template_handle, filesize( $template_source ) ) ) ) {
				
					// Relinquish the resource
					fclose( $template_handle );
					
				} // end if
				
			} // end if
						
			// Now write the data from the string to the destination
			if( null != ( $template_handle = @fopen( $template_destination, 'r+' ) ) ) {
			
				// Attempt to write the contents of the file to disk
				if( null != fwrite( $template_handle, $template_content, strlen( $template_content ) ) ) {
				
					// Relinquish the resource
					fclose( $template_handle );
					
				} // end if
			} // end if
			
		} // end if

	} // end register_project_template
	
	/**
	 * Checks to see if project view templates exsist in theme
	 * if not wordpress uses templates from the 'templates' directory.
	 *
	 * @verison	1.0
	 * @since	1.0
	 */
	 
	public function communityjar_set_template( $template ){
		if( is_post_type_archive('cj_project') )
			if ( $theme_file = locate_template( array ( 'archive-cj_project.php' ) ) ) {
				$template = $theme_file;
			} else {
				$template = plugin_dir_path( __FILE__ ) . 'templates/archive-cj_project.php';
			}
		if( is_singular('cj_project') )
			if ( $theme_file = locate_template( array ( 'single-cj_project.php' ) ) ) {
				$template = $theme_file;
			} else {
				$template = plugin_dir_path( __FILE__ ) . 'templates/single-cj_project.php';
			}
		return $template;
	}
	 
 	/*---------------------------------------------------------*
	 * 8. Programmatically Create Pages, Email Templates, And Default Admin Options
	 *---------------------------------------------------------*/
	
	/**
	 * If they do not already exist, programmatically generates the email templates upon plugin activation.
	 */
	public static function create_email_templates() {
		
		// Volunteer. Volunteer Approved.
		if( null == get_page_by_title( '[Volunteer] Volunteer Approved', OBJECT, 'cj_email' ) ) {
			self::create_page( 'volunteer-volunteer-approved', '[Volunteer] Volunteer Approved', 'publish', 'cj_email',  COMMUNITYJAR_DIR . 'views/email/volunteer.volunteer-approved.html' );
		} // end if
 		
		// Project Owner. Volunteer Approved.
		if( null == get_page_by_title( '[Project Owner] Volunteer Approved', OBJECT, 'cj_email' ) ) {
			self::create_page( 'project-owner-volunteer-approved', '[Project Owner] Volunteer Approved', 'publish', 'cj_email',  COMMUNITYJAR_DIR . 'views/email/project-owner.volunteer-approved.html' );
		} // end if
		
		// Project Owner. Project Approved.
		if( null == get_page_by_title( '[Project Owner] Project Approved', OBJECT, 'cj_email' ) ) {
			self::create_page( 'project-owner-project-approved', '[Project Owner] Project Approved', 'publish', 'cj_email',  COMMUNITYJAR_DIR . 'views/email/project-owner.project-approved.html'  );
		} // end if
		
		// Project Owner. Project Updated.
		if( null == get_page_by_title( '[Project Owner] Project Updated', OBJECT, 'cj_email' ) ) {
			self::create_page( 'project-owner-project-updated', '[Project Owner] Project Updated', 'publish', 'cj_email',  COMMUNITYJAR_DIR . 'views/email/project-owner.project-updated.html' );
		} // end if
		
		// Project Owner. Project Completed.
		if( null == get_page_by_title( '[Project Owner] Project Completed', OBJECT, 'cj_email' ) ) {
			self::create_page( 'project-owner-project-completed', '[Project Owner] Project Completed', 'publish', 'cj_email',  COMMUNITYJAR_DIR .  'views/email/project-owner.project-completed.html' );
		} // end if
		
		// Admin. Project Submission.
		if( null == get_page_by_title( '[Admin] Project Submitted', OBJECT, 'cj_email' ) ) {
			self::create_page( 'admin-project-submitted', '[Admin] Project Submitted', 'publish', 'cj_email',  COMMUNITYJAR_DIR . 'views/email/admin.project-submitted.html' );
		} // end if
		
		// Admin. Volunteer Submission.
		if( null == get_page_by_title( '[Admin] Volunteer Submitted', OBJECT, 'cj_email' ) ) {
			self::create_page( 'admin-volunteer-submitted', '[Admin] Volunteer Submitted', 'publish', 'cj_email', COMMUNITYJAR_DIR . 'views/email/admin.volunteer-submitted.html');
		} // end if
		if(False == get_option('cj_settings')){
			update_option( 'cj_settings', array('from_name'=>'Community Jar','from_email'=>get_option( 'admin_email' ),'admin_email'=>get_option( 'admin_email' )) );
		}

	} // end create_email_templates
	
	/**
	 * If it does not already exist, programmatically creates the Project Submission page
	 * upon plugin activation.
	 */
	public static function create_project_pages() {
		
		if( null == get_page_by_title( 'Project Submission', OBJECT, 'page' ) ) {
		
			$page_id = self::create_page( 'project-submission', 'Project Submission', 'publish', 'page' );
			update_post_meta( $page_id, '_wp_page_template', 'community-jar-project-submission.php' );
			
		} // end if

	} // end create_project_pages

	/**
	 * When the plugin is deactivated, the Project Submission page will be deleted.
	 */
	public static function delete_project_submission_page() {
		
		$page = get_page_by_title( 'Project Submission', OBJECT, 'page' );
		wp_delete_post( $page->ID, true );
		
		
	} // end create_project_submission_page
	
 	/*---------------------------------------------*
	 * Helper Functions
	 *---------------------------------------------*/
	 
	 /**
	  * A helper function for programmatically creating pages.
	  *
	  * @param		string	$slug			The string used to represent the page slug (as part of the URL)
	  * @param		string	$title			The title of the page that's displayed when the post is displayed
	  * @param		string	$post_status	The publish status of the post
	  * @param		string	$post_type		The type of post (i.e., page, post, email, project, etc.) to insert
	  * @return		int						The ID of the page that was created.
	  * @version	1.0
	  * @since		1.0
	  */
	 private static function create_page( $slug, $title, $post_status, $post_type, $content = ''  ) {
		 
		 $author_id = 1;
		 
		 // If the content isn't empty, then let's parse the subject element and the body element from the file
		 $subject = '';
		 if( '' != $content ) {
			 
			 // Read the response from the URL.
			 $response = wp_remote_get( $content );
			 $response = $response['body'];
			 
			 // Parse out the subject line
			 preg_match_all("/\<subject\>(.*?)\<\/subject\>/", $response, $matches );
			 $subject = $matches[1][0];
			 
			 // Parse out the body
			 preg_match_all("/<message[^>]*>(.*?)<\/message>/is", $response, $matches );
			 $content = $matches[1][0];
			 
		 } // end if
		 
		 // Insert the post
		 $post_id = wp_insert_post(
		 	array(
		 		'comment_status'	=>	'closed',
		 		'ping_status'		=>	'closed',
		 		'post_author'		=>	$author_id,
		 		'post_name'			=>	$slug,
		 		'post_title'		=>	$title,
		 		'post_status'		=>	$post_status,
		 		'post_type'			=>	$post_type,
		 		'post_content'		=>	$content
		 	)
		 );
		 
		 // If the subject isn't empty and the post exists
		 if( '' != $subject && -1 != $post_id ) {
			 
			 // Set the meta data for the subject
			 update_post_meta( $post_id, 'email_subject', $subject );
			 
		 } // end if
		 
		 return $post_id;
		 
	 } // end create_page
	 
	 /**
	  * Determines whether or not the specified key has been set.
	  *
	  * @version	1.0
	  * @since		1.0
	  */
	 private function option_is_set( $post_id, $key ) {
		 return true == get_post_meta( $post_id, $key, true ) ? true : false;
	 } // end option_is_set
	 
	 /**
	  * Determines whether or not the current user has the ability to save meta data associated with this post.
	  *
	  * @param		int		$post_id	The ID of the post being save
	  * @param		bool				Whether or not the user has the ability to save this post.
	  * @version	1.0
	  * @since		1.0
	  */
	 private function user_can_save( $post_id, $nonce ) {
		
	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) ) ? true : false;
	    
	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision) && $is_valid_nonce;

	 } // end user_can_save
	 
	 /**
	  * Determines if the specified post is being updated or being published.
	  *
	  * @param		int		$post_id	The ID of the post being updated.
	  * @return		bool				Whether or not the specified post is being published.
	  * @version	1.1
	  * @since		1.0
	  */
	 private function post_is_published( $post_id ) {
		
		return 
			'cj_project' == get_post_type() &&
			'publish' == get_post_status( $post_id ) && 
			'true' != get_post_meta( $post_id, 'project_owner_emailed', true );
		 
	 } // end post_is_published
	 
	 /**
	  * Determines if the specified post is being updated or being published.
	  *
	  * @param		int		$post_id	The ID of the post being updated.
	  * @return		bool				Whether or not the specified post is being updated or not.
	  * @version	1.1
	  * @since		1.0
	  */
	 private function post_is_updated( $post_id ) {
		 
		 return 
		 	'cj_project' == get_post_type() &&
		 	'true' != get_post_meta( $post_id, 'project_owner_emailed', true ) && 
		 	'inherit' == get_post_status( $post_id ) && 
		 	'update' == strtolower( $_POST['original_publish'] ) &&
		 	'trash' != $_GET['action'];
		 
	 } // end post_is_updated

	 /**
	  * Sends an email notifying the user that the Project has been published.
	  *
	  * @param		int		$post_id	The ID of the post being published.
	  * @version	1.0
	  * @since		1.0
	  */	 
	 private function send_published_email( $post_id ) {
		
		// Get the email page
		$page = get_page_by_title( '[Project Owner] Project Approved', OBJECT, 'cj_email' );
		
		// Get the email's subject
		$subject = get_post_meta( $page->ID, 'email_subject', true ); 
		$searchArray = array('&#8217;', '&quot;');
		$replaceArray = array("'", '"');
		$subject = str_ireplace( $searchArray, $replaceArray, $this->replace_tags( $subject, null, $post_id ) );
		
		
		$email = get_post( $page->ID );

		// Make sure new lines are replaced with page breaks
		$message = str_ireplace( "\n", "<br />", $email->post_content );
		$message = $this->replace_tags( $message, null, $post_id );

		// Now get the project owner's email address
		$project_owner_email = get_post_meta( $post_id, 'project_owner', true );
		
		add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );
		
		if( wp_mail( $project_owner_email, $subject, $message, $this->cj_email_headers ) ) {
		
			// Mark that we've notified the project owner
			update_post_meta( $post_id, 'project_owner_emailed', 'true' );	
			
		} // end if
		 
	 } // end send_published_email

	 /**
	  * Sends an email notifying the user that the Project has been completed.
	  *
	  * @version	1.0
	  * @since		1.0
	  */
	 private function send_completed_email( $post_id ) {
		
		// Get the email page
		$page = get_page_by_title( '[Project Owner] Project Completed', OBJECT, 'cj_email' );
		
		// Get the email's subject
		$subject = get_post_meta( $page->ID, 'email_subject', true ); 
		$searchArray = array('&#8217;', '&quot;');
		$replaceArray = array("'", '"');
		$subject = str_ireplace( $searchArray, $replaceArray, $this->replace_tags( $subject, null, $post_id ) );

		
		$email = get_post( $page->ID );
		
		// Make sure new lines are replaced with page breaks
		$message = str_ireplace( "\n", "<br />", $email->post_content );
		$message = $this->replace_tags( $message, null, $post_id );
		
		// Now get the project owner's email address
		$project_owner_email = get_post_meta( $post_id, 'project_owner', true );
		
		add_filter( 'wp_mail_content_type', create_function ( '', 'return "text/html";' ) );
		
		wp_mail( $project_owner_email, $subject, $message, $this->cj_email_headers );
		 
	 } // end send_completed email

	 /**
	  * Returns a list of all of the volunteers for the specified project. 
	  *
	  * @param		int		$post_id	
	  * @param		bool	$active
	  * @return		array	$volunteers	The list of volunteers that are [active|inactive] for the given project.
	  * @version	1.0
	  * @since		1.0
	  */
	 public function get_volunteers_for( $post_id, $active = false ) {
		 
		 // Initialize the $volunteers array
		 $volunteers = array();
		 
		 // First, we setup the array for all of the roles
		 $roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber', 'volunteer' );
		 
		 // If we're only looking at the list of active volunteers
		 if( $active ) {
			 
			 // We iterate through each role, querying the users for each role
			 foreach( $roles as $role ) {
				 
				 $args = array(
				 	'role'			=> 	$role,
				 	'meta_query'	=>	array(
				 							array(
				 								'key'	=>	$post_id,
				 								'value'	=>	'project'
				 							)
				 						)
				 );
				 
				 // Now we store the results of the query into $volunteer_results
				 $volunteer_results = new WP_User_Query( $args );
				 $volunteer_results = $volunteer_results->get_results();
				 
				 // If there are results, we'll merge them into the $volunteers array
				 if( $volunteer_results ) {
					 $volunteers = array_merge( $volunteers, $volunteer_results );
				 } // end if
				 
			 } // end foreach
		
		 // Otherewise, we're looking for inactive volunteers	 
		 } else { 
			 
			 // We iterate through each role, querying the users for each role
			 foreach( $roles as $role ) {
				 
				 $args = array(
				 	'role'			=> 	$role,
				 	'meta_query'	=>	array(
				 							array(
				 								'key'		=>	$post_id,
				 								'value'		=>	'project',
				 								'compare'	=>	'NOT EXISTS'
				 							)
				 						)
				 );
				 
				 // Now we store the results of the query into $volunteer_results
				 $volunteer_results = new WP_User_Query( $args );
				 $volunteer_results = $volunteer_results->get_results();
				 
				 // If there are results, we'll merge them into the $volunteers array
				 if( $volunteer_results ) {
					 $volunteers = array_merge( $volunteers, $volunteer_results );
				 } // end if
				 
			 } // end foreach
			 
		 } // end if/else
		 
		return $volunteers;
		 
	 } // end get_volunteers_for

	 /**
	  * Returns a list of all of the volunteers who have signed up (or are pending) for a project
	  *
	  * @param		int		$post_id	The ID of the project
	  * @return		array	$volunteers	The list of pending volunteers that are [active|inactive] for the given project.
	  * @version	1.0
	  * @since		1.0
	  */
	 private function get_pending_volunteers_for( $post_id ) {
		 
		 $volunteers = array();
		 
		 // First, we setup the array for all of the roles
		 $roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber', 'volunteer' );
		 
		 // We iterate through each role, querying the users for each role
		 foreach( $roles as $role ) {
			 
			 $args = array(
			 	'role'			=> 	$role,
			 	'meta_query'	=>	array(
			 							array(
			 								'key'	=>	$post_id,
			 								'value'	=>	'pending'
			 							)
			 						)
			 );
			 
			 // Now we store the results of the query into $volunteer_results
			 $volunteer_results = new WP_User_Query( $args );
			 $volunteer_results = $volunteer_results->get_results();
			 
			 // If there are results, we'll merge them into the $volunteers array
			 if( $volunteer_results ) {
				 $volunteers = array_merge( $volunteers, $volunteer_results );
			 } // end if
			 
		 } // end foreach

		 return $volunteers;
		 
	 } // end get_pending_volunteers_for
	 
	 /**
	  * Returns a count of all of the volunteers for the specified project. 
	  *
	  * @param		int		$post_id	
	  * @return		int		The number of volunteers for this project
	  * @version	1.0
	  * @since		1.0
	  */
	 public function get_volunteer_count( $project_id ) {
		 return count( $this->get_volunteers_for( $project_id, true ) );
	 } // end get_volunteer_count
	 
	 /**
	  * Determines if the current page being displayed has the Community Jar Project Submission
	  * template applied to it.
	  *
	  * @return		bool	True if the current page has the template; otherwise, false.
	  * @version	1.0
	  * @since		1.0
	  */
	 private function using_project_submission_template() {
		 return 'community-jar-project-submission.php' == get_page_template_slug();
	 } // end using_project_submission_template
	 
	 /**
	  * Determines if the parameters of the incoming input contains empty values.
	  * 
	  * @param  array $input 	The Project input
	  * @return bool  $is_valid	Whether or not the project values are valid.
	  * @version 1.0
	  * @since  1.0
	  */
	 private function validate_project( $input ) {

	 	$is_valid = true;

	 	foreach( $input as $key => $val ) {

	 		// If any of the fields *except* the 'is-anonymous' is empty, then it's invalid
	 		if( 'is-anonymous' != $key && empty( $input[  $key ] ) ) {

	 			$is_valid = false;
	 			break;

	 		} // end if

	 	} // end foreach

	 	return $is_valid;

	 } // end validate_project
	 
	 /**
	  * Determines if the parameters of the incoming input contains empty values.
	  * 
	  * @param  array $input 	The Volunteer input
	  * @return bool  $is_valid	Whether or not the project values are valid.
	  * @version 1.0
	  * @since  1.0
	  */
	 private function validate_volunteer( $input ) {
		 
	 	$is_valid = true;

	 	foreach( $input as $key => $val ) {

	 		if( 'volunteer-comments' != $key && empty( $input[  $key ] ) ) {

	 			$is_valid = false;
	 			break;

	 		} // end if

	 	} // end foreach

	 	return $is_valid;
		 
	 } // end validate_volunteer

	 /**
	  * Retrieves or generates a user for the project based on the incoming email address.
	  * 
	  * @param  string $owner_address	The email address for the Project Owner.
	  * @return object $user			The user that was created or retrieved.
	  */
	 private function generate_project_user( $user_address ) {

		// Next, check to see if the user exists. If not, create one as a volunteer.
		if ( false == ( $user = get_user_by( 'email', $user_address ) ) ) {

			// Create the password and the user
			$password = wp_generate_password( 12, false, false );
			$user_id = wp_create_user( $user_address, $password, $user_address );

			// Now set the role
			$user = new WP_User( $user_id );
			$user->set_role( 'volunteer' );
			
		} // end if

		return $user;

	 } // end generate_project_user
	 
	 /**
	  * Updates the associated user information based on what's been in the project field.
	  *
	  * @param	int		$user_id	The ID of the user who's meta data we are setting.
	  * @param	string	$name		The name of the user who is being saved.
	  * @param	string	$phone		The user's phone number
	  * @param	string	$email		the email address for the user who has been created
	  */
	 private function set_user_info( $user_id, $name, $phone, $email ) {
		 
		 // Split the first name and the last name
		 $owner_name = explode(' ', $name );
		 $first_name = '';
		 $last_name = '';
		 if( 1 < count( $owner_name ) ) {
			 
			 $first_name = $owner_name[0];
			 $last_name = $owner_name[1];
			 
		 } else {
		 
		 	$first_name = $name;
			 
		 } // end if
		 
		 //update_user_meta( $user_id, 'first_name', $name, get_user_meta( $user_id, 'first_name', true ) );
		 //update_user_meta( $user_id, 'last_name', $last_name, get_user_meta( $user_id, 'last_name', true )  );
		 update_user_meta( $user_id, 'phone-number', $phone, get_user_meta( $user_id, 'phone-number', true )  );
		 update_user_meta( $user_id, 'nickname', $name, get_user_meta( $user_id, 'nickname', true )  );
		 update_user_meta( $user_id, 'display_name', $name);
		 wp_update_user( array ('ID' => $user_id, 'display_name' => $name ) ) ;
		 
	 } // end function set_user_info

	 /**
	  * Creates a project post based on the incoming argumnets.
	  * 
	  * @param  object 		$user	The user that is the project owner.
	  * @param  string 		$title	The title of the project.
	  * @param  string 		$desc	The description of the project.
	  * @return int | bool			The ID of the project, or WP_Error on failure.
	  */
	 private function create_project( $user, $title, $desc ) {

		$title = strip_tags( stripslashes( trim( $title ) ) );

		// Set the arguments for the post (we set it in pending status until the admin approves it)
		$post_args = array(
			'comment_status'	=>	'closed',
			'ping_status'		=>	'closed',
			'post_author'		=>	$user->data->ID,
			'post_title'		=>	$title,
			'post_content'		=>	$desc,
			//'post_excerpt'		=>	$desc,
			'post_name'			=>	$title,
			'post_status'		=>	'pending',
			'post_type'			=>	'cj_project'
		);

		return wp_insert_post( $post_args, true );
		
	 } // end create_project
	 
	 /**
	  * Updates a project post based on the incoming argumnets.
	  * 
	  * @param	int			$id		The ID of the project to update
	  * @param  object 		$user	The user that is the project owner.
	  * @param  string 		$title	The title of the project.
	  * @param  string 		$desc	The description of the project.
	  * @return int | bool			The ID of the project, or WP_Error on failure.
	  */
	 private function update_project( $id, $user, $title, $desc ) {
	
		
		// Set the arguments for the post
		$post_args = array(
			'ID'				=>	$id,
			'post_author'		=>	$user->data->ID,
			'post_title'		=>	wp_strip_all_tags( $title ),
			'post_content'		=>	$desc
		);

		return wp_update_post( $post_args, true );
		
	 } // end update_project
	 
	 /**
	  * Retrieves the name of the project owner
	  *
	  * @param	int		$project_id		The ID of the project
	  *	@return	string	$name			The first and last name of the project owner.
	  */
	 public function get_project_owner( $project_id ) {
		 
		 // First, get the user object
		 $user = get_user_by( 'email', get_post_meta( $project_id, 'project_owner', true ) );
		 
		 // Next, read their user meta
		 $first_name = get_user_meta( $user->data->ID, 'first_name', true );
		 $last_name = get_user_meta( $user->data->ID, 'last_name', true );
		 
		 If($first_name && $last_name){
			$name = $first_name.' '.$last_name; 
		 }else{
			$name = get_user_meta( $user->data->ID, 'nickname', true );
		}
		 // Build the actual name
		
		 
		 // If the name doesn't exist, use the email address
		 if( 0 == strlen( trim( $name ) ) ) {
			 $name = $user->data->user_nicename;
		 } // end if
		 
		 return $name;
		 
		 
	 } // end get_project_owner
	 /**
	  * Retrieves the project date and converts it to normal format
	  *
	  * @param	int		$project_id			The ID of the project
	  *	@return	string	$date_formatted		Converted date format
	  * @version	1.1.2
	  * @since 		1.1.2
	  */

	 private function get_project_date_formatted( $project_id ) {
		
		 $date = get_post_meta( $project_id, 'project_date', true );
		 if ( !empty ( $date) ){
			return date("m/d/Y", strtotime($date));
		 }
		 
		 
	 } // end get_project_date_formatted
	  /**
	  * Flushes rewite rules for custom post type
	  *
	  * @param	int		$project_id		The ID of the project
	  *	@return	string	$name			The first and last name of the project owner.
	  */
	 public function communityjar_plugin_activation() {
		
		// Register types to register the rewrite rules  
		$this->register_post_types();  
  
		// Then flush them  
		flush_rewrite_rules(); 
		 
	 } // end flush_rules
	 /**
	  * Flushes rewite rules for custom post type
	  *
	  * @param	int		$project_id		The ID of the project
	  *	@return	string	$name			The first and last name of the project owner.
	  */
	 public function communityjar_plugin_deactivation() {  
  
		// Then flush them  
		flush_rewrite_rules(); 
		 
	 } // end flush_rules
	
	/**
	  * Returns the project edit url.
	  *
	  * @uses add_query_arg() & get_template_page_url()
	  *
	  * @param string $project_id which template are you looking for?
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function create_project_edit_url($project_id) {
		
		$project_hash = get_post_meta( $project_id, 'project_hash', true); 
		return add_query_arg( array('my_project' => $project_hash ), $this->get_template_page_url('community-jar-project-submission.php'));
		
	}
	 /**
	  * Returns the first page which page currently is using a custom page template.
	  *
	  * @uses get_pages()
	  *
	  * @param string $page which template are you looking for?
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	 public function get_template_page_url($pagetemplate) {
		$page_using_template = get_pages(
			array(
				'meta_key' => '_wp_page_template',
				'meta_value' => $pagetemplate,
				'number' => 1
			)
		);
		return get_permalink($page_using_template[0]->ID);
	}
	 /**
	  * Removes project_hash from postmeta database to help keep site secure.
	  * If trashed project is restored, recreate project_hash.
	  *
	  * @param string $new_status, $old_status, $post
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	public function project_trashed( $new_status, $old_status, $post ) {
		if ($new_status == 'trash' && 'cj_project' == get_post_type($post->ID)) {
			delete_post_meta($post->ID, 'project_hash');
		}
		if ($old_status == 'trash' && 'cj_project' == get_post_type($post->ID)) {
			update_post_meta($post->ID, 'project_hash', sha1(wp_salt('auth').$post->ID));
		}
	}
	 /**
	  * Decides whether you are submitting a new project or updating an old one, then attaches correct functions.
	  *
	  * RETURNS project if found
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	public function submission_form_validation(){
		if( isset( $_GET['my_project'] ) && !empty($_GET['my_project'])) {
				$project_id = $this->detect_project( $_GET['my_project']);
				if(NULL != $project_id){
					$this->update_existing_project( $project_id  ); 
					$project = get_post( $project_id );
					return $project;
				}  else {	
					$this->register_new_project(); 
				}
			} else {	
				$this->register_new_project();
			} // end if/else
	}
	 /**
	  * Adds Volunteer registration function to all single project view pages
	  *
	  * @param string $name
	  *
	  * @version	1.0
	  * @since 		1.0
	  */
	public function single_project_header_hook( $name ) {
		if( 'cj_project' == get_post_type() && is_single()) {
			$this->register_new_volunteer();
		}
	}
	 /**
	  * Fixes post type names, and changes all old 'email' types to the new 'cj_email' type.
	  *
	  *
	  * @version	1.1.2
	  * @since 		1.1.2
	  */
	public function convert_emails(){
		
		if(!post_type_exists('email')){
			
			$args = array( 'post_type' => 'email','meta_key'=> 'email_subject','posts_per_page' => -1, 'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'));

			$myposts = get_posts( $args );
			
				foreach ( $myposts as $post ) {
					$my_post = array(
					  'post_type'	=> 'cj_email',
					  'ID'			=> $post->ID
					);
					// Insert the post into the database
					wp_update_post( $my_post );
				} 
		
			wp_reset_postdata();
			}
	

	}
	/**
	  * Fixes post type names, and changes all old 'project' types to the new 'cj_project' type.
	  *
	  *
	  * @version	1.1
	  * @since 		1.1
	  */
	public function convert_projects(){

	$args = array( 'post_type' => 'project','meta_key'=> 'owner_visibility','posts_per_page' => -1, 'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'));

	$myposts = get_posts( $args );
		foreach ( $myposts as $post ) {
			$my_post = array(
			  'post_type'	=> 'cj_project',
			  'ID'			=> $post->ID
			);
			// Insert the post into the database
			wp_update_post( $my_post );
		} 
	
		wp_reset_postdata();

	}
	 /**
	  * Fixes date format in database
	  *
	  *
	  * @version	1.1.2
	  * @since 		1.1.2
	  */
	public function convert_date_formats(){ 

		$args = array( 'post_type' => array('project','cj_project'),'meta_key'=> 'owner_visibility','posts_per_page' => -1, 'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'));

		$myposts = get_posts( $args );
			foreach ( $myposts as $post ) {
				$date = get_post_meta( $post->ID, 'project_date', true );
				$date_formatted = date("Y-m-d", strtotime($date));
				update_post_meta ($post->ID,'project_date',$date_formatted);
		} 
		
		wp_reset_postdata();

	}
	/**
	  * Performs routine maintenance during updates. 
	  *
	  * Runs on admin_init
	  *
	  * @version	1.1.2
	  * @since 		1.1.2
	  */
	public function cj_upgradecheck(){
		
		$cj_current_version = get_option('cj_version');
		
		if($cj_current_version != COMMUNITY_JAR_VERSION){
		
			
			if($cj_current_version < '1.1.2'){
				$this->convert_emails();
				$this->convert_projects();
				$this->convert_date_formats();
			}
			
			update_option('cj_version',COMMUNITY_JAR_VERSION);
		}
	}
} // end class

$GLOBALS['community-jar'] = new CommunityJar( new Community_Jar_Volunteer_Manager(), new Community_Jar_Tokenizer(), new Community_Jar_Notification() );
?>