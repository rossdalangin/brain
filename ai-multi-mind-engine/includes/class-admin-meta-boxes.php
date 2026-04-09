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
	}

	public function render_mind_meta_box( $post ) {
		$role      = get_post_meta( $post->ID, 'amm_mind_role', true );
		$framework = get_post_meta( $post->ID, 'amm_mind_framework', true );
		$style     = get_post_meta( $post->ID, 'amm_mind_style', true );
		$structure = get_post_meta( $post->ID, 'amm_mind_structure', true );

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
		<p><em>The post content will be used as the **Hidden Prompt Engineering Layer**.</em></p>
		<?php
	}

	public function save_minds_meta_data( $post_id ) {
		if ( ! isset( $_POST['amm_mind_nonce'] ) || ! wp_verify_nonce( $_POST['amm_mind_nonce'], 'amm_save_mind_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array( 'amm_mind_role', 'amm_mind_framework', 'amm_mind_style', 'amm_mind_structure' );
		foreach ( $fields as $field ) {
			if ( isset( $_POST[$field] ) ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
			}
		}
	}
}
