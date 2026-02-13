<?php
/**
 * Plugin Name: Currency Converter Widget
 * Plugin URI: https://wordpress.org/plugins/currency-converter-widget/
 * Description: Add a beautiful, customizable currency converter widget to your WordPress site. Real-time exchange rates, 11 widget styles, 170+ currencies. Powered by Currency.Wiki
 * Version: 4.0.1
 * Author: Currency.Wiki
 * Author URI: https://currency.wiki
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: currency-converter-widget
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CWC_VERSION', '4.0.1');
define('CWC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CWC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CWC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Currency Wiki Converter Class
 */
class Currency_Wiki_Converter {

    /**
     * Instance
     */
    private static $instance = null;

    /**
     * Default widget options
     */
    private $defaults = [
        'style' => 'compact',
        'theme' => 'auto',
        'accent' => '2563eb',
        'from' => 'USD',
        'to' => 'EUR',
        'amount' => '1',
        'language' => 'en',
        'show_flags' => true,
        'show_labels' => true,
        'show_swap' => true,
        'show_branding' => true,
        'show_branding_link' => true,
        'lock_currencies' => false,
        'number_format' => 'auto',
        'decimals' => 2,
        'chart_period' => '7d',
        'dropdown_preset' => 'top10',
        'dropdown_currencies' => 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN',
        'currency_display' => 'iso', // 'iso' for ISO codes (USD), 'symbol' for symbols ($)
        'display_preset' => 'top3',
        'display_currencies' => 'EUR,GBP,JPY',
        'add_dropdown_preset' => 'top10',
        'add_dropdown_currencies' => 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN',
        'size' => '',
        'custom_width' => '',
        'custom_height' => '',
    ];

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load text domain
        add_action('init', [$this, 'load_textdomain']);

        // Register shortcodes - using unique prefix to avoid conflicts
        add_shortcode('currencywiki_converter', [$this, 'render_shortcode']);
        add_shortcode('currencywiki', [$this, 'render_shortcode']); // Short alias

        // Register Gutenberg block
        add_action('init', [$this, 'register_block']);

        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_init', [$this, 'register_settings']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
            add_filter('plugin_action_links_' . CWC_PLUGIN_BASENAME, [$this, 'add_settings_link']);

            // AJAX handlers for review notice
            add_action('wp_ajax_cwc_dismiss_review', [$this, 'ajax_dismiss_review']);

            // Add settings-updated parameter to redirect URL after saving
            add_filter('wp_redirect', [$this, 'add_settings_updated_param']);
        }

        // Enqueue frontend scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }

    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('currency-converter-widget', false, dirname(CWC_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Register Gutenberg block
     */
    public function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register block editor script
        wp_register_script(
            'cwc-block-editor',
            CWC_PLUGIN_URL . 'assets/js/block-editor.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            CWC_VERSION,
            true
        );

        // Set up JavaScript translations for the block editor
        wp_set_script_translations('cwc-block-editor', 'currency-converter-widget', CWC_PLUGIN_DIR . 'languages');

        // Localize script with currencies and settings
        wp_localize_script('cwc-block-editor', 'cwcBlockData', [
            'currencies' => $this->get_currencies(),
            'widgetStyles' => $this->get_widget_styles(),
            'languages' => $this->get_languages(),
            'defaults' => $this->defaults,
            'previewUrl' => 'https://widget.currency.wiki/v3/embed',
        ]);

        // Register block
        register_block_type('currency-wiki/converter', [
            'editor_script' => 'cwc-block-editor',
            'render_callback' => [$this, 'render_block'],
            'attributes' => [
                'style' => ['type' => 'string', 'default' => 'compact'],
                'theme' => ['type' => 'string', 'default' => 'auto'],
                'accent' => ['type' => 'string', 'default' => '2563eb'],
                'from' => ['type' => 'string', 'default' => 'USD'],
                'to' => ['type' => 'string', 'default' => 'EUR'],
                'amount' => ['type' => 'string', 'default' => '1'],
                'language' => ['type' => 'string', 'default' => 'en'],
                'showFlags' => ['type' => 'boolean', 'default' => true],
                'showLabels' => ['type' => 'boolean', 'default' => true],
                'showSwap' => ['type' => 'boolean', 'default' => true],
                'showBranding' => ['type' => 'boolean', 'default' => true],
                'showBrandingLink' => ['type' => 'boolean', 'default' => true],
                'lockCurrencies' => ['type' => 'boolean', 'default' => false],
                'numberFormat' => ['type' => 'string', 'default' => 'auto'],
                'decimals' => ['type' => 'number', 'default' => 2],
                'chartPeriod' => ['type' => 'string', 'default' => '7d'],
                'width' => ['type' => 'string', 'default' => ''],
                'height' => ['type' => 'string', 'default' => ''],
                'selectedCurrencies' => ['type' => 'array', 'default' => ['EUR', 'GBP', 'JPY']],
                'dropdownPreset' => ['type' => 'string', 'default' => 'top10'],
                'dropdownCurrencies' => ['type' => 'string', 'default' => 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN'],
                'displayPreset' => ['type' => 'string', 'default' => 'top3'],
                'displayCurrencies' => ['type' => 'string', 'default' => 'EUR,GBP,JPY'],
                'size' => ['type' => 'string', 'default' => 'default'],
                'customWidth' => ['type' => 'string', 'default' => ''],
                'customHeight' => ['type' => 'string', 'default' => ''],
                'currencyDisplayMode' => ['type' => 'string', 'default' => 'iso'],
            ],
        ]);
    }

    /**
     * Render Gutenberg block
     */
    public function render_block($attributes) {
        // Use dropdownCurrencies from block attributes
        if (!empty($attributes['dropdownCurrencies'])) {
            $attributes['dropdown'] = $attributes['dropdownCurrencies'];
        } else {
            $saved_options = get_option('cwc_widget_options', []);
            $attributes['dropdown'] = $saved_options['dropdown_currencies'] ?? 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
        }

        // Handle "Add Currency" dropdown for multi-expandable style
        if (($attributes['style'] ?? 'compact') === 'multi-expandable') {
            $dropdownPreset = $attributes['dropdownPreset'] ?? 'top10';
            if ($dropdownPreset === 'top10') {
                $attributes['addDropdown'] = 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
            } elseif ($dropdownPreset === 'top20') {
                $attributes['addDropdown'] = 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN,BRL,KRW,SGD,HKD,NOK,SEK,DKK,NZD,ZAR,RUB';
            } elseif ($dropdownPreset === 'all') {
                $attributes['addDropdown'] = 'all';
            } elseif ($dropdownPreset === 'custom' && !empty($attributes['dropdownCurrencies'])) {
                $attributes['addDropdown'] = $attributes['dropdownCurrencies'];
            } else {
                $attributes['addDropdown'] = 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
            }
        }

        // Handle display currencies for multi-currency widgets
        // Convert displayCurrencies string to selectedCurrencies array
        $displayCurrencyStyles = ['multi-expandable', 'multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];
        if (in_array($attributes['style'] ?? 'compact', $displayCurrencyStyles)) {
            // Get currencies based on displayPreset or displayCurrencies
            $displayPreset = $attributes['displayPreset'] ?? 'top3';
            $displayCurrencies = $attributes['displayCurrencies'] ?? 'EUR,GBP,JPY';

            if ($displayPreset === 'top3') {
                $displayCurrencies = 'EUR,GBP,JPY';
            } elseif ($displayPreset === 'top10') {
                $displayCurrencies = 'EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN,BRL';
            } elseif ($displayPreset === 'all') {
                $displayCurrencies = 'all';
            }
            // For 'custom', use the displayCurrencies value as-is

            // Convert to array for selectedCurrencies
            if ($displayCurrencies !== 'all') {
                $attributes['selectedCurrencies'] = array_map('trim', explode(',', strtoupper($displayCurrencies)));
            } else {
                // For 'all', use a default set (the API will handle 'all')
                $attributes['selectedCurrencies'] = ['EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'MXN', 'BRL'];
            }
        }

        // Handle custom size
        if (!empty($attributes['size']) && $attributes['size'] === 'custom') {
            if (!empty($attributes['customWidth'])) {
                $attributes['width'] = $attributes['customWidth'];
            }
            if (!empty($attributes['customHeight'])) {
                $attributes['height'] = $attributes['customHeight'];
            }
        }

        // Ensure all attributes have proper defaults for generate_widget_script
        $attributes = wp_parse_args($attributes, [
            'style' => 'compact',
            'theme' => 'auto',
            'accent' => '2563eb',
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => '1',
            'language' => 'en',
            'showFlags' => true,
            'showLabels' => true,
            'showSwap' => true,
            'showBranding' => true,
            'showBrandingLink' => true,
            'lockCurrencies' => false,
            'numberFormat' => 'auto',
            'decimals' => 2,
            'chartPeriod' => '7d',
            'currencyDisplayMode' => 'iso',
        ]);

        // Use script embed by default for proper dropdown overflow
        return $this->generate_widget_script($attributes);
    }

    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        // Track usage for review notice timing (only on frontend, not in admin)
        if (!is_admin()) {
            $this->track_usage();
        }

        $defaults = $this->defaults;
        $saved_options = get_option('cwc_widget_options', []);

        // Merge defaults with saved options
        $defaults = array_merge($defaults, $saved_options);

        // Parse shortcode attributes
        $atts = shortcode_atts([
            'style' => $defaults['style'],
            'theme' => $defaults['theme'],
            'accent' => $defaults['accent'],
            'from' => $defaults['from'],
            'to' => $defaults['to'],
            'amount' => $defaults['amount'],
            'lang' => $defaults['language'],
            'language' => $defaults['language'],
            'flags' => $defaults['show_flags'] ? '1' : '0',
            'labels' => $defaults['show_labels'] ? '1' : '0',
            'swap' => $defaults['show_swap'] ? '1' : '0',
            'branding' => $defaults['show_branding'] ? '1' : '0',
            'brandinglink' => $defaults['show_branding_link'] ? '1' : '0',
            'lock' => $defaults['lock_currencies'] ? '1' : '0',
            'format' => $defaults['number_format'],
            'decimals' => $defaults['decimals'],
            'chart' => '0',
            'chartperiod' => $defaults['chart_period'],
            'width' => !empty($defaults['custom_width']) ? $defaults['custom_width'] : '',
            'height' => !empty($defaults['custom_height']) ? $defaults['custom_height'] : '',
            'currencies' => '',
            'dropdown' => $defaults['dropdown_currencies'] ?? 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN',
            'display' => $defaults['currency_display'] ?? 'iso', // 'iso' or 'symbol'
            'displaycurrencies' => $defaults['display_currencies'] ?? 'EUR,GBP,JPY',
            'adddropdown' => $defaults['add_dropdown_currencies'] ?? 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN',
            'embed' => 'script', // Default to script embed for proper dropdown overflow
        ], $atts, 'currencywiki_converter');

        // Convert to block-style attributes
        $block_atts = [
            'style' => $atts['style'],
            'theme' => $atts['theme'],
            'accent' => ltrim($atts['accent'], '#'),
            'from' => strtoupper($atts['from']),
            'to' => strtoupper($atts['to']),
            'amount' => $atts['amount'],
            'language' => $atts['lang'] ?: $atts['language'],
            'showFlags' => $atts['flags'] === '1' || $atts['flags'] === 'true',
            'showLabels' => $atts['labels'] === '1' || $atts['labels'] === 'true',
            'showSwap' => $atts['swap'] === '1' || $atts['swap'] === 'true',
            'showBranding' => $atts['branding'] === '1' || $atts['branding'] === 'true',
            'showBrandingLink' => $atts['brandinglink'] === '1' || $atts['brandinglink'] === 'true',
            'lockCurrencies' => $atts['lock'] === '1' || $atts['lock'] === 'true',
            'numberFormat' => $atts['format'],
            'decimals' => intval($atts['decimals']),
            'chart' => $atts['chart'] === '1' || $atts['chart'] === 'true',
            'chartPeriod' => $atts['chartperiod'],
            'width' => $atts['width'],
            'height' => $atts['height'],
            'selectedCurrencies' => $atts['currencies'] ? explode(',', strtoupper($atts['currencies'])) : [],
            'dropdown' => $atts['dropdown'],
            'currencyDisplayMode' => $atts['display'],
            'displayCurrencies' => $atts['displaycurrencies'],
            'addDropdown' => $atts['adddropdown'],
            'embed' => $atts['embed'],
        ];

        // Use script embed by default, iframe only if explicitly requested
        if ($atts['embed'] === 'iframe') {
            return $this->generate_widget_iframe($block_atts);
        }
        return $this->generate_widget_script($block_atts);
    }

    /**
     * Generate widget iframe HTML
     */
    private function generate_widget_iframe($atts) {
        // Get dimensions based on style
        $dimensions = $this->get_style_dimensions($atts['style']);
        $width = !empty($atts['width']) ? $atts['width'] : $dimensions['width'];
        $height = !empty($atts['height']) ? $atts['height'] : $dimensions['height'];

        // Build query params
        $params = [
            'style' => $atts['style'],
            'theme' => $atts['theme'],
            'accent' => ltrim($atts['accent'] ?? '2563eb', '#'),
            'lang' => $atts['language'] ?? 'en',
            'from' => $atts['from'] ?? 'USD',
            'to' => $atts['to'] ?? 'EUR',
            'amount' => $atts['amount'] ?? '1',
            'lock' => ($atts['lockCurrencies'] ?? false) ? '1' : '0',
            'flags' => ($atts['showFlags'] ?? true) ? '1' : '0',
            'labels' => ($atts['showLabels'] ?? true) ? '1' : '0',
            'swap' => ($atts['showSwap'] ?? true) ? '1' : '0',
            'branding' => ($atts['showBranding'] ?? true) ? '1' : '0',
            'brandinglink' => ($atts['showBrandingLink'] ?? true) ? '1' : '0',
            'format' => $atts['numberFormat'] ?? 'auto',
            'decimals' => $atts['decimals'] ?? 2,
            'display' => $atts['currencyDisplayMode'] ?? 'iso',
        ];

        // Add chart params if applicable (always for chart styles)
        $chartStyles = ['mini-chart', 'rates-viewer', 'rates-viewer-compact'];
        if (!empty($atts['chart']) || in_array($atts['style'], $chartStyles)) {
            $params['chart'] = '1';
            $params['chartperiod'] = $atts['chartPeriod'] ?? '7d';
        }

        // Add currencies for multi-currency widgets
        if (!empty($atts['selectedCurrencies']) && is_array($atts['selectedCurrencies'])) {
            $params['currencies'] = implode(',', $atts['selectedCurrencies']);
        } elseif (!empty($atts['displayCurrencies'])) {
            $params['currencies'] = $atts['displayCurrencies'];
        }

        // Add dropdown currencies
        if (!empty($atts['dropdown'])) {
            $params['dropdown'] = $atts['dropdown'];
        }

        // Add "Add Currency" dropdown currencies for multi-expandable style
        if (!empty($atts['addDropdown'])) {
            $params['adddropdown'] = $atts['addDropdown'];
        }

        // Add source tracking for analytics
        $params['source'] = 'wp';

        $query_string = http_build_query($params);
        $iframe_url = 'https://widget.currency.wiki/v3/embed?' . $query_string;

        // Generate unique ID
        $widget_id = 'cwc-widget-' . wp_generate_uuid4();

        $html = sprintf(
            '<div class="currency-converter-widget-wrapper" id="%s">
                <iframe
                    src="%s"
                    width="%s"
                    height="%s"
                    frameborder="0"
                    scrolling="no"
                    style="border-radius: 12px; overflow: hidden; max-width: 100%%;"
                    title="%s"
                    loading="lazy"
                ></iframe>
            </div>',
            esc_attr($widget_id),
            esc_url($iframe_url),
            esc_attr($width),
            esc_attr($height),
            esc_attr__('Currency Converter Widget', 'currency-converter-widget')
        );

        return $html;
    }

    /**
     * Generate widget script embed HTML (preferred - allows dropdown overflow)
     */
    private function generate_widget_script($atts) {
        // Get dimensions based on style
        $dimensions = $this->get_style_dimensions($atts['style']);
        $width = !empty($atts['width']) ? $atts['width'] : $dimensions['width'];
        $height = !empty($atts['height']) ? $atts['height'] : $dimensions['height'];

        // Build query params
        $params = [
            'style' => $atts['style'],
            'theme' => $atts['theme'],
            'accent' => ltrim($atts['accent'] ?? '2563eb', '#'),
            'lang' => $atts['language'] ?? 'en',
            'from' => $atts['from'] ?? 'USD',
            'to' => $atts['to'] ?? 'EUR',
            'amount' => $atts['amount'] ?? '1',
            'lock' => ($atts['lockCurrencies'] ?? false) ? '1' : '0',
            'flags' => ($atts['showFlags'] ?? true) ? '1' : '0',
            'labels' => ($atts['showLabels'] ?? true) ? '1' : '0',
            'swap' => ($atts['showSwap'] ?? true) ? '1' : '0',
            'branding' => ($atts['showBranding'] ?? true) ? '1' : '0',
            'brandinglink' => ($atts['showBrandingLink'] ?? true) ? '1' : '0',
            'format' => $atts['numberFormat'] ?? 'auto',
            'decimals' => $atts['decimals'] ?? 2,
            'display' => $atts['currencyDisplayMode'] ?? 'iso',
            'width' => $width,
            'height' => $height,
        ];

        // Add dropdown param
        if (!empty($atts['dropdown'])) {
            $params['dropdown'] = $atts['dropdown'];
        }

        // Add display currencies for multi-converter styles
        if (!empty($atts['displayCurrencies'])) {
            $params['currencies'] = $atts['displayCurrencies'];
        }

        // Add "Add Currency" dropdown currencies for multi-expandable style
        if (!empty($atts['addDropdown'])) {
            $params['adddropdown'] = $atts['addDropdown'];
        }

        // Add chart params if applicable (always for chart styles)
        $chartStyles = ['mini-chart', 'rates-viewer', 'rates-viewer-compact'];
        if (!empty($atts['chart']) || in_array($atts['style'], $chartStyles)) {
            $params['chart'] = '1';
            $params['chartperiod'] = $atts['chartPeriod'] ?? '7d';
        }

        // Add currencies for multi-currency widgets
        if (!empty($atts['selectedCurrencies']) && is_array($atts['selectedCurrencies'])) {
            $params['currencies'] = implode(',', $atts['selectedCurrencies']);
        }

        // Generate unique container ID
        $widget_id = 'cwc-' . substr(md5(uniqid()), 0, 8);
        $params['container'] = $widget_id;

        // Add source tracking for analytics
        $params['source'] = 'wp';

        $query_string = http_build_query($params);
        $script_url = 'https://widget.currency.wiki/v3/script.js?' . $query_string;

        $html = sprintf(
            '<div class="currency-converter-widget-wrapper">
                <div id="%s"></div>
                <script src="%s"></script>
            </div>',
            esc_attr($widget_id),
            esc_url($script_url)
        );

        return $html;
    }

    /**
     * Get style dimensions
     */
    private function get_style_dimensions($style) {
        $dimensions = [
            'mini' => ['width' => '250', 'height' => '140'],
            'square' => ['width' => '250', 'height' => '250'],
            'tall' => ['width' => '200', 'height' => '280'],
            'inline' => ['width' => '480', 'height' => '56'],
            'compact' => ['width' => '280', 'height' => '200'],
            'mini-chart' => ['width' => '250', 'height' => '260'],
            'multi-expandable' => ['width' => '300', 'height' => '400'],
            'multi-fixed' => ['width' => '300', 'height' => '340'],
            'rates-compact' => ['width' => '220', 'height' => '300'],
            'rates-viewer' => ['width' => '300', 'height' => '500'],
            'rates-viewer-compact' => ['width' => '300', 'height' => '400'],
        ];

        return $dimensions[$style] ?? ['width' => '300', 'height' => '180'];
    }

    /**
     * Add admin menu - Top-level dashboard like Google Site Kit
     */
    public function add_admin_menu() {
        // Currency.Wiki brand logo SVG for sidebar menu (uses currentColor to inherit WordPress admin colors)
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 1440" fill="currentColor"><path d="M1375.63 731.24c-19.65-63.67-127.53-92.45-278.84-85.18-10.55-114.74-49.02-299.27-102.56-461.98-233.4 108.87-433.17 98.66-637.71 50.53 153.37 193.34 291.21 483.5 316.17 821.94 94.98-20.82 291.22-71.18 421.57-154.97 8.63-77.27 11.48-158.07 2.62-254.86 115.76-2.58 196 20.32 210.91 68.69 17.27 56.06-57.01 133.17-184.78 205.21-.03.03-.09.06-.12.1-.05.02-.17.09-.32.17-.38.2-.99.55-1.89 1.04-.47.3-.93.56-1.43.79-9.44 5.32-19.19 10.55-29.21 15.78.02-.2.05-.41.09-.61-77.57 39.26-280.4 133.81-477.61 157.67-.09 0-.14.02-.24.02-195.19 32.3-344.09 14.45-364.99-53.29-12.97-42.2 25.96-96.34 100.51-151.27 19.93-14.7 42.41-29.45 67.13-44.05-24.87 14.06-48.03 28.37-69.24 42.77-106.33 72.16-164.03 146.6-146.3 204.07 33.44 108.7 323.94 115.58 648.76 15.47 324.83-100.12 560.99-269.36 527.48-378.04zM688 510.44l-24.62 12.95-12.55-23.86c-17.23 8.09-35.93 12.17-48.37 11.65l-7.92-33.34c13.59.23 31.18-2.29 47.5-10.87 14.31-7.53 21.2-18.21 15.91-28.26-5.02-9.54-16.22-11.35-38.11-7.85-31.63 5.1-56.56 2.17-69.38-22.18-11.63-22.1-5.17-47.63 18.93-67.03l-12.55-23.86 24.62-12.95 11.62 22.1c17.23-8.1 30.38-10.53 40.79-10.87l7.64 32.22c-7.99.67-22.58.96-40.67 10.48-16.32 8.6-17.9 18.4-14.19 25.43 4.35 8.29 15.91 8.93 41.5 5.74 35.43-5.17 54.75 2.31 66.64 24.91 11.76 22.35 5.97 49.76-20.27 69.98L688 510.44zM911.41 830l11.44 57.29-45.67 9.1-10.74-53.98c-35.33 5.85-72.38 2.82-95.06-6.6l4.59-55.27c24.25 8.45 57.8 13.95 91.72 7.21 35.09-7 55.58-28.99 50.27-55.74-4.95-25.06-27.67-37.3-71.88-44.17-62.65-9.48-105.83-28.67-116.11-80.47-9.56-47.88 16.58-91.25 70.82-112.46l-10.87-54.56 45.67-9.12 10.3 51.81c35.26-5.85 60.86-2.27 80.44 3.1l-4.82 54.2c-14.58-3.46-41.6-11.37-79.47-3.85-38.97 7.73-48.92 30.56-45.14 49.45 4.56 22.86 27.09 31.7 78.19 41.19 67.03 10.97 100.83 35.51 110.67 85.07 9.44 47.27-15.1 95.59-74.35 117.8z"/><path d="M632.42 1070.23c-9.69-339.25-197.08-613.88-197.08-613.88l-267.96 90.84C341.64 666.36 483.8 798.83 631.92 1071.6c-.54-1.61-1.1-3.21-1.66-4.82 1.38 2.24 2.16 3.45 2.16 3.45zM434.69 630.29c-17.39 10.96-37.82 13.93-56.03 7.07-8.59-3-16.95-8.39-24.47-16.79l-11.53 7.27-7.95-12.59 9.76-6.16c-.55-.89-1.23-1.95-1.9-3.02-1.13-1.77-2.07-3.65-3.18-5.44l-9.93 6.26-7.95-12.59 11.88-7.49c-3.73-11.03-4.62-21.88-3.09-32.02 2.96-17.73 12.89-33.41 29.56-43.94 10.82-6.82 21.78-10.27 29.95-11.45l7.79 23.34c-5.84.96-14.57 3.25-22.37 8.16-8.51 5.38-14.52 13.12-15.77 23.33-.69 4.41-.06 9.72 1.6 15.11l44.17-27.86 7.94 12.59-46.82 29.53c.94 1.89 2.17 3.84 3.3 5.62.67 1.07 1.11 1.77 1.78 2.83l47-29.65 7.94 12.59-44.52 28.08c4.6 4.54 9.13 7.37 13.68 8.72 9.71 2.81 19.72.21 28.59-5.39 8.16-5.15 14.82-13.05 17.31-17.36l16.68 17.06c-3.9 7.18-12.06 17.03-23.42 24.19z"/><path d="M235.02 656.67 105.78 766.52c96.86 42.15 176.05 84.31 239.91 123.22.71.42 1.41.85 2.11 1.28 164.61 100.63 226.47 179.21 226.47 179.21-74.31-206.78-339.25-413.56-339.25-413.56zm54.43 177.16-14.81-7.78c1.06-11.81-3.71-28.07-16.14-34.58-2.57-1.35-4.86-2.32-7.62-3.29l-9.4 17.93-18.84-9.88 7.68-14.63c-5.06-1.48-10.65-3.72-15.59-6.3-23.05-12.09-29.41-36.88-16.55-61.4 5.27-10.07 11.4-16.41 15.71-19.28l18.64 15.6c-3.84 2.42-7.8 6.87-11.35 13.63-6.8 12.99-.5 21.43 8.28 26.04 4.75 2.49 9.43 4.25 14.58 5.55l13.15-25.07 18.84 9.88-11.89 22.68c4.67 2.21 8.68 4.79 12.13 8.46 3.7 4.04 6.75 9.38 8.29 15.79l.37.18 25.61-48.85 22.68 11.89-43.77 83.43z"/></svg>');

        // Add top-level menu page (single page - no submenus needed)
        add_menu_page(
            __('Currency Kit', 'currency-converter-widget'),           // Page title
            __('Currency Kit', 'currency-converter-widget'),           // Menu title
            'manage_options',                                         // Capability
            'currency-converter-widget-kit',                            // Menu slug
            [$this, 'render_admin_page'],                            // Callback function
            $icon_svg,                                                // Icon
            30                                                        // Position (after Comments)
        );
    }

    /**
     * Render Help & Support page (kept for backwards compatibility)
     */
    public function render_help_page() {
        ?>
        <div class="cwc-admin-wrap">
            <!-- Header -->
            <div class="cwc-admin-header">
                <div class="cwc-header-content">
                    <div class="cwc-logo">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="40" height="40" rx="10" fill="rgba(255,255,255,0.15)"/>
                            <circle cx="20" cy="20" r="12" stroke="white" stroke-width="2" fill="none"/>
                            <path d="M24 14h-8a3 3 0 1 0 0 6h4a3 3 0 1 1 0 6h-8" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <path d="M20 10v4M20 26v4" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="cwc-header-text">
                        <h1><?php _e('Help & Support', 'currency-converter-widget'); ?></h1>
                        <p><?php _e('Get help with Currency Kit', 'currency-converter-widget'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Help Content -->
            <div class="cwc-card">
                <div class="cwc-card-header">
                    <h2><span class="dashicons dashicons-book"></span> <?php _e('Documentation', 'currency-converter-widget'); ?></h2>
                </div>
                <div class="cwc-card-body">
                    <p><?php _e('Learn how to use Currency Kit to add beautiful currency converter widgets to your WordPress site.', 'currency-converter-widget'); ?></p>
                    <ul class="cwc-steps">
                        <li><?php _e('Use the <strong>[currencywiki_converter]</strong> shortcode to add widgets to posts and pages', 'currency-converter-widget'); ?></li>
                        <li><?php _e('Customize the widget style, theme, and colors from the Dashboard', 'currency-converter-widget'); ?></li>
                        <li><?php _e('Use the Currency Kit block in the Gutenberg editor for visual editing', 'currency-converter-widget'); ?></li>
                        <li><?php _e('Copy the embed code to use on external websites', 'currency-converter-widget'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="cwc-card">
                <div class="cwc-card-header">
                    <h2><span class="dashicons dashicons-editor-help"></span> <?php _e('Frequently Asked Questions', 'currency-converter-widget'); ?></h2>
                </div>
                <div class="cwc-card-body">
                    <div style="margin-bottom: 16px;">
                        <strong><?php _e('How do I add a currency converter to my page?', 'currency-converter-widget'); ?></strong>
                        <p style="color: #6b7280; margin: 4px 0 0;"><?php _e('Use the shortcode [currencywiki_converter] or add the Currency Kit block in the Gutenberg editor.', 'currency-converter-widget'); ?></p>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <strong><?php _e('Can I customize the widget colors?', 'currency-converter-widget'); ?></strong>
                        <p style="color: #6b7280; margin: 4px 0 0;"><?php _e('Yes! Go to Currency Kit → Dashboard and select your preferred accent color from the color palette.', 'currency-converter-widget'); ?></p>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <strong><?php _e('How often are exchange rates updated?', 'currency-converter-widget'); ?></strong>
                        <p style="color: #6b7280; margin: 4px 0 0;"><?php _e('Exchange rates are updated in real-time from Currency.Wiki API.', 'currency-converter-widget'); ?></p>
                    </div>
                </div>
            </div>

            <div class="cwc-card cwc-support-card">
                <div class="cwc-card-body">
                    <div class="cwc-support-content">
                        <span class="dashicons dashicons-sos"></span>
                        <div>
                            <h3><?php _e('Need More Help?', 'currency-converter-widget'); ?></h3>
                            <p><?php _e('Contact our support team or visit our documentation for more information.', 'currency-converter-widget'); ?></p>
                            <a href="https://currency.wiki/support" target="_blank" class="button"><?php _e('Get Support', 'currency-converter-widget'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('cwc_settings', 'cwc_widget_options', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_options'],
            'default' => $this->defaults,
        ]);
    }

    /**
     * Sanitize options
     */
    public function sanitize_options($input) {
        $sanitized = [];

        $sanitized['style'] = sanitize_text_field($input['style'] ?? 'compact');
        $sanitized['theme'] = sanitize_text_field($input['theme'] ?? 'auto');
        $sanitized['accent'] = sanitize_hex_color_no_hash($input['accent'] ?? '2563eb');
        $sanitized['from'] = strtoupper(sanitize_text_field($input['from'] ?? 'USD'));
        $sanitized['to'] = strtoupper(sanitize_text_field($input['to'] ?? 'EUR'));
        $sanitized['amount'] = sanitize_text_field($input['amount'] ?? '1');
        $sanitized['language'] = sanitize_text_field($input['language'] ?? 'en');
        $sanitized['show_flags'] = !empty($input['show_flags']);
        $sanitized['show_labels'] = !empty($input['show_labels']);
        $sanitized['show_swap'] = !empty($input['show_swap']);
        $sanitized['show_branding'] = !empty($input['show_branding']);
        $sanitized['show_branding_link'] = !empty($input['show_branding_link']);
        $sanitized['lock_currencies'] = !empty($input['lock_currencies']);
        $sanitized['number_format'] = sanitize_text_field($input['number_format'] ?? 'auto');
        $sanitized['decimals'] = absint($input['decimals'] ?? 2);
        $sanitized['chart_period'] = sanitize_text_field($input['chart_period'] ?? '7d');
        $sanitized['currency_display'] = in_array($input['currency_display'] ?? 'iso', ['iso', 'symbol']) ? $input['currency_display'] : 'iso';

        // Dropdown presets and currencies
        $sanitized['dropdown_preset'] = sanitize_text_field($input['dropdown_preset'] ?? 'top10');
        $sanitized['dropdown_currencies'] = sanitize_text_field($input['dropdown_currencies'] ?? 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN');

        // Display currencies (for multi-converter style)
        $sanitized['display_preset'] = sanitize_text_field($input['display_preset'] ?? 'top3');
        $sanitized['display_currencies'] = sanitize_text_field($input['display_currencies'] ?? 'EUR,GBP,JPY');

        // Add Currency Dropdown (for multi-expandable style)
        $sanitized['add_dropdown_preset'] = sanitize_text_field($input['add_dropdown_preset'] ?? 'top10');
        $sanitized['add_dropdown_currencies'] = sanitize_text_field($input['add_dropdown_currencies'] ?? 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN');

        // Size and custom dimensions (admin JS syncs preset dimensions to custom_width/custom_height)
        $sanitized['size'] = sanitize_text_field($input['size'] ?? '');
        $sanitized['custom_width'] = absint($input['custom_width'] ?? 0) ?: '';
        $sanitized['custom_height'] = absint($input['custom_height'] ?? 0) ?: '';

        return $sanitized;
    }

    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=currency-converter-widget-kit'),
            __('Settings', 'currency-converter-widget')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Add settings-updated parameter to redirect URL after saving settings
     */
    public function add_settings_updated_param($location) {
        // Only modify redirects from options.php back to our admin page
        if (strpos($location, 'currency-converter-widget-kit') !== false &&
            isset($_POST['option_page']) &&
            $_POST['option_page'] === 'cwc_settings') {

            // Add settings-updated=true if not already present
            if (strpos($location, 'settings-updated') === false) {
                $location = add_query_arg('settings-updated', 'true', $location);
            }
        }
        return $location;
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our Currency Kit dashboard page
        if ($hook !== 'toplevel_page_currency-converter-widget-kit') {
            return;
        }

        wp_enqueue_style(
            'cwc-admin-styles',
            CWC_PLUGIN_URL . 'assets/css/admin.css',
            [],
            CWC_VERSION
        );

        wp_enqueue_script(
            'cwc-admin-script',
            CWC_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            CWC_VERSION,
            true
        );

        wp_localize_script('cwc-admin-script', 'cwcAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cwc_admin_nonce'),
            'currencies' => $this->get_currencies(),
            'widgetStyles' => $this->get_widget_styles(),
            'previewUrl' => 'https://widget.currency.wiki/v3/embed',
        ]);
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'cwc-frontend-styles',
            CWC_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            CWC_VERSION
        );
    }

    /**
     * AJAX handler for dismissing review notice
     */
    public function ajax_dismiss_review() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cwc_admin_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
            return;
        }

        $action = sanitize_text_field($_POST['dismiss_action'] ?? '');
        $review_status = get_option('cwc_review_status', [
            'dismissed' => false,
            'remind_later' => false,
            'remind_date' => 0,
        ]);

        switch ($action) {
            case 'rated':
            case 'dismiss':
                // Permanently dismiss
                $review_status['dismissed'] = true;
                $review_status['remind_later'] = false;
                break;

            case 'remind':
                // Remind in 14 days
                $review_status['remind_later'] = true;
                $review_status['remind_date'] = time() + (14 * DAY_IN_SECONDS);
                break;
        }

        update_option('cwc_review_status', $review_status);
        wp_send_json_success(['message' => 'Review notice dismissed']);
    }

    /**
     * Track widget usage for review notice timing
     */
    private function track_usage() {
        $usage_count = get_option('cwc_usage_count', 0);
        update_option('cwc_usage_count', $usage_count + 1);
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        include CWC_PLUGIN_DIR . 'templates/admin-page.php';
    }

    /**
     * Get currencies list
     */
    private function get_currencies() {
        return [
            ['iso' => 'USD', 'name' => 'US Dollar', 'flag' => 'us'],
            ['iso' => 'EUR', 'name' => 'Euro', 'flag' => 'eu'],
            ['iso' => 'GBP', 'name' => 'British Pound', 'flag' => 'gb'],
            ['iso' => 'JPY', 'name' => 'Japanese Yen', 'flag' => 'jp'],
            ['iso' => 'CAD', 'name' => 'Canadian Dollar', 'flag' => 'ca'],
            ['iso' => 'AUD', 'name' => 'Australian Dollar', 'flag' => 'au'],
            ['iso' => 'CHF', 'name' => 'Swiss Franc', 'flag' => 'ch'],
            ['iso' => 'CNY', 'name' => 'Chinese Yuan', 'flag' => 'cn'],
            ['iso' => 'INR', 'name' => 'Indian Rupee', 'flag' => 'in'],
            ['iso' => 'MXN', 'name' => 'Mexican Peso', 'flag' => 'mx'],
            ['iso' => 'BRL', 'name' => 'Brazilian Real', 'flag' => 'br'],
            ['iso' => 'KRW', 'name' => 'South Korean Won', 'flag' => 'kr'],
            ['iso' => 'SGD', 'name' => 'Singapore Dollar', 'flag' => 'sg'],
            ['iso' => 'HKD', 'name' => 'Hong Kong Dollar', 'flag' => 'hk'],
            ['iso' => 'NZD', 'name' => 'New Zealand Dollar', 'flag' => 'nz'],
            ['iso' => 'SEK', 'name' => 'Swedish Krona', 'flag' => 'se'],
            ['iso' => 'NOK', 'name' => 'Norwegian Krone', 'flag' => 'no'],
            ['iso' => 'DKK', 'name' => 'Danish Krone', 'flag' => 'dk'],
            ['iso' => 'ZAR', 'name' => 'South African Rand', 'flag' => 'za'],
            ['iso' => 'THB', 'name' => 'Thai Baht', 'flag' => 'th'],
            ['iso' => 'PLN', 'name' => 'Polish Zloty', 'flag' => 'pl'],
            ['iso' => 'RUB', 'name' => 'Russian Ruble', 'flag' => 'ru'],
            ['iso' => 'TRY', 'name' => 'Turkish Lira', 'flag' => 'tr'],
            ['iso' => 'ILS', 'name' => 'Israeli Shekel', 'flag' => 'il'],
            ['iso' => 'AED', 'name' => 'UAE Dirham', 'flag' => 'ae'],
            ['iso' => 'SAR', 'name' => 'Saudi Riyal', 'flag' => 'sa'],
            ['iso' => 'PHP', 'name' => 'Philippine Peso', 'flag' => 'ph'],
            ['iso' => 'IDR', 'name' => 'Indonesian Rupiah', 'flag' => 'id'],
            ['iso' => 'MYR', 'name' => 'Malaysian Ringgit', 'flag' => 'my'],
            ['iso' => 'VND', 'name' => 'Vietnamese Dong', 'flag' => 'vn'],
        ];
    }

    /**
     * Get widget styles
     */
    private function get_widget_styles() {
        return [
            'mini' => ['name' => 'Mini', 'description' => 'Compact single-line widget (250x140)'],
            'square' => ['name' => 'Square', 'description' => '1:1 ratio widget (250x250)'],
            'tall' => ['name' => 'Tall Sidebar', 'description' => 'Vertical sidebar layout (200x280)'],
            'inline' => ['name' => 'Inline', 'description' => 'Horizontal inline embed (480x56)'],
            'compact' => ['name' => 'Compact', 'description' => 'Small footprint widget (280x200)'],
            'mini-chart' => ['name' => 'Mini + Chart', 'description' => 'Compact with sparkline chart (250x260)'],
            'multi-expandable' => ['name' => 'Multi-Converter', 'description' => 'Multiple currencies, expandable (300x400)'],
            'multi-fixed' => ['name' => 'Exchange Rates', 'description' => 'Multiple currencies, fixed list (300x340)'],
            'rates-compact' => ['name' => 'Rates Compact', 'description' => 'Condensed exchange rates (220x300)'],
            'rates-viewer' => ['name' => 'Display Charts', 'description' => 'Full chart view (300x500)'],
            'rates-viewer-compact' => ['name' => 'Charts Compact', 'description' => 'Compact chart view (300x400)'],
        ];
    }

    /**
     * Get languages
     */
    private function get_languages() {
        return [
            'af' => 'Afrikaans',
            'am' => 'Amharic',
            'ar' => 'Arabic',
            'bn' => 'Bengali',
            'bs' => 'Bosnian',
            'bg' => 'Bulgarian',
            'my' => 'Burmese',
            'zh-CN' => 'Chinese (Simplified)',
            'zh-TW' => 'Chinese (Traditional)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'et' => 'Estonian',
            'fil' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'ka' => 'Georgian',
            'de' => 'German',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'is' => 'Icelandic',
            'id' => 'Indonesian',
            'ga' => 'Irish',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'kk' => 'Kazakh',
            'km' => 'Khmer',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'mt' => 'Maltese',
            'mn' => 'Mongolian',
            'ne' => 'Nepali',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt-BR' => 'Portuguese (Brazil)',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'si' => 'Sinhala',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'sw' => 'Swahili',
            'sv' => 'Swedish',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'hy' => 'Armenian',
        ];
    }
}

// Initialize plugin
function cwc_init() {
    return Currency_Wiki_Converter::get_instance();
}
add_action('plugins_loaded', 'cwc_init');

/*******************************************************************************
 * LEGACY V2 BACKWARDS COMPATIBILITY SUPPORT
 *
 * This section provides backwards compatibility for users upgrading from V2.
 * It supports the old [currency_bcc] shortcode and bcc_currency_widget_class
 * sidebar widget that were used in plugin versions prior to 3.0.0.
 *
 * The legacy widget embeds point to https://currency.wiki/widget/embed which
 * must remain functional on the server side for these to work.
 *
 * TODO: REMOVE THIS SECTION IN A FUTURE VERSION (e.g., v5.0.0)
 * Once sufficient time has passed for users to migrate to the new V4 shortcodes
 * and blocks, this entire section can be removed. Items to remove:
 *
 * 1. The [currency_bcc] shortcode registration and handler
 * 2. The bcc_currency_widget_class WP_Widget class
 * 3. The register_bcc_currency_widget() function and widgets_init hook
 * 4. The cwc_legacy_bcc_shortcode() function
 *
 * Migration guide for users:
 * - Replace [currency_bcc] with [currencywiki_converter] or [currencywiki]
 * - Replace sidebar widget with the new Gutenberg block
 * - Update attribute names (see mapping in cwc_legacy_bcc_shortcode)
 *
 * @since 4.0.0
 * @deprecated Will be removed in v5.0.0
 ******************************************************************************/

/**
 * Legacy V2 shortcode handler for [currency_bcc]
 *
 * Maps old V2 shortcode attributes to new V3 widget via classic embed URL.
 * This ensures existing [currency_bcc] shortcodes continue to work after upgrade.
 *
 * V2 Attribute Mapping:
 * - type   => size type (auto, fix, custom)
 * - a      => amount
 * - f      => from currency
 * - t      => to currency
 * - lang   => language
 * - w      => width
 * - h      => height
 * - c      => background/style color (hex without #)
 * - fc     => font color (hex without #)
 * - g      => gradient (on/off)
 * - sh     => shadow (on/off)
 * - b      => border (on/off)
 * - fl     => show flag (on/off)
 * - p      => display mode (c=converter, e=exchange rates)
 * - cs     => currencies list
 * - s      => symbol position (off, left, right)
 * - mf     => monetary format (1-4)
 * - df     => decimal format (0-6)
 * - d      => date format (1-3)
 * - su     => support/branding (on/off)
 *
 * @since 4.0.0
 * @deprecated Use [currencywiki_converter] instead
 */
function cwc_legacy_bcc_shortcode($atts) {
    // Normalize attribute keys to lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    // Parse with V2 defaults
    $atts = shortcode_atts([
        'type' => 'fix',
        'a'    => '1',
        'f'    => 'USD',
        't'    => 'EUR',
        'lang' => 'en-US',
        'w'    => 200,
        'h'    => 350,
        'c'    => '4f7ccb',
        'fc'   => 'FFFFFF',
        'g'    => 'on',
        'sh'   => 'on',
        'b'    => 'on',
        'fl'   => 'on',
        'p'    => 'c',
        'cs'   => '',
        's'    => 'off',
        'mf'   => '1',
        'df'   => '2',
        'd'    => '1',
        'su'   => 'on',
    ], $atts, 'currency_bcc');

    // Sanitize V2 attributes (only those that have V3 equivalents)
    $amount = sanitize_text_field($atts['a']);
    $from = strtoupper(sanitize_text_field($atts['f']));
    $to = strtoupper(sanitize_text_field($atts['t']));
    $lang = sanitize_text_field($atts['lang']);
    $flag = sanitize_text_field($atts['fl']);
    $display = sanitize_text_field($atts['p']);
    $currencies = sanitize_text_field($atts['cs']);
    $monetary_format = sanitize_text_field($atts['mf']);
    $decimal_format = sanitize_text_field($atts['df']);
    $support = sanitize_text_field($atts['su']);

    // --- Map V2 params to V3 ---

    // p=c (converter) → style=tall, p=e (exchange rates) → style=rates-compact
    $style = ($display === 'e') ? 'rates-compact' : 'tall';

    // V3 dimensions for the chosen style
    $style_dimensions = [
        'tall'          => ['width' => '200', 'height' => '280'],
        'rates-compact' => ['width' => '220', 'height' => '300'],
    ];
    $dims = $style_dimensions[$style];

    // Normalize lang: "en-US" → "en", "pt-BR" → "pt-br", "-1" → "en"
    if (empty($lang) || $lang === '-1') {
        $lang = 'en';
    } else {
        $lang = strtolower($lang);
        // Strip region for English (en-us, en-gb → en)
        if (strpos($lang, 'en-') === 0) {
            $lang = 'en';
        }
    }

    // Map V2 monetary format codes to V3 format values
    $format_map = [
        '1' => 'en-US',   // 1,000.00 (US)
        '2' => 'de-DE',   // 1.000,00 (European)
        '3' => 'de-CH',   // 1'000.00 (Swiss)
        '4' => 'fr-FR',   // 1 000,00 (French)
    ];
    $format = isset($format_map[$monetary_format]) ? $format_map[$monetary_format] : 'auto';

    // Build V3 query params
    // Note: V2 'c' was a background color (e.g. ffffff = white bg). V3 'accent' is used
    // for result text and interactive elements. Mapping bg→accent causes invisible text
    // (white accent on white bg), so we always use the V3 default accent color.
    $params = [
        'style'    => $style,
        'theme'    => 'auto',
        'accent'   => '2563eb',
        'lang'     => $lang,
        'from'     => $from,
        'to'       => $to,
        'amount'   => $amount,
        'flags'    => ($flag === 'off') ? '0' : '1',
        'labels'   => '1',
        'swap'     => '1',
        'branding' => ($support === 'off') ? '0' : '1',
        'brandinglink' => '1',
        'format'   => $format,
        'decimals' => absint($decimal_format),
        'display'  => 'iso',
        'width'    => $dims['width'],
        'height'   => $dims['height'],
        'source'   => 'wp-legacy',
    ];

    // Map currencies list for rates-compact style
    if (!empty($currencies) && $style === 'rates-compact') {
        $params['currencies'] = strtoupper($currencies);
    }

    // Generate unique container ID (V3 pattern)
    $widget_id = 'cwc-' . substr(md5(uniqid()), 0, 8);
    $params['container'] = $widget_id;

    $query_string = http_build_query($params);
    $script_url = 'https://widget.currency.wiki/v3/script.js?' . $query_string;

    // Legacy wrapper includes overflow:visible so WordPress theme containers
    // don't clip the V3 widget's box shadow, and padding for breathing room.
    $html = sprintf(
        '<div class="currency-converter-widget-wrapper cwc-legacy-wrapper" style="overflow:visible;padding:4px;">
            <div id="%s"></div>
            <script src="%s"></script>
        </div>',
        esc_attr($widget_id),
        esc_url($script_url)
    );

    return $html;
}

// Register legacy [currency_bcc] shortcode
add_shortcode('currency_bcc', 'cwc_legacy_bcc_shortcode');

/**
 * Legacy V2 Sidebar Widget Class
 *
 * Provides backwards compatibility for existing sidebar widgets created with V2.
 * Users who had the "Currency Converter Widget" in their sidebar will continue
 * to see their widgets after upgrading to V3.
 *
 * @since 4.0.0
 * @deprecated Use the Gutenberg block instead
 */
class bcc_currency_widget_class extends WP_Widget {

    /**
     * Constructor - registers the widget
     */
    public function __construct() {
        $widget_options = [
            'classname'   => 'bcc_currency_widget',
            'description' => __('Display currency converter on your sidebar. (Legacy V2 widget - consider using the Gutenberg block instead)', 'currency-converter-widget'),
        ];
        parent::__construct(
            'bcc_currency_widget',
            __('Currency Converter Widget (Legacy)', 'currency-converter-widget'),
            $widget_options
        );
    }

    /**
     * Front-end display of the widget
     */
    public function widget($args, $instance) {
        $title = isset($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';

        // Get saved settings with defaults
        $from = isset($instance['from']) ? $instance['from'] : 'USD';
        $to = isset($instance['to']) ? $instance['to'] : 'EUR';
        $amount = isset($instance['amount']) ? $instance['amount'] : 1;
        $lang = isset($instance['lang']) ? $instance['lang'] : 'en-US';
        $size = isset($instance['size']) ? $instance['size'] : 'fix';
        $width = isset($instance['width']) ? $instance['width'] : 200;
        $height = isset($instance['height']) ? $instance['height'] : 350;
        $font_color = isset($instance['font_color']) ? str_replace('#', '', $instance['font_color']) : 'FFFFFF';
        $style = isset($instance['style']) ? str_replace('#', '', $instance['style']) : '4f7ccb';
        $gradient = isset($instance['gradient']) ? $instance['gradient'] : 'on';
        $shadow = isset($instance['shadow']) ? $instance['shadow'] : 'on';
        $border = isset($instance['border']) ? $instance['border'] : 'on';
        $flag = isset($instance['flag']) ? $instance['flag'] : 'on';
        $display = isset($instance['display']) ? $instance['display'] : 'c';
        $currencies = isset($instance['currencies']) ? $instance['currencies'] : [];
        $symbol = isset($instance['symbol']) ? $instance['symbol'] : 'off';
        $monetary_format = isset($instance['monetary_format']) ? $instance['monetary_format'] : '1';
        $decimal_format = isset($instance['decimal_format']) ? $instance['decimal_format'] : '2';
        $date_format = isset($instance['date_format']) ? $instance['date_format'] : '1';
        $support = isset($instance['support']) ? $instance['support'] : 'on';

        // Build shortcode attributes
        $shortcode_atts = [
            'type' => $size,
            'a'    => $amount,
            'f'    => $from,
            't'    => $to,
            'lang' => $lang,
            'w'    => $width,
            'h'    => $height,
            'c'    => $style,
            'fc'   => $font_color,
            'g'    => $gradient,
            'sh'   => $shadow,
            'b'    => $border,
            'fl'   => $flag,
            'p'    => $display,
            'cs'   => is_array($currencies) ? implode(',', $currencies) : $currencies,
            's'    => $symbol,
            'mf'   => $monetary_format,
            'df'   => $decimal_format,
            'd'    => $date_format,
            'su'   => $support,
        ];

        // Output widget
        echo $args['before_widget'];

        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        // Use the legacy shortcode handler
        echo cwc_legacy_bcc_shortcode($shortcode_atts);

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form (simplified for legacy support)
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $from = !empty($instance['from']) ? $instance['from'] : 'USD';
        $to = !empty($instance['to']) ? $instance['to'] : 'EUR';
        ?>
        <p style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 15px;">
            <strong><?php _e('Legacy Widget', 'currency-converter-widget'); ?></strong><br>
            <?php _e('This is a legacy V2 widget. For more features and better customization, use the new Currency Converter block in the Gutenberg editor.', 'currency-converter-widget'); ?>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'currency-converter-widget'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('from')); ?>">
                <?php _e('From Currency:', 'currency-converter-widget'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('from')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('from')); ?>"
                   type="text"
                   value="<?php echo esc_attr($from); ?>"
                   placeholder="USD">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('to')); ?>">
                <?php _e('To Currency:', 'currency-converter-widget'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('to')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('to')); ?>"
                   type="text"
                   value="<?php echo esc_attr($to); ?>"
                   placeholder="EUR">
        </p>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=currency-converter-widget-kit')); ?>" class="button">
                <?php _e('Advanced Settings', 'currency-converter-widget'); ?>
            </a>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values
     */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
        $instance['from'] = strtoupper(sanitize_text_field($new_instance['from'] ?? 'USD'));
        $instance['to'] = strtoupper(sanitize_text_field($new_instance['to'] ?? 'EUR'));

        // Preserve other settings from old instance
        $preserve_keys = ['amount', 'lang', 'size', 'width', 'height', 'font_color', 'style',
                          'gradient', 'shadow', 'border', 'flag', 'display', 'currencies',
                          'symbol', 'monetary_format', 'decimal_format', 'date_format', 'support'];

        foreach ($preserve_keys as $key) {
            if (isset($old_instance[$key])) {
                $instance[$key] = $old_instance[$key];
            }
        }

        return $instance;
    }
}

/**
 * Register legacy widget
 */
function register_bcc_currency_widget() {
    register_widget('bcc_currency_widget_class');
}
add_action('widgets_init', 'register_bcc_currency_widget');

/*******************************************************************************
 * END LEGACY V2 BACKWARDS COMPATIBILITY SUPPORT
 ******************************************************************************/

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default options if not exists
    if (!get_option('cwc_widget_options')) {
        add_option('cwc_widget_options', [
            'style' => 'compact',
            'theme' => 'auto',
            'accent' => '2563eb',
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => '1',
            'language' => 'en',
            'show_flags' => true,
            'show_labels' => true,
            'show_swap' => true,
            'show_branding' => true,
            'show_branding_link' => true,
            'lock_currencies' => false,
            'number_format' => 'auto',
            'decimals' => 2,
            'chart_period' => '7d',
        ]);
    }

    // Set activation date for review notice timing
    if (!get_option('cwc_activation_date')) {
        add_option('cwc_activation_date', time());
    }

    // Initialize usage count
    if (!get_option('cwc_usage_count')) {
        add_option('cwc_usage_count', 0);
    }

    // Initialize review status
    if (!get_option('cwc_review_status')) {
        add_option('cwc_review_status', [
            'dismissed' => false,
            'remind_later' => false,
            'remind_date' => 0,
        ]);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Cleanup if needed
});
