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
	public function add_member( $team_id, $user_id, $role = 'member', $permissions = array('can_generate', 'can_view_workspace') ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'amm_team_members', array(
			'team_id' => $team_id,
			'user_id' => $user_id,
			'role'    => $role,
			'permissions' => json_encode($permissions)
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

	/**
	 * Update branding (White-Label)
	 */
	/**
	 * Get member permissions
	 */
	public function get_member_permissions( $team_id, $user_id ) {
		global $wpdb;
		$perms = $wpdb->get_var( $wpdb->prepare(
			"SELECT permissions FROM {$wpdb->prefix}amm_team_members WHERE team_id = %d AND user_id = %d",
			$team_id, $user_id
		));
		return $perms ? json_decode($perms, true) : array();
	}

	public function update_branding( $team_id, $logo, $color ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'amm_teams',
			array( 'custom_logo' => $logo, 'primary_color' => $color ),
			array( 'id' => $team_id )
		);
	}

	/**
	 * Create an invitation
	 */
	public function create_invite( $team_id, $email ) {
		global $wpdb;
		$token = wp_generate_password( 20, false );
		$wpdb->insert( $wpdb->prefix . 'amm_invites', array(
			'team_id' => $team_id,
			'email'   => $email,
			'token'   => $token,
		));
		return $token;
	}

	/**
	 * Get pending invites
	 */
	public function get_pending_invites( $team_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amm_invites WHERE team_id = %d AND status = 'pending'",
			$team_id
		));
	}

	/**
	 * Verify and consume a token
	 */
	public function verify_and_consume_token( $token ) {
		global $wpdb;
		$invite = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amm_invites WHERE token = %s AND status = 'pending'",
			$token
		));

		if ( $invite ) {
			$wpdb->update( $wpdb->prefix . 'amm_invites', array( 'status' => 'accepted' ), array( 'id' => $invite->id ) );
			return $invite->team_id;
		}

		return false;
	}
}
