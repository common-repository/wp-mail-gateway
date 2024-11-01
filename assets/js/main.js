(function () {
    var configFieldsDiv = jQuery("#config_fields");

    const GATEWAY_PROVIDERS = ["amazon_ses", "mailgun", "mailjet", "mandrill", "postmark", "sendgrid", "sendinblue", "smtp"];
    const GATEWAY_PROVIDERS_NAME = {"amazon_ses": "Amazon SES", "mailgun": "Mailgun", "mailjet": "MailJet", "mandrill": "Mandrill", "postmark": "Postmark", "sendgrid": "Sendgrid", "sendinblue": "SendinBlue", "smtp": "SMTP"};
    const CONFIG_FORM_ID = "wmg_provider_config_form";

    /**
     * Get Active config form
     *
     * @returns {*}
     */
    function getActiveConfigProviderForm() {
        var active_provider = null;
        GATEWAY_PROVIDERS.forEach(function (provider) {
            if (jQuery("#config_fields_" + provider).css('display') === "block") {
                active_provider = provider;
            }
        });

        return active_provider;
    }

    function showProviderConfiguration(provider) {
        jQuery("#config_fields_" + provider).show();
        jQuery("#instructions_for_" + provider).show();
        jQuery("#emailGatewayProvider").val(provider);
    }

    /**
     * Get provider specific field's value
     *
     * @param provider
     * @param key
     * @returns {jQuery}
     */
    function getFormFieldValueByProvider(provider, key) {
        var selector = jQuery("#" + provider + "_" + key);
        if(key === "active"){
            return selector.prop('checked');
        }

        return selector.val();
    }

    //Hide all form specific fields at init
    jQuery("document").ready(function () {
        GATEWAY_PROVIDERS.forEach(function (provider) {
            jQuery("#config_fields_" + provider).hide();
            jQuery("#instructions_for_" + provider).hide();
        });
    });

    //On select gateway provider, display that form
    jQuery("#wmg_provider_config_form select[name=gateway_provider]").on("change", function (e) {
        var selectedProvider = jQuery(this).val();
        jQuery("#config_fields_" + selectedProvider).show();
        jQuery("#instructions_for_" + selectedProvider).show();

        GATEWAY_PROVIDERS.forEach(function (provider) {
            if (provider !== selectedProvider) {
                jQuery("#config_fields_" + provider).hide();
                jQuery("#instructions_for_" + provider).hide();
            }
        });
    });

    jQuery("#wmg_provider_config_form").on("submit", function (e) {
        e.preventDefault();

        jQuery(".wmg-ajax-loader-save-config").show();

        var configured_provider = getActiveConfigProviderForm();

        var configs = {};

        if (configured_provider === "amazon_ses") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("amazon_ses", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("amazon_ses", "from_email");
            configs[configs['provider'] + '_access_key'] = getFormFieldValueByProvider("amazon_ses", "access_key");
            configs[configs['provider'] + '_secret_key'] = getFormFieldValueByProvider("amazon_ses", "secret_key");
            configs[configs['provider'] + '_verify_peer'] = getFormFieldValueByProvider("amazon_ses", "verify_peer");
            configs[configs['provider'] + '_verify_host'] = getFormFieldValueByProvider("amazon_ses", "verify_host");
            configs[configs['provider'] + '_region'] = getFormFieldValueByProvider("amazon_ses", "region");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("amazon_ses", "active");
        }else if (configured_provider === "mailgun") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("mailgun", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("mailgun", "from_email");
            configs[configs['provider'] + '_api_key'] = getFormFieldValueByProvider("mailgun", "api_key");
            configs[configs['provider'] + '_domain'] = getFormFieldValueByProvider("mailgun", "domain");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("mailgun", "active");
        }else if (configured_provider === "mailjet") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("mailjet", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("mailjet", "from_email");
            configs[configs['provider'] + '_api_key'] = getFormFieldValueByProvider("mailjet", "api_key");
            configs[configs['provider'] + '_api_secret'] = getFormFieldValueByProvider("mailjet", "api_secret");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("mailjet", "active");
        }else if (configured_provider === "mandrill") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("mandrill", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("mandrill", "from_email");
            configs[configs['provider'] + '_api_key'] = getFormFieldValueByProvider("mandrill", "api_key");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("mandrill", "active");
        }else if (configured_provider === "postmark") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("postmark", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("postmark", "from_email");
            configs[configs['provider'] + '_api_token'] = getFormFieldValueByProvider("postmark", "api_token");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("postmark", "active");
        }else if (configured_provider === "sendgrid") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("sendgrid", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("sendgrid", "from_email");
            configs[configs['provider'] + '_api_key'] = getFormFieldValueByProvider("sendgrid", "api_key");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("sendgrid", "active");
        }else if (configured_provider === "sendinblue") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("sendinblue", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("sendinblue", "from_email");
            configs[configs['provider'] + '_access_key'] = getFormFieldValueByProvider("sendinblue", "access_key");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("sendinblue", "active");
        }else if (configured_provider === "smtp") {
            configs['provider'] = configured_provider;
            configs[configs['provider'] + '_from_name'] = getFormFieldValueByProvider("smtp", "from_name");
            configs[configs['provider'] + '_from_email'] = getFormFieldValueByProvider("smtp", "from_email");
            configs[configs['provider'] + '_host'] = getFormFieldValueByProvider("smtp", "host");
            configs[configs['provider'] + '_username'] = getFormFieldValueByProvider("smtp", "username");
            configs[configs['provider'] + '_password'] = getFormFieldValueByProvider("smtp", "password");
            configs[configs['provider'] + '_port'] = getFormFieldValueByProvider("smtp", "port");
            configs[configs['provider'] + '_active'] = getFormFieldValueByProvider("smtp", "active");
        }

        //Post config data to backend via Ajax
        var data = {
            'action': 'wmg_save_provider_config',
            'configs': JSON.stringify(configs)
        };

        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajaxurl, data, function (response) {
            jQuery(".wmg-ajax-loader-save-config").hide();

            //console.log(response);
            if(response.hasOwnProperty("data") && response.data.hasOwnProperty("success") && response.data.success === true){
                Swal.fire(
                    'Great!',
                    'Configuration has been updated!',
                    'success'
                )
            }
        });

        //console.log(configs);
    });

    jQuery("#wmg_test_provider_configuration").on("submit", function (e) {
        e.preventDefault();

        jQuery(".wmg-ajax-loader-test-config").show();

        var formData = {};
        formData.to = jQuery("#test_configuration_to").val();
        formData.subject = jQuery("#test_configuration_subject").val();
        formData.content = jQuery("#test_configuration_content").val();

        var data = {
            'action': 'wmg_test_provider_config_send_mail',
            'configs': JSON.stringify(formData)
        };

        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajaxurl, data, function (response) {
            jQuery(".wmg-ajax-loader-test-config").hide();
            //console.log(response);
            if(response.hasOwnProperty("data") && response.data.hasOwnProperty("success") && response.data.success === true){
                if(response.data.mail_sent === true){
                    Swal.fire(
                        'Great!',
                        'Email has been sent successfully. So your configurations are correct!',
                        'success'
                    )
                }else{
                    Swal.fire(
                        'Oops!',
                        'Email couldn\'t be sent. Please check your configuration again.',
                        'error'
                    )
                }
            }
        });
    });

    function getSavedConfigs() {
        var data = {
            'action': 'wmg_get_saved_configs'
        };

        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajaxurl, data, function (response) {
            //console.log(response);
            if (response.hasOwnProperty("data") && response.data.hasOwnProperty("saved_configs")) {

                GATEWAY_PROVIDERS.forEach(function (provider) {
                    if (response.data.saved_configs.hasOwnProperty(provider)) {
                        jQuery.each(response.data.saved_configs[provider], function (key, val) {
                            jQuery("#" + provider + "_" + key).val(val);

                            //Is active === true?
                            if(key === "active" && val === true){
                                jQuery("#" + provider + "_" + key).attr("checked", "checked");
                                jQuery("#wmg_provider_configuration h5.card-title").append("<span> [ Currently Active: " + GATEWAY_PROVIDERS_NAME[provider] + " ]</span>");
                                showProviderConfiguration(provider);
                            }
                        });
                    }
                })
            } else {
                //console.log("No saved configs found!");
            }
        });
    }

    getSavedConfigs();
})();