<?php
/*
Plugin Name: Link Share
Plugin URI: https://www.ixiqin.com/2017/08/linkshare-links-wordpress-share-plug-ins/
Description: Support You A new Post Type to Share WebPage to your friends.
Version: 0.0.6
Author: Bestony
Author URI: https://www.ixiqin.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bestony
Text Domain: link-share
Domain Path: /languages

Link Share is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Link Share is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Link Share. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

/**
 * 注册短代码
 */
function return_link_list(){
	$args = array( 'post_type' => 'linkshare', 'posts_per_page' => 100 );
	$the_query = new WP_Query( $args );
	$str = "<ul class='linkshare-list'>";
	if ( $the_query->have_posts() ){
		while ( $the_query->have_posts() ) {

			$the_query->the_post();
			?>
			<?php
			$meta = get_post_meta(get_the_ID(), '', true);
			?>
			<li class="linkshare-item">
				<a class="linkshare-link" href="<?php $meta['_share_url'][0] ?>" target="_blank" title="<?php echo esc_attr($meta['_share_comment'][0]) ?>"><?php esc_attr(the_title()); ?></a> - <small><?php echo $meta['_share_comment'][0] ?> - <?php the_time(get_option('date_format'));?>发布</small>
			</li>

			<?php

		}
	}
	$str = $str . "</ul>";
	wp_reset_postdata();
	return $str;

}
/**
 * 注册文章类型
 */
function linkshare_setup_post_types(){
	$labels = array(
		'name'                  => __( 'LinkShare','link-share'),
		'singular_name'         => __( 'LinkShare','link-share'),
		'menu_name'             => __( 'LinkShare','link-share'),
		'name_admin_bar'        => __( 'LinkShare','link-share'),
		'add_new'               => __( 'New','link-share'),
		'add_new_item'          => __( 'New LinkShare','link-share'),
		'new_item'              => __( 'New LinkShare','link-share'),
		'edit_item'             => __( 'Edit LinkShare','link-share'),
		'view_item'             => __( 'View LinkShare','link-share'),
		'all_items'             => __( 'All LinkShares','link-share'),
		'search_items'          => __( 'Search LinkShares','link-share'),
		'parent_item_colon'     => __( 'Parent LinkShare','link-share'),
		'not_found'             => __( 'This LinkShare Not Found','link-share'),
		'not_found_in_trash'    => __( 'Here is no Linkshare','link-share'),
		'archives'              => __( 'LinkShare Archives','link-share'),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __('Quickly Share a new WebPage to your friends','link-share'),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'share' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'map_meta_cap' => true,
		'supports'           => array('title'),
		'menu_icon'          => 'dashicons-share',
	);

	register_post_type( 'linkshare', $args );
}

/**
 * 注册信息框
 */
function linkshare_register_meta_boxes() {
	add_meta_box( 'meta-box-id', __( 'LinkShare Detail Information', 'link-share' ), 'linkshare_my_display_callback', 'linkshare' );
}

/**
 * 信息框内容展示
 *
 * @param WP_Post $post Current post object.
 */
function linkshare_my_display_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'linkshare_meta_box_nonce' );

	$comment = get_post_meta($post->ID,'_share_comment',true);
	$url = get_post_meta($post->ID,'_share_url',true);

	?>
	<div class='inside'>
		<table class="form-table">

			<tr>
				<th scope="row">
					<label for="my-text-field"><?php _e( 'Comment', 'link-share' ); ?></label>
				</th>

				<td>
					<input type="text" name="comment" value="<?php echo $comment; ?>"  width="100%" style="width:400px !important;"/>
					<br>
					<span class="description"><?php _e('The Comment of This webpage of you. You can leave it blank.','link-share') ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="my-text-field"><?php _e( 'Web Page URL', 'link-share' ); ?></label>
				</th>

				<td>
					<input type="text" name="url" value="<?php echo $url; ?>"  width="100%" style="width:400px !important;" />
					<br>
					<span class="description"><?php _e("URL of the web page ,you can copy it from your web browser address bar","link-share") ;?></span>
				</td>
			</tr>
		</table>

	</div>
	<?php
}

/**
 * 保存内容
 *
 * @param int $post_id Post ID
 */
function wpdocs_save_meta_box( $post_id ) {
	if ( !isset( $_POST['linkshare_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['linkshare_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}

	if ( isset( $_REQUEST['comment'] ) && $_REQUEST['comment'] != '' ) {
		$comment = sanitize_text_field( $_POST['comment'] );
		update_post_meta( $post_id, '_share_comment', $comment );
	}
	if ( isset( $_REQUEST['url'] ) && $_REQUEST['comment'] != '' ) {
		$url = esc_url( $_POST['url'] );
		update_post_meta( $post_id, '_share_url', $url );
	}

}

function i10n(){
    $current_locale = get_locale();
    if(!empty($current_locale)){
        $mo_file = dirname(__FILE__).'/languages/'.$current_locale.".mo";
        if (@file_exists($mo_file)&& is_readable($mo_file))
            load_textdomain('link-share',$mo_file);
    }
}

/**
 * 激活 Hook
 */
function linkshare_install()
{
	linkshare_setup_post_types();
	flush_rewrite_rules();
}

/**
 * 冻结 Hook
 */
function linkshare_deactivation()
{
	flush_rewrite_rules();
}

// Activation Hook
add_action('init','i10n');
add_action( 'init', 'linkshare_setup_post_types' );
register_activation_hook( __FILE__, 'linkshare_install' );
add_action( 'add_meta_boxes', 'linkshare_register_meta_boxes' );
add_action( 'save_post', 'wpdocs_save_meta_box' );
add_shortcode("linkshare", "return_link_list");
/**
 * 允许在侧边栏执行
 */
add_filter( 'widget_text', 'do_shortcode' );

//Deactivation Hooks
register_deactivation_hook( __FILE__, 'linkshare_deactivation' );
