<?php
/*
Plugin Name: NextGEN Gallery Sell Photo
Version: 1.0.1
Plugin URI: http://noorsplugin.com/2014/08/17/nextgen-gallery-sell-photo-plugin/
Author: naa986
Author URI: http://noorsplugin.com/
Description: NextGEN Gallery addon for selling photos from your website
*/

if(!defined('ABSPATH')) exit;
if(!class_exists('NG_SELL_PHOTO'))
{
    class NG_SELL_PHOTO
    {
        var $plugin_version = '1.0.1';
        var $plugin_url;
        var $plugin_path;
        function __construct()
        {
            define('NG_SELL_PHOTO_VERSION', $this->plugin_version);
            define('NG_SELL_PHOTO_SITE_URL',site_url());
            define('NG_SELL_PHOTO_URL', $this->plugin_url());
            define('NG_SELL_PHOTO_PATH', $this->plugin_path());
            $this->plugin_includes();
            $this->loader_operations();
            add_action( 'wp_enqueue_scripts', array( &$this, 'plugin_scripts' ), 0 );
        }
        function plugin_includes()
        {
            if(is_admin())
            {
                add_filter('plugin_action_links', array(&$this,'add_plugin_action_links'), 10, 2 );
            }
            add_action('admin_menu', array( &$this, 'add_options_menu' ));
            add_filter('ngg_render_template', 'ng_sell_photo_gallery', 10, 2);
        }
        function loader_operations()
        {
            register_activation_hook( __FILE__, array(&$this, 'activate_handler') );
            add_action('plugins_loaded',array(&$this, 'plugins_loaded_handler'));
        }
        function plugins_loaded_handler()  //Runs when plugins_loaded action gets fired
        {
            $this->check_upgrade();
        }
        
        function activate_handler()
        {
            add_option('ngsp_plugin_version', $this->plugin_version);
            add_option('ngsp_paypal_email', get_bloginfo('admin_email'));
            add_option('ngsp_currency_code', 'USD');
            add_option('ngsp_price_amount', '5.00');
            add_option('ngsp_button_anchor', 'Buy Now');
            add_option('ngsp_return_url', get_bloginfo('wpurl'));
        }

        function check_upgrade()
        {
            if(is_admin())
            {
                $plugin_version = get_option('ngsp_plugin_version');
                if(!isset($plugin_version) || $plugin_version != $this->plugin_version)
                {
                    $this->activate_handler();
                    update_option('ngsp_plugin_version', $this->plugin_version);
                }
            }
        }
        function plugin_scripts()
        {
            if (!is_admin()) 
            {
                
            }
        }
        function plugin_url()
        {
            if($this->plugin_url) return $this->plugin_url;
            return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
        }
        function plugin_path()
        { 	
            if ( $this->plugin_path ) return $this->plugin_path;		
            return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
        }
        function add_plugin_action_links($links, $file)
        {
            if ( $file == plugin_basename( dirname( __FILE__ ) . '/main.php' ) )
            {
                $links[] = '<a href="options-general.php?page=nextgen-sell-photo-settings">Settings</a>';
            }
            return $links;
        }
        function add_options_menu()
        {
            if(is_admin())
            {
                add_options_page('NextGEN Gallery Sell Photo Settings', 'NG Sell Photo', 'manage_options', 'nextgen-sell-photo-settings', array(&$this, 'options_page'));
            }
        }
        function options_page()
        {
            $wpvl_plugin_tabs = array(
                'nextgen-sell-photo-settings' => 'General'
            );
            echo '<div class="wrap">'.screen_icon().'<h2>NextGEN Gallery Sell Photo v'.NG_SELL_PHOTO_VERSION.'</h2>';;    
            echo '<div id="poststuff"><div id="post-body">';  

            if(isset($_GET['page'])){
                $current = $_GET['page'];
                if(isset($_GET['action'])){
                    $current .= "&action=".$_GET['action'];
                }
            }
            $content = '';
            $content .= '<h2 class="nav-tab-wrapper">';
            foreach($wpvl_plugin_tabs as $location => $tabname)
            {
                if($current == $location){
                    $class = ' nav-tab-active';
                } else{
                    $class = '';    
                }
                $content .= '<a class="nav-tab'.$class.'" href="?page='.$location.'">'.$tabname.'</a>';
            }
            $content .= '</h2>';
            echo $content;

            $this->general_settings();

            echo '</div></div>';
            echo '</div>';
        }
        function general_settings()
        {
            if (isset($_POST['ngsp_update_settings']))
            {
                $nonce = $_REQUEST['_wpnonce'];
                if ( !wp_verify_nonce($nonce, 'ngsp_general_settings')){
                        wp_die('Error! Nonce Security Check Failed! please save the settings again.');
                }
                update_option('ngsp_enable_testmode', ($_POST["enable_testmode"]=='1')?'1':'');
                update_option('ngsp_paypal_email', trim($_POST["paypal_email"]));
                update_option('ngsp_currency_code', trim($_POST["currency_code"]));
                update_option('ngsp_price_amount', trim($_POST["price_amount"]));
                update_option('ngsp_button_anchor', trim($_POST["button_anchor"]));
                update_option('ngsp_return_url', trim($_POST["return_url"]));
                echo '<div id="message" class="updated fade"><p><strong>';
                echo 'Settings Saved!';
                echo '</strong></p></div>';
            }
            ?>

            <div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">	
            <p><?php _e("For documentation please visit", "NGSP"); ?><br />
            <a href="http://noorsplugin.com/2014/08/17/nextgen-gallery-sell-photo-plugin/" target="_blank"><?php _e("NextGEN Sell Photo plugin page", "NGSP"); ?></a></p>
            </div>

            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('ngsp_general_settings'); ?>

            <table class="form-table">

            <tbody>

            <tr valign="top">
            <th scope="row">Enable Test Mode</th>
            <td> <fieldset><legend class="screen-reader-text"><span>Enable Test Mode</span></legend><label for="enable_testmode">
            <input name="enable_testmode" type="checkbox" id="enable_testmode" <?php if(get_option('ngsp_enable_testmode')== '1') echo ' checked="checked"';?> value="1">
            Check this option if you want to enable PayPal sandbox for testing</label>
            </fieldset></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="paypal_email">PayPal Email</label></th>
            <td><input name="paypal_email" type="text" id="paypal_email" value="<?php echo get_option('ngsp_paypal_email'); ?>" class="regular-text">
            <p class="description">Your PayPal email address</p></td>
            </tr>

            <tr valign="top">
            <th scope="row"><label for="currency_code">Currency Code</label></th>
            <td><input name="currency_code" type="text" id="currency_code" value="<?php echo get_option('ngsp_currency_code'); ?>" class="regular-text">
            <p class="description">The currency of the payment (example: USD, CAD, GBP, EUR)</p></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="price_amount">Price Amount</label></th>
            <td><input name="price_amount" type="text" id="price_amount" value="<?php echo get_option('ngsp_price_amount'); ?>" class="regular-text">
            <p class="description">The default price of each gallery photo (example: 2.00)</p></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="button_anchor">Button Text/Image</label></th>
            <td><input name="button_anchor" type="text" id="button_anchor" value="<?php echo get_option('ngsp_button_anchor'); ?>" class="regular-text">
            <p class="description">The text for the Buy button. To use an image you can enter a URL instead</p></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="return_url">Return URL</label></th>
            <td><input name="return_url" type="text" id="return_url" value="<?php echo get_option('ngsp_return_url'); ?>" class="regular-text">
            <p class="description">The URL where a user will be redirected to after the payment</p></td>
            </tr>

            </tbody>

            </table>

            <p class="submit"><input type="submit" name="ngsp_update_settings" id="ngsp_update_settings" class="button button-primary" value="Save Changes"></p></form>

            <?php
        }
    }
    $GLOBALS['ng_sell_photo'] = new NG_SELL_PHOTO();
}

function ng_sell_photo_gallery($custom_template, $template_name) 
{
    if ($template_name == 'ngsellphoto') 
    {
        $custom_template = dirname(__FILE__) . "/gallery-$template_name.php";
    } 
    return $custom_template;
}

function ng_sell_photo_get_button_code_for_paypal($item_name)
{
    $url = "https://www.paypal.com/cgi-bin/webscr";
    $testmode = get_option('ngsp_enable_testmode');
    if(isset($testmode) && !empty($testmode)){
        $url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    }
    $paypal_email = get_option('ngsp_paypal_email');
    $amount = get_option('ngsp_price_amount');
    $currency = get_option('ngsp_currency_code');
    $return_url = get_option('ngsp_return_url'); 
    $button = get_option('ngsp_button_anchor');
    $image_button = strstr($button, 'http');
    if($image_button==FALSE){
        $button = '<input type="submit" class="ng_sell_photo_button" value="'.$button.'">';	
    }
    else{
        $button = '<input type="image" src="'.$button.'" border="0" name="submit" alt="'.$item_name.'">';
    }
    $button_code = <<<EOT
    <form method="post" action="$url">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="$paypal_email">
    <input type="hidden" name="item_name" value="$item_name">
    <input type="hidden" name="amount" value="$amount">
    <input type="hidden" name="currency_code" value="$currency">
    <input type="hidden" name="return" value="$return_url">
    $button
    </form>
EOT;
    return $button_code;
}