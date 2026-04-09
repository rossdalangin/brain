<?php
/**
 * Abstract Base Class for AI Minds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AMM_Mind_Base {

	protected $name;
	protected $role;
	protected $thinking_framework;
	protected $decision_style;
	protected $output_structure;
	protected $hidden_prompt;

	/**
	 * Get the full system prompt for this mind
	 */
	public function get_system_prompt() {
		return "You are {$this->name}, acting as a {$this->role}.
		Thinking Framework: {$this->thinking_framework}
		Decision Style: {$this->decision_style}
		Output Structure: {$this->output_structure}

		Context: {$this->hidden_prompt}";
	}

	/**
	 * Get Mind data for the UI
	 */
	public function get_data() {
		return array(
			'name'               => $this->name,
			'role'               => $this->role,
			'thinking_framework' => $this->thinking_framework,
			'decision_style'     => $this->decision_style,
			'output_structure'   => $this->output_structure,
		);
	}

	/**
	 * Format the user's request
	 */
	public function format_request( $user_input ) {
		return "Based on your expertise, please address the following: " . $user_input;
	}
}
