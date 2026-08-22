<?php
/**
 * Tests for Link Share.
 */
class Link_Share_Test extends WP_UnitTestCase {

	public function test_post_type_registered() {
		$this->assertTrue( post_type_exists( 'linkshare' ) );
	}

	public function test_shortcode_registered() {
		$this->assertTrue( shortcode_exists( 'linkshare' ) );
	}

	public function test_saves_link_meta() {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'linkshare',
				'post_author' => $user_id,
				'post_title'  => 'Example',
			)
		);

		$_POST['linkshare_meta_box_nonce'] = wp_create_nonce( 'Link-share.php' );
		$_POST['comment']                  = 'Nice site';
		$_REQUEST['comment']               = 'Nice site';
		$_POST['url']                      = 'https://example.com/page';
		$_REQUEST['url']                   = 'https://example.com/page';

		wpdocs_save_meta_box( $post_id );

		$this->assertSame( 'Nice site', get_post_meta( $post_id, '_share_comment', true ) );
		$this->assertSame( 'https://example.com/page', get_post_meta( $post_id, '_share_url', true ) );
	}

	public function test_shortcode_lists_links() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'  => 'linkshare',
				'post_title' => 'Shared Page',
				'post_status'=> 'publish',
			)
		);
		update_post_meta( $post_id, '_share_url', 'https://example.com' );
		update_post_meta( $post_id, '_share_comment', 'A comment' );

		ob_start();
		$returned = do_shortcode( '[linkshare]' );
		$echoed   = ob_get_clean();

		$this->assertStringContainsString( 'linkshare-list', $returned );
		$this->assertStringContainsString( 'Shared Page', $echoed );
		$this->assertStringContainsString( 'A comment', $echoed );
	}
}
