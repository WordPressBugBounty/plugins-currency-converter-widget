<?php
class bcc_currency_widget_class extends WP_Widget {
	/**
	* Initialize the wiki widget
	**/
	public function __construct() {
		// This is where we add the style and script
        add_action( 'load-widgets.php', array(&$this, 'bcc_load_scripts') );
		$widget_options = array( 
			'classname' => 'bcc_currency_widget',
			'description' => 'Display currency converter on your sidebar.',
		);
		parent::__construct( 'bcc_currency_widget', 'Currency Converter Widget', $widget_options );
	}

	/**
	* wiki widget load scripts
	* Method @bcc_load_color_picker
	*/
    function bcc_load_scripts() {    
        wp_enqueue_script( 'jquery' );    

        wp_enqueue_style( 'wp-color-picker' );        
        wp_enqueue_script( 'wp-color-picker' );    

        wp_enqueue_style( "slimselect_style", plugins_url( 'css/slimselect.min.css', __FILE__ ) );
        wp_enqueue_script( 'slimselect_script', plugins_url( 'js/slimselect.js', __FILE__ ) );
    }

	/**
	* wiki widget front-end
	* Method @widget
	*/
	public function widget( $args, $instance ) {
        $title = (isset($instance[ 'title' ])) ? apply_filters( 'widget_title', $instance[ 'title' ] ) : "";
		$width = (isset($instance[ 'width' ])) ? $instance[ 'width' ] : "";
		$height = (isset($instance[ 'height' ])) ? $instance[ 'height' ] : "";
		$font_color = (isset($instance[ 'font_color' ])) ? str_replace("#", "", $instance[ 'font_color' ]) : "";
		$style = (isset($instance[ 'style' ])) ? str_replace("#", "", $instance[ 'style' ]) : "";
        $amount = (isset($instance[ 'amount' ])) ? $instance[ 'amount' ] : 1;
		$from = (isset($instance[ 'from' ])) ? $instance[ 'from' ] : "";
		$to = (isset($instance[ 'to' ])) ? $instance[ 'to' ] : "";
		$size = (isset($instance[ 'size' ])) ? $instance[ 'size' ] : "";
		$lang = (isset($instance[ 'lang' ])) ? $instance[ 'lang' ] : "";
		$widget = $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];
		$uniq_id = uniqid();

        $gradient = isset( $instance['gradient'] ) ? $instance['gradient'] : 'on';
        $shadow = isset( $instance['shadow'] ) ? $instance['shadow'] : 'on';
        $border = isset( $instance['border'] ) ? $instance['border'] : 'on';
        $flag = isset( $instance['flag'] ) ? $instance['flag'] : 'on';
        $display = isset( $instance['display'] ) ? $instance['display'] : 'c';
        $currencies = isset( $instance['currencies'] ) ? $instance['currencies'] : [];
        $symbol = isset( $instance['symbol'] ) ? $instance['symbol'] : 'off';
        $monetary_format = isset( $instance['monetary_format'] ) ? $instance['monetary_format'] : '1';
        $decimal_format = isset( $instance['decimal_format'] ) ? $instance['decimal_format'] : '2';
        $date_format = isset( $instance['date_format'] ) ? $instance['date_format'] : '1';
        $support = isset( $instance['support'] ) ? $instance['support'] : 'on';
		
		$atts = array(
            'a' => isset($amount) ? $amount : 1,
			'w' => ($width != "") ? $width : 200,
			'h' => ($height != "") ? $height : 350,
			'c' => ($style != "") ? $style : '4f7ccb',
			'fc' => ($font_color != "") ? $font_color : 'FFFFFF',
			'f' => ($from != "") ? $from :'USD',
			't' => ($to != "") ? $to : 'EUR',
			'type' => ($size != "") ? $size : 'fix',
			'lang' => ($lang != "") ? $lang : '',

            'g' => $gradient,
            'sh' => $shadow,
            'b' => $border,
            'fl' => $flag,
            'p' => $display,
            'cs' => $currencies,
            's' => $symbol,
            'mf' => $monetary_format,
            'df' => $decimal_format,
            'd' => $date_format,
            'su' => $support,
		);

		ob_start();
		?>
		<div id="currency-bcc-<?php echo $uniq_id; ?>" class="currency-bcc">
		</div>
		<script type="text/javascript">
		var wc = document.createElement("DIV");
		function widgetTrigger(type, lang) {
            var uniqID = '<?php echo $uniq_id; ?>';

            var langg = (lang != '-1' && typeof lang != 'undefined') ? '&lang='+lang : "";
            
            var width = (type == 'custom') ? '<?php echo $atts['w']; ?>' : ((type == 'fix') ? 200 : 0);
            var height = (type == 'custom') ? '<?php echo $atts['h']; ?>' : ((type == 'fix') ? 350 : 350);

            var bg_color = '<?php echo $atts['c']; ?>'
            var font_color = '<?php echo $atts['fc']; ?>'

            var amount = '<?php echo $atts['a']; ?>';
            var from = '<?php echo $atts['f']; ?>';
            var to = '<?php echo $atts['t']; ?>'
            
            var gradient = '<?php echo $atts['g']; ?>'
            var shadow = '<?php echo $atts['sh']; ?>'
            var border = '<?php echo $atts['b']; ?>'
            var flag = '<?php echo $atts['fl']; ?>'
            var display = '<?php echo $atts['p']; ?>'
            var currencies = '<?php echo implode(",", $atts["cs"]); ?>'
            var symbol = '<?php echo $atts['s']; ?>'
            var monetary_format = '<?php echo $atts['mf']; ?>'
            var decimal_format = '<?php echo $atts['df']; ?>'
            var date_format = '<?php echo $atts['d']; ?>'
            var support = '<?php echo $atts['su']; ?>'

			wc.id = "wc"+uniqID;
            wc.name = "wc"+uniqID;
            var wc_style = "position:relative;display:inline-block;border:none;min-width:200px;min-height:350px;";
            if (shadow && shadow == 'on') {
                wc_style += "box-shadow:0 0 5px #000;";
            }

            var fr = document.createElement("IFRAME");
            if (width && height) {
                if (width < 200 ) width = 200;
                if (height < 350 ) height = 350;

                fr.width = width+"px";
                fr.height = height+"px";
                wc_style += "width:"+width+"px;";
                wc_style += "height:"+height+"px;";
            }
            else {
                fr.width= "100%";
                if (!height || height < 350 ) height = 350;
                fr.height = height + "px";
                wc_style += "width:100%;";
                wc_style += "height:"+height+"px;";
            }
            wc.style = wc_style;
            wc.innerHTML = ''
            wc.appendChild(fr)

            if(support && support == 'on') {
                var lb=document.createElement("DIV");
                lb.style="position:absolute;display:inline-block;box-sizing:border-box;width:100%;left:0;bottom:0;padding:0 15px;text-align:right;line-height:25px;color:#{{$fc}};font-size:13px;font-family:Arial,Helvetica,sans-serif;"
                var lb_1=document.createElement("A");
                lb_1.innerHTML=from+"/"+to;
                lb_1.href="https://www.currency.wiki/"+from.toLowerCase()+"_"+to.toLowerCase();
                lb_1.rel="nofollow";
                lb_1.target="_blank";
                lb_1.style="float:left;margin:0;padding:0;text-transform:uppercase;line-height:25px;color:#"+font_color+";"
                var lb_2=document.createElement("A");
                lb_2.innerHTML="Currency.Wiki";
                lb_2.href="https://www.currency.wiki";
                lb_2.rel="nofollow";
                lb_2.target="_blank";
                lb_2.style="line-height:25px;margin:0;padding:0;color:#"+font_color+";"
                lb.appendChild(lb_1);
                lb.appendChild(lb_2);
                wc.appendChild(lb);
            }

            fr.id = uniqID;
            fr.name = uniqID;
            fr.style = "border:none;min-width:200px;min-height:350px";
            fr.width = (type == 'custom') ? width+"px" : ((type == 'fix') ? "200px" : "100%");
            fr.height = (type == 'custom') ? height+"px" : ((type == 'fix') ? "350px" : "100%");

			document.getElementById("currency-bcc-"+uniqID).appendChild(wc);

            var yp = JSON.stringify({
                cd:uniqID,
                a: amount ? amount : 1, // amount
                w: width, // width
                h: height, // height
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
            
            var url = "https://www.currency.wiki/widget/embed?wd=1&f="+from+"&t="+to+"&cs="+currencies+"&d="+date_format+"&tm="+<?php echo time(); ?>+langg;
            url = url.replace(/\"/g, "");
            fr.setAttribute("src", url);
            var w = window.frames[uniqID];
            fr.onload = function() {
                w.postMessage({"t": yp}, "*");
            }
		}
		widgetTrigger('<?php echo $atts['type']; ?>', '<?php echo $atts['lang']; ?>');
		</script>
		<style>
	        .currency-bcc iframe {border:none; outline: none;}
	    </style>
		<?php
		/*============================*/

		$widget .= ob_get_clean();
		echo $widget .= $args['after_widget'];
	}

	/**
	* wiki widget form
	* Method @form
	*/
	public function form( $instance ) {
        error_log('widget form function: ' . $this->id);
		?>
		<h2><?php echo _e("Widget Parameters"); ?></h2>
		<?php
        $uniq_id = uniqid();
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$width = ! empty( $instance['width'] ) ? $instance['width'] : '';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '';
		$font_color = ! empty( $instance['font_color'] ) ? $instance['font_color'] : '';
		$style = ! empty( $instance['style'] ) ? $instance['style'] : '';
        $amount = ! empty( $instance['amount'] ) ? $instance['amount'] : 1;
		$from = ! empty( $instance['from'] ) ? $instance['from'] : '';
		$to = ! empty( $instance['to'] ) ? $instance['to'] : '';
		$size = ! empty( $instance['size'] ) ? $instance['size'] : '';
		$lang = ! empty( $instance['lang'] ) ? $instance['lang'] : '';
		$from_val = esc_attr( $from );
		$to_val = esc_attr( $to );

        $gradient = ! empty( $instance['gradient'] ) ? $instance['gradient'] : 'on';
        $shadow = ! empty( $instance['shadow'] ) ? $instance['shadow'] : 'on';
        $border = ! empty( $instance['border'] ) ? $instance['border'] : 'on';
        $flag = ! empty( $instance['flag'] ) ? $instance['flag'] : 'on';
        $display = ! empty( $instance['display'] ) ? $instance['display'] : 'c';

        $currencies = isset( $instance['currencies'] ) ? $instance['currencies'] : [];
        $symbol = ! empty( $instance['symbol'] ) ? $instance['symbol'] : 'off';
        $monetary_format = ! empty( $instance['monetary_format'] ) ? $instance['monetary_format'] : '1';
        $decimal_format = ! empty( $instance['decimal_format'] ) ? $instance['decimal_format'] : '2';
        $date_format = ! empty( $instance['date_format'] ) ? $instance['date_format'] : '1';
        $support = ! empty( $instance['support'] ) ? $instance['support'] : 'on';

        $country_arr = array('error'=>'ERROR-#116: Either bcc-countries.php file is missing Or not present on the include path.');
		if(file_exists(plugin_dir_path(__FILE__) . 'bcc-countries.php')) {
			$country_arr = include('bcc-countries.php');
		}
		$language_arr = array('error'=>'ERROR on line#120 Either bcc-languages.php file is missing or not present on the include path.');
		if(file_exists(plugin_dir_path(__FILE__) . 'bcc-languages.php')) {
			$language_arr = include('bcc-languages.php');
		}
		?>
        <p class="alert"><a href="https://wordpress.org/support/plugin/currency-converter-widget/reviews/#new-post" target="_blank">Please support us! Rate our plugin ➔ (click here)</a></p>
		<?php if ( !is_customize_preview() ) : ?>            
    <p class="note">
        <?php echo _e("NOTE: For older WordPress versions, click 'Save' to activate the preview. For newer versions, simply click outside the currency widget box. Remember, this widget might not be compatible with all themes. Please test with different themes if needed."); ?>
    </p>
<?php endif; ?>
		<table>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Title"); ?>:</label>
				</td>
				<td>
					<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo ($title != "") ? esc_attr( $title ) : ''; ?>" style="max-width: 160px;" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'size' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Type"); ?>:</label>
				</td>
    			<td>
    				<select name="<?php echo $this->get_field_name( 'size' ); ?>" id="<?php echo $this->get_field_id( 'size' ); ?>" class="size-<?php echo $uniq_id; ?> currency-bcc-widget-input" style="max-width: 160px; min-width: 160px;">
    					<option value="auto" <?php echo ($size == 'auto') ? 'selected' : ''; ?>><?php echo _e("Auto"); ?></option>
    					<option value="fix" <?php echo ($size == 'fix') ? 'selected' : ((!$size)?'selected':''); ?>><?php echo _e("200x350"); ?></option>
    					<option value="custom" <?php echo ($size == 'custom') ? 'selected' : ''; ?>><?php echo _e("Custom"); ?></option>
    				</select>
    			</td>
    		</tr>
			<tr id="width-section-<?php echo $this->get_field_id( 'width' ); ?>" class="width-section-<?php echo $uniq_id; ?>" <?php echo ($size != 'custom') ? "style='display: none;'" : "" ; ?>>
				<td>
					<label for="<?php echo $this->get_field_id( 'width' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Width"); ?>:</label>
				</td>
				<td>
					<input type="text" class="width currency-bcc-widget-input" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" value="<?php echo ($width != '') ? esc_attr( $width ) : '200'; ?>" />
				</td>
			</tr>
			<tr id="height-section-<?php echo $this->get_field_id( 'height' ); ?>" class="height-section-<?php echo $uniq_id; ?>" <?php echo ($size != 'custom') ? "style='display: none;'" : "" ; ?>>
				<td>
					<label for="<?php echo $this->get_field_id( 'height' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Height"); ?>:</label>
				</td>
				<td>
					<input type="text" class="height currency-bcc-widget-input" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" value="<?php echo ($height != '') ? esc_attr( $height ) : '350'; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'font_color' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Font Color"); ?>:</label>
				</td>
				<td>
					<input type="text" id="<?php echo $this->get_field_id( 'font_color' ); ?>" class="font_color-<?php echo $uniq_id; ?> currency-bcc-widget-input" name="<?php echo $this->get_field_name( 'font_color' ); ?>" value="<?php echo ($font_color != '') ? esc_attr( $font_color ) : '#FFFFFF'; ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'style' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Style"); ?>:</label>
				</td>
				<td>
					<input type="text" id="<?php echo $this->get_field_id( 'style' ); ?>" class="style-<?php echo $uniq_id; ?> currency-bcc-widget-input" name="<?php echo $this->get_field_name( 'style' ); ?>" value="<?php echo ($style != '') ? esc_attr( $style ) : '#4f7ccb'; ?>" />
				</td>
			</tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'amount' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Amount"); ?>:</label>
                </td>
                <td>
                    <input type="number" class="amount currency-bcc-widget-input" id="<?php echo $this->get_field_id( 'amount' ); ?>" name="<?php echo $this->get_field_name( 'amount' ); ?>" value="<?php echo ($amount != '') ? esc_attr( $amount ) : '1'; ?>" />
                </td>
            </tr>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'from' ); ?>" class="currency-bcc-widget-label"><?php echo _e("From"); ?>:</label>
				</td>
				<td>
					<select class="from currency-bcc-widget-input" id="<?php echo $this->get_field_id( 'from' ); ?>" name="<?php echo $this->get_field_name( 'from' ); ?>" style="max-width: 160px;">
						<?php foreach ($country_arr as $key => $value) : ?>
							<option value="<?php echo $key; ?>" 
								<?php echo ($from_val != "" && $from_val == $key) ? 'selected="selected"' : (('USD' == $key) ? 'selected="selected"' : ""); ?>>
								<?php echo $value; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'to' ); ?>" class="currency-bcc-widget-label"><?php echo _e("To"); ?>:</label>
				</td>
				<td>
					<select class="to currency-bcc-widget-input" id="<?php echo $this->get_field_id( 'to' ); ?>" name="<?php echo $this->get_field_name( 'to' ); ?>" style="max-width: 160px;">
						<?php foreach ($country_arr as $key => $value) : ?>
							<option value="<?php echo $key; ?>"
								<?php echo ($to_val != "" && $to_val == $key) ? 'selected="selected"' : (('EUR' == $key) ? 'selected="selected"' : ""); ?>>
								<?php echo $value; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label for="<?php echo $this->get_field_id( 'lang' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Language"); ?>:</label>
				</td>
				<td>
					<select id="<?php echo $this->get_field_id( 'lang' ); ?>" name="<?php echo $this->get_field_name( 'lang' ); ?>" class="currency-bcc-widget-input lang" style="max-width: 160px; min-width: 160px;">
                        <?php foreach ($language_arr as $key => $value) : ?>
							<option value="<?php echo $key; ?>"
								<?php echo ($key == $lang) ? 'selected' : ''; ?>>
								<?php echo $value['name']; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'gradient' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Gradient"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'gradient' ); ?>" name="<?php echo $this->get_field_name( 'gradient' ); ?>" class="currency-bcc-widget-input bcc-gradient">
                        <option value="on" <?php echo $gradient == 'on' ? 'selected' : ''; ?>>On</option> 
                        <option value="off" <?php echo $gradient == 'off' ? 'selected' : ''; ?>>Off</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'shadow' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Shadow"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'shadow' ); ?>" name="<?php echo $this->get_field_name( 'shadow' ); ?>" class="currency-bcc-widget-input bcc-shadow">
                        <option value="on" <?php echo $shadow == 'on' ? 'selected' : ''; ?>>On</option>
                        <option value="off" <?php echo $shadow == 'off' ? 'selected' : ''; ?>>Off</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'border' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Border"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'border' ); ?>" name="<?php echo $this->get_field_name( 'border' ); ?>" class="currency-bcc-widget-input bcc-border">
                        <option value="on" <?php echo $border == 'on' ? 'selected' : ''; ?>>On</option>
                        <option value="off" <?php echo $border == 'off' ? 'selected' : ''; ?>>Off</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'display' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Display"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>" class="currency-bcc-widget-input bcc-display">
                        <option value="c" <?php echo $display == 'c' ? 'selected' : ''; ?>>Converter</option>
                        <option value="e" <?php echo $display == 'e' ? 'selected' : ''; ?>>Exchange Rates</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'currencies' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Currencies"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'currencies' ); ?>" class="filter-currencies" name="<?php echo $this->get_field_name( 'currencies' ); ?>[]" class="bcc-currencies" multiple>
                        <?php foreach ($country_arr as $key => $value) : ?>
                            <option value="<?php echo $key; ?>" <?php if(in_array($key, $currencies)) echo "selected" ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'flag' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Flag"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'flag' ); ?>" name="<?php echo $this->get_field_name( 'flag' ); ?>" class="currency-bcc-widget-input bcc-flag">
                        <option value="on" <?php echo $flag == 'on' ? 'selected' : ''; ?>>On</option>
                        <option value="off" <?php echo $flag == 'off' ? 'selected' : ''; ?>>Off</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'symbol' ); ?>symbol" class="currency-bcc-widget-label"><?php echo _e("Symbol"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'symbol' ); ?>" name="<?php echo $this->get_field_name( 'symbol' ); ?>" class="currency-bcc-widget-input bcc-symbol">
                        <option value="off" <?php echo $symbol == 'off' ? 'selected' : ''; ?>>Off</option>
                        <option value="left" <?php echo $symbol == 'left' ? 'selected' : ''; ?>>Left</option>
                        <option value="right" <?php echo $symbol == 'right' ? 'selected' : ''; ?>>Right</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'monetary_format' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Format"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'monetary_format' ); ?>" name="<?php echo $this->get_field_name( 'monetary_format' ); ?>" class="currency-bcc-widget-input bcc-monetary_format">
                        <option value="1" <?php echo $monetary_format == '1' ? 'selected' : ''; ?>>1,234.56</option>
                        <option value="2" <?php echo $monetary_format == '2' ? 'selected' : ''; ?>>1.234,56</option>
                        <option value="3" <?php echo $monetary_format == '3' ? 'selected' : ''; ?>>1 234.56</option>
                        <option value="4" <?php echo $monetary_format == '4' ? 'selected' : ''; ?>>1 234,56</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'decimal_format' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Separator"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'decimal_format' ); ?>" name="<?php echo $this->get_field_name( 'decimal_format' ); ?>" class="currency-bcc-widget-input bcc-decimal_format">
                        <option value="2" <?php echo $decimal_format == '2' ? 'selected' : ''; ?>>0.02</option>
                        <option value="3" <?php echo $decimal_format == '3' ? 'selected' : ''; ?>>0.003</option>
                        <option value="4" <?php echo $decimal_format == '4' ? 'selected' : ''; ?>>0.0004</option>
                        <option value="5" <?php echo $decimal_format == '5' ? 'selected' : ''; ?>>0.00005</option>
                        <option value="6" <?php echo $decimal_format == '6' ? 'selected' : ''; ?>>0.000006</option>
                        <option value="0" <?php echo $decimal_format == '0' ? 'selected' : ''; ?>>Don't display</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'date_format' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Date"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'date_format' ); ?>" name="<?php echo $this->get_field_name( 'date_format' ); ?>" class="currency-bcc-widget-input bcc-date_format">
                        <option value="1" <?php echo $date_format == '1' ? 'selected' : ''; ?>>yyyy-mm-dd</option>
                        <option value="2" <?php echo $date_format == '2' ? 'selected' : ''; ?>>mm-dd-yyyy</option>
                        <option value="3" <?php echo $date_format == '3' ? 'selected' : ''; ?>>dd-mm-yyyy</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id( 'support' ); ?>" class="currency-bcc-widget-label"><?php echo _e("Support Us"); ?>:</label>
                </td>
                <td>
                    <select id="<?php echo $this->get_field_id( 'support' ); ?>" name="<?php echo $this->get_field_name( 'support' ); ?>" class="currency-bcc-widget-input bcc-support">
                        <option value="on" <?php echo $support == 'on' ? 'selected' : ''; ?>>On</option>
                        <option value="off" <?php echo $support == 'off' ? 'selected' : ''; ?>>Off</option>
                    </select>
                </td>
            </tr>
		</table>
		<?php if ( is_customize_preview() ) : ?>
			<script type="text/javascript">
                jQuery.noConflict();
				jQuery(document).ready(function($) {
					var s = '#'+jQuery('.style-<?php echo $uniq_id; ?>').attr('id');
					var fc = '#'+jQuery('.font_color-<?php echo $uniq_id; ?>').attr('id');
					if (jQuery('.style-<?php echo $uniq_id; ?>').attr('id').replace('widget-bcc_currency_widget-', "").replace('-style',"") != '__i__') {
						jQuery(s).wpColorPicker({
			                change: _.throttle( function () { $(this).trigger('change'); }, 1000, {leading: false} ),
			                width : 150
			            });
					}

					if (jQuery('.font_color-<?php echo $uniq_id; ?>').attr('id').replace('widget-bcc_currency_widget-', "").replace('-font_color',"") != '__i__') {
						jQuery(fc).wpColorPicker({
			                change: _.throttle( function () { $(this).trigger('change'); }, 1000, {leading: false} ),
			                width : 150
			            });
					}
					
				});
				jQuery('.size-<?php echo $uniq_id; ?>').change(function() {
					if (jQuery('.size-<?php echo $uniq_id; ?>').val() == 'custom') {
						jQuery('.width-section-<?php echo $uniq_id; ?>').show();
						jQuery('.height-section-<?php echo $uniq_id; ?>').show();
					} else {
						jQuery('.width-section-<?php echo $uniq_id; ?>').hide();
						jQuery('.height-section-<?php echo $uniq_id; ?>').hide();
					}
				});
			</script>
		<?php else: ?>
			<div id="currency-bcc-<?php echo $this->id; ?>" class="currency-bcc">
			</div>
			<script type='text/javascript'>
                jQuery.noConflict();
	            jQuery(document).ready(function($) {
	            	$('#<?php echo $this->get_field_id( "style" ); ?>, #<?php echo $this->get_field_id( 'font_color' ); ?>').wpColorPicker({
	            		width : 150,
                        change: function(event, ui){
                            var color = ui.color.toString();
                            $(event.target).val(color)
                            widgetTrigger()
                        },
	            	});

                    new SlimSelect({
                        select: '#<?php echo $this->get_field_id( "currencies" ); ?>'
                    })

                    $('.currency-bcc-widget-input').bind('change', function(){
                        widgetTrigger()
                    })
		        });
                var wc = document.createElement("DIV");
				function widgetTrigger() {
                    var uniqID = "<?php echo $this->id; ?>"
                    var width = jQuery('#<?php echo $this->get_field_id( 'width' ); ?>').val();
					var height = jQuery('#<?php echo $this->get_field_id( 'height' ); ?>').val();
					var font_color = jQuery('#<?php echo $this->get_field_id( 'font_color' ); ?>').val();
                    font_color = font_color.replace('#', '')
					var style = jQuery('#<?php echo $this->get_field_id( 'style' ); ?>').val();
                    style = style.replace('#', '')
                    var amount = jQuery('#<?php echo $this->get_field_id( 'amount' ); ?>').val();
					var from = jQuery('#<?php echo $this->get_field_id( 'from' ); ?>').val();
					var to = jQuery('#<?php echo $this->get_field_id( 'to' ); ?>').val();
					var type = jQuery('#<?php echo $this->get_field_id( 'size' ); ?>').val();
					var lang = jQuery('#<?php echo $this->get_field_id( 'lang' ); ?>').val();
					var langg = (lang != '-1' && typeof lang != 'undefined') ? '&lang='+lang : "";

                    var gradient = jQuery('#<?php echo $this->get_field_id( 'gradient' ); ?>').val();
                    var shadow = jQuery('#<?php echo $this->get_field_id( 'shadow' ); ?>').val();
                    var border = jQuery('#<?php echo $this->get_field_id( 'border' ); ?>').val();
                    var shadow = jQuery('#<?php echo $this->get_field_id( 'shadow' ); ?>').val();
                    var flag = jQuery('#<?php echo $this->get_field_id( 'flag' ); ?>').val();
                    var display = jQuery('#<?php echo $this->get_field_id( 'display' ); ?>').val();
                    var currencies = jQuery('#<?php echo $this->get_field_id( 'currencies' ); ?>').val();
                    if (currencies && currencies.length > 0) {
                        currencies = currencies.join(",")
                    }
                    else {
                        currencies = ''
                    }
                    var symbol = jQuery('#<?php echo $this->get_field_id( 'symbol' ); ?>').val();
                    var monetary_format = jQuery('#<?php echo $this->get_field_id( 'monetary_format' ); ?>').val();
                    var decimal_format = jQuery('#<?php echo $this->get_field_id( 'decimal_format' ); ?>').val();
                    var date_format = jQuery('#<?php echo $this->get_field_id( 'date_format' ); ?>').val();
                    var support = jQuery('#<?php echo $this->get_field_id( 'support' ); ?>').val();

                    wc.id = "wc"+uniqID
                    wc.name = "wc"+uniqID
                    var wc_style = "position:relative;display:inline-block;border:none;min-width:200px;min-height:350px;";
                    if (shadow && shadow == 'on') {
                        wc_style += "box-shadow:0 0 5px #000;";
                    }

                    var fr = document.createElement("IFRAME");
                    if (width && height) {
                        if (width < 200 ) width = 200;
                        if (height < 350 ) height = 350;

                        fr.width = width+"px";
                        fr.height = height+"px";
                        wc_style += "width:"+width+"px;";
                        wc_style += "height:"+height+"px;";
                    }
                    else {
                        fr.width= "100%";
                        if (!height || height < 350 ) height = 350;
                        fr.height = height + "px";
                        wc_style += "width:100%;";
                        wc_style += "height:"+height+"px;";
                    }
                    wc.style = wc_style;
                    wc.innerHTML = ''
                    wc.appendChild(fr)

                    if(support && support == 'on') {
                        var lb=document.createElement("DIV");
                        lb.style="position:absolute;display:inline-block;box-sizing:border-box;width:100%;left:0;bottom:0;padding:0 15px;text-align:right;line-height:25px;color:#{{$fc}};font-size:13px;font-family:Arial,Helvetica,sans-serif;"
                        var lb_1=document.createElement("A");
                        lb_1.innerHTML=from+"/"+to;
                        lb_1.href="https://www.currency.wiki/"+from.toLowerCase()+"_"+to.toLowerCase();
                        lb_1.rel="nofollow";
                        lb_1.target="_blank";
                        lb_1.style="float:left;margin:0;padding:0;text-transform:uppercase;line-height:25px;color:#"+font_color+";"
                        var lb_2=document.createElement("A");
                        lb_2.innerHTML="Currency.Wiki";
                        lb_2.href="https://www.currency.wiki";
                        lb_2.rel="nofollow";
                        lb_2.target="_blank";
                        lb_2.style="line-height:25px;margin:0;padding:0;color:#"+font_color+";"
                        lb.appendChild(lb_1);
                        lb.appendChild(lb_2);
                        wc.appendChild(lb);
                    }
					
					fr.id = uniqID;
                    fr.name = uniqID;
                    fr.style = "border:none;min-width:200px;min-height:350px";
                    fr.width = (type == 'custom') ? width+"px" : ((type == 'fix') ? "200px" : "100%");
                    fr.height = (type == 'custom') ? height+"px" : ((type == 'fix') ? "350px" : "100%");

					document.getElementById("currency-bcc-"+uniqID).appendChild(wc);

                    var yp = JSON.stringify({
						w:width,
						h:height,
						cd:uniqID,
                        a: amount ? amount : 1,
						f:from,
						t:to,
						c:style,
						fc:font_color,
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

                    var url = "https://www.currency.wiki/widget/embed?wd=1&f="+from+"&t="+to+"&cs="+currencies+"&d="+date_format+"&tm="+uniqID+langg;
					url = url.replace(/\"/g, "");
                    fr.setAttribute("src", url);
					var w = window.frames[uniqID];
                    fr.onload = function() {
                        // w.postMessage({"t": yp}, "*");
                        fr.contentWindow.postMessage({"t": yp}, "*");
					}
				}

                widgetTrigger();

				jQuery('#<?php echo $this->get_field_id( 'size' ); ?>').change(function() {
					if (jQuery('#<?php echo $this->get_field_id( 'size' ); ?>').val() == 'custom') {
						jQuery('#width-section-<?php echo $this->get_field_id( 'width' ); ?>').show();
						jQuery('#height-section-<?php echo $this->get_field_id( 'height' ); ?>').show();
					} else {
						jQuery('#width-section-<?php echo $this->get_field_id( 'width' ); ?>').hide();
						jQuery('#height-section-<?php echo $this->get_field_id( 'height' ); ?>').hide();
					}
				});

	        </script>
		<style>
	        .currency-bcc iframe {border:none; outline: none;}
	    </style>
		<?php endif; ?>
		<style type="text/css">
			/*.wp-picker-input-wrap {
			    display: none !important;
			}*/
			p.alert {
				background: yellow;
    			padding: 5px 10px;
			}
            p.alert a {
                color: inherit;
                text-decoration: none;
            }

            p.note {
                color: #bf0000;
            }
		</style>
		<?php
	}

	/**
	* Save the YP widget instances
	* Method @update
	*/
	public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'width' ] = strip_tags( $new_instance[ 'width' ] );
		$instance[ 'height' ] = strip_tags( $new_instance[ 'height' ] );
		$instance[ 'font_color' ] = strip_tags( $new_instance[ 'font_color' ] );
		$instance[ 'style' ] = strip_tags( $new_instance[ 'style' ] );
        $instance[ 'amount' ] = isset( $new_instance[ 'amount' ] ) ? $new_instance[ 'amount' ] : 1;
		$instance[ 'from' ] = strip_tags( $new_instance[ 'from' ] );
		$instance[ 'to' ] = strip_tags( $new_instance[ 'to' ] );
		$instance[ 'size' ] = strip_tags( $new_instance[ 'size' ] );
		$instance[ 'lang' ] = strip_tags( $new_instance[ 'lang' ] );
        $instance[ 'gradient' ] = isset($new_instance[ 'gradient' ]) ? $new_instance[ 'gradient' ] : 'on';
        $instance[ 'shadow' ] = isset($new_instance[ 'shadow' ]) ? $new_instance[ 'shadow' ] : 'on';
        $instance[ 'border' ] = isset($new_instance[ 'border' ]) ? $new_instance[ 'border' ] : 'on';
        $instance[ 'flag' ] = isset($new_instance[ 'flag' ]) ? $new_instance[ 'flag' ] : 'on';
        $instance[ 'display' ] = isset($new_instance[ 'display' ]) ? $new_instance[ 'display' ] : 'c';
        $instance[ 'currencies' ] = isset($new_instance['currencies']) ? esc_sql( $new_instance['currencies'] ) : esc_sql([]);
        $instance[ 'symbol' ] = isset($new_instance[ 'symbol' ]) ? $new_instance[ 'symbol' ] : 'off';
        $instance[ 'monetary_format' ] = isset($new_instance[ 'monetary_format' ]) ? $new_instance[ 'monetary_format' ] : '1';
        $instance[ 'decimal_format' ] = isset($new_instance[ 'decimal_format' ]) ? $new_instance[ 'decimal_format' ] : '2';
        $instance[ 'date_format' ] = isset($new_instance[ 'date_format' ]) ? $new_instance[ 'date_format' ] : '1';
        $instance[ 'support' ] = isset($new_instance[ 'support' ]) ? $new_instance[ 'support' ] : 'on';

        return $instance;
	}
}
function register_bcc_currency_widget() { 
	register_widget( 'bcc_currency_widget_class' );
}
add_action( 'widgets_init', 'register_bcc_currency_widget' );