<?php
/**
 * LINE for WP
 *
 * @version     1.0.0
 * @package 	Admin Screen
 * @author 		ArtisanWorkshop
 */
use \ArtisanWorkshop\WooCommerce\PluginFramework\v2_0_8 as Framework;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LineWP_Admin_Screen {
	/**
	 * Error messages.
	 *
	 * @var array
	 */
	public $errors = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	public $messages = array();

    /**
     * Japanized for WooCommerce Framework.
     *
     * @var object
     */
	public $jp4wc_plugin;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'line_wp_admin_menu' ) ,50 );
		add_action( 'admin_init', array( $this, 'line_wp_setting_init') );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        // Show a checkbox on the posting screen
        add_action( 'add_meta_boxes', array( $this, 'add_send_to_line_checkbox'), 10, 2 );
//        add_action( 'save_post', 'line_wp_save_meta_box_data' );
		$this->jp4wc_plugin = new Framework\JP4WC_Plugin();
	}

	/**
	 * Add Admin SubMenu at Option
	 */
	public function line_wp_admin_menu() {
        add_options_page(
            __( 'LINE Setting', 'line-wp' ),
            __( 'LINE Setting', 'line-wp' ),
            'manage_options',
            'line-settings',
            array( $this, 'display_line_settings' )
        );
    }

    /**
     * Display of LINE settings.
     */
    function display_line_settings(){
        $this->jp4wc_plugin->show_messages( $this );
        $title = __( 'LINE settings', 'line-wp' );
	    // Output HTML
        echo <<< EOM
<div class="wrap">
<h2>{$title}</h2>
	<div class="line-wp-settings metabox-holder">
    <form id="line-wp-setting-form" method="post" action="" enctype="multipart/form-data">
        <div id="main-sortables" class="meta-box-sortables ui-sortable">
EOM;
        //Display Setting Screen
        settings_fields( 'line_wp_setting_options' );
        $this->jp4wc_plugin->do_settings_sections( 'line_wp_setting_options' );
        echo '            <p class="submit">';
        submit_button( '', 'primary', 'save_line_wp_setting_options', false );
        echo '            </p>';
        echo <<< EOM
        </div>
    </form>
    </div>
    <div class="clear"></div>
    <script type="text/javascript">
    //<![CDATA[
    jQuery(document).ready( function ($) {
        // close postboxes that should be closed
        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        // postboxes setup
        postboxes.add_postbox_toggles('line_wp_setting_options');
    });
    //]]>
    </script>
</div>

EOM;
    }

    function line_wp_setting_init(){
        register_setting(
			'line_wp_setting_options',
			'line_wp_setting_options_name',
			array( $this, 'validate_options' )
		);

        // LINE Messenger API Settings
        add_settings_section(
            'line_wp_general', __( 'LINE Messenger API settings', 'line-wp' ),
            '',
            'line_wp_setting_options'
        );
        add_settings_field(
            'line_wp_channel_secret',
            __( 'Channel Secret', 'line-wp' ),
            array( $this, 'line_wp_channel_secret' ),
            'line_wp_setting_options',
            'line_wp_general'
        );
        add_settings_field(
            'line_wp_channel_access_token',
            __( 'Channel Access Token', 'line-wp' ),
            array( $this, 'line_wp_channel_access_token' ),
            'line_wp_setting_options',
            'line_wp_general'
        );

        if( isset( $_POST['_wpnonce']) and isset($_GET['page']) and $_GET['page'] == 'line-settings' ){
            //Save general setting
            $add_methods = array(
                'channel_secret',
                'channel_access_token',
            );
            $this->jp4wc_plugin->jp4wc_save_methods( $add_methods, LINE_WP_PLUGIN_PREFIX, LINE_WP_ENCRYPT_PASSWORD );
            $this->messages[] = __( 'Your settings have been saved.', 'line-wp' );
        }
    }

    /**
     * Channel secret option.
     *
     * @return mixed
     */
	public function line_wp_channel_secret() {
		$title = __( 'Channel Secret', 'line-wp' );
		$description = sprintf(__( 'You must get and input %s here. Please check it <a href="https://developers.line.biz/ja/" target="_blank">LINE developer site</a>.', 'line-wp' ), $title);
		$this->jp4wc_plugin->jp4wc_input_text('channel_secret', $description, 60, '', LINE_WP_PLUGIN_PREFIX, '',LINE_WP_ENCRYPT_PASSWORD);
	}

    /**
     * Channel secret option.
     *
     * @return mixed
     */
    public function line_wp_channel_access_token() {
        $title = __( 'Channel Access Token', 'line-wp' );
        $description = sprintf(__( 'You must get and input %s here. Please check it <a href="https://developers.line.biz/ja/" target="_blank">LINE developer site</a>.', 'line-wp' ), $title);
        $this->jp4wc_plugin->jp4wc_input_text('channel_access_token', $description, 60, '', LINE_WP_PLUGIN_PREFIX,'',LINE_WP_ENCRYPT_PASSWORD);
    }

    /**
     * Validate options.
     *
     * @param array input
     * @return mixed
     */
    public function validate_options( $input ) {
        // Create our array for storing the validated options
        $output = array();

        // Loop through each of the incoming options
        foreach( $input as $key => $value ) {
            if( $key == 'channel_access_token' ){
                $regexp_channel_access_token = '/^[a-zA-Z0-9+\/=]{100,}$/';
                if (!preg_match($regexp_channel_access_token, $value)) {
                    return $this->errors[] = __( 'The Channel Access Token is incorrect.', 'line-wp' );
                }
            }elseif( $key == 'channel_secret' ){
                $regexp_channel_secret = '/^[a-z0-9]{30,}$/';
                if (!preg_match($regexp_channel_secret, $value)) {
                    return $this->errors[] = __( 'The Channel Secret is incorrect.', 'line-wp' );
                }
            }
            // Strip all HTML and PHP tags and properly handle quoted strings
            $output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
        }
        // Return the array processing any additional functions filtered by this action
        return apply_filters( 'line_wp_validate_input', $output, $input );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @global string page
     */
    public function admin_enqueue_scripts( $hook ) {
        if ( !isset($_GET['page']) && 'line-settings' != $_GET['page'] ) {
            return;
        }
        wp_enqueue_script( 'line-wp-admin-script',  LINE_WP_URL_PATH.'assets/js/admin-settings.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-button', 'jquery-ui-slider' ), LINE_WP_VERSION );
        wp_enqueue_script( 'postbox' );
        wp_register_style( 'line-wp-admin', LINE_WP_URL_PATH.'assets/css/admin.css', false, LINE_WP_VERSION );
        wp_enqueue_style( 'line-wp-admin' );
    }

    function add_send_to_line_checkbox(){
        add_meta_box(
            'line-wp-send-checkbox',
            __( 'Send LINE message', 'line-wp' ),
            array($this, 'show_send_to_line_checkbox'),
            'post',
            'advanced',
            'high'
        );
    }

    /**
     * Show checkbox to send message to LINE
     *
     * @param object WP_Post
     */
    function show_send_to_line_checkbox( $post ) {
        $nonce_field = wp_nonce_field(
            'line-wp-nonce-action_post',
            'line-wp-nonce-name_post',
            true,
            false
        );
        $late_send_line_date = get_post_meta( $post->ID, '_line_wp_last_send_date', true );
        if( $late_send_line_date ){
            $display_last_date = '<span class="line-wp-send-last-date">['.__('Last posted to LINE: ', 'line-wp'). $late_send_line_date.']</span>';
        }else{
            $display_last_date = '';
        }
        $line_message = get_post_meta( $post->ID, '_line_wp_send_text', true );
        if( $line_message == false ) $line_message = '';

        echo
            '<p>' .
            $nonce_field .
            '<label>' . "\n" .
            '<input type="checkbox" name="line-wp-send-checkbox" value="ON">' .
            __( 'Send a message to LINE', 'line-wp' ) . '<br/>' . "\n" . $display_last_date .
            '</label>' . "\n" .
            '<label>' . "\n" . '<hr/>' .
            __( 'Send text to LINE', 'line-wp' ) . '<br/>' . "\n" .
            '<textarea name="line-wp-send-text" cols="40" rows="5">' . wp_strip_all_tags($line_message) . '</textarea>' .
            '</label>' . "\n" .
            '</p>';
    }
    /**
     * Save custom data when the post is saved
     *
     * @param int Post ID
     */
    function line_wp_save_meta_box_data( $post_id ){
        // Check if nonce is set
//        if ( ! isset( $_POST['line-wp-nonce-name_post'] ) ) return;
        // Verify if nonce is correct
//        if ( ! wp_verify_nonce( $_POST['line-wp-nonce-name_post'], 'line_wp_save_meta_box_data' ) ) return;
        // Do nothing for auto-save
//        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        // Save only if you have edit permission
//        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        // Check if the data is set
//        if ( ! isset( $_POST['line-wp-send-checkbox'] ) ) return;

        // Sanitize input data
        $send_check = sanitize_text_field( $_POST['line-wp-send-checkbox'] );
        $send_text = sanitize_text_field( $_POST['line-wp-send-text'] );

        // Update metadata
        if( $send_check == 'ON' ){
            update_post_meta( $post_id, '_line_wp_send_text', $send_text );
            update_post_meta( $post_id, '_line_wp_last_send_date', date_i18n('Y-m-d') );
        }
    }
}

new LineWP_Admin_Screen();