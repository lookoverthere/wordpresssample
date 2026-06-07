<?php
/**
 * Plugin Name: Partner Organizations
 * Description: Registers a Partner Organizations custom post type with logo, website URL, and category.
 * Version: 1.0.0
 */
namespace Partner_Organizations;

require_once plugin_dir_path(__FILE__) . 'includes/classes/partner.php';
require_once plugin_dir_path(__FILE__) . 'includes/classes/meta.php';
require_once plugin_dir_path(__FILE__) . 'includes/classes/api.php';
require_once plugin_dir_path(__FILE__) . 'includes/classes/block.php';

function partner_manager_init() {
	Partner::init();
	Meta::init();
    Api::init();
	Block::init();
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'partner-organizations-list',
		plugins_url( 'assets/css/partner-list.css', __FILE__ ),
		[],
		'1.0.0'
	);
});

add_action('plugins_loaded', 'Partner_Organizations\partner_manager_init');
register_activation_hook(__FILE__, ['Partner_Organizations\Partner', 'activate']);