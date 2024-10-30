<?php
/**
 * Display Project Date
 *
 * @param string $project_id (optional) to use outside loop, or in Secondary loops.
 *
 * @version		1.0.1
 * @since 		1.0
 */	
Function cj_project_date($project_id = false) {
	If(!$project_id){
		global $post;
		$project_id = get_the_ID(); 	
	}
		$date = new DateTime( get_post_meta( $project_id, 'project_date', true ) ); 
		echo $date->format( get_option( 'date_format' ) );
}

/**
 * Displays Volunteer Count for current project.
 *
 * @param string $zero Text for no volunteers
 * @param string $one Text for one volunteer
 * @param string $more Text for more than one volunteer
 *
 * @param string $project_id (optional) to use outside loop, or in Secondary loops.
 *
 * @version		1.0.1
 * @since 		1.0
 */	
Function cj_volunteer_number($zero = false, $one = false, $more = false, $project_id = false){
	If(!$project_id){
		global $post;
		$project_id = get_the_ID(); 	
	}
	$cj = $GLOBALS['community-jar'];
	$number = $cj->get_volunteer_count( $project_id );

	if ( $number > 1 )
			$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Volunteers') : $more);
	elseif ( $number == 0 )
			$output = ( false === $zero ) ? __('No Volunteers') : $zero;
	else // must be one
			$output = ( false === $one ) ? __('One Volunteer') : $one;

	echo $output;
	
}

Function get_cj_volunteer_number($zero = false, $one = false, $more = false, $project_id = false){
	If(!$project_id){
		global $post;
		$project_id = get_the_ID(); 	
	}
	$cj = $GLOBALS['community-jar'];
	$number = $cj->get_volunteer_count( get_the_ID() );

	if ( $number > 1 )
			$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Volunteers') : $more);
	elseif ( $number == 0 )
			$output = ( false === $zero ) ? __('No Volunteers') : $zero;
	else // must be one
			$output = ( false === $one ) ? __('One Volunteer') : $one;

	return $output;
	
}
/**
 * Display Project Owners Name, If owner is set to anonymous, Returns "Anonymous".
 *
 * @param string $project_id (optional) to use outside loop, or in Secondary loops.
 *
 * @version		1.0.1
 * @since 		1.0
 */
Function cj_project_owner ($project_id = false){
	If(!$project_id){
		global $post;
		$project_id = get_the_ID(); 	
	}
	
	$cj = $GLOBALS['community-jar'];
	if( 'public' == get_post_meta( $project_id, 'owner_visibility', true ) ) { 
		echo $cj->get_project_owner( $project_id );
	}else{
		echo 'Anonymous';
	} // end if
}

/**
 * Displays Text stating if a project is complete or not
 *
 * @param string $project_id (optional) to use outside loop, or in Secondary loops.
 *
 * @version		1.0.1
 * @since 		1.0
 */
Function cj_project_complete ($project_id = false){
If(!$project_id){
		global $post;
		$project_id = get_the_ID(); 	
	}
	echo get_post_meta( $project_id, 'project_is_complete',true);
}

/**
 * Display Project Owners Name, If owner is set to anonymous, Returns "Anonymous".
 *
 * @uses get_posts()
 *
 * @param string $num number of recent project you want to return.
 *
 * @version		1.1
 * @since 		1.0
 */
function cj_get_recent_projects($num = false) {
	if($num){
		$recentNum = absint($num) ;
	} else {
		$recentNum = 3;
	}
	$args = array(
		'numberposts' 		=> $recentNum,
		'post_type' 		=> 'cj_project',
		'suppress_filters' => false
	);		
	$recent_projects = get_posts( $args );
	
	return $recent_projects;
}



/**
 * Displays Form used to for volunteer Signups
 *
 *
 * @version		1.0
 * @since 		1.0
 */
function cj_volunteer_form($alertmessage = false){
	
	
		if( isset( $_GET['success'] ) ) { ?>
			<div id="post-submission">
				<?php if( 0 == $_GET['success'] ) { ?>
					<div class="alert cj-submit-error">
						<?php _e( "There was a problem with your submission. Please try to resubmit.", 'community-jar' ); ?>
					</div><!-- /.alert-error -->
				<?php } elseif ( 1 == $_GET['success'] ) { ?>
					<div class="alert cj-alert-success">
					
						<?php if (false === $alertmessage ){
							_e( "Thank you for volunteering, you will recieve an email when you are approved to help with this project", 'community-jar' ); 
						}else{
							echo $alertmessage;
						}
						?>
					</div><!-- /.alert-success -->
				<?php } // end if/else ?>
			</div><!-- /.row-fluid -->
		<?php } // end if ?>
		<section id="sign-up" class="cj-signup-form">
			<h4><?php _e( 'Sign Up To Volunteer', 'community-jar' ); ?></h4>

			<form id="volunteer-sign-up" method="post" action="">

				<label for="volunteer-name">
					<?php _e( 'Name', 'community-jar' ); ?>
				</label>
				<input type="text" id="volunteer-name" name="volunteer-name" value="" />
			
				<label for="volunteer-email">
					<?php _e( 'Email Address', 'community-jar' ); ?>
				</label>
				<input type="text" id="volunteer-email" name="volunteer-email" value="" />

				<label for="volunteer-phone">
					<?php _e( 'Phone Number', 'community-jar' ); ?>
				</label>
				<input type="text" id="volunteer-phone" name="volunteer-phone" value="" />
				
				<label for="volunteer-comments">
					<?php _e( 'Comments (Please keep them it', 'community-jar' ); ?>&nbsp;<span id="count">300</span>&nbsp;<?php _e( 'characters or less).', 'community-jar' ); ?>
				</label>
				<textarea id="volunteer-comments" name="volunteer-comments" maxlength="300"></textarea>
				<p></p>
				
				<input type="hidden" id="project-id" name="project-id" value="<?php the_ID(); ?>" />
				
				<p>
					<input type="button" class="btn btn-inverse" id="submit-volunteer" name="submit-volunteer" value="<?php _e( 'Sign Up', 'community-jar' ); ?>" />
					<input type="button" class="btn" id="cancel-volunteer" name="cancel-volunteer" value="<?php _e( 'Cancel', 'community-jar' ); ?>" />
				</p>
				
			</form>
			
		
			<div class="alert cj-alert-error" id="name-error">
				<strong><?php _e( "You must enter a name.", 'community-jar' ); ?></strong>
			</div><!-- /#title-error -->
			
			<div class="alert cj-alert-error" id="email-error">
				<strong><?php _e( "You must enter a valid email address.", 'community-jar' ); ?></strong>
			</div><!-- /#email-error -->
	
			<div class="alert cj-alert-error" id="number-error">
				<strong><?php _e( "You must enter a valid phone number.", 'community-jar' ); ?></strong>
			</div><!-- /#number-error -->
	
			<div class="alert cj-alert-error" id="comment-error">
				<strong><?php _e( "You must enter a comment.", 'community-jar' ); ?></strong>
			</div><!-- /#date-error -->
		</section><!-- /#sign-up -->
	<?php			
}

/**
 * Displays Project submission form.
 *
 * @version		1.0
 * @since 		1.0
 */
function cj_submission_form(){
	$project = null;
	
	// If the project is specified in the ID, we'll prefill the data
	if( isset( $_GET['project_id'] ) ) {
	
		$GLOBALS['community-jar']->update_existing_project( $_GET['project_id'] ); 
		$project = get_post( $_GET['project_id'] );
		
	// Otherwise, we register a new project
	} else {	
		$GLOBALS['community-jar']->register_new_project(); 
	} // end if/else
	ob_start(); ?>
	<section id="cj-post" class="post_content">
		<div class="alert cj-alert-error" id="title-error">
			<?php _e( "You must enter a title.", 'community-jar' ); ?>
		</div><!-- /#title-error -->
		
		<div class="alert cj-alert-error" id="desc-error">
			<?php _e( "You must provide a project description.", 'community-jar' ); ?>
		</div><!-- /#desc-error -->

		<div class="alert cj-alert-error" id="name-error">
			<?php _e( "You must enter a name.", 'community-jar' ); ?>
		</div><!-- /#name-error -->
		
		<div class="alert cj-alert-error" id="email-error">
			<?php _e( "You must enter a valid email address.", 'community-jar' ); ?>
		</div><!-- /#email-error -->

		<div class="alert cj-alert-error" id="number-error">
			<?php _e( "You must enter a valid phone number.", 'community-jar' ); ?>
		</div><!-- /#number-error -->

		<div class="alert cj-alert-error" id="date-error">
			<?php _e( "You must enter a date.", 'community-jar' ); ?>
		</div><!-- /#date-error -->
		
		<?php if( isset( $_GET['success'] ) ) { ?>
			
			<div id="post-submission">
				<?php if( 0 == $_GET['success'] ) { ?>
					<div class="alert cj-alert-error">
						<?php _e( "There was a problem with your project. Please try to resubmit.", 'community-jar' ); ?>
					</div><!-- /.alert-error -->
				<?php } elseif ( 1 == $_GET['success'] ) { ?>
					<div class="alert cj-alert-success">
						<?php if( null == $project ) { 
							 _e( '<strong>Thanks!</strong> Your project has been submitted!', 'community-jar' ); 
						} else { 
							 _e( '<strong>Thanks!</strong> Your project has been updated!', 'community-jar' );
						 } // end if/else ?>
					</div><!-- /.alert-success -->
				<?php } // end if/else ?>
			</div><!-- /post-submission-->
		<?php } // end if ?>
		
		<form method="post" action="">
			<label for="project-owner-name">
				<?php _e( 'Project Owner Name', 'community-jar' ); ?>
			</label>
			<input type="text" name="project-owner-name" id="project-owner-name" value="<?php if(get_user_meta( $project->post_author, 'first_name',true)){
			echo get_user_meta( $project->post_author, 'first_name',true) . '&nbsp;' . get_user_meta( $project->post_author, 'last_name', true );
			}; ?>" /><!-- /#project-owner-name -->
		
			<label for="project-owner">
				<?php _e( 'Project Owner Email Address', 'community-jar' ); ?>
			</label>
			<input type="text" name="project-owner" id="project-owner" value="<?php echo get_userdata($project->post_author)->user_email; ?>" /><!-- /#project-owner -->
		
			<label for="project-owner-phone">
				<?php _e( 'Project Owner Phone', 'community-jar' ); ?>
			</label>
			<input type="text" name="project-owner-phone" id="project-owner-phone" value="<?php echo get_user_meta( $project->post_author, 'phone-number', true ); ?>" /><!-- /#project-owner-phone -->
			
			<label for="project-title">
				<?php _e( 'Project Title', 'community-jar' ); ?>
			</label>
			<input type="text" name="project-title" id="project-title" value="<?php echo $project->post_title; ?>" /><!-- /#project-title -->
		
			<label for="project-date">
				<?php _e( 'Project Date', 'community-jar' ); ?>
			</label>
			<input type="text" id="project-date" name="project-date" value="<?php echo get_post_meta( $project->ID, 'project_date', true ); ?>" /><!-- /#project-date -->
		
			<label for="project-content">
				<?php _e( 'Project Description', 'community-jar' ); ?>
			</label>
			<textarea id="project-description" name="project-description"><?php echo $project->post_content; ?></textarea><!-- /#project-submission -->
			<br/>
			<label for="is-anonymous">
				<input type="checkbox" id="is-anonymous" name="is-anonymous" value="1" <?php checked( 'anonymous', get_post_meta( $project->ID, 'owner_visibility', true ), true ); ?> /><!-- /#is-anonymous -->
				<?php _e( 'Should the project owner remain anonymous?', 'community-jar' ); ?>
			</label>
		
			<?php if( 'publish' == get_post_status() && isset( $_GET['owner'] ) && 'true' == $_GET['owner'] ) { ?>
				<p>
					<label for="project_is_complete">
						<input type="checkbox" name="project_is_complete" id="project_is_complete" value="1" <?php checked( 1, get_post_meta( $project->ID, 'project_is_complete', true ), true ); ?> />
						&nbsp;
						<?php _e( 'Project Completed?', 'community-jar' ); ?>
					</label>
				</p>
			<?php } // end if ?>
			
			<p>
				<?php if( null == $project ) { ?>
					<input type="button" class="btn-primary" id="submit-project" name="submit-project" value="<?php _e( 'Submit Project', 'community-jar' ); ?>" />
				<?php } else { ?>
					<input type="button" class="btn-primary" id="submit-project" name="submit-project" value="<?php _e( 'Update Project', 'community-jar' ); ?>" />
				<?php } // end if/else ?>
				<input type="button" class="btn" id="cancel-project" name="cancel-project" value="<?php _e( 'Cancel', 'community-jar' ); ?>" />
			</p>
			
		</form>
		
		<p>
			<?php // If we're working on an actual project, then let's display the active volunteers 
			 if( null != $project ) { ?>
			
				<h3><?php _e( 'Current Volunteers', 'community-jar' ); ?></h3>
				<?php $volunteers = $GLOBALS['community-jar']->get_volunteers_for( $_GET['project_id'], true );
				 if( 0 < count( $volunteers ) ) { ?>
				
					<dl>
						<?php foreach( $volunteers as $volunteer ) { 
						
							 // Check for phone number and comments. 
							 $phone_number = get_user_meta( $volunteer->ID, 'phone-number', true ); 
							
								$comment_id = get_user_meta( $volunteer->ID, 'project-comment-' . $_GET['project_id'], true ); 
								$comment = get_comment( $comment_id );
							
							if( '' == $phone_number ) {
								$phone_number = 'Not Given';
							} // end if
							?>

							<dt><h4><?php echo $volunteer->display_name; ?></h4></dt>
							<dd><strong>Email:</strong>&nbsp;<?php echo $volunteer->user_email; ?></dd>
							<dd><strong>Phone:</strong>&nbsp;<?php echo $phone_number; ?></dd>
							<?php if( null != $comment->comment_content) {
								echo '<dd><strong>Comment:</strong>&nbsp;'.$comment->comment_content.'</dd>';
							} // end if
							?>
						<?php } // end foreach ?>
					</dl>
					
				<?php } else { ?>
				
					<span><?php _e( 'Currently, you have no volunteers.', 'community-jar' ); ?></span>
				
				<?php } // end if/else 
				
			} // end if ?>
		</p>
	</section> <!-- end article section -->
	
	<?php echo ob_get_clean();						
}
?>