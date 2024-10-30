<?php
/**
 * Registers and enqueues admin-specific styles.
 *
 * @version		1.1
 * @since 		1.0
 */
function register_admin_styles() {
	if( 'cj_project' == get_current_screen()->id ) {
		wp_enqueue_style( 'jquery-ui-datepicker', COMMUNITYJAR_DIR .'css/cj-admin-fresh.css');
	}
	wp_enqueue_style( 'community-jar', COMMUNITYJAR_DIR . 'css/admin.css' );	
} // end register_admin_styles

add_action( 'admin_print_styles', 'register_admin_styles');
	
		
/**
 * Registers and enqueues admin-specific JavaScript.
 *
 * @version		1.1
 * @since 		1.0
 */	
function register_admin_scripts() {
	//wp_register_script( 'community-jar-notice', COMMUNITYJAR_DIR .'js/notices.min.js' );
    wp_enqueue_script( 'community-jar-notice' );
	
	// If we're on the post edit page, then let's add the 'Volunteer' script
	if( 'cj_project' == get_current_screen()->id ) {
		wp_enqueue_script( 'community-jar', COMMUNITYJAR_DIR .'js/admin.min.js' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'community-jar-volunteers', COMMUNITYJAR_DIR .'js/admin.volunteers.min.js');	
	}else if( 'cj_email' == get_current_screen()->id ) {
		wp_enqueue_script( 'community-jar', COMMUNITYJAR_DIR .'js/admin.min.js' );
	}// end if
	
} // end register_admin_scripts
add_action( 'admin_enqueue_scripts', 'register_admin_scripts' );	

/**
* Removes unwanted buttons from the TinyMCE editor for email template editing.
*
* @version		1.1
* @since 		1.0
*/
function myformatTinyMCE($in) {
	if( 'cj_email' == get_current_screen()->id) {
		$in['plugins']='inlinepopups,tabfocus,paste,fullscreen,wordpress,wplink,wpdialogs,wpfullscreen';
		$in['apply_source_formatting']=false;
		$in['theme_advanced_buttons1']='bold,italic,underline,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,wp_fullscreen';
		return $in;
	}else{
		return $in;
	}
	
}
add_filter('tiny_mce_before_init', 'myformatTinyMCE' );

/**
 * Removes the Add Media button from the Email Template Editor Screens.
 *
 * @version		1.0
 * @since 		1.0
 */
function email_remove_media_controls() {
	if( 'cj_email' == get_current_screen()->id  && is_admin()) {
		remove_action( 'media_buttons', 'media_buttons' );
	}
}
add_action('admin_head','email_remove_media_controls');

/**
 * Changes the Editor screen icon to the Jar Icon.
 *
 * @version		1.1
 * @since 		1.0
 */
function communityjar_plugin_header_image() {
	global $post_type;

	if ((isset($_GET['post_type']) && $_GET['post_type'] == 'cj_project') || ($post_type == 'cj_project')) : ?>
		<style>
			#icon-edit { background:transparent url('<?php echo  COMMUNITYJAR_DIR .'images/jar32.png' ?>') no-repeat; }		
		</style>
	<?php endif; 
}
add_action('admin_head','communityjar_plugin_header_image');

/**
  * Adds new custom columns for 'cj_project' Custom Post Type admin.
  *
  * @version 1.1
  * @since  1.0
  */
  
function set_custom_edit_project_columns($columns) {
	$columns['cb'] = '<input type="checkbox" />';
	unset($columns['date']);
	$columns['title'] = _x('Title', 'column name');
	$columns['author'] = __('Project Owner');
	$columns['email'] = __('Owner Email');
	$columns['phone'] = __('Owner Phone');
	$columns['vcount'] = __('Volunteers');
	$columns['complete'] = __('Complete');
	$columns['pdate'] = __('Project Date');
	
	return $columns;
}
add_filter( 'manage_edit-cj_project_columns', 'set_custom_edit_project_columns');

/**
 * Sets what data should be used form custom columns in 'cj_project' Custom Post Type Admin.
 *
 * @version 1.1
 * @since  1.0
*/
function custom_project_column( $column, $post_id ) {
	global $post;
	switch ( $column ) {
		
		case 'email' :
			the_author_meta('user_email');
			break;
		case 'phone' :
			the_author_meta('phone-number');
			break;
		case 'vcount' :
		if(get_cj_volunteer_number('0','1','%')>0){
			echo '<span aria-hidden="true" class="icomoon-communityjar_fill admin_blue"></span><span class="cj-count">'.get_cj_volunteer_number('0','1','%').'</span>';
		}else{
			echo '<span aria-hidden="true" class="icomoon-communityjar_fill"></span><span class="cj-count">'.get_cj_volunteer_number('0','1','%').'</span>';
		}
			break;
		case 'complete' :
			if(1 == get_post_meta( $post->ID, 'project_is_complete', true )){
				echo '<strong>True</strong>';
			}else{
				echo 'false';
			}
			
			break;	
		case 'pdate' :
			$date = new DateTime( get_post_meta( $post_id, 'project_date', true ) ); 
			echo $date->format( get_option( 'date_format' ) );
			break;

	}
}
add_action( 'manage_cj_project_posts_custom_column', 'custom_project_column', 10, 2 );

/**
 * Sets which columns are sortable in 'cj_project' Custom Post Type Admin.
 *
 * @version 1.1
 * @since  1.0
*/
function sortable_project_columns($columns) {

	$columns['pdate'] = 'pdate';
	$columns['title'] = 'title';
	return $columns;
}
add_filter( 'manage_edit-cj_project_sortable_columns', 'sortable_project_columns');

/**
 * Enables column sorting for project date in 'cj_project' Custom Post Type Admin.
 *
 * @version 1.1
 * @since  1.0
*/
function project_column_orderby( $query ) {  
	if( ! is_admin() )  
		return;  

	$orderby = $query->get('orderby');  

	if( 'pdate' == $orderby ) {  
		$query->set('meta_key','project_date');  
		$query->set('orderby','meta_value');  
	}  
} 
add_action( 'pre_get_posts', 'project_column_orderby', 10, 2 );

/**
 * Enables column sorting for project date in 'cj_project' Custom Post Type Admin.
 *
 * @version 1.1
 * @since  1.0
*/
function hide_volunteer_comments( $query ) {  
	$query->query_vars['type'] = -'volunteer';
	
}
add_action( 'pre_get_comments', 'hide_volunteer_comments', 10, 1 );

/**
 * Change Column View for 'cj_email' Custom Post Type Removes CB and Date.
 *
 * @version 1.1
 * @since  1.0
*/
function set_custom_edit_email_columns($columns) {
	unset($columns['cb']);
	unset($columns['date']);
	$columns['title'] = _x('Title', 'column name');
	return $columns;
}
add_filter( 'manage_edit-cj_email_columns', 'set_custom_edit_email_columns'); //removes cb and date from view

/**
 * Change Row View for 'cj_email' Custom Post Type Removes trash, quick edit and preview on hover.
 *
 * @version 1.0
 * @since  1.0
*/
function remove_email_row_actions( $actions )
{
	if('cj_email' == get_post_type()  ){
		unset( $actions['view'] );
		unset( $actions['trash'] );
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
}
add_filter( 'post_row_actions', 'remove_email_row_actions');

/**
 * Removes column sorting for 'cj_email' Custom Post Type.
 *
 * @version 1.1
 * @since  1.0
*/
add_filter( 'manage_edit-cj_email_sortable_columns', '__return_empty_array' );

/**
 * Removes bulk action dropdown for 'cj_email' Custom Post Type.
 *
 * @version 1.1
 * @since  1.0
*/
add_filter( 'bulk_actions-' . 'edit-cj_email', '__return_empty_array' );

/**
 * Removes 'all' and 'published' links for 'cj_email' Custom Post Type Removes trash, quick edit and preview on hover.
 *
 * @version 1.1
 * @since  1.0
*/
add_filter( 'views_edit-cj_email', '__return_empty_array' );

/**
 * Removes the Slug, Revisions, and Author div elements from the Project editor.
 *
 * @version		1.1
 * @since		1.0
 */
function remove_project_meta_boxes() {
	
	remove_meta_box( 'slugdiv', 'cj_project', 'normal' );
	remove_meta_box( 'revisionsdiv', 'cj_project', 'normal' );
	remove_meta_box( 'authordiv', 'cj_project', 'normal' );
	remove_meta_box( 'submitdiv', 'cj_email', 'side' );
	
} // end remove_project_meta_boxes
add_action( 'admin_menu', 'remove_project_meta_boxes' );




?>