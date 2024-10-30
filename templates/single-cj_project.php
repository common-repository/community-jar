<?php
/**
 * Single Project View
 *
 ****************** NOTICE: ****************
 * Do not make changes to this file. Any changes made to this file will be overwritten if the plug-in is updated.
 *
 * To overwrite this template with your own, make a copy of it (with the same name) in your theme directory. 
 *
 * WordPress will automatically prioritise the template in your theme directory.
 ***************** NOTICE: *****************
 * 
 * @package Community Jar
 * @since 	1.0.0
 * @version	1.1.0
 */
get_header();
 ?>

<div id="primary" class="site-content">
	<div id="content" role="main">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header><!-- .entry-header -->

				<div class="entry-content">
					
					<section id="cj-project" class="post_content">
							
								<div class="page-header clearfix">
									<div class="cj-jar-counter">
										<span aria-hidden="true" class="icomoon-communityjar"></span>
										<span class="cj-count"><?php cj_volunteer_number('0','1','%'); ?></span>
									</div>
									
									<div class="cj-project-info">
										<?php the_title( '<h3>', '</h3>' ); ?>
										<strong>Created by:</strong> <?php the_author(); ?> <br />
										<strong>Project End Date:</strong>&nbsp;<?php cj_project_date(); ?> <br />
										<strong>Volunteer Count:</strong>&nbsp;<?php cj_volunteer_number('0','1','%'); ?>									
									</div>
								
								</div><!-- /.page-header -->
								<h4>Project Information</h4>
								<?php the_content(); ?>	
								
						</section>
						</div><!-- .entry-content -->
						<?php cj_volunteer_form('Thanks for volunteering, you will be notified via email when you are approved to help with this project.'); ?>

				<footer class="entry-meta">
					<?php edit_post_link( __( 'Edit', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?>
				</footer><!-- .entry-meta -->
			</article><!-- #post -->


		<?php endwhile; // end of the loop. ?>
			<?php else : ?>
				
				<div class="entry-content">		
				<article id="post-not-found">
					<header class="entry-header">
						<h1 class="entry-title">Not Found</h1>
					</header><!-- .entry-header -->
					<div class="entry-content">	
						<p><?php _e("Sorry, but the requested project was not found on this site.", "community-jar"); ?></p>
					</div>
				</article>
			<?php endif; ?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>