<?php


	function cj_options_page(){
		$cj = $GLOBALS['community-jar'];
		
		ob_start();?>
		<div class="wrap">
			<?php screen_icon();?>
			<h2>Community Jar Settings</h2>
			
			<form method="post" action="options.php">
				
				<?php settings_fields('cj_settings_group'); ?>
				<h4>Admin Options</h4>
				<p>
					<label class="description" for="cj_settings[admin_email]"><?php _e("Email(s):",'community-jar'); ?></label>
					<input id="cj_settings[admin_email]" name="cj_settings[admin_email]" type="text" value="<?php echo $cj->cj_options['admin_email'];?>" size="40"/>
					<p class="description"><?php _e("Sets who receives moderation notification emails. Can be set to mulitple emails ex: john@doe.com, jane@doe.com",'community-jar');?>.</p>
				</p>
				<h4>From Email Options</h4>
				<p>
					<label class="description" for="cj_settings[from_name]"><?php _e("Name:",'community-jar'); ?></label>
					<input id="cj_settings[from_name]" name="cj_settings[from_name]" type="text" value="<?php echo $cj->cj_options['from_name'];?>" size="40" />
				</p>
				<p>
					<label class="description" for="cj_settings[from_email]"><?php _e("Email:",'community-jar'); ?></label>
					<input id="cj_settings[from_email]" name="cj_settings[from_email]" type="text" value="<?php echo sanitize_email($cj->cj_options['from_email']);?>" size="40" />
				</p>
				<?php submit_button('Save Settings', 'primary', 'community-jar'); ?>
			</form>
			
		</div>
		<?php
		echo ob_get_clean();

	}

	function cj_add_options_link(){
		add_options_page('Community Jar Settings','Community Jar','manage_options','cj-settings','cj_options_page');
	}

	add_action('admin_menu', 'cj_add_options_link');

	function cj_register_settings(){
		register_setting('cj_settings_group', 'cj_settings', 'cj_validate');
	}

	add_action('admin_init', 'cj_register_settings');

	function cj_validate( $input ) {

		// Create our array for storing the validated options
		$output = array();
		
		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {
			
			// Check to see if the current option has a value. If so, process it.
			if( isset( $input[$key] ) ) {
			
				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
				
			} // end if
			
		} // end foreach
		
		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'cj_validate', $output, $input );

	}
?>