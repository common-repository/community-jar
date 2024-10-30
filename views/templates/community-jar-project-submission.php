<?php
/**
 * Template Name: Project Submission
 *
 * Used to submit a 'Project' from the front-end of the site. Also allows for editing.
 * 
 * @package Community Jar
 * @since 	1.0
 * @version	1.0
 */
 $project = $GLOBALS['community-jar']->submission_form_validation();
?>

<?php get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
						<div class="entry-thumbnail">
							<?php the_post_thumbnail(); ?>
						</div>
						<?php endif; ?>

						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<?php the_content(); ?>
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
											<?php if( null == $project ) { ?>
												<?php _e( '<strong>Thanks!</strong> Your project has been submitted!', 'community-jar' ); ?>
											<?php } else { ?>
												<?php _e( '<strong>Thanks!</strong> Your project has been updated!', 'community-jar' ); ?>
											<?php } // end if/else ?>
										</div><!-- /.alert-success -->
									<?php } // end if/else ?>
								</div><!-- /post-submission-->
							<?php } // end if ?>
							
							<form method="post" action="" id="community-jar-submit-form">
								<label for="project-owner-name">
									<?php _e( 'Project Owner Name', 'community-jar' ); ?>
								</label>
								<input type="text" name="project-owner-name" id="project-owner-name" value="<?php if(isset($project->post_author)){ echo esc_attr(get_user_meta( $project->post_author, 'nickname',true)); }; ?>" /><!-- /#project-owner-name -->
							
								<label for="project-owner">
									<?php _e( 'Project Owner Email Address', 'community-jar' ); ?>
								</label>
								<input type="text" name="project-owner" id="project-owner" value="<?php if(isset($project->post_author)){ echo esc_attr(get_userdata($project->post_author)->user_email); }; ?>" /><!-- /#project-owner -->
							
								<label for="project-owner-phone">
									<?php _e( 'Project Owner Phone', 'community-jar' ); ?>
								</label>
								<input type="text" name="project-owner-phone" id="project-owner-phone" value="<?php if(isset($project->post_author)){ echo esc_attr(get_user_meta( $project->post_author, 'phone-number', true )); }; ?>" /><!-- /#project-owner-phone -->
								
								<label for="project-title">
									<?php _e( 'Project Title', 'community-jar' ); ?>
								</label>
								<input type="text" name="project-title" id="project-title" value="<?php if(isset($project->post_author)){ echo esc_attr($project->post_title); }; ?>" /><!-- /#project-title -->
							
								<label for="project-date">
									<?php _e( 'Project Date', 'community-jar' ); ?>
								</label>
								<input type="text" id="project-date" name="project-date" value="<?php if(isset($project->post_author)){ echo  date("m/d/Y", strtotime(esc_attr(get_post_meta( $project->ID, 'project_date', true )))); }; ?>" /><!-- /#project-date -->
							
								<label for="project-content">
									<?php _e( 'Project Description', 'community-jar' ); ?>
								</label>
								<textarea id="project-description" name="project-description"><?php if(isset($project->post_author)){  echo esc_textarea($project->post_content); }; ?></textarea><!-- /#project-submission -->
								<br/>
								<label for="is-anonymous">
									<input type="checkbox" id="is-anonymous" name="is-anonymous" value="1" <?php if(isset($project->post_author)){ checked( 'anonymous', get_post_meta( $project->ID, 'owner_visibility', true ), true ); }; ?> /><!-- /#is-anonymous -->
									<?php _e( 'Should the project owner remain anonymous?', 'community-jar' ); ?>
								</label>
							
								<?php if( 'publish' == get_post_status() && isset($project->post_author) ) { ?>
									<p>
										<label for="project_is_complete">
											<input type="checkbox" name="project_is_complete" id="project_is_complete" value="1" <?php  checked( 1, get_post_meta( $project->ID, 'project_is_complete', true ), true ); ?> />
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
								</p>
								
							</form>
							
							<p>
								<?php // If we're working on an actual project, then let's display the active volunteers ?>
								<?php if( null != $project ) { ?>
								
									<h3><?php _e( 'Current Volunteers', 'community-jar' ); ?></h3>
									<?php $volunteers = $GLOBALS['community-jar']->get_volunteers_for( $project->ID, true ); ?>
									<?php if( 0 < count( $volunteers ) ) { ?>
									
										<dl>
											<?php foreach( $volunteers as $volunteer ) { ?>
											
												<?php // Check for phone number and comments. ?>
												<?php $phone_number = get_user_meta( $volunteer->ID, 'phone-number', true ); ?>
												<?php 
													$comment_id = get_user_meta( $volunteer->ID, 'project-comment-' . $project->ID, true ); 
													$comment = get_comment( $comment_id );
												?>
												
												<?php 
											
												if( '' == $phone_number ) {
													$phone_number = 'Not Given';
												} // end if
												
												
												?>

												<dt><h4><?php echo $volunteer->display_name; ?></h4></dt>
												<dd><strong>Email:</strong>&nbsp;<?php echo $volunteer->user_email; ?></dd>
												<dd><strong>Phone:</strong>&nbsp;<?php echo $phone_number; ?></dd>
												<?php if( isset($comment->comment_content) && null != $comment->comment_content) {
													echo '<dd><strong>Comment:</strong>&nbsp;'.$comment->comment_content.'</dd>';
												} // end if
												?>
											<?php } // end foreach ?>
										</dl>
										
									<?php } else { ?>
									
										<span><?php _e( 'Currently, you have no volunteers.', 'community-jar' ); ?></span>
									
									<?php } // end if/else ?>
									
								<?php } // end if ?>
							</p>
								
							
								
							
							
						</section>
						<?php wp_link_pages( array( 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentythirteen' ) . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>' ) ); ?>
					</div><!-- .entry-content -->

					<footer class="entry-meta">
						<?php edit_post_link( __( 'Edit', 'twentythirteen' ), '<span class="edit-link">', '</span>' ); ?>
					</footer><!-- .entry-meta -->
				</article><!-- #post -->

				<?php comments_template(); ?>
			<?php endwhile; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>