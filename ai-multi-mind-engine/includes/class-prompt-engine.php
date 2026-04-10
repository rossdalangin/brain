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
	public function prepare_prompts( $mind_id, $output_type, $user_input, $language = 'English' ) {
		$mind = $this->get_mind_instance( $mind_id );
		if ( ! $mind ) return new WP_Error( 'invalid_mind', 'The selected AI Mind is invalid.' );

		$global_context = get_option( 'amm_global_system_context' );
		$system_prompt = $global_context ? $global_context . "\n\n" : "";
		$system_prompt .= $mind->get_system_prompt();
		$system_prompt .= "\n\nREQUIRED OUTPUT TYPE: " . $this->get_output_type_instructions( $output_type );
		$system_prompt .= "\n\nCRITICAL: The entire output MUST be written in {$language}.";

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
			'report'         => "Format as a detailed Business Report with data analysis, key findings, and actionable conclusions.",
			'blog_post'      => "Format as a high-authority Blog Post with an engaging title, SEO-optimized headers, and a clear call to action.",
			'ad_copy'        => "Format as high-converting Ad Copy for platforms like Facebook or Google, focusing on hooks, benefits, and a strong CTA.",
			'video_script'   => "Format as a Video Script with visual cues, a compelling hook, and a structured narrative arc.",
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
			case 'visionary':
				return new AMM_Mind_Visionary();
			case 'dominator':
				return new AMM_Mind_Dominator();
			case 'automation_expert':
				return new AMM_Mind_Automation_Expert();
			case 'storyteller':
				return new AMM_Mind_Storytelling_Expert();
			case 'board_advisor':
				return new AMM_Mind_Board_Advisor();
			case 'brand_authority':
				return new AMM_Mind_Brand_Authority();
			case 'objection_killer':
				return new AMM_Mind_Objection_Killer();
			case 'negotiation_master':
				return new AMM_Mind_Negotiation_Master();
			case 'systems_builder':
				return new AMM_Mind_Systems_Builder();
			case 'pricing_strategist':
				return new AMM_Mind_Pricing_Strategist();
			case 'cost_cutter':
				return new AMM_Mind_Cost_Cutter();
			case 'product_strategist':
				return new AMM_Mind_Product_Strategist();
			case 'ux_expert':
				return new AMM_Mind_UX_Expert();
			case 'saas_architect':
				return new AMM_Mind_SaaS_Architect();
			case 'email_specialist':
				return new AMM_Mind_Email_Specialist();
			case 'risk_analyst':
				return new AMM_Mind_Risk_Analyst();
			case 'policy_generator':
				return new AMM_Mind_Policy_Generator();
			case 'leadership_coach':
				return new AMM_Mind_Leadership_Coach();
			case 'decision_expert':
				return new AMM_Mind_Decision_Expert();
			case 'magic_bff':
				return new AMM_Mind_Magic_BFF();
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
