<?php

/**
 * Parses cj_project queries and alters the WP_Query object appropriately
 *
 * Parse's the query, and sets date range and other cj_project specific query variables.
 * If query is for 'cj_project' post type - the posts_* filters are added.
 *
 * Hooked onto pre_get_posts
 * @since 1.0.0
 * @version 1.1.2
 * @access private
 * @ignore;
 *
 * @param WP_Query $query The query
 */
function communityjar_pre_get_posts( $query ) {


	//If not on project, stop here.
	if('cj_project'!= $query->get('post_type') ){
		return $query;
	}
	//If project archive filter results.
	if ( 'cj_project'== $query->get('post_type') && !is_admin()) {
		//$query->set( 'posts_per_page', 1 );
		$meta_query = $query->get('meta_query');
		$meta_query = empty($meta_query) ? array() : $meta_query;
		$meta_query = array(
			array( //Only show project that happen today or in the future.
				'key' => 'project_date',
				'value' => date('Y-m-d'),
				'compare' => '>='
			),
			array( //Only show incomplete projects.
				'key' => 'project_is_complete',
				'compare' => 'NOT EXISTS'
			)
		);
		$query->set('meta_query', $meta_query ) ;
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', 'project_date' );
		$query->set( 'order', 'ASC' );
        return;
    }
}
add_action( 'pre_get_posts', 'communityjar_pre_get_posts' );
?>