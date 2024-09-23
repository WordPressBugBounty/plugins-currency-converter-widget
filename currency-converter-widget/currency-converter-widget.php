<?php
/**
Plugin Name: Currency Converter Widget - Exchange Rates
Plugin URI: https://www.currencyconverterplugin.com
Description: Fast loading and easy to use currency converter widget with builtin exchange rates by Currency.Wiki. This plugin includes advanced settings to customize the color, layout, and other features with a preview function. Over 160 currencies (Bitcoin included) and over 45 supported languages.
Author: Currency.Wiki
Author URI: https://www.currency.wiki
Version: 3.0.3
*/

/**
 * Adds a new top-level menu to the bottom of the WordPress administration menu.
 */
if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(file_exists(plugin_dir_path(__FILE__) . 'bcc-widget.php')) {
	include('bcc-widget.php');
}

if(file_exists(plugin_dir_path(__FILE__) . 'bcc-admin-notices.php')) {
    include('bcc-admin-notices.php');

    $admin_notice = BCC_Admin_Notices::get_instance();
    $admin_notice->info('Rate', 'rate');
}

function bcc_admin_notice__success() {
    ?>
    <!-- <div class="notice notice-success is-dismissible">
        <div class="bcc-rate-notice-container">
            <div class="logo-img">
                <img src="<?php echo plugins_url( 'img/rating_logo.png', __FILE__ );?>" style="width:70px;">
            </div>
            <div>
                <h2>Please rate our free currency plugin :)</h2>
                <p>Your valuable feedback will help us improve. It will only take a few minutes.</p>
                <p><a href="https://wordpress.org/support/plugin/currency-converter-widget/reviews/#new-post" target="_blank">Sure, I'll rate you now!</a></p>
            </div>
        </div>
    </div>
    <style>
        .bcc-rate-notice-container {
            display: flex;
            padding: 10px 0;
        }
        .bcc-rate-notice-container .logo-img {
            margin-right: 15px;
        }
        .bcc-rate-notice-container h2 {
            margin: 0;
        }
        .bcc-rate-notice-container p {
            padding: 0;
            margin: 0;
        }
    </style> -->
    <?php
}
// add_action( 'admin_notices', 'bcc_admin_notice__success' );

function bcc_create_menu_page() { 
    add_menu_page('Currency Tool', 'Currency Tool', 'administrator', 'currency-bcc', 'bcc_menu_page_display', ''); 
} // end bcc_create_menu_page

add_action('admin_menu', 'bcc_create_menu_page');

/**
 * Renders the basic display of the menu page for the theme.
 */
function bcc_menu_page_display() {
    ob_start();
    wp_enqueue_script('jquery');
    wp_enqueue_style( "bcc_style", plugins_url( 'css/admin/bcc.css', __FILE__ ) );
    wp_enqueue_style( "slimselect_style", plugins_url( 'css/slimselect.min.css', __FILE__ ) );
	wp_enqueue_style( 'wp-color-picker' );        
    wp_enqueue_script( 'wp-color-picker' );
	$country_arr = array('error'=>'ERROR on line#28: Either bcc-countries.php file is missing or not present on the include path.');
	if(file_exists(plugin_dir_path(__FILE__) . 'bcc-countries.php')) {
		$country_arr = include('bcc-countries.php');
	}

	$language_arr = array('error'=>'ERROR on line#32 Either bcc-languages.php file is missing or not present on the include path.');
	if(file_exists(plugin_dir_path(__FILE__) . 'bcc-languages.php')) {
		$language_arr = include('bcc-languages.php');
	}
    $currency_arr = array('error'=>'ERROR on line#32 Either bcc-currencies.php file is missing or not present on the include path.');
    if(file_exists(plugin_dir_path(__FILE__) . 'bcc-currencies.php')) {
        $currency_arr = include('bcc-currencies.php');
    }
	wp_register_script( 'bcc_script', plugins_url( 'js/admin/bcc.js', __FILE__ ) );
    wp_register_script( 'slimselect_script', plugins_url( 'js/slimselect.js', __FILE__ ) );
	$uniq_id = uniqid();
	?>
	<div class="wrap">
    	<h1 class="wp-heading-inline"><?php _e("Widget Parameters"); ?></h1>
    	<hr class="wp-header-end">
        <div class="row">
        	<div class="col-md-6 currency-bcc-configs">
    	    	<table>                
                    <tr>
                        <td>
                            <label for="from" class="currency-bcc-widget-label"><?php echo _e("From"); ?>:</label>
                        </td>
                        <td>
                            <select name="from" class="currency-bcc-widget-input bcc-from" id="from">
                                <?php foreach ($country_arr as $key => $value) : ?>
                                    <option value="<?php echo $key; ?>"
                                    <?php echo ("USD" == $key) ? 'selected="selected"' : ""; ?>>
                                        <?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="to" class="currency-bcc-widget-label"><?php echo _e("To"); ?>:</label>
                        </td>
                        <td>
                            <select name="to" class="currency-bcc-widget-input bcc-to" id="to">
                                <?php foreach ($country_arr as $key => $value) : ?>
                                    <option value="<?php echo $key; ?>" 
                                        <?php echo ("EUR" == $key) ? 'selected="selected"' : ""; ?>>
                                        <?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="lang" class="currency-bcc-widget-label"><?php echo _e("Language"); ?>:</label>
                        </td>
                        <td>
                            <select name="lang" class="currency-bcc-widget-input bcc-lang" id="lang">
                                <?php foreach ($language_arr as $key => $value) : ?>
                                    <option value="<?php echo $key; ?>">
                                        <?php echo $value['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="amount" class="currency-bcc-widget-label bcc-amount"><?php echo _e("Amount"); ?>:</label>
                        </td>
                        <td>
                            <input type="number" class="currency-bcc-widget-input" id="amount" name="amount" value="1" />
                        </td>
                    </tr>
    	    		<tr>
                        <td>
                            <label class="currency-bcc-widget-label"><?php echo _e("Size"); ?>:</label>
                        </td>
    	    			<td>
    	    				<div class="size input-block">
    						    <label for="size-auto"><input type="radio" name="size" id="size-auto" value="auto"><?php echo _e("Auto"); ?></label>
    						    <label for="size-fix"><input type="radio" name="size" id="size-fix" value="fix" checked="checked"><?php echo _e("200x350"); ?></label>
    						    <label for="size-custom"><input type="radio" name="size" id="size-custom" value="custom"><?php echo _e("Custom"); ?></label>
    						</div>
    	    			</td>
    	    		</tr>
    				<tr id="width-section" style="display: none;">
    					<td>
    						<label for="width" class="currency-bcc-widget-label bcc-width"><?php echo _e("Width"); ?>:</label>
    					</td>
    					<td>
    						<input type="text" class="currency-bcc-widget-input" id="width" name="width" value="200" />
    					</td>
    				</tr>
    				<tr id="height-section" style="display: none;">
    					<td>
    						<label for="height>" class="currency-bcc-widget-label bcc-height"><?php echo _e("Height"); ?>:</label>
    					</td>
    					<td>
    						<input type="text" class="currency-bcc-widget-input" id="height" name="height>" value="350" />
    					</td>
    				</tr>
    				<tr>
    					<td>
    						<label for="font_color" class="currency-bcc-widget-label"><?php echo _e("Font Color"); ?>:</label>
    					</td>
    					<td>
    						<input type="text" class="currency-bcc-widget-input bcc-font-color" id="font_color" name="font_color" value="#FFFFFF" />
    					</td>
    				</tr>
    				<tr>
    					<td>
    						<label for="style" class="currency-bcc-widget-label"><?php echo _e("Style"); ?>:</label>
    					</td>
    					<td>
    						<input type="text" class="currency-bcc-widget-input bcc-style" id="style" name="style" value="#4f7ccb" />
    					</td>
    				</tr>
                    <tr>
                        <td>
                            <label for="gradient" class="currency-bcc-widget-label"><?php echo _e("Gradient"); ?>:</label>
                        </td>
                        <td>
                            <select name="gradient" class="currency-bcc-widget-input bcc-gradient" id="gradient">
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="shadow" class="currency-bcc-widget-label"><?php echo _e("Shadow"); ?>:</label>
                        </td>
                        <td>
                            <select name="shadow" class="currency-bcc-widget-input bcc-shadow" id="shadow">
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="border" class="currency-bcc-widget-label"><?php echo _e("Border"); ?>:</label>
                        </td>
                        <td>
                            <select name="border" class="currency-bcc-widget-input bcc-border" id="border">
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="display" class="currency-bcc-widget-label"><?php echo _e("Display"); ?>:</label>
                        </td>
                        <td>
                            <select name="display" class="currency-bcc-widget-input bcc-display" id="display">
                                <option value="c" selected>Converter</option>
                                <option value="e">Exchange Rates</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="currencies" class="currency-bcc-widget-label"><?php echo _e("Currencies"); ?>:</label>
                        </td>
                        <td>
                            <select name="currencies" class="bcc-currencies" id="currencies" multiple>
                                <?php foreach ($country_arr as $key => $value) : ?>
                                    <option value="<?php echo $key; ?>">
                                        <?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="flag" class="currency-bcc-widget-label"><?php echo _e("Flag"); ?>:</label>
                        </td>
                        <td>
                            <select name="flag" class="currency-bcc-widget-input bcc-flag" id="flag">
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="symbol" class="currency-bcc-widget-label"><?php echo _e("Symbol"); ?>:</label>
                        </td>
                        <td>
                            <select name="symbol" class="currency-bcc-widget-input bcc-symbol" id="symbol">
                                <option value="off" selected>Off</option>
                                <option value="left">Left</option>
                                <option value="right">Right</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="monetary_format" class="currency-bcc-widget-label"><?php echo _e("Format"); ?>:</label>
                        </td>
                        <td>
                            <select name="monetary_format" class="currency-bcc-widget-input bcc-monetary_format" id="monetary_format">
                                <option value="1">1,234.56</option>
                                <option value="2">1.234,56</option>
                                <option value="3">1 234.56</option>
                                <option value="4">1 234,56</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="decimal_format" class="currency-bcc-widget-label"><?php echo _e("Separator"); ?>:</label>
                        </td>
                        <td>
                            <select name="decimal_format" class="currency-bcc-widget-input bcc-decimal_format" id="decimal_format">
                                <option value="2">0.02</option>
                                <option value="3">0.003</option>
                                <option value="4">0.0004</option>
                                <option value="5">0.00005</option>
                                <option value="6">0.000006</option>
                                <option value="0">Don't display</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="date_format" class="currency-bcc-widget-label"><?php echo _e("Date"); ?>:</label>
                        </td>
                        <td>
                            <select name="date_format" class="currency-bcc-widget-input bcc-date_format" id="date_format">
                                <option value="1">yyyy-mm-dd</option>
                                <option value="2">mm-dd-yyyy</option>
                                <option value="3">dd-mm-yyyy</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="support" class="currency-bcc-widget-label"><?php echo _e("Support Us"); ?>:</label>
                        </td>
                        <td>
                            <select name="support" class="currency-bcc-widget-input bcc-support" id="support">
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
                            </select>
                        </td>
                    </tr>
    				<!-- <tr>
    					<td colspan="2" style="text-align: right;">
    						<button class="currency-bcc-widget-input-preview button button-bcc-widget">
    							<?php _e("Preview"); ?>
    						</button>
    					</td>
    				</tr> -->
    			</table>
    		</div>
    		<div class="col-md-6">
    			<div id="currency-bcc-<?php echo $uniq_id; ?>" class="widget-preview-container">
    			</div>
    		</div>
        </div>
		<div class="shortcode-wrapper">
            <label for="shortcode-input" class="currency-bcc-widget-label">Short Code</label>
			<textarea id="shortcode-input" readonly></textarea>
		</div>
	</div>
	<?php

	$arguments = array(
		'time' => time(),
		'uniqID' => $uniq_id
	);

	wp_localize_script( 'bcc_script', 'bcc', $arguments );
	wp_enqueue_script( 'bcc_script' );
    wp_enqueue_script( 'slimselect_script' );
	echo ob_get_clean();
} // end bcc_menu_page_display

function currency_bcc_shortcode( $atts ) {

    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    // Set default attributes
    $atts = shortcode_atts( array(
        'type' => 'fix',
        'a' => '1',
        'f' => 'USD',
        't' => 'EUR',
        'lang' => 'en-US',
        'w' => 200,
        'h' => 350,
        'c' => '4f7ccb',
        'fc' => 'FFFFFF',
        'g' => 'on',
        'sh' => 'on',
        'b' => 'on',
        'fl' => 'on',
        'p' => 'c',
        'cs' => '',
        's' => 'off',
        'mf' => '1',
        'df' => '2',
        'd' => '1',
        'su' => 'on',
    ), $atts, 'currency_bcc' );

    // Sanitize each attribute
    $type = sanitize_text_field($atts['type']);
    $a = sanitize_text_field($atts['a']);
    $f = sanitize_text_field($atts['f']);
    $t = sanitize_text_field($atts['t']);
    $lang = sanitize_text_field($atts['lang']);
    $w = absint($atts['w']);  // Assuming width should be an integer
    $h = absint($atts['h']);  // Assuming height should be an integer
    $c = sanitize_hex_color_no_hash($atts['c']);
    $fc = sanitize_hex_color_no_hash($atts['fc']);
    $g = sanitize_text_field($atts['g']);
    $sh = sanitize_text_field($atts['sh']);
    $b = sanitize_text_field($atts['b']);
    $fl = sanitize_text_field($atts['fl']);
    $p = sanitize_text_field($atts['p']);
    $cs = sanitize_text_field($atts['cs']);
    $s = sanitize_text_field($atts['s']);
    $mf = sanitize_text_field($atts['mf']);
    $df = sanitize_text_field($atts['df']);
    $d = sanitize_text_field($atts['d']);
    $su = sanitize_text_field($atts['su']);

    // Generate unique ID for this instance of the shortcode
    $uniq_id = uniqid();
    
    ob_start();
    ?>
    <div id="currency-bcc-<?php echo esc_attr($uniq_id); ?>" class="currency-bcc"></div>

<script type="text/javascript">
    // Define the widgetTrigger function
    function widgetTrigger(type, lang) {
        var uniqID = '<?php echo esc_js($uniq_id); ?>';

        var langg = (lang != '-1' && typeof lang != 'undefined') ? '&lang=' + lang : "";
        var width = (type == 'custom') ? '<?php echo esc_js($atts['w']); ?>' : ((type == 'fix') ? '200' : '100%');
        var height = (type == 'custom') ? '<?php echo esc_js($atts['h']); ?>' : ((type == 'fix') ? '350' : '300');

        var bg_color = '<?php echo esc_js($atts['c']); ?>';
        var font_color = '<?php echo esc_js($atts['fc']); ?>';

        var amount = '<?php echo esc_js($atts['a']); ?>';

        var from = '<?php echo esc_js($atts['f']); ?>';
        var to = '<?php echo esc_js($atts['t']); ?>';

        var gradient = '<?php echo esc_js($atts['g']); ?>';
        var shadow = '<?php echo esc_js($atts['sh']); ?>';
        var border = '<?php echo esc_js($atts['b']); ?>';
        var flag = '<?php echo esc_js($atts['fl']); ?>';
        var display = '<?php echo esc_js($atts['p']); ?>';
        var currencies = '<?php echo esc_js($atts['cs']); ?>';
        var symbol = '<?php echo esc_js($atts['s']); ?>';
        var monetary_format = '<?php echo esc_js($atts['mf']); ?>';
        var decimal_format = '<?php echo esc_js($atts['df']); ?>';
        var date_format = '<?php echo esc_js($atts['d']); ?>';
        var support = '<?php echo esc_js($atts['su']); ?>';

        var wc = document.createElement("DIV");
        wc.id = "wc" + uniqID;
        wc.name = "wc" + uniqID;
        var wc_style = "position:relative;display:inline-block;border:none;min-width:200px;min-height:350px;";
        if (shadow && shadow == 'on') {
            wc_style += "box-shadow:0 0 5px #000;";
        }

        var fr = document.createElement("IFRAME");
        if (width && height) {
            if (width < 200) width = 200;
            if (height < 350) height = 350;

            fr.width = width + "px";
            fr.height = height + "px";
            wc_style += "width:" + width + "px;";
            wc_style += "height:" + height + "px;";
        } else {
            fr.width = "100%";
            if (!height || height < 350) height = 350;
            fr.height = height + "px";
            wc_style += "width:100%;";
            wc_style += "height:" + height + "px;";
        }
        wc.style = wc_style;
        wc.innerHTML = '';
        wc.appendChild(fr);

        if (support && support == 'on') {
            var lb = document.createElement("DIV");
            lb.style = "position:absolute;display:inline-block;box-sizing:border-box;width:100%;left:0;bottom:0;padding:0 15px;text-align:right;line-height:25px;color:#{{$fc}};font-size:13px;font-family:Arial,Helvetica,sans-serif;";
            var lb_1 = document.createElement("A");
            lb_1.innerHTML = from + "/" + to;
            lb_1.href = "https://www.currency.wiki/" + from.toLowerCase() + "_" + to.toLowerCase();
            lb_1.rel = "nofollow";
            lb_1.target = "_blank";
            lb_1.style = "float:left;margin:0;padding:0;text-transform:uppercase;line-height:25px;color:#" + font_color + ";";
            var lb_2 = document.createElement("A");
            lb_2.innerHTML = "Currency.Wiki";
            lb_2.href = "https://www.currency.wiki";
            lb_2.rel = "nofollow";
            lb_2.target = "_blank";
            lb_2.style = "line-height:25px;margin:0;padding:0;color:#" + font_color + ";";
            lb.appendChild(lb_1);
            lb.appendChild(lb_2);
            wc.appendChild(lb);
        }

        fr.id = uniqID;
        fr.name = uniqID;
        fr.style = "border:none;min-width:200px;min-height:350px";
        fr.width = (type == 'custom') ? width + "px" : ((type == 'fix') ? "200px" : "100%");
        fr.height = (type == 'custom') ? height + "px" : ((type == 'fix') ? "350px" : "100%");

        document.getElementById("currency-bcc-" + uniqID).appendChild(wc);

        var yp = JSON.stringify({
            cd: uniqID,
            w: width, // width
            h: height, // height
            a: amount ? amount : 1, // amount
            f: from.toUpperCase(), // from currency
            t: to.toUpperCase(), // to currency
            fc: font_color, // font color
            c: bg_color, // bg color
            g: gradient, // gradient bg
            sh: shadow, // shadow
            b: border, // border
            fl: flag, // flag
            p: display, // page - converter or exchange rates
            cs: currencies, // currencies 
            s: symbol, // symbol right, left, off
            mf: monetary_format, // monetary format
            df: decimal_format, // decimal format
            d: date_format, // date
            su: support, // support us
        });

        var url = "https://www.currency.wiki/widget/embed?wd=1&f=" + from + "&t=" + to + "&cs=" + currencies + "&d=" + date_format + "&tm=<?php echo time(); ?>" + langg;
        url = url.replace(/\"/g, "");
        fr.setAttribute("src", url);
        var w = window.frames[uniqID];
        fr.onload = function () {
            w.postMessage({ "t": yp }, "*");
        }
    }

    // Add an event listener for DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        // Call widgetTrigger with the appropriate parameters
        widgetTrigger('<?php echo esc_js($atts['type']); ?>', '<?php echo esc_js($atts['lang']); ?>');
    });
</script>
<style>
    .currency-bcc iframe {border:none; outline: none;}
</style>
<?php

return ob_get_clean(); // Return captured output
}
add_shortcode( 'currency_bcc', 'currency_bcc_shortcode' );
