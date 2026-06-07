<?php
namespace Partner_Organizations;

class Partner {
	const POST_TYPE = 'partner';
	const TAXONOMY  = 'partner_category';

	public static function init() {
		add_action('init', [__CLASS__, 'register_post_type']);
		add_action('init', [__CLASS__, 'register_taxonomy']);
		add_filter('manage_' . self::POST_TYPE . '_posts_columns', [__CLASS__, 'list_columns']);
		add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [__CLASS__, 'render_list_column'], 10, 2);
	}

	public static function activate() {
		self::register_post_type();
		self::register_taxonomy();
		self::insert_default_terms();
		flush_rewrite_rules();
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			[
				'labels'       => [
					'name'               => __('Partners', 'partner-manager'),
					'singular_name'      => __('Partner', 'partner-manager'),
					'add_new'            => __('Add New', 'partner-manager'),
					'add_new_item'       => __('Add New Partner', 'partner-manager'),
					'edit_item'          => __('Edit Partner', 'partner-manager'),
					'new_item'           => __('New Partner', 'partner-manager'),
					'view_item'          => __('View Partner', 'partner-manager'),
					'search_items'       => __('Search Partners', 'partner-manager'),
					'not_found'          => __('No partners found.', 'partner-manager'),
					'not_found_in_trash' => __('No partners found in Trash.', 'partner-manager'),
					'menu_name'          => __('Partners', 'partner-manager'),
                ],
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-groups',
				'supports'     => ['title', 'thumbnail'],
				'has_archive'  => true,
				'rewrite'      => ['slug' => 'partners'],
            ]
		);
	}

	public static function register_taxonomy() {
		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			[
				'labels'       => [
					'name'          => __('Partner Categories', 'partner-manager'),
					'singular_name' => __('Partner Category', 'partner-manager'),
					'search_items'  => __('Search Categories', 'partner-manager'),
					'all_items'     => __('All Categories', 'partner-manager'),
					'edit_item'     => __('Edit Category', 'partner-manager'),
					'update_item'   => __('Update Category', 'partner-manager'),
					'add_new_item'  => __('Add New Category', 'partner-manager'),
					'new_item_name' => __('New Category Name', 'partner-manager'),
					'menu_name'     => __('Categories', 'partner-manager'),
                ],
				'public'       => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite'      => ['slug' => 'partner-category'],
            ]
		);
	}

	private static function insert_default_terms() {
		$defaults = ['Education', 'Nonprofit', 'Corporate'];
		foreach ($defaults as $term) {
			if (!term_exists($term, self::TAXONOMY)) {
				wp_insert_term($term, self::TAXONOMY);
			}
		}
	}

	public static function list_columns($columns) {
		$new = [];
		foreach ($columns as $key => $label) {
			$new[$key] = $label;
			if ('title' === $key) {
				$new['partner_logo']     = __('Logo', 'partner-manager');
				$new['partner_website']  = __('Website', 'partner-manager');
				$new['partner_category'] = __('Category', 'partner-manager');
			}
		}

		return $new;
	}

	public static function render_list_column($column, $post_id) {
		switch ($column) {
			case 'partner_logo':
				$logo_id = (int) get_post_meta($post_id, '_partner_logo_id', true);
				if (!empty($logo_id)) {
					echo wp_get_attachment_image($logo_id, [50, 50]);
				}
				break;
			case 'partner_website':
				$url = get_post_meta($post_id, '_partner_website_url', true);
				if (!empty($url)) {
                    echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($url) . '</a>';
				}
				break;
			case 'partner_category':
				$terms = get_the_terms($post_id, self::TAXONOMY);
				if (!empty($terms) && !is_wp_error($terms)) {
					echo esc_html(implode(', ', wp_list_pluck($terms, 'name')));
				}
				break;
		}
	}
}