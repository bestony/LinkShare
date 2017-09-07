<?php
/*
Plugin Name: Link Share
Plugin URI: https://www.ixiqin.com/2017/08/linkshare-links-wordpress-share-plug-ins/
Description: 为你提供链接分享功能
Version: 0.0.5
Author: Bestony
Author URI: https://www.ixiqin.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bestony
Domain Path: /plugin

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
		'name'                  => __( '分享'),
		'singular_name'         => __( '分享'),
		'menu_name'             => __( '分享'),
		'name_admin_bar'        => __( '分享'),
		'add_new'               => __( '新建'),
		'add_new_item'          => __( '新建分享'),
		'new_item'              => __( '新分享'),
		'edit_item'             => __( '编辑分享'),
		'view_item'             => __( '查看分享'),
		'all_items'             => __( '所有分享'),
		'search_items'          => __( '搜索分享'),
		'parent_item_colon'     => __( '上级分享'),
		'not_found'             => __( '分享未找到。'),
		'not_found_in_trash'    => __( '垃圾箱中没有分享'),
		'featured_image'        => __( '分享图片'),
		'set_featured_image'    => __( '设置分享图片'),
		'remove_featured_image' => __( '移除分享图片'),
		'use_featured_image'    => __( '使用分享图片'),
		'archives'              => __( '分享列表'),
		'insert_into_item'      => __( '插入新的分享'),
		'uploaded_to_this_item' => __( '上传到这个分享'),
		'filter_items_list'     => __( '筛选分享'),
		'items_list_navigation' => __( '分享列表导航'),
		'items_list'            => __( '分享列表'),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => "快速分享",
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
	add_meta_box( 'meta-box-id', __( '分享信息', 'textdomain' ), 'linkshare_my_display_callback', 'linkshare' );
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
					<label for="my-text-field"><?php _e( '分享评论', 'linkshare_metaBox' ); ?></label>
				</th>

				<td>
					<input type="text" name="comment" value="<?php echo $comment; ?>"  width="100%" style="width:400px !important;"/>
					<br>
					<span class="description">你自己针对文章的评论.</span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="my-text-field"><?php _e( '分享链接', 'linkshare_metaBox' ); ?></label>
				</th>

				<td>
					<input type="text" name="url" value="<?php echo $url; ?>"  width="100%" style="width:400px !important;" />
					<br>
					<span class="description">文章的链接.</span>
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
