<?php


namespace ShahariaAzam\WPMailGateway;

class Functions {
	public static function custom_plugin_row_meta( $links, $file ) {
		if ( strpos( $file, WP_MAIL_GATEWAY_PLUGIN_FILE ) !== false ) {
			$new_links = array(
				'configuration' => '<a href="' . admin_url('?page=wp-mail-gateway') .'">Configuration</a>'
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	public static function onActivatingPlugin() {

	}

	public static function onDeactivatingPlugin() {

	}

	public static function onDeletingPlugin() {
		//on delete plugin, cleanup options data
		delete_option(WP_MAIL_GATEWAY_PLUGIN_OPTIONS_KEY);
	}

	public static function registerWhitelistedOptions() {
		register_setting( WP_MAIL_GATEWAY_PLUGIN_OPTIONS_KEY, WP_MAIL_GATEWAY_PLUGIN_OPTIONS_GROUP );
	}

	public static function adminMenuInit() {
		//Custom CSS to fix admin menu
		add_action( 'admin_head', array( Functions::class, 'overrideAdminMenuCSS' ) );

		$menuItems = [
			[
				'page_title'        => "Manage WP Mail Gateway",
				'menu_title'        => "WP Mail Gateway",
				'capabilities'      => 'manage_options',
				'menu_slug'         => "wp-mail-gateway",
				'callback_function' => array( Functions::class, 'adminPageDisplay' ),
				'menu_icon'         => "dashicons-email",
			]
		];
		foreach ( $menuItems as $item ) {
			add_menu_page( $item['page_title'], $item['menu_title'], $item['capabilities'], $item['menu_slug'], $item['callback_function'], $item['menu_icon'] );
		}
	}

	public static function overrideAdminMenuCSS() {

	}

	public static function adminPageDisplay() {
		echo "<div class='wrap wmg'><div class='container-fluid'><div class='wp-mail-gateway-plugin-adminpage' id='wpMailGatewayPluginAdminPage'>";
		$htmlParse = new HTMLParser(file_get_contents(WP_MAIL_GATEWAY_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . "src/admin_template.html"), [
			'mailgun_logo' => plugins_url( 'assets/img/mailgun_logo.png', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ),
			'mailjet_logo' => plugins_url( '/assets/img/mailjet_logo.png', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ),
			'aws_ses_logo' => plugins_url( '/assets/img/amazon_ses_logo.png', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ),
			'mandrill_logo' => plugins_url( '/assets/img/mandrill_logo.png', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ),
			'postmark_logo' => plugins_url( '/assets/img/postmark_logo.png', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ),
			'sendgrid_logo' => plugins_url( '/assets/img/sendgrid_logo.png', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ),
			'sendinblue_logo' => plugins_url( '/assets/img/sendinblue_logo.png', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ),
			'loader_gif' => plugins_url( '/assets/img/loader.gif', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL )
		]);
		echo $htmlParse->output();
		echo "</div></div></div>";
	}

	public static function loadPluginAdminPageStaticAssets() {
		wp_register_style( 'bootstrap.wmg', plugins_url( 'assets/css/bootstrap-wmg.css', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ), false, WP_MAIL_GATEWAY_PLUGIN_VERSION );
		wp_enqueue_style( 'bootstrap.wmg' );

		wp_register_style( 'sweetalert2.min', plugins_url( 'assets/css/sweetalert2.min.css', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ), array( "bootstrap.wmg" ), WP_MAIL_GATEWAY_PLUGIN_VERSION );
		wp_enqueue_style( 'sweetalert2.min' );

		wp_register_style( 'wpg.main', plugins_url( 'assets/css/main.css', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ), array( "bootstrap.wmg" ), WP_MAIL_GATEWAY_PLUGIN_VERSION );
		wp_enqueue_style( 'wpg.main' );

		wp_register_script( 'popperjs', plugins_url( 'assets/js/popper.min.js', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ), array( 'jquery' ), WP_MAIL_GATEWAY_PLUGIN_VERSION, true );
		wp_enqueue_script( "popperjs" );

		wp_register_script( 'bootstrap.min', plugins_url( 'assets/js/bootstrap.min.js', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ), array( 'jquery' ), WP_MAIL_GATEWAY_PLUGIN_VERSION, true );
		wp_enqueue_script( 'bootstrap.min' );

		wp_register_script( 'sweetalert2.min', plugins_url( 'assets/js/sweetalert2.min.js', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ), array( 'bootstrap.min' ), WP_MAIL_GATEWAY_PLUGIN_VERSION, true );
		wp_enqueue_script( 'sweetalert2.min' );

		wp_register_script( 'wpg.main', plugins_url( 'assets/js/main.js', WP_MAIL_GATEWAY_PLUGIN_FILE_PATH_FULL ), array(
			'bootstrap.min'
		), WP_MAIL_GATEWAY_PLUGIN_VERSION, true );
		wp_enqueue_script( 'wpg.main' );
	}

	public static function getProviderConfigsAjax() {
		$configs = self::getOptions();
		if(!empty($configs['gateway_provider'])){
			wp_send_json_success(array("success" => true, 'saved_configs' => $configs['gateway_provider']));
			exit();
		}

		wp_send_json_success( array("success" => false, 'saved_configs' => []) );
		exit();
	}

	public static function saveProviderConfigAjax() {
		$postData = $_POST;
		$configs = json_decode(stripslashes($postData['configs']), true);

		$gatewayProvider = $configs['provider'];
		$finalConfigs = [];
		if($gatewayProvider === "amazon_ses"){
			$finalConfigs['amazon_ses'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'access_key' => $configs[$gatewayProvider . "_access_key"],
				'secret_key' => $configs[$gatewayProvider . "_secret_key"],
				'verify_peer' => $configs[$gatewayProvider . "_verify_peer"],
				'verify_host' => $configs[$gatewayProvider . "_verify_host"],
				'region' => $configs[$gatewayProvider . "_region"],
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}elseif($gatewayProvider === "mailgun"){
			$finalConfigs['mailgun'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'api_key' => $configs[$gatewayProvider . "_api_key"],
				'domain' => $configs[$gatewayProvider . "_domain"],
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}elseif($gatewayProvider === "mailjet"){
			$finalConfigs['mailjet'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'api_key' => $configs[$gatewayProvider . "_api_key"],
				'api_secret' => $configs[$gatewayProvider . "_api_secret"],
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}elseif($gatewayProvider === "mandrill"){
			$finalConfigs['mandrill'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'api_key' => $configs[$gatewayProvider . "_api_key"],
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}elseif($gatewayProvider === "postmark"){
			$finalConfigs['postmark'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'api_token' => $configs[$gatewayProvider . "_api_token"],
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}elseif($gatewayProvider === "sendgrid"){
			$finalConfigs['sendgrid'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'api_key' => $configs[$gatewayProvider . "_api_key"],
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}elseif($gatewayProvider === "sendinblue"){
			$finalConfigs['sendinblue'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'access_key' => $configs[$gatewayProvider . "_access_key"],
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}elseif($gatewayProvider === "smtp"){
			$finalConfigs['smtp'] = array(
				'from_name' => $configs[$gatewayProvider . "_from_name"],
				'from_email' => $configs[$gatewayProvider . "_from_email"],
				'host' => $configs[$gatewayProvider . "_host"],
				'username' => $configs[$gatewayProvider . "_username"],
				'password' => $configs[$gatewayProvider . "_password"],
				'port' => intval($configs[$gatewayProvider . "_port"]),
                'active' => $configs[$gatewayProvider . "_active"]
			);
		}else{
			wp_send_json_success( ['success' => false, 'message' => 'Invalid gateway provider configuration'] );
			exit();
		}

		$existingGatewayConfigs = self::getOptions();

		//Unset rest of the active value
		foreach ($existingGatewayConfigs['gateway_provider'] as $provider => $config){
		    if(isset($config['active']) && $config['active'] === true){
		        unset($config['active']);
                $existingGatewayConfigs['gateway_provider'][$provider] = $config;
            }
        }

        $existingGatewayConfigs['gateway_provider'][$gatewayProvider] = $finalConfigs[$gatewayProvider];
		$updatedOptions = self::updateOptions(['gateway_provider' => $existingGatewayConfigs['gateway_provider']]);

		wp_send_json_success( ['success' => true, 'gateway_provider' => $gatewayProvider, 'configs' => $finalConfigs] );
		exit();
	}

	public static function testProviderConfigSendMailAjax(){
	    $postData = $_POST;
        $configs = json_decode(stripslashes($postData['configs']), true);
        $result = wp_mail($configs['to'], $configs['subject'], $configs['content']);
	    wp_send_json_success(['success' => true, 'mail_sent' => $result, 'message' => "Mail has been sent", "config" => $configs]);
	    exit();
    }

	/**
	 * @param array $options
	 *
	 * @return array|mixed
	 */
	public static function updateOptions( array $options = [] ) {
		$existingOptions = self::getOptions();
		foreach ($options as $item => $value){
			$existingOptions[$item] = $value;
		}

		update_option(WP_MAIL_GATEWAY_PLUGIN_OPTIONS_KEY, serialize($existingOptions));
		return $existingOptions;
	}

	public static function getOptions( $key = null ) {
		$options = get_option( WP_MAIL_GATEWAY_PLUGIN_OPTIONS_KEY );
		if(!empty($options)){
			return unserialize($options);
		}

		return [];
	}

	/**
	 * @return |null
	 */
	public static function getActiveProvider(){
		$options = self::getOptions();
		if(!isset($options['gateway_provider'])){
			return null;
		}

		foreach ($options['gateway_provider'] as $provider => $config){
			if(isset($config['active']) && $config['active'] ===  true){
				return [$provider => $config];
			}
		}

		return null;
	}
}