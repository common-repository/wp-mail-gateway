<?php


namespace ShahariaAzam\WPMailGateway;


use Inchev\WPPlugin\EasyCookieConsent\Administration\Components;
use Inchev\WPPlugin\EasyCookieConsent\AdminMenu;

class Bootstrap {
	public function __construct() {

	}

	public function load(){
		if(is_admin()/* && current_user_can('administrator')*/){
			//Whitelist WP options keys
			add_action( 'admin_init', array( Functions::class, 'registerWhitelistedOptions' ) );

			add_filter( 'plugin_row_meta', array( Functions::class, 'custom_plugin_row_meta' ), 10, 2 );

			//Plugin hooks
			register_activation_hook( WP_MAIL_GATEWAY_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . WP_MAIL_GATEWAY_PLUGIN_FILE, array(
				Functions::class,
				'onActivatingPlugin'
			) );

			register_deactivation_hook( WP_MAIL_GATEWAY_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . WP_MAIL_GATEWAY_PLUGIN_FILE, array(
				Functions::class,
				'onDeactivatingPlugin'
			) );
			register_uninstall_hook( WP_MAIL_GATEWAY_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . WP_MAIL_GATEWAY_PLUGIN_FILE, array(
				Functions::class,
				'onDeletingPlugin'
			) );
			// End of plugin hooks

			// Build Plugin Admin Menu
			add_action( 'admin_menu', array( Functions::class, 'adminMenuInit' ) );

			//Admin load custom scripts
			if ( isset( $_GET['page'] ) && $_GET['page'] === WP_MAIL_GATEWAY_PLUGIN_SLUG ) {
				add_action( 'admin_enqueue_scripts', array( Functions::class, 'loadPluginAdminPageStaticAssets' ) );
			}

			add_action( 'wp_ajax_wmg_get_saved_configs', array( Functions::class, "getProviderConfigsAjax" ) );
			add_action( 'wp_ajax_wmg_save_provider_config', array( Functions::class, "saveProviderConfigAjax" ) );
			add_action( 'wp_ajax_wmg_test_provider_config_send_mail', array( Functions::class, "testProviderConfigSendMailAjax" ) );
		}
	}
}