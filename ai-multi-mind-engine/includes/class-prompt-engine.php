<?php
/**
 * Prompt Engineering Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Prompt_Engine {

	/**
	 * Prepare the final system and user prompts
	 */
	public function prepare_prompts( $mind_id, $output_type, $user_input ) {
		$mind = $this->get_mind_instance( $mind_id );
		if ( ! $mind ) return new WP_Error( 'invalid_mind', 'The selected AI Mind is invalid.' );

		$system_prompt = $mind->get_system_prompt();
		$system_prompt .= "\n\nREQUIRED OUTPUT TYPE: " . $this->get_output_type_instructions( $output_type );

		$user_prompt = $mind->format_request( $user_input );

		return array(
			'system' => $system_prompt,
			'user'   => $user_prompt
		);
	}

	/**
	 * Get specific instructions for different output types
	 */
	private function get_output_type_instructions( $type ) {
		$types = array(
			'business_plan' => "Format as a professional multi-section Business Plan with executive summary, market analysis, and financial projections.",
			'marketing_plan' => "Format as a strategic Marketing Plan focusing on customer acquisition, funnel stages, and conversion metrics.",
			'sales_script'   => "Format as a high-conversion Sales Script with objection handling and closing techniques.",
			'sop'            => "Format as a step-by-step Standard Operating Procedure (SOP) with clear instructions and checklists.",
			'proposal'       => "Format as a persuasive Project Proposal with scope, deliverables, and investment details.",
		);

		return $types[$type] ?? "Provide a structured, professional business response.";
	}

	/**
	 * Factory to get Mind instance
	 */
	private function get_mind_instance( $mind_id ) {
		// First check if it's a hardcoded core mind
		switch ( $mind_id ) {
			case 'ceo':
				return new AMM_Mind_CEO();
			case 'strategist':
				return new AMM_Mind_Strategist();
			case 'funnel_builder':
				return new AMM_Mind_Funnel_Builder();
			case 'growth_hacker':
				return new AMM_Mind_Growth_Hacker();
			case 'copywriter':
				return new AMM_Mind_Copywriting_Master();
			case 'sales_closer':
				return new AMM_Mind_Sales_Closer();
			case 'profit_maximizer':
				return new AMM_Mind_Profit_Maximizer();
			case 'offer_creator':
				return new AMM_Mind_Offer_Creator();
			case 'sop_architect':
				return new AMM_Mind_SOP_Architect();
			case 'viral_creator':
				return new AMM_Mind_Viral_Creator();
		}

		// Otherwise, look for it in the CPT
		$mind_post = get_page_by_path( $mind_id, OBJECT, 'ai_minds' );
		if ( ! $mind_post ) {
			// Try by ID
			$mind_post = get_post( $mind_id );
		}

		if ( $mind_post && $mind_post->post_type === 'ai_minds' ) {
			return new AMM_Mind_Dynamic( $mind_post );
		}

		return null;
	}
}
