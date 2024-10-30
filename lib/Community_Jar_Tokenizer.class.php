<?php
/**
 * Community Jar Tokenizer is responsible for looking for certain tokens or key values
 * (like in shortcodes) and replacing it from information from the database.
 *
 * Specifically, this class is responsible for taken specific tokens and replace them
 * with meta data values.
 *
 * Below are the breakdown of the shortcodes that this class supports
 *
 *	## Project
 *
 *	- [project-title]
 *	- [project-owner-name]
 *	- [project-owner-email]
 *	- [project-owner-phone]
 *	- [project-date]
 *	- [project-description]
 *	- [project-edit-url]
 *	- [project-url]
 *	
 *	## Volunteers
 *	
 *	- [volunteer-name]
 *	- [volunteer-email]
 *	- [volunteer-phone]
 *	- [volunteer-comment] (or if no comment given “No additional comments”)
 *	
 *	## Administrators
 *	
 *	- [admin-email]
 *	- [admin-project-url]
 *	- [admin-login-url]
 *
 * @version 1.0
 */
class Community_Jar_Tokenizer {

	/*--------------------------------------------*
	 * Functions
	 *--------------------------------------------*/
	 
	 /**
	  * Retrieves the specified value from the tokens defined in the class description.
	  *
	  * @param	int		$id						The ID of the project of the volunteer.
	  * @param	int		$key					The token to replace.
	  * @param	int		$project_id	optional	The ID of the project for which information is being retrieved.
	  * @return	string							The title of the project.
	  */
	 public function get_value( $id, $key, $content, $project_id = -1 ) {
		 
		 $value = null;
 
		 // Read all the arguments
		 //echo "<br />";
		 //print_r( func_get_args() );
		 //echo "<br />";
		 
		 // Print what we're switching on
		 //print_r( 'Switching on: ' . $key );
		 //echo "<br />";
		 
		 switch( strtolower( $key ) ) {

			 /* --- Projects ---------- */
			 
			 case '[project-title]':
			 	$value = get_the_title( $project_id );
			 	break;
			 	
			 case '[project-owner-name]':
			 	$value = $this->get_project_owner( $id, $key, $project_id );
			 	break;

			 case '[project-owner-email]':
			 	$value = $this->get_project_owner( $id, $key, $project_id );
			 	break;
			 	
			 case '[project-owner-phone]':
			 	$value = $this->get_project_owner( $id, $key, $project_id );
			 	break;
			 	
			 case '[project-date]':
			 	$value = $GLOBALS['community-jar']->get_project_date_formatted($project_id);
			 	break;
			 	
			 case '[project-description]':
			 	$value = $this->get_project_description( $project_id );
			 	break;				 	

			 case '[project-edit-url]':
			 	$value = $GLOBALS['community-jar']->create_project_edit_url($project_id);
			 	break;

			 case '[project-url]':
			 	$value = get_permalink( $project_id );
			 	break;
			 		
			 /* --- Volunteers ------- */
			 
			 case '[volunteer-name]':
			 	$value = $this->get_volunteer( $id, $key, $project_id );
			 	break;
			 	
			 case '[volunteer-email]':
				$value = $this->get_volunteer( $id, $key, $project_id );
			 	break;
			 	
			 case '[volunteer-phone]':
			    $value = get_user_meta( $id, 'phone-number', true );
			 	break;	
			 
			 case '[volunteer-comment]':
			 	$value = $this->get_volunteer( $id, $key, $project_id );
			 	break;
			 
			 /* --- Administrators --- */
			 			 
			 case '[admin-email]':
				$cj_options = get_option('cj_settings');
			 	$value = sanitize_text_field($cj_options['from_email']);
			 	break;
			 
			 case '[admin-project-url]':
			 	$value = get_site_url() . '/wp-admin/post.php?post=' . $project_id . '&action=edit';
			 	break;
			 	
			 case '[admin-login-url]':
			 	$value = wp_login_url();
			 	break;
			 	
			 /* --- Default --- */			 	
			 default:
			 	$value = null;
			 	break;
			 
		 } // end switch/case
		 
		 // If we've retrieves a value, let's replace it.
		 if( null != $value ) {
			$content = $this->replace( $key, $value, $content );		 
		 } // end if
		 
		 return $content;
		 
	 } // end get_value
	
	/*--------------------------------------------*
	 * Private Functions
	 *--------------------------------------------*/
	 
	 /**
	  * Retrieves information about the project owner based on the incoming key.
	  *
	  * Can retrieve the project owner full name, email, or phone number.
	  *
	  * @param	int		$id		The ID of the project
	  * @param	string	$key	The key of the shortcode that we're retrieving
	  * @return	string			The full name, email, or phone number.
	  */
	 private function get_project_owner( $id, $key, $project_id = -1 ) {
		 
		 $info = null;

		 // First, get the user object
		 $user = get_user_by( 'email', get_post_meta( $project_id, 'project_owner', true ) );
		 
		 // If the user is asking for the email, or if the name doesn't exist, use the email address
		 if( '[project-owner-email]' == strtolower( $key ) ) {
		 
			 $info = $user->data->user_email;
		
		 // If the user is asking for the phone number, return that information
	     } elseif( '[project-owner-phone]' == strtolower( $key ) ) {
	     
		     $info = get_user_meta( $id, 'phone-number', true );
		     
		 // If the user is asking for the first name, return it
		 } elseif( '[project-owner-name]' == strtolower( $key ) ) {
		     
		     $info = $user->data->display_name;
		     
		 // Build the actual name. This will be returned if no other token is returned.    
		 } 
		 
		 return $info;
		 
	 } // end get_project_owner

	 /**
	  * Retrieves the description of the project.
	  *
	  * @param	int		$id	The ID of the project
	  * @return	string		The description of the project.
	  */
	 private function get_project_description( $id ) {
		 
		$description = '';
		 
		$project = get_post( $id );
		$description = $project->post_content;
		
		return $description;
		 
	 } // end get_project_description
	 
	 /**
	  * Retrieves information about the volunteer based on the incoming key.
	  *
	  * Can retrieve the volunteer's full name, first name, email address, or
	  * comment for the specified project.
	  *
	  * @param	int		$id		The ID of the volunteer
	  * @param	string	$key	The key of the shortcode that we're retrieving
	  * @return	string			The full name, first name, email address, or comment for the specified project.
	  */
	 private function get_volunteer( $id, $key, $project_id, $comment_id = -1 ) {
		 
		 $info = null;

		 // First, get the user object
		 $volunteer = get_user_by( 'id', $id );
		 $volunteer_info = get_userdata($id);
		 
		 // If the user is asking for the name
		 if( '[volunteer-name]' == strtolower( $key ) ) {
		 
			 $info = $volunteer_info-> display_name;
			 
		 // If they are asking for the email, then use the nicename (since that's what we're setting it to in the plugin)	 
		 } elseif( '[volunteer-email]' == strtolower( $key ) ) {
		 
			 $info = $volunteer_info-> user_email;
			 
		 // Retrieving the comment for this volunteer and the project
		 } elseif( '[volunteer-comment]' == strtolower( $key ) ) {
		 
			 $comment_id = get_user_meta( $id, 'project-comment-' . $project_id, true );
			 if('' != $comment_id){
				 $comment = get_comment( $comment_id );
				 $info = $comment->comment_content;
				 
				 if( '' == $info || null == $info || false == $info ) {
					 $info = __( 'No additional comments', 'cj' );		 
				 } // end if
			 }
			 
		 } // end if/else
		 
		 return $info;
		 
	 } // end get_volunteer
	 
	 /**
	  * Finds all instances of the specified key and replaces it with the specified
	  * value.
	  *
	  * @param	string	$key	The key to replace
	  * @param	string	$value	The value that will replace the specified key
	  * @return	string			The updated value.
	  */
	 private function replace( $key, $value, $content ) {
		
		$new_content = '';
		$new_content = str_replace( $key, $value, $content );
		
		return $new_content;
		 
	 } // end replace
	 
} // end class