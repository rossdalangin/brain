<?php
/**
 * Team Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Team_Manager {

	/**
	 * Create a new team
	 */
	public function create_team( $owner_id, $name ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'amm_teams', array(
			'owner_id'  => $owner_id,
			'team_name' => $name,
		));
		return $wpdb->insert_id;
	}

	/**
	 * Add member to team
	 */
	public function add_member( $team_id, $user_id, $role = 'member' ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'amm_team_members', array(
			'team_id' => $team_id,
			'user_id' => $user_id,
			'role'    => $role,
		));
	}

	/**
	 * Get user's team(s)
	 */
	public function get_user_teams( $user_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT t.*, tm.role FROM {$wpdb->prefix}amm_teams t
			 JOIN {$wpdb->prefix}amm_team_members tm ON t.id = tm.team_id
			 WHERE tm.user_id = %d",
			$user_id
		));
	}
}
