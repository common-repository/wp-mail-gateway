<?php
/*
Plugin Name:  WP Mail Gateway
Plugin URI:   https://developer.wordpress.org/plugins/wp-mail-gateway/
Description:  Send mail via multiple email gateway provider from your Wordpress. Supports various mail provider. i.e: Mandrill, MailGun, SMTP, MailJet, Amazon SES, etc..
Version:      1.8
Author:       Shaharia Azam <mail@shaharia.com>
Author URI:   http://www.shaharia.com?utm_source=wp_plugin_wp-mail-gateway_v1_8&utm_campaign=wp-mail-gateway&utm_term=author_uri
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wp-mail-gateway
*/

if( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

require "vendor/autoload.php";

define( "WP_MAIL_GATEWAY_PLUGIN_ROOT_DIR", __DIR__ );
define( "WP_MAIL_GATEWAY_PLUGIN_VERSION", 1.8 );
define( "WP_MAIL_GATEWAY_PLUGIN_FILE", basename(__FILE__) );
define( "WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL", __FILE__ );
define( "WP_MAIL_GATEWAY_PLUGIN_SLUG", basename(__DIR__) );
define( "WP_MAIL_GATEWAY_PLUGIN_OPTIONS_KEY", "wp_mail_gateway_options" );
define( "WP_MAIL_GATEWAY_PLUGIN_OPTIONS_GROUP", "wp_mail_gateway_options" );

$bootstrap = new \ShahariaAzam\WPMailGateway\Bootstrap();
$bootstrap->load();


//Override mail function
if ( ! function_exists( 'wp_mail' )  && !empty(\ShahariaAzam\WPMailGateway\Functions::getActiveProvider()) ) {
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		$atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

		$mailData = [
			"text" => null,
			"html" => null
		];

		if(isset($atts['to'])){
			$mailData['to'] = $atts['to'];
		}

		if(isset($atts['subject'])){
			$mailData['subject'] = $atts['subject'];
		}

		if(isset($atts['message'])){
			$mailData['html'] = $atts['message'];
		}

		//Provider build
		$provider = null;
		$activeProvider = \ShahariaAzam\WPMailGateway\Functions::getActiveProvider();
		$configs = $activeProvider[key($activeProvider)];

		if(key($activeProvider) === "amazon_ses"){
			$provider = new \Omnimail\AmazonSES($configs['access_key'], $configs['secret_key'], $configs['region'], $configs['verify_peer'], $configs['verify_peer']);
		}else if(key($activeProvider) === "mailgun"){
			$provider = new \Omnimail\Mailgun($configs['api_key'], $configs['domain']);
		}else if(key($activeProvider) === "mailjet"){
			$provider = new \Omnimail\Mailjet($configs['api_key'], $configs['api_secret']);
		}else if(key($activeProvider) === "mandrill"){
			$provider = new \Omnimail\Mandrill($configs['api_key']);
		}else if(key($activeProvider) === "postmark"){
			$provider = new \Omnimail\Postmark($configs['api_token']);
		}else if(key($activeProvider) === "sendgrid"){
			$provider = new \Omnimail\Sendgrid($configs['api_key']);
		}else if(key($activeProvider) === "sendinblue"){
			$provider = new \Omnimail\SendinBlue($configs['access_key']);
		}else if(key($activeProvider) === "smtp"){
			$provider = new \Omnimail\SMTP($configs['host'], $configs['username'], $configs['password'], ['port' => !empty($configs['port']) ? $configs['port'] : 587]);
		}else{
			return false;
		}
		// End of provider config

		$email = new \Omnimail\Email();
		$email->setFrom($configs['from_email'], $configs['from_name']);
		$email->addTo($mailData['to']);
		$email->setSubject($mailData['subject']);
		$email->setTextBody($mailData['text']);
		$email->setHtmlBody($mailData['html']);

		// Add attachment
		foreach ($attachments as $attachment){
			$at = new \Omnimail\Attachment();
			$at->setPath($attachment);
			$email->addAttachment($at);
		}

		try {
			$provider->send( $email );
			return  true;
		} catch ( \Omnimail\Exception\Exception $e ) {
			return false;
		}
	}
}
