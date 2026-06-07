<?php
namespace Partner_Organizations;

class Block {
	public static function init() {
		add_action('init', [__CLASS__, 'register']);
	}

	public static function register() {
		register_block_type(
			'partner-organizations/partner-list',
			[
				'api_version'     => 3,
				'title'           => __('Partner List', 'partner-manager'),
				'description'     => __('Display partner logos, names, and website links.', 'partner-manager'),
				'category'        => 'widgets',
				'icon'            => 'groups',
				'attributes'      => [
					'category' => [
						'type'    => 'string',
						'default' => '',
					],
                    'title' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
					'columns'  => [
						'type'    => 'number',
						'default' => 3,
					],
				],
				'render_callback' => [__CLASS__, 'render'],
				'editor_script'   => 'partner-organizations-admin-block-editor',
			]
		);

		wp_register_script(
			'partner-organizations-admin-block-editor',
			plugins_url('assets/js/admin-block-editor.js', dirname(__DIR__, 1) ),
			['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render'],
			'1.0.0',
			true
		);
	}

	public static function render($attributes) {
		$query_args = [
			'post_type'      => Partner::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

        // set limits if we have it
        if (!empty($attributes['columns'])) {
            $query_args['posts_per_page'] = (int)$attributes['columns'];
        }

        // filter categories if we have it
		if (!empty($attributes['category'])) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => Partner::TAXONOMY,
					'field'    => 'slug',
					'terms'    => sanitize_title($attributes['category']),
				],
			];
		}

        // filter title if we have it
        if (!empty($attributes['title'])) {
            $query_args['s'] = trim($attributes['title']);
        }

		$query = new \WP_Query($query_args);
		if (!$query->have_posts()) {
			return '<p>' . esc_html__( 'No partners found.', 'partner-manager' ) . '</p>';
		}

		ob_start();
		?>
		<ul class="partner-list" style="--partner-columns: <?php echo esc_attr( $columns ); ?>;">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$id       = get_the_ID();
				$name     = get_the_title();
				$website  = get_post_meta( $id, Meta::META_WEBSITE, true );
				$logo_id  = (int) get_post_meta( $id, Meta::META_LOGO, true );
				$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
				?>
				<li class="partner-list__item">
					<?php if ( $website ) : ?>
						<a class="partner-list__link" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
					<?php endif; ?>

					<?php if ( $logo_url ) : ?>
						<img class="partner-list__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
					<?php endif; ?>

					<span class="partner-list__name"><?php echo esc_html( $name ); ?></span>

					<?php if ( $website ) : ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}
}