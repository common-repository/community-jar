<?php
/**
 * Community Jar Volunteer Manager is responsible for managing creating new users in the
 * Community Jar application and associating them with projects.
 *
 * It handles creating WordPress Users, adding additional fields, and introducing
 * a new role.
 *
 * @version 1.0
 */
class Community_Jar_Volunteer_Manager {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	 
	/**
	 * Initializes the Manager by introducing a new role and adding profile field values.
	 *
	 * @version		1.0
     * @since 		1.0
	 */ 
	public function __construct() {

		// Add the volunteer role to the application
		add_action( 'init', array( $this, 'add_volunteer_role' ) );

		// Add the JavaScript that will append a new form field
		//add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Add a filter for updating a user's profile
		add_filter( 'user_contactmethods', array( $this, 'add_phone_number_to_profile' ) );
		
		// Registration hook adding a phone number
		add_action( 'user_register', array( $this, 'create_new_user' ) );
		
	} // end constructor

	/*--------------------------------------------*
	 * Actions
	 *--------------------------------------------*/	 

	/** 
	 * Adds the volunteer role to the application, if it doesn't already exist.
	 * 
	 * @since	1.0
	 * @version	1.0
	 */
	public function add_volunteer_role() {
		
		if( null == get_role( 'volunteer' ) ) {
		
			$capabilities = array(
				'read'	=> true
			);
			add_role( 'volunteer', __( 'Volunteer', 'cj' ), $capabilities );
			
		} // end if
		
	} // end add_volunteer_role

	/** 
	 * Updates the Phone Number of the user based on if it's specified in the $_POST data.
	 * 
	 * @param	int		$user_id	The ID of the user that we're creating
	 * @since	1.0
	 * @version	1.0
	 */  
	public function create_new_user( $user_id ) {
		
		// If the phone number is set...
		if( isset( $_POST['phone-number'] ) ) {
			
			// And if it already exists, then delete it
			if( 0 != get_user_meta( $user_id, 'phone-number', true ) ) {
				delete_user_meta( $user_id, 'phone-number' );
			} // end if
			
			// Then update the user meta
			update_user_meta( $user_id, 'phone-number' , stripslashes( strip_tags( $_POST['phone-number'] ) ) );
			
		} // end if
		
	} // and create_new_user
	
	/** 
	 * Adds a field for phone numbers.
	 * 
	 * @param	array $user_contactmethods	The array of contact fields for the user's profile.
	 * @return	array The updated array of contact methods.
	 * @since	1.0
	 * @version	1.0
	 */ 
	public function register_admin_scripts() {
		wp_enqueue_script( 'community-jar-phone-number', plugins_url( 'community-jar/js/admin.phone-number.min.js' ) );
	} // end register_admin_scripts
	 
	/*--------------------------------------------*
	 * Filters
	 *--------------------------------------------*/	 

	/** 
	 * Adds a field for phone numbers.
	 * 
	 * @param	array $user_contactmethods	The array of contact fields for the user's profile.
	 * @return	array The updated array of contact methods.
	 * @since	1.0
	 * @version	1.0
	 */
	public function add_phone_number_to_profile( $user_contactmethods ) {
		
		$user_contactmethods['phone-number'] = __( '<span class="volunteer-phone-number" id="volunteer-phone-number">Phone Number</span>', 'cj' );
	
		return $user_contactmethods;
		
	} // end add_phone_number_to_profile
	 
} // end class