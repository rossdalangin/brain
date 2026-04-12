<?php
/**
 * Admin Meta Boxes for AI Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Admin_Meta_Boxes {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_minds_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_minds_meta_data' ) );
	}

	public function add_minds_meta_boxes() {
		add_meta_box(
			'amm_mind_details',
			'AI Mind Configuration',
			array( $this, 'render_mind_meta_box' ),
			'ai_minds',
			'normal',
			'high'
		);

		add_meta_box(
			'amm_template_marketplace',
			'Marketplace Settings',
			array( $this, 'render_template_marketplace_meta_box' ),
			'ai_templates',
			'side',
			'default'
		);

		add_meta_box(
			'amm_mind_featured',
			'Promotion Settings',
			array( $this, 'render_featured_meta_box' ),
			'ai_minds',
			'side',
			'default'
		);

		add_meta_box(
			'amm_template_details',
			'Template Configuration',
			array( $this, 'render_template_meta_box' ),
			'ai_templates',
			'normal',
			'high'
		);
	}

	public function render_template_marketplace_meta_box( $post ) {
		wp_nonce_field( 'amm_save_template_market', 'amm_template_market_nonce' );
		?>
		<p>
			<label><input type="checkbox" name="amm_template_is_premium" value="yes" <?php checked( get_post_meta($post->ID, 'amm_template_is_premium', true), 'yes' ); ?>> <strong>Premium Template?</strong></label>
		</p>
		<p>
			<label for="amm_template_price"><strong>Price ($):</strong></label><br>
			<input type="number" name="amm_template_price" value="<?php echo esc_attr( get_post_meta($post->ID, 'amm_template_price', true) ?: '19' ); ?>" style="width:100%;">
		</p>
		<?php
	}

	public function render_template_meta_box( $post ) {
		wp_nonce_field( 'amm_save_template_meta', 'amm_template_nonce' );
		?>
		<p>
			<label for="amm_min_plan"><strong>Minimum Plan Required:</strong></label>
			<select id="amm_min_plan" name="amm_min_plan" style="width:100%;">
				<option value="free" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'free' ); ?>>Free</option>
				<option value="starter" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'starter' ); ?>>Starter</option>
				<option value="pro" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'pro' ); ?>>Pro</option>
				<option value="agency" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'agency' ); ?>>Agency</option>
			</select>
		</p>
		<?php
	}

	public function render_featured_meta_box( $post ) {
		wp_nonce_field( 'amm_save_featured_meta', 'amm_featured_nonce' );
		?>
		<p>
			<label><input type="checkbox" name="amm_is_featured" value="yes" <?php checked( get_post_meta($post->ID, 'amm_is_featured', true), 'yes' ); ?>> <strong>Featured Item?</strong><br><small>Highlight this at the top of the dashboard library.</small></label>
		</p>
		<?php
	}

	public function render_mind_meta_box( $post ) {
		$role      = get_post_meta( $post->ID, 'amm_mind_role', true );
		$framework = get_post_meta( $post->ID, 'amm_mind_framework', true );
		$style     = get_post_meta( $post->ID, 'amm_mind_style', true );
		$structure = get_post_meta( $post->ID, 'amm_mind_structure', true );
		$is_premium = get_post_meta( $post->ID, 'amm_is_premium', true );

		wp_nonce_field( 'amm_save_mind_meta', 'amm_mind_nonce' );

		?>
		<p>
			<label for="amm_mind_role"><strong>Role:</strong></label><br />
			<input type="text" id="amm_mind_role" name="amm_mind_role" value="<?php echo esc_attr( $role ); ?>" style="width:100%;" placeholder="e.g. CEO of a Fortune 500 Company" />
		</p>
		<p>
			<label for="amm_mind_framework"><strong>Thinking Framework:</strong></label><br />
			<input type="text" id="amm_mind_framework" name="amm_mind_framework" value="<?php echo esc_attr( $framework ); ?>" style="width:100%;" placeholder="e.g. First Principles, SWOT" />
		</p>
		<p>
			<label for="amm_mind_style"><strong>Decision Style:</strong></label><br />
			<input type="text" id="amm_mind_style" name="amm_mind_style" value="<?php echo esc_attr( $style ); ?>" style="width:100%;" placeholder="e.g. Decisive, data-driven" />
		</p>
		<p>
			<label for="amm_mind_structure"><strong>Output Structure:</strong></label><br />
			<input type="text" id="amm_mind_structure" name="amm_mind_structure" value="<?php echo esc_attr( $structure ); ?>" style="width:100%;" placeholder="e.g. Executive Summary, Roadmap" />
		</p>
		<p>
			<label for="amm_is_premium"><strong>Premium Mind?</strong></label>
			<select id="amm_is_premium" name="amm_is_premium">
				<option value="no" <?php selected( $is_premium, 'no' ); ?>>No (Standard)</option>
				<option value="yes" <?php selected( $is_premium, 'yes' ); ?>>Yes (Marketplace/Pro)</option>
			</select>
		</p>
		<p>
			<label for="amm_min_plan"><strong>Minimum Plan Required:</strong></label>
			<select id="amm_min_plan" name="amm_min_plan">
				<option value="free" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'free' ); ?>>Free</option>
				<option value="starter" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'starter' ); ?>>Starter</option>
				<option value="pro" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'pro' ); ?>>Pro</option>
				<option value="agency" <?php selected( get_post_meta($post->ID, 'amm_min_plan', true), 'agency' ); ?>>Agency</option>
			</select>
		</p>
		<p><em>The post content will be used as the **Hidden Prompt Engineering Layer**.</em></p>
		<?php
	}

	public function save_minds_meta_data( $post_id ) {
		// Template Meta
		if ( isset( $_POST['amm_template_nonce'] ) && wp_verify_nonce( $_POST['amm_template_nonce'], 'amm_save_template_meta' ) ) {
			if ( isset( $_POST['amm_min_plan'] ) ) {
				update_post_meta( $post_id, 'amm_min_plan', sanitize_text_field( $_POST['amm_min_plan'] ) );
			}
		}
		if ( isset( $_POST['amm_template_market_nonce'] ) && wp_verify_nonce( $_POST['amm_template_market_nonce'], 'amm_save_template_market' ) ) {
			update_post_meta( $post_id, 'amm_template_is_premium', isset( $_POST['amm_template_is_premium'] ) ? 'yes' : 'no' );
			update_post_meta( $post_id, 'amm_template_price', (int)$_POST['amm_template_price'] );
		}

		// Mind Meta
		if ( ! isset( $_POST['amm_mind_nonce'] ) || ! wp_verify_nonce( $_POST['amm_mind_nonce'], 'amm_save_mind_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array( 'amm_mind_role', 'amm_mind_framework', 'amm_mind_style', 'amm_mind_structure', 'amm_is_premium', 'amm_min_plan' );
		foreach ( $fields as $field ) {
			if ( isset( $_POST[$field] ) ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
			}
		}

		// Save Featured Status separately
		if ( isset( $_POST['amm_featured_nonce'] ) && wp_verify_nonce( $_POST['amm_featured_nonce'], 'amm_save_featured_meta' ) ) {
			update_post_meta( $post_id, 'amm_is_featured', isset( $_POST['amm_is_featured'] ) ? 'yes' : 'no' );
		}
	}
}
