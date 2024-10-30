<?php
/**
 * LINE for WP
 *
 * @version     1.0.1
 * @package 	Post send to LINE
 * @author 		ArtisanWorkshop
 */
use \ArtisanWorkshop\WooCommerce\PluginFramework\v2_0_8 as Framework;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LineWP_Post_Send
{
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
    public function __construct(){
        add_action('publish_post', array($this, 'send_to_line'), 1, 99);
        add_action('admin_notices', array($this, 'error_send_to_line') );
        add_action('admin_notices', array($this, 'success_send_to_line') );
        add_filter('line_post_message', array($this, 'line_wp_change_message'), 2, 10);
        $this->jp4wc_plugin = new Framework\JP4WC_Plugin();
    }

    /**
     * Send a post to a LINE message
     *
     * @param int Post ID
     * @param object post data
     */
    function send_to_line($post_ID, $post){
        // Ignore if not logged in
        if (!is_user_logged_in()) return;
        // Ignore if the value set by nonce is not received by POST
        if (!isset($_POST['line-wp-nonce-name_post']) || !$_POST['line-wp-nonce-name_post']) return;
        // If there is a problem with the check result of the value set by nonce
        if (!check_admin_referer('line-wp-nonce-action_post', 'line-wp-nonce-name_post')) return;
        // Ignore if the LINE message send checkbox is unchecked
        if ($_POST['line-wp-send-checkbox'] != 'ON') return;

        // Get ChannelAccessToken from OPTIONS table
        $channel_access_token = $this->jp4wc_plugin->decrypt(get_option( LINE_WP_PLUGIN_PREFIX.'channel_access_token'), LINE_WP_ENCRYPT_PASSWORD);
        // Get ChannelSecret from OPTIONS table
        $channel_secret = $this->jp4wc_plugin->decrypt(get_option( LINE_WP_PLUGIN_PREFIX.'channel_secret'), LINE_WP_ENCRYPT_PASSWORD);

        if (strlen($channel_access_token) > 0 && strlen($channel_secret) > 0) {
            $title = sanitize_text_field($post->post_title);
            $link = get_permalink($post_ID);
            $excerpt = $post->post_excerpt;
            if($excerpt){
                $content = $excerpt;
            }else{
                $body = preg_replace("/( |　|\n|\r)/", "", strip_tags(sanitize_text_field($post->post_content)));
                $content = mb_substr($body, 0, 30);
                if ($content != $body) {
                    $content .= "…";
                }
            }
            $message = apply_filters('line_post_message', __('Post Title:', 'line-wp') . $title . "\r\n" . $content, $post_ID ). "\r\n" . $link;
            self::require_line_sdk();
            // Send to LINE
            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
            // Send Image
            if(has_post_thumbnail( $post_ID )){
                $originalContentUrl = get_the_post_thumbnail_url( $post_ID, 'full' );
                $previewImageUrl = get_the_post_thumbnail_url( $post_ID );
                $imageMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalContentUrl, $previewImageUrl);
                $response_image = $bot->broadcast($imageMessageBuilder);
                if ($response_image->getHTTPStatus() !== 200) {
                    set_transient('line-wp-error-send-to-line-image', __( 'Failed to send Image to LINE.', 'line-wp' ) . $response_image->getRawBody(), 10);
                }
            }
            // Send Text Message
            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
            $response = $bot->broadcast($textMessageBuilder);

            if ($response->getHTTPStatus() !== 200) {
                set_transient('line-wp-error-send-to-line', __( 'Failed to send to LINE.', 'line-wp' ) . $response->getRawBody(), 10);
            }
        }
    }

    /**
     *
     * @param int Post ID
     * @param string Title
     * @param string Content
     * @return string
     */
    function line_wp_change_message( $content, $post_ID ){
        if(get_post_type($post_ID) == 'post'){
            // Sanitize input data
            $send_check = sanitize_text_field( $_POST['line-wp-send-checkbox'] );
            $send_text = wp_strip_all_tags( $_POST['line-wp-send-text'] );

            // Update metadata
            if( $send_check == 'ON' ){
                update_post_meta( $post_ID, '_line_wp_send_text', $send_text );
                update_post_meta( $post_ID, '_line_wp_last_send_date', date_i18n('Y-m-d') );
                if(empty($send_text)){
                    return $content;
                }else{
                    return $send_text;
                }
            }
        }
        return $content;
    }

    /**
     * Message display when LINE transmission is successful when posting (publishing)
     */
    function success_send_to_line() {
        if (false !== ($success_send_to_line = get_transient('line-wp-success-send-to-line'))) {
            echo self::getNotice($success_send_to_line, 'success' );
        }
    }

    /**
     * Message display when LINE transmission fails when posting (publishing)
     */
    function error_send_to_line() {
        if (false !== ($error_send_to_line = get_transient('line-wp-error-send-to-line'))) {
            echo self::getNotice($error_send_to_line, 'error');
        }
    }

    /**
     * Generate / get notification tags
     * @param string Message to notify
     * @param string type (error/warning/success/info)
     * @return string html code
     */
    static function getNotice($message, $type) {
        return
            '<div class="notice notice-' . $type . ' is-dismissible">' .
            '<p><strong>' . esc_html($message) . '</strong></p>' .
            '<button type="button" class="notice-dismiss">' .
            '<span class="screen-reader-text">' . __( 'Dismiss this notice.', 'line-wp' ) . '</span>' .
            '</button>' .
            '</div>';
    }

    /**
     * Loading the LINE SDK
     */
    static function require_line_sdk(){
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/Response.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/HTTPClient/Curl.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/Constant/MessageType.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/MessageBuilder.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/Constant/Meta.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/HTTPClient.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/HTTPClient/CurlHTTPClient.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/MessageBuilder/TextMessageBuilder.php');
        require_once( LINE_WP_ABSPATH.'packages/LINE/LINEBot/MessageBuilder/ImageMessageBuilder.php');
    }
}

new LineWP_Post_Send();