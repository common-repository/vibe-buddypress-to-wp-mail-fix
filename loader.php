<?php
/*
Plugin Name: Vibe BuddyPress to WP Mail fix
Plugin URI: https://www.Vibethemes.com
Description: Send BuddyPress emails via WP Mail
Version: 1.2
Author: VibeThemes
Author URI: https://vibethemes.com
Tags: buddypress,mail
Requires at least: 6.0
Tested up to: 6.5.3
License: GPLv3
Text Domain: vibe-bp-wpmail
Domain Path: /languages/
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vibe_BP_WPMail{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_BP_WPMail();
        return self::$instance;
    }

	private function __construct(){
		add_filter( 'bp_email_use_wp_mail', '__return_true' );
		remove_filter( 'wp_mail_content_type', array($this,'set_html_content_type' ));
		add_filter( 'wp_mail_content_type', array($this,'set_html_content_type' ));
		add_filter( 'bp_email_get_content_plaintext', array($this,'get_bp_email_content_plaintext'), 10, 4 );

		add_action( 'bp_register_admin_settings', array($this,'admin_settings'),99);
		

		add_filter('wp_mail_from',array($this,'noreply_from_email'));

		add_filter('wp_mail_from_name',array($this,'noreply_from_name'));

	}


	
	function admin_settings(){
		
		add_settings_section( 'vibebp_bp_mail_section', __( 'Vibe BuddyPress WPMail Settings', 'vibe-bp-wpmail' ), array($this,'callback_mail_section'), 'buddypress' );
		// Hide toolbar for logged out users setting.
		add_settings_field( 'noreply_from_email', __( 'No Reply mail', 'vibe-bp-wpmail' ), array($this,'callback_noreply_from_email'), 'buddypress', 'vibebp_bp_mail_section' );

		register_setting( 'buddypress', 'noreply_from_email', array('type'=>'string','sanitize_callback'=>'sanitize_email' ) );	

		// Hide toolbar for logged out users setting.
		add_settings_field( 'noreply_from_name', __( 'NoReply Name', 'vibe-bp-wpmail' ), array($this,'callback_noreply_from_name'), 'buddypress', 'vibebp_bp_mail_section' );

		register_setting( 'buddypress', 'noreply_from_name', array('type'=>'string','sanitize_callback'=>'sanitize_text_field' ));	
		
	}
	function set_html_content_type() {
	    return 'text/html';
	}

	function callback_mail_section(){
		
	}
	function callback_noreply_from_email() {
	?>

		<input  name="noreply_from_email" type="text" value="<?php esc_html_e($this->noreply_from_email()); ?>" />
		<label ><?php esc_html_e( 'NoReply From Email', 'vibe-bp-wpmail' ); ?></label>
	<?php
	}

	function noreply_from_email( $default = 'noreply@noreply.com' ) {
		return bp_get_option( 'noreply_from_email', $default );
	}

	function callback_noreply_from_name() {
	?>

		<input  name="noreply_from_name" type="text" value="<?php echo esc_html_e($this->noreply_from_name()); ?>" />
		<label ><?php _e( 'NoReply From Name', 'vibe-bp-wpmail' ); ?></label>
	<?php
	}

	function noreply_from_name( $default = 'noreply' ) {
		return bp_get_option( 'noreply_from_name', $default );
	}


	function get_bp_email_content_plaintext( $content = '', $property = 'content_plaintext', $transform = 'replace-tokens', $bp_email ) {
	    if ( ! did_action( 'bp_send_email' ) ) {
	        return $content;
	    }
	    return $bp_email->get_template( 'add-content' );
	}

}

Vibe_BP_WPMail::init();



add_action('plugins_loaded','vibe_buddypress_wp_mail_translations');
function vibe_buddypress_wp_mail_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'vibe-bp-wpmail');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'vibe-bp-wpmail', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'vibe-bp-wpmail', $mofile_global );
    } else {
        load_textdomain( 'vibe-bp-wpmail', $mofile_local );
    }  
}
