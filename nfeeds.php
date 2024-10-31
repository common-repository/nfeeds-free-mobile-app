<?php
/**
 * @package N-Feeds
 * @version 1.0
 */
/*
Plugin Name: N-Feeds Free Mobile App
Plugin URI: http://wordpress.org/plugins/n-feeds-free-mobile-app/
Description: The fastest way to get a free mobile app for you Wordpress site
Author: Tooleap
Version: 1.0
Author URI: http://nfeeds.tooleap.com
*/

if ( is_admin() ) { // admin actions
    add_action('admin_menu', 'nfeeds_register_my_custom_submenu_page');
    add_action('wp_ajax_nfeeds_save', 'nfeeds_save_ajax');
}

function nfeeds_save_ajax() {

    check_ajax_referer('mobile-app-generator', 'security');

    $data = array();
    if (isset($_POST["generated_url"])) {
        $data["generated_url"] = esc_url_raw(sanitize_text_field($_POST["generated_url"]));
    }
    
    if(!is_array(get_option('nfeeds_data'))) {
        $options = array();
    } else {
        $options = get_option('nfeeds_data');
    }

    if(!empty($data)) {
        $diff = array_diff($options, $data);
        $diff2 = array_diff($data, $options);
        $diff = array_merge($diff, $diff2);
    } else {
        $diff = array();
    }

    if(!empty($diff)) {	
        if(update_option('nfeeds_data', $data)) {
            die('1');
        } else {
            die('0');
        }
    } else {
        die('1');	
    }
}

function nfeeds_register_my_custom_submenu_page() {
    add_submenu_page(
        'options-general.php',
        'N-Feeds Mobile App Generator',
        'Mobile App Generator',
        'manage_options',
        'mobile-app-generator',
        'admin_page_init' );
}
 
function admin_page_init(){
    
    $nfeeds_data = get_option('nfeeds_data');
    
    ?>

    <div class="wrap">
    
        <div class="section-header">
            <h1 class="section-title text-center wow fadeInDown">Start Now!</h1>
            <p class="text-center wow fadeInDown">Generate a mobile app for your site in an instant!</p>
        </div>

        <div id="current-url-card" class="card" style="margin: 20px 0px; display: none;">
            <h2>You app is ready!</h2>
            <a href="<?php echo esc_url($nfeeds_data['generated_url']); ?>" target="_blank"><span id="current-url"><?php echo esc_url($nfeeds_data['generated_url']); ?></span></a>
            <br/>
            <br/>
            Share this link on your website, and let your readers follow you on their mobile phone.
        </div>
        
        <div class="row wow fadeInUp">
            
            <div class="col-lg-12">

                <div id="generator-message" class="alert alert-danger" style="display: none;" role="alert">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <span class="sr-only">Error:</span>
                    <span class="msg"></span>
                </div>
                
                <div id="saved"></div>
                
                <div id="go-mobile-btn" style=""><input type="submit" name="submit" id="submit" class="button button-primary" value="Generate My App" data-title="Generate My App"></div>
            </div>

        </div>
        
        <p>Please <a href="https://nfeeds.tooleap.com">visit our website</a> for more options</p>
        
    </div>

    <script type="text/javascript">
        
        var currentGeneratedUrl = "<?php echo esc_url($nfeeds_data['generated_url']); ?>";
        
        function generateLink() {
            
            var SERVER_API_URL = "https://top-news.tooleap.com/gen-install-link";
            
            jQuery("#go-mobile-btn input").prop("disabled", true).attr("value", "Working...");
            
            jQuery.get(SERVER_API_URL, {
                url: encodeURIComponent("<?php echo esc_url(get_site_url()); ?>")
            }, function(data, status) {
                if ((data) && (data.url)) {
                    show_message(1, "Great Success!").delay(2000).fadeOut();
                    jQuery("#current-url-card").fadeIn();
                    jQuery("#current-url").text(data.url);
                    jQuery("#current-url-card a").attr("href", data.url);
                    
                    var data = {
                        'action': 'nfeeds_save',
                        'generated_url': data.url,
                        'security': '<?php echo wp_create_nonce('mobile-app-generator'); ?>'
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                        console.log('Got this from the server: ' + response);
                    });
                    
                } else {
                    show_message(0, 'Failed :( Please <a href="http://nfeeds.tooleap.com" target="_blank">contact us</a>');
                }
            }).error(function () {
                show_message(0, 'Failed :( Please <a href="http://nfeeds.tooleap.com" target="_blank">contact us</a>');
            }).always(function() {
                jQuery("#go-mobile-btn input").prop("disabled", false).attr("value", jQuery("#go-mobile-btn input").attr("data-title"));
            });
        }
        
        jQuery(document).ready(function($) {

            if (currentGeneratedUrl.length > 0) {
                $("#current-url-card").show();
            }
            
            jQuery("#go-mobile-btn").click(function() {
                generateLink();
                return false;
            });
            
        });

        function show_message(n, msg) {
            if(n == 1) {
                return jQuery('#saved').html('<div id="message" class="updated fade"><p><strong>' + (msg ? msg : "<?php _e('Options saved.'); ?>") + '</strong></p></div>').fadeIn();
            } else {
                return jQuery('#saved').html('<div id="message" class="error fade"><p><strong>' + (msg ? msg : "<?php _e('Options could not be saved.'); ?>") + '</strong></p></div>');
            }
        }
        
	</script>

    <?php
}

?>
