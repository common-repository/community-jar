<?php class Community_Jar_Notification {

    /*--------------------------------------------*
     * Core Functions
     *---------------------------------------------*/
 
    /**
     * Upon activation, add a new option used to track whether or not to display the notification.
     */
    public function activate() {
        add_option( 'cj_notification', false );
    } // end activate
 
    /**
     * Upon deactivation, removes the option that was created when the plugin was activated.
     */
    public function deactivate() {
        delete_option( 'cj_notification' );
    } // end deactivate
 
 
    /**
     * Renders the administration notice. Also renders a hidden nonce used for security when processing the Ajax request.
     */
    public function display_admin_notice() {
 
        $html = '<div id="ajax-notification" class="updated">';
            $html .= '<p>';
                $html .= __( 'Community Jar Version 1.1 now uses The custom post type of "cj_project" to remove conflicts with other plugins and themes. <a href="javascript:;" id="dismiss-ajax-notification">Click here to update projects and Dismiss this message</a>.', 'ajax-notification' );
            $html .= '</p>';
            $html .= '<span id="ajax-notification-nonce" class="hidden">' . wp_create_nonce( 'ajax-notification-nonce' ) . '</span>';
        $html .= '</div><!-- /.updated -->';
 
        echo $html;
 
    } // end display_admin_notice
 
    /**
     * JavaScript callback used to hide the administration notice when the 'Dismiss' anchor is clicked on the front end.
     */
    public function hide_admin_notification() {
		$GLOBALS['community-jar']->convert_projects();
        // First, check the nonce to make sure it matches what we created when displaying the message.
        // If not, we won't do anything.
        if( wp_verify_nonce( $_REQUEST['nonce'], 'ajax-notification-nonce' ) ) {
 
            // If the update to the option is successful, send 1 back to the browser;
            // Otherwise, send 0.
            if( update_option( 'cj_notification', true ) ) {
                die( '1' );
            } else {
                die( '0' );
            } // end if/else
 
        } // end if
 
    } // end hide_admin_notification
 
} // end class
 

?>