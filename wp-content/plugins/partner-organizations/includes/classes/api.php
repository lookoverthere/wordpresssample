<?php
namespace Partner_Organizations;

// be sure to enable Post Name in permalinks to access /wp-json/custom/v1/partners
// but otherwise do it by localhost:8080?rest_route=/custom/v1/partners
class Api {

	const NAMESPACE = 'custom/v1';
	const ROUTE     = '/partners';
	const RATE_LIMIT = 10;        // requests per window
	const RATE_WINDOW = 60;      // seconds

	public static function init() {
		add_action('rest_api_init', [__CLASS__, 'register_routes']);
	}

	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [__CLASS__, 'get_partners'],
				'permission_callback' => [__CLASS__, 'can_read'],
				'args'                => self::collection_args(),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			self::ROUTE . '/(?P<id>\d+)',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [__CLASS__, 'get_partner'],
				'permission_callback' => [__CLASS__, 'can_read'],
				'args'                => [
					'id' => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	public static function collection_args() {
		return [
			'page'     => [
				'default'           => 1,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'minimum'           => 1,
			],
			'per_page' => [
				'default'           => 25,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'minimum'           => 1,
				'maximum'           => 25,
			],
			'category' => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'description'       => 'Filter by partner_category slug',
			],
			'search'   => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	public static function can_read(\WP_REST_Request $request) {
		if (self::is_rate_limited()) {
			return new \WP_Error(
				'rate_limit_exceeded',
				__('Too many requests. Please try again later.', 'partner-manager' ),
				['status' => 429]
			);
		}

		self::record_request();

		return true;
	}

	private static function client_key() {
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		return 'partner_api_client_key_' . md5($ip);
	}

	private static function is_rate_limited() {
		$count = get_transient(self::client_key());
		return $count >= self::RATE_LIMIT;
	}

	private static function record_request() {
		$key   = self::client_key();
		$count = get_transient($key);
		set_transient($key, $count + 1, self::RATE_WINDOW);
	}

	public static function get_partners(\WP_REST_Request $request) {
		$page     = max(1, (int)$request->get_param('page'));
		$per_page = min(25, max(1, (int)$request->get_param('per_page')));

		$args = [
			'post_type'      => Partner::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		if ($request->get_param('search')) {
			$args['s'] = $request->get_param('search');
		}

		if ($request->get_param('category')) {
			$args['tax_query'] = [
				[
					'taxonomy' => Partner::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $request->get_param('category'),
				],
			];
		}

		$query = new \WP_Query($args);

		$items = array_map(
			[__CLASS__, 'format_partner'],
			$query->posts
		);

		$response = new \WP_REST_Response(
			[
				'data' => $items,
				'meta' => [
					'page'        => $page,
					'per_page'    => $per_page,
					'total'       => $query->found_posts,
					'total_pages' => $query->max_num_pages,
				],
			],
			200
		);

		$response->header('X-RateLimit-Limit', self::RATE_LIMIT);
		$response->header('X-RateLimit-Window', self::RATE_WINDOW);

		return $response;
	}

	public static function get_partner(\WP_REST_Request $request) {
		$post = get_post($request['id']);

		if (!$post || Partner::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status) {
			return new \WP_Error(
				'partner_not_found',
				__('Partner not found.', 'partner-manager'),
				['status' => 404]
			);
		}

		return new \WP_REST_Response(self::format_partner($post), 200);
	}

	/**
	 * Map WP post → stable API schema.
	 */
	private static function format_partner(\WP_Post $post) {
		$logo_id  = get_post_meta($post->ID, Meta::META_LOGO, true);
		$website  = get_post_meta($post->ID, Meta::META_WEBSITE, true);
		$terms    = get_the_terms($post->ID, Partner::TAXONOMY);
		$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';

		$categories = [];
		if ($terms && ! is_wp_error($terms)) {
			foreach ($terms as $term) {
				$categories[] = [
					'name' => $term->name,
					'slug' => $term->slug,
				];
			}
		}

		return [
			'name'       => get_the_title($post),
			'slug'       => $post->post_name,
			'website'    => $website ? esc_url_raw($website) : '',
			'logo'       => ['url' => $logo_url],
			'categories' => $categories,
		];
	}
}