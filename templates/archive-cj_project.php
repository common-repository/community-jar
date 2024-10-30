<?php
/**
 * The template for displaying a listing of incomplete projects
 *
 ***************** NOTICE: *****************
 * Do not make changes to this file. Any changes made to this file will be overwritten if the plug-in is updated.
 *
 * To overwrite this template with your own, make a copy of it (with the same name) in your theme directory. 
 *
 * WordPress will automatically prioritise the template in your theme directory.
 ***************** NOTICE: *****************
 *
 * @package Community Jar (plug-in)
 * @since   1.0.0
 * @version	1.1.0
 * 
 * Note: This archive only shows incomplete projects
 */

//Call the template header
get_header(); ?>

	<section id="primary" class="site-content">
		<div id="content" role="main">

		<?php if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title">Projects</h1>
			</header><!-- .archive-header -->

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post(); ?>

			
				<div id="post-<?php the_ID(); ?> cj-project" class="entry-content">
							
						<div class="page-header clearfix">
							<div class="cj-jar-counter">
								<span aria-hidden="true" class="icomoon-communityjar"></span>
								<span class="cj-count"><?php cj_volunteer_number('0','1','%'); ?></span>
							</div>
							
							<div class="cj-project-info">
								<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
								<strong>Created by:</strong> <?php cj_project_owner(); ?> <br />
								<strong>Project End Date:</strong>&nbsp;<?php cj_project_date(); ?> <br />
								<strong>Volunteer Count:</strong>&nbsp;<?php cj_volunteer_number('0','1','%'); ?>									
							</div>
						
						</div><!-- /.page-header -->
						<h4>Project Information</h4>
						<?php the_excerpt();?>
						<p><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">Find Out More and Sign Up To Volunteer</a></p>
						<hr>
						
				</div>
			
			<?php endwhile;

			if ( $wp_query->max_num_pages > 1 ) : ?>
				<nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">
					<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
					<div class="nav-previous alignleft"><?php previous_posts_link( __( '<span class="meta-nav">&larr;</span> Back', 'twentytwelve' ) ); ?></div>
					<div class="nav-next alignright"><?php next_posts_link( __( 'More Projects <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?></div>
				</nav><!-- #<?php echo $html_id; ?> .navigation -->
			<?php endif;
			?>

		<?php else : ?>
			<article id="post-0" class="post no-results not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'No Projects Found', 'community-jar' ); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php _e( 'Apologies, but no projects were found. Perhaps searching will help find a related project.', 'community-jar' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-0 -->
		<?php endif; ?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
