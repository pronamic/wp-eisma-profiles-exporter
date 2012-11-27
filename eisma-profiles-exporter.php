<?php
/*
Plugin Name: Eisma Profiles Expoter
Plugin URI: http://pronamic.eu/wordpress/eisma-profiles-expoter/
Description: This plugin can export the 'profile' custom post type with the custom meta data to an CSV file.

Version: 0.1
Requires at least: 3.0

Author: Pronamic
Author URI: http://pronamic.eu/

Text Domain: eisma_profile_exporter
Domain Path: /languages/

License: GPL

GitHub URI: https://github.com/pronamic/wp-eisma-profile-exporter
*/

class Pronamic_EismaProfilesExpoter_Plugin {
	/**
	 * The plugin file
	 * 
	 * @var string
	 */
	public static $file;

	/**
	 * The plugin dirname
	 * 
	 * @var string
	 */
	public static $dirname;

	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 */
	public static function bootstrap( $file ) {
		self::$file    = $file;
		self::$dirname = dirname( $file );

		add_action( 'init',       array( __CLASS__, 'init' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public static function init() {
		// Text domain
		$rel_path = dirname( plugin_basename( self::$file ) ) . '/languages/';
	
		load_plugin_textdomain( 'eisma_profiles_exporter', false, $rel_path );
	}
	
	//////////////////////////////////////////////////

	/**
	 * Admin initialize
	 */
	public static function admin_init() {
		// Export
		self::maybe_export();
	}

	//////////////////////////////////////////////////

	/**
	 * Admin menu
	 */
	public static function admin_menu() {
		add_submenu_page( 
			'edit.php?post_type=profile', // parent_slug
			__( 'Profiles Export', 'eisma_profiles_exporter' ), // page_title
			__( 'Export', 'eisma_profiles_exporter' ), // menu_title
			'read', // capability
			'eisma_profiles_exporter', // menu_slug
			array( __CLASS__, 'page_export' ) // function 
		);
	}


	//////////////////////////////////////////////////

	/**
	 * Get export
	 */
	public static function get_export() {	
		global $wpdb;

		// $wpdb->
		$results = $wpdb->get_results("
			SELECT
				post.ID AS post_id,
				post.post_title,

				MAX(IF(usermeta.meta_key = 'first_name', usermeta.meta_value, NULL)) AS profile_first_name,
				MAX(IF(usermeta.meta_key = 'last_name', usermeta.meta_value, NULL)) AS profile_last_name,
				MAX(IF(usermeta.meta_key = 'nickname', usermeta.meta_value, NULL)) AS profile_nickname,
				MAX(IF(usermeta.meta_key = 'function_name', usermeta.meta_value, NULL)) AS profile_function_name,
				MAX(IF(usermeta.meta_key = 'phone_number', usermeta.meta_value, NULL)) AS profile_phone_number,
				MAX(IF(usermeta.meta_key = 'mobile_phone_number', usermeta.meta_value, NULL)) AS profile_mobile_phone_number,

				user.ID AS user_id,
				user.user_login,
				user.user_email
			FROM
				wp_posts AS post
					LEFT JOIN
				wp_users AS user
						ON post.post_author = user.ID
					LEFT JOIN
				wp_usermeta AS usermeta
						ON user.ID = usermeta.user_id
			WHERE
				post_type = 'profile'
					AND
				post_status IN ( 'publish', 'pending', 'draft', 'future' )
			GROUP BY
				post.ID
			;
		");

		return $results;
	}

	/**
	 * Export to CSV
	 */
	public static function maybe_export() {
		if ( empty( $_POST ) || !wp_verify_nonce( filter_input( INPUT_POST, 'eisma_profiles_export_nonce', FILTER_SANITIZE_STRING ), 'eisma_profiles_export' ) )
			return;

		// Set headers for download
		$filename  = __( 'eisma-profiles-export', 'eisma_profiles_exporter' );
		$filename .= '-' . date('Y-m-d_H-i') . '.csv';

		header( 'Content-Type: text/csv;' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		// Results
		$results = self::get_export();

		$data = array();

		$resource = fopen( 'php://output', 'w' );

		// Header
		$header = array( 
			__( 'Post ID', 'eisma_profiles_exporter' ), 
			__( 'First Name', 'eisma_profiles_exporter' ),
			__( 'Last Name', 'eisma_profiles_exporter' ),
			__( 'Nickname', 'eisma_profiles_exporter' ),
			__( 'Function Name', 'eisma_profiles_exporter' ),
			__( 'Phone Number', 'eisma_profiles_exporter' ),
			__( 'Mobile Phone Number', 'eisma_profiles_exporter' ),
			__( 'User ID', 'eisma_profiles_exporter' ),
			__( 'User Login', 'eisma_profiles_exporter' ),
			__( 'User E-mail', 'eisma_profiles_exporter' ),
			__( 'Locations', 'eisma_profiles_exporter' ),
			__( 'Companies', 'eisma_profiles_exporter' ),
			__( 'Departments', 'eisma_profiles_exporter' ),
			__( 'Publications', 'eisma_profiles_exporter' )
		);

		fputcsv( $resource, $header );

		foreach ( $results as $result ) {
			// Locations
			$locations = array();

			$terms = get_the_terms( $result->post_id, 'location' );
			if ( $terms && ! is_wp_error( $terms ) ) {				
				foreach ( $terms as $term ) {
					$locations[] = $term->parent . ',' . $term->name;
				}
			}

			// Companies
			$companies = array();

			$terms = get_the_terms( $result->post_id, 'company' );
			if ( $terms && ! is_wp_error( $terms ) ) {				
				foreach ( $terms as $term ) {
					$companies[] = $term->parent . ',' . $term->name;
				}
			}

			// Departments
			$departments = array();

			$terms = get_the_terms( $result->post_id, 'department' );
			if ( $terms && ! is_wp_error( $terms ) ) {				
				foreach ( $terms as $term ) {
					$departments[] = $term->parent . ',' . $term->name;
				}
			}

			// Publications
			$publications = array();

			$terms = get_the_terms( $result->post_id, 'department' );
			if ( $terms && ! is_wp_error( $terms ) ) {				
				foreach ( $terms as $term ) {
					$publications[] = $term->parent . ',' . $term->name;
				}
			}

			// Row
			$row = array( 
				$result->post_id,
				$result->profile_first_name,
				$result->profile_last_name,
				$result->profile_nickname,
				$result->profile_function_name,
				$result->profile_phone_number,
				$result->profile_mobile_phone_number,
				$result->user_id,
				$result->user_login,
				$result->user_email,
				implode( "\r\n", $locations ),
				implode( "\r\n", $companies ),
				implode( "\r\n", $departments ),
				implode( "\r\n", $publications )
			);

			fputcsv( $resource, $row );
		}

		exit;
	}

	//////////////////////////////////////////////////

	/**
	 * Admin include file
	 * 
	 * @param string $file
	 */
	public static function include_file( $file ) {
		include Pronamic_EismaProfilesExpoter_Plugin::$dirname . '/admin/' . $file;
	}

	//////////////////////////////////////////////////

	/**
	 * Page export
	 */
	function page_export() {
		include 'admin/export.php';
	}
}

Pronamic_EismaProfilesExpoter_Plugin::bootstrap( __FILE__ );
