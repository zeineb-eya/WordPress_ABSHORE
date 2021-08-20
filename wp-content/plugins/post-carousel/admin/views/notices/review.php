<?php
/**
 * The admin review notice.
 *
 * @since        2.2.3
 * @version      2.2.3
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/admin/views/notices
 * @author     ShapedPlugin<support@shapedplugin.com>
 */

/**
 * The admin review notice.
 */
class SPS_Review {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
		add_action( 'wp_ajax_smart-post-show-never-show-review-notice', array( $this, 'dismiss_review_notice' ) );
	}

	/**
	 * Display admin notice.
	 *
	 * @return void
	 */
	public function display_admin_notice() {

		// Show only to Admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Variable default value.
		$review = get_option( 'smart_post_show_review_notice_dismiss' );
		$time   = time();
		$load   = false;

		if ( ! $review ) {
			$review = array(
				'time'      => $time,
				'dismissed' => false,
			);
			add_option( 'smart_post_show_review_notice_dismiss', $review );
		} else {
			// Check if it has been dismissed or not.
			if ( ( isset( $review['dismissed'] ) && ! $review['dismissed'] ) && ( isset( $review['time'] ) && ( ( $review['time'] + ( DAY_IN_SECONDS * 3 ) ) <= $time ) ) ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}
		?>
		<div id="smart-post-show-review-notice" class="smart-post-show-review-notice">
			<div class="sp-sps-plugin-icon">
				<img src="<?php echo esc_url( SP_PC_URL . 'admin/assets/img/images/sps-icon.svg' ); ?>" alt="Smart Post Show">
			</div>
			<div class="sp-sps-notice-text">
				<h3>Enjoying <strong>Smart Post Show</strong>?</h3>
				<p>Hope that you had a good experience with the <strong>Smart Post Show</strong>. Would you please show us a little love by rating us in the <a href="https://wordpress.org/support/plugin/post-carousel/reviews/?filter=5#new-post" target="_blank"><strong>WordPress.org</strong></a>?
				Just a minute to rate it. Thank you!</p>

				<p class="sp-sps-review-actions">
					<a href="https://wordpress.org/support/plugin/post-carousel/reviews/?filter=5#new-post" target="_blank" class="button button-primary notice-dismissed rate-testimonial">Rate Smart Post Show</a>
					<a href="#" class="notice-dismissed remind-me-later"><span class="dashicons dashicons-clock"></span>Nope, maybe later
</a>
					<a href="#" class="notice-dismissed never-show-again"><span class="dashicons dashicons-dismiss"></span>Never show again</a>
				</p>
			</div>
		</div>

		<script type='text/javascript'>

			jQuery(document).ready( function($) {
				$(document).on('click', '#smart-post-show-review-notice.smart-post-show-review-notice .notice-dismissed', function( event ) {
					if ( $(this).hasClass('rate-testimonial') ) {
						var notice_dismissed_value = "1";
					}
					if ( $(this).hasClass('remind-me-later') ) {
						var notice_dismissed_value =  "2";
						event.preventDefault();
					}
					if ( $(this).hasClass('never-show-again') ) {
						var notice_dismissed_value =  "3";
						event.preventDefault();
					}

					$.post( ajaxurl, {
						action: 'smart-post-show-never-show-review-notice',
						notice_dismissed_data : notice_dismissed_value,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'sps_review_notice' ) ); ?>'
					});

					$('#smart-post-show-review-notice.smart-post-show-review-notice').hide();
				});
			});

		</script>
		<?php
	}

	/**
	 * Dismiss review notice
	 *
	 * @since  2.2.3
	 *
	 * @return void
	 **/
	public function dismiss_review_notice() {
		$post_data = wp_unslash( $_POST );

		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( sanitize_key( $post_data['nonce'] ), 'sps_review_notice' ) ) {
			return;
		}

		$review = get_option( 'smart_post_show_review_notice_dismiss' );
		if ( ! $review ) {
			$review = array();
		}
		switch ( isset( $post_data['notice_dismissed_data'] ) ? $post_data['notice_dismissed_data'] : '' ) {
			case '1':
				$review['time']      = time();
				$review['dismissed'] = false;
				break;
			case '2':
				$review['time']      = time();
				$review['dismissed'] = false;
				break;
			case '3':
				$review['time']      = time();
				$review['dismissed'] = true;
				break;
		}
		update_option( 'smart_post_show_review_notice_dismiss', $review );
		die;
	}
}

new SPS_Review();
