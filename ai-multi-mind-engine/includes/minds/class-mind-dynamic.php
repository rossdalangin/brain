<?php
/**
 * Dynamic AI Mind based on Custom Post Type
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Mind_Dynamic extends AMM_Mind_Base {

	public function __construct( $post ) {
		$this->name = $post->post_title;
		$this->role = get_post_meta( $post->ID, 'amm_mind_role', true );
		$this->thinking_framework = get_post_meta( $post->ID, 'amm_mind_framework', true );
		$this->decision_style = get_post_meta( $post->ID, 'amm_mind_style', true );
		$this->output_structure = get_post_meta( $post->ID, 'amm_mind_structure', true );
		$this->hidden_prompt = $post->post_content;
	}
}
