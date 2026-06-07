<?php
namespace Partner_Organizations;

class Meta {
	const META_LOGO    = '_partner_logo_id';
	const META_WEBSITE = '_partner_website_url';
	const NONCE_ACTION = 'partner_details_save';
	const NONCE_NAME   = 'partner_details_nonce';

	public static function init() {
		add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
		add_action('save_post_partner', [__CLASS__, 'save'], 10, 2);
		add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
	}

	public static function add_meta_boxes() {
		add_meta_box(
			'partner_details',
			__('Partner Details', 'partner-manager'),
			[__CLASS__, 'render_meta_box'],
			'partner',
			'normal',
			'high'
		);
	}

	public static function enqueue_admin_assets($hook) {
		global $post;

		if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
			return;
		}

		if (!$post || 'partner' !== $post->post_type) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'partner-manager-admin',
			plugin_dir_url(__FILE__) . '../../assets/js/admin-meta.js',
			['jquery']
		);
	}

	public static function render_meta_box($post) {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$logo_id  = get_post_meta($post->ID, self::META_LOGO, true);
		$website  = get_post_meta($post->ID, self::META_WEBSITE, true);
		$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';

        include plugin_dir_path(__FILE__) . '../templates/meta/boxes.php';
	}

	public static function save($post_id, $post) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

        // eh probably just redundent check
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// nonce check
		if (
			!isset($_POST[self::NONCE_NAME]) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[ self::NONCE_NAME ])), self::NONCE_ACTION)
		) {
			return;
		}

		// save the image/logo
		if (isset($_POST['partner_logo_id'])) {
			$logo_id = absint($_POST['partner_logo_id']);

			if (!empty($logo_id) && 'attachment' === get_post_type($logo_id)) {
				update_post_meta($post_id, self::META_LOGO, $logo_id);
			} else {
				delete_post_meta($post_id, self::META_LOGO);
			}
		}

		// save the weburl
		if (isset($_POST['partner_website_url'])) {
			$url = esc_url_raw(wp_unslash($_POST['partner_website_url']));

			if (!empty($url)) {
				update_post_meta($post_id, self::META_WEBSITE, $url);
			} else {
				delete_post_meta($post_id, self::META_WEBSITE);
			}
		}
	}
}