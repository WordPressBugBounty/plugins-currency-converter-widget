<?php
/**
 * Admin Settings Page Template
 *
 * @package Currency_Wiki_Converter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('cwc_widget_options', []);

// Get review notice status
$review_status = get_option('cwc_review_status', [
    'dismissed' => false,
    'remind_later' => false,
    'remind_date' => 0,
]);
$activation_date = get_option('cwc_activation_date', time());
$usage_count = get_option('cwc_usage_count', 0);

// Determine if we should show the review notice
$show_review_notice = false;
$days_since_activation = (time() - $activation_date) / DAY_IN_SECONDS;

if (!$review_status['dismissed']) {
    if ($review_status['remind_later'] && time() > $review_status['remind_date']) {
        // Remind later period has passed
        $show_review_notice = true;
    } elseif (!$review_status['remind_later'] && $days_since_activation >= 7 && $usage_count >= 2) {
        // First time showing: 7+ days and 2+ uses
        $show_review_notice = true;
    }
}

$defaults = [
    'style' => 'compact',
    'size' => 'medium',
    'custom_width' => 300,
    'custom_height' => 180,
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
    'currency_display' => 'iso', // 'iso' for ISO codes (USD), 'symbol' for symbols ($)
];
$options = wp_parse_args($options, $defaults);

// Load all currencies from the centralized data file
require_once dirname(__FILE__) . '/../includes/currencies-data.php';
$currencies = cwc_get_all_currencies();
$top10 = cwc_get_top10_currencies();
$top20 = cwc_get_top20_currencies();

$widget_styles = [
    'mini' => ['name' => 'Mini', 'desc' => 'Compact (250x140)', 'icon' => 'minus', 'width' => 250, 'height' => 140],
    'square' => ['name' => 'Square', 'desc' => '1:1 Ratio (250x250)', 'icon' => 'grid-view', 'width' => 250, 'height' => 250],
    'tall' => ['name' => 'Tall Sidebar + Classic', 'desc' => 'Vertical (200x280)', 'icon' => 'align-right', 'width' => 200, 'height' => 280],
    'inline' => ['name' => 'Inline', 'desc' => 'Horizontal (480x56)', 'icon' => 'minus', 'width' => 480, 'height' => 56],
    'compact' => ['name' => 'Compact', 'desc' => 'Standard (280x200)', 'icon' => 'screenoptions', 'width' => 280, 'height' => 200],
    'mini-chart' => ['name' => 'Mini + Chart', 'desc' => 'With Sparkline (250x260)', 'icon' => 'chart-line', 'width' => 250, 'height' => 260],
    'multi-expandable' => ['name' => 'Multi-Converter', 'desc' => 'Multiple Currencies (300x400)', 'icon' => 'list-view', 'width' => 300, 'height' => 400],
    'multi-fixed' => ['name' => 'Exchange Rates + Classic', 'desc' => 'Fixed List (300x340)', 'icon' => 'editor-ul', 'width' => 300, 'height' => 340],
    'rates-compact' => ['name' => 'Exchange Rates - Compact', 'desc' => 'Condensed Rates (220x300)', 'icon' => 'list-view', 'width' => 220, 'height' => 300],
    'rates-viewer' => ['name' => 'Display Charts', 'desc' => 'Full Charts (300x500)', 'icon' => 'chart-area', 'width' => 300, 'height' => 500],
    'rates-viewer-compact' => ['name' => 'Display Charts - Compact', 'desc' => 'Compact Charts (300x400)', 'icon' => 'chart-bar', 'width' => 300, 'height' => 400],
];

// Size options per style
$size_options = [
    'mini' => [
        'small' => ['label' => 'Default (250x140)', 'width' => 250, 'height' => 140],
        'custom' => ['label' => 'Custom', 'width' => 250, 'height' => 140],
    ],
    'square' => [
        'square' => ['label' => 'Default (250x250)', 'width' => 250, 'height' => 250],
        'custom' => ['label' => 'Custom', 'width' => 250, 'height' => 250],
    ],
    'tall' => [
        'small' => ['label' => 'Default (200x280)', 'width' => 200, 'height' => 280],
        'tiny' => ['label' => 'Tiny (180x250)', 'width' => 180, 'height' => 250],
        'custom' => ['label' => 'Custom', 'width' => 200, 'height' => 280],
    ],
    'inline' => [
        'inline' => ['label' => 'Default (480x56)', 'width' => 480, 'height' => 56],
        'wide' => ['label' => 'Wide (600x60)', 'width' => 600, 'height' => 60],
        'custom' => ['label' => 'Custom', 'width' => 480, 'height' => 56],
    ],
    'compact' => [
        'medium' => ['label' => 'Default (280x200)', 'width' => 280, 'height' => 200],
        'small' => ['label' => 'Small (250x180)', 'width' => 250, 'height' => 180],
        'large' => ['label' => 'Large (300x250)', 'width' => 300, 'height' => 250],
        'custom' => ['label' => 'Custom', 'width' => 280, 'height' => 200],
    ],
    'mini-chart' => [
        'small' => ['label' => 'Default (250x260)', 'width' => 250, 'height' => 260],
        'medium' => ['label' => 'Medium (280x280)', 'width' => 280, 'height' => 280],
        'large' => ['label' => 'Large (300x260)', 'width' => 300, 'height' => 260],
        'custom' => ['label' => 'Custom', 'width' => 250, 'height' => 260],
    ],
    'multi-expandable' => [
        'large' => ['label' => 'Default (300x400)', 'width' => 300, 'height' => 400],
        'custom' => ['label' => 'Custom', 'width' => 300, 'height' => 400],
    ],
    'multi-fixed' => [
        'medium' => ['label' => 'Default (300x340)', 'width' => 300, 'height' => 340],
        'large' => ['label' => 'Large (300x420)', 'width' => 300, 'height' => 420],
        'custom' => ['label' => 'Custom', 'width' => 300, 'height' => 340],
    ],
    'rates-compact' => [
        'medium' => ['label' => 'Default (220x300)', 'width' => 220, 'height' => 300],
        'small' => ['label' => 'Compact (200x280)', 'width' => 200, 'height' => 280],
        'custom' => ['label' => 'Custom', 'width' => 220, 'height' => 300],
    ],
    'rates-viewer' => [
        'large' => ['label' => 'Default (300x500)', 'width' => 300, 'height' => 500],
        'medium' => ['label' => 'Compact (200x480)', 'width' => 200, 'height' => 480],
        'custom' => ['label' => 'Custom', 'width' => 300, 'height' => 500],
    ],
    'rates-viewer-compact' => [
        'large' => ['label' => 'Default (300x400)', 'width' => 300, 'height' => 400],
        'custom' => ['label' => 'Custom', 'width' => 300, 'height' => 400],
    ],
];

$languages = [
    'en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German',
    'it' => 'Italian', 'pt' => 'Portuguese', 'ru' => 'Russian', 'zh' => 'Chinese',
    'ja' => 'Japanese', 'ko' => 'Korean', 'ar' => 'Arabic', 'hi' => 'Hindi',
    'tr' => 'Turkish', 'pl' => 'Polish', 'nl' => 'Dutch', 'sv' => 'Swedish',
    'da' => 'Danish', 'no' => 'Norwegian', 'fi' => 'Finnish', 'th' => 'Thai',
    'vi' => 'Vietnamese', 'id' => 'Indonesian', 'ms' => 'Malay',
];

$accent_colors = [
    '2563eb' => 'Blue',
    '7c3aed' => 'Purple',
    '059669' => 'Green',
    'ea580c' => 'Orange',
    'dc2626' => 'Red',
    'db2777' => 'Pink',
    '475569' => 'Slate',
];
?>

<div class="wrap cwc-admin-wrap">
    <!-- Review Notice Bar -->
    <?php if ($show_review_notice) : ?>
    <div class="cwc-review-notice" id="cwc-review-notice">
        <div class="cwc-review-content">
            <span class="cwc-review-icon">⭐</span>
            <div class="cwc-review-text">
                <strong><?php esc_html_e('Enjoying Currency Converter Widget?', 'currency-converter-widget'); ?></strong>
                <span><?php esc_html_e('Your feedback helps us improve! It only takes a moment.', 'currency-converter-widget'); ?></span>
            </div>
        </div>
        <div class="cwc-review-actions">
            <a href="https://wordpress.org/support/plugin/currency-converter-widget/reviews/#new-post" target="_blank" class="cwc-review-btn cwc-review-btn-primary" id="cwc-rate-now">
                <?php esc_html_e('Rate Now', 'currency-converter-widget'); ?> ★
            </a>
            <button type="button" class="cwc-review-btn cwc-review-btn-secondary" id="cwc-remind-later">
                <?php esc_html_e('Maybe Later', 'currency-converter-widget'); ?>
            </button>
            <button type="button" class="cwc-review-dismiss" id="cwc-dismiss-review" title="<?php esc_attr_e('Dismiss', 'currency-converter-widget'); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="cwc-admin-header">
        <div class="cwc-header-content">
            <div class="cwc-logo">
                <svg width="48" height="48" viewBox="0 0 1440 1440" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1375.63 731.24c-19.65-63.67-127.53-92.45-278.84-85.18-10.55-114.74-49.02-299.27-102.56-461.98-233.4 108.87-433.17 98.66-637.71 50.53 153.37 193.34 291.21 483.5 316.17 821.94 94.98-20.82 291.22-71.18 421.57-154.97 8.63-77.27 11.48-158.07 2.62-254.86 115.76-2.58 196 20.32 210.91 68.69 17.27 56.06-57.01 133.17-184.78 205.21-.03.03-.09.06-.12.1-.05.02-.17.09-.32.17-.38.2-.99.55-1.89 1.04-.47.3-.93.56-1.43.79-9.44 5.32-19.19 10.55-29.21 15.78.02-.2.05-.41.09-.61-77.57 39.26-280.4 133.81-477.61 157.67-.09 0-.14.02-.24.02-195.19 32.3-344.09 14.45-364.99-53.29-12.97-42.2 25.96-96.34 100.51-151.27 19.93-14.7 42.41-29.45 67.13-44.05-24.87 14.06-48.03 28.37-69.24 42.77-106.33 72.16-164.03 146.6-146.3 204.07 33.44 108.7 323.94 115.58 648.76 15.47 324.83-100.12 560.99-269.36 527.48-378.04zM688 510.44l-24.62 12.95-12.55-23.86c-17.23 8.09-35.93 12.17-48.37 11.65l-7.92-33.34c13.59.23 31.18-2.29 47.5-10.87 14.31-7.53 21.2-18.21 15.91-28.26-5.02-9.54-16.22-11.35-38.11-7.85-31.63 5.1-56.56 2.17-69.38-22.18-11.63-22.1-5.17-47.63 18.93-67.03l-12.55-23.86 24.62-12.95 11.62 22.1c17.23-8.1 30.38-10.53 40.79-10.87l7.64 32.22c-7.99.67-22.58.96-40.67 10.48-16.32 8.6-17.9 18.4-14.19 25.43 4.35 8.29 15.91 8.93 41.5 5.74 35.43-5.17 54.75 2.31 66.64 24.91 11.76 22.35 5.97 49.76-20.27 69.98L688 510.44zM911.41 830l11.44 57.29-45.67 9.1-10.74-53.98c-35.33 5.85-72.38 2.82-95.06-6.6l4.59-55.27c24.25 8.45 57.8 13.95 91.72 7.21 35.09-7 55.58-28.99 50.27-55.74-4.95-25.06-27.67-37.3-71.88-44.17-62.65-9.48-105.83-28.67-116.11-80.47-9.56-47.88 16.58-91.25 70.82-112.46l-10.87-54.56 45.67-9.12 10.3 51.81c35.26-5.85 60.86-2.27 80.44 3.1l-4.82 54.2c-14.58-3.46-41.6-11.37-79.47-3.85-38.97 7.73-48.92 30.56-45.14 49.45 4.56 22.86 27.09 31.7 78.19 41.19 67.03 10.97 100.83 35.51 110.67 85.07 9.44 47.27-15.1 95.59-74.35 117.8z"/>
                    <path d="M632.42 1070.23c-9.69-339.25-197.08-613.88-197.08-613.88l-267.96 90.84C341.64 666.36 483.8 798.83 631.92 1071.6c-.54-1.61-1.1-3.21-1.66-4.82 1.38 2.24 2.16 3.45 2.16 3.45zM434.69 630.29c-17.39 10.96-37.82 13.93-56.03 7.07-8.59-3-16.95-8.39-24.47-16.79l-11.53 7.27-7.95-12.59 9.76-6.16c-.55-.89-1.23-1.95-1.9-3.02-1.13-1.77-2.07-3.65-3.18-5.44l-9.93 6.26-7.95-12.59 11.88-7.49c-3.73-11.03-4.62-21.88-3.09-32.02 2.96-17.73 12.89-33.41 29.56-43.94 10.82-6.82 21.78-10.27 29.95-11.45l7.79 23.34c-5.84.96-14.57 3.25-22.37 8.16-8.51 5.38-14.52 13.12-15.77 23.33-.69 4.41-.06 9.72 1.6 15.11l44.17-27.86 7.94 12.59-46.82 29.53c.94 1.89 2.17 3.84 3.3 5.62.67 1.07 1.11 1.77 1.78 2.83l47-29.65 7.94 12.59-44.52 28.08c4.6 4.54 9.13 7.37 13.68 8.72 9.71 2.81 19.72.21 28.59-5.39 8.16-5.15 14.82-13.05 17.31-17.36l16.68 17.06c-3.9 7.18-12.06 17.03-23.42 24.19z"/>
                    <path d="M235.02 656.67 105.78 766.52c96.86 42.15 176.05 84.31 239.91 123.22.71.42 1.41.85 2.11 1.28 164.61 100.63 226.47 179.21 226.47 179.21-74.31-206.78-339.25-413.56-339.25-413.56zm54.43 177.16-14.81-7.78c1.06-11.81-3.71-28.07-16.14-34.58-2.57-1.35-4.86-2.32-7.62-3.29l-9.4 17.93-18.84-9.88 7.68-14.63c-5.06-1.48-10.65-3.72-15.59-6.3-23.05-12.09-29.41-36.88-16.55-61.4 5.27-10.07 11.4-16.41 15.71-19.28l18.64 15.6c-3.84 2.42-7.8 6.87-11.35 13.63-6.8 12.99-.5 21.43 8.28 26.04 4.75 2.49 9.43 4.25 14.58 5.55l13.15-25.07 18.84 9.88-11.89 22.68c4.67 2.21 8.68 4.79 12.13 8.46 3.7 4.04 6.75 9.38 8.29 15.79l.37.18 25.61-48.85 22.68 11.89-43.77 83.43z"/>
                </svg>
            </div>
            <div class="cwc-header-text">
                <h1><?php esc_html_e('Currency Converter Widget', 'currency-converter-widget'); ?></h1>
                <p><?php esc_html_e('Beautiful currency converter widgets for WordPress', 'currency-converter-widget'); ?></p>
            </div>
        </div>
        <div class="cwc-header-actions">
            <a href="https://currency.wiki/tools/widget-builder" target="_blank" class="button">
                <span class="dashicons dashicons-external"></span>
                <?php esc_html_e('Advanced Builder', 'currency-converter-widget'); ?>
            </a>
            <a href="https://currency.wiki/tools/widget-builder/doc" target="_blank" class="button">
                <span class="dashicons dashicons-book"></span>
                <?php esc_html_e('Documentation', 'currency-converter-widget'); ?>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="cwc-admin-content">
        <!-- Settings Form -->
        <div class="cwc-settings-panel">
            <form method="post" action="options.php" id="cwc-settings-form">
                <?php settings_fields('cwc_settings'); ?>

                <!-- Widget Style -->
                <div class="cwc-card">
                    <div class="cwc-card-header">
                        <h2><span class="dashicons dashicons-layout"></span> <?php esc_html_e('Widget Style', 'currency-converter-widget'); ?></h2>
                    </div>
                    <div class="cwc-card-body">
                        <div class="cwc-style-grid">
                            <?php foreach ($widget_styles as $style_key => $style_data) : ?>
                                <label class="cwc-style-option <?php echo $options['style'] === $style_key ? 'selected' : ''; ?>">
                                    <input type="radio" name="cwc_widget_options[style]" value="<?php echo esc_attr($style_key); ?>" <?php checked($options['style'], $style_key); ?>>
                                    <span class="style-icon"><span class="dashicons dashicons-<?php echo esc_attr($style_data['icon']); ?>"></span></span>
                                    <span class="style-name"><?php echo esc_html($style_data['name']); ?></span>
                                    <span class="style-desc"><?php echo esc_html($style_data['desc']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <!-- Size Selection -->
                        <div class="cwc-size-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                            <div class="cwc-form-row">
                                <div class="cwc-form-group" style="flex: 1;">
                                    <label for="cwc_size"><?php esc_html_e('Size', 'currency-converter-widget'); ?></label>
                                    <select name="cwc_widget_options[size]" id="cwc_size" class="cwc-size-select" style="width: 100%;">
                                        <?php
                                        $current_style = $options['style'];
                                        $current_sizes = isset($size_options[$current_style]) ? $size_options[$current_style] : $size_options['compact'];
                                        foreach ($current_sizes as $size_key => $size_data) :
                                        ?>
                                            <option value="<?php echo esc_attr($size_key); ?>"
                                                    data-width="<?php echo esc_attr($size_data['width']); ?>"
                                                    data-height="<?php echo esc_attr($size_data['height']); ?>"
                                                    <?php selected($options['size'], $size_key); ?>>
                                                <?php echo esc_html($size_data['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Custom Dimensions (shown only when Custom size is selected) -->
                            <div id="cwc-custom-dimensions" class="cwc-custom-dimensions" style="display: <?php echo $options['size'] === 'custom' ? 'block' : 'none'; ?>; margin-top: 15px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <h4 style="margin: 0 0 12px 0; font-size: 13px; font-weight: 600; color: #475569;"><?php esc_html_e('Custom Dimensions', 'currency-converter-widget'); ?></h4>
                                <div class="cwc-form-row" style="gap: 15px;">
                                    <div class="cwc-form-group" style="flex: 1;">
                                        <label for="cwc_custom_width" style="font-size: 12px; color: #64748b;"><?php esc_html_e('Width (px)', 'currency-converter-widget'); ?></label>
                                        <input type="number" name="cwc_widget_options[custom_width]" id="cwc_custom_width"
                                               value="<?php echo esc_attr($options['custom_width']); ?>"
                                               min="150" max="800" class="small-text" style="width: 100%;">
                                    </div>
                                    <div class="cwc-form-group" style="flex: 1;">
                                        <label for="cwc_custom_height" style="font-size: 12px; color: #64748b;"><?php esc_html_e('Height (px)', 'currency-converter-widget'); ?></label>
                                        <input type="number" name="cwc_widget_options[custom_height]" id="cwc_custom_height"
                                               value="<?php echo esc_attr($options['custom_height']); ?>"
                                               min="100" max="800" class="small-text" style="width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Currency Settings -->
                <div class="cwc-card">
                    <div class="cwc-card-header">
                        <h2><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('Currency Settings', 'currency-converter-widget'); ?></h2>
                    </div>
                    <div class="cwc-card-body">
                        <div class="cwc-form-row">
                            <div class="cwc-form-group">
                                <label for="cwc_from"><?php esc_html_e('From Currency', 'currency-converter-widget'); ?></label>
                                <select name="cwc_widget_options[from]" id="cwc_from" class="cwc-currency-select">
                                    <?php foreach ($currencies as $code => $currency) : ?>
                                        <option value="<?php echo esc_attr($code); ?>" <?php selected($options['from'], $code); ?>>
                                            <?php echo esc_html($code . ' - ' . $currency['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="cwc-form-group" id="cwc-to-currency-group">
                                <label for="cwc_to"><?php esc_html_e('To Currency', 'currency-converter-widget'); ?></label>
                                <select name="cwc_widget_options[to]" id="cwc_to" class="cwc-currency-select">
                                    <?php foreach ($currencies as $code => $currency) : ?>
                                        <option value="<?php echo esc_attr($code); ?>" <?php selected($options['to'], $code); ?>>
                                            <?php echo esc_html($code . ' - ' . $currency['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="cwc-form-row">
                            <div class="cwc-form-group">
                                <label for="cwc_amount"><?php esc_html_e('Default Amount', 'currency-converter-widget'); ?></label>
                                <input type="text" name="cwc_widget_options[amount]" id="cwc_amount" value="<?php echo esc_attr($options['amount']); ?>" class="regular-text">
                            </div>
                            <div class="cwc-form-group" id="cwc-lock-currencies-group">
                                <label class="cwc-checkbox-label">
                                    <input type="checkbox" name="cwc_widget_options[lock_currencies]" value="1" <?php checked($options['lock_currencies'], true); ?>>
                                    <?php esc_html_e('Lock currencies (users cannot change)', 'currency-converter-widget'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- Display Currencies Section (only for multi-converter style) -->
                        <div id="cwc-display-currencies-section" class="cwc-display-currencies-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: none;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <label style="font-size: 13px; font-weight: 600; color: #374151;">
                                    <?php esc_html_e('Display Currencies', 'currency-converter-widget'); ?>
                                </label>
                                <span id="cwc-max-currencies-label" style="font-size: 12px; color: #64748b;"><?php esc_html_e('Max 10', 'currency-converter-widget'); ?></span>
                            </div>

                            <!-- Display Currency Preset Buttons -->
                            <div class="cwc-display-preset-buttons" style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <?php
                                $display_preset = isset($options['display_preset']) ? $options['display_preset'] : 'top3';
                                ?>
                                <button type="button" class="cwc-display-preset <?php echo $display_preset === 'top3' ? 'active' : ''; ?>" data-preset="top3" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $display_preset === 'top3' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $display_preset === 'top3' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('Top 3', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-display-preset <?php echo $display_preset === 'top10' ? 'active' : ''; ?>" data-preset="top10" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $display_preset === 'top10' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $display_preset === 'top10' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('Top 10', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-display-preset <?php echo $display_preset === 'custom' ? 'active' : ''; ?>" data-preset="custom" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $display_preset === 'custom' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $display_preset === 'custom' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('Custom', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" id="cwc-display-preset-all" class="cwc-display-preset <?php echo $display_preset === 'all' ? 'active' : ''; ?>" data-preset="all" style="display: none; padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $display_preset === 'all' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $display_preset === 'all' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('All', 'currency-converter-widget'); ?>
                                </button>
                            </div>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" name="cwc_widget_options[display_preset]" id="cwc_display_preset" value="<?php echo esc_attr($display_preset); ?>">
                            <input type="hidden" name="cwc_widget_options[display_currencies]" id="cwc_display_currencies" value="<?php echo esc_attr(isset($options['display_currencies']) ? $options['display_currencies'] : 'EUR,GBP,JPY'); ?>">

                            <!-- Selected currencies display -->
                            <div style="padding: 10px 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
                                <p style="margin: 0 0 8px 0; font-size: 12px; color: #64748b;">
                                    <?php esc_html_e('Most popular currencies', 'currency-converter-widget'); ?>
                                </p>
                                <div id="cwc-display-currencies-pills" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    <?php
                                    $display_currencies = isset($options['display_currencies']) ? explode(',', $options['display_currencies']) : ['EUR', 'GBP', 'JPY'];
                                    foreach ($display_currencies as $code) :
                                        $currency_data = $currencies[$code] ?? null;
                                        if ($currency_data) :
                                    ?>
                                    <span class="cwc-display-pill" data-currency="<?php echo esc_attr($code); ?>" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; font-size: 12px;">
                                        <?php if (!empty($currency_data['flag'])) : ?><img src="https://cdn.currency.wiki/flags/<?php echo esc_attr($currency_data['flag']); ?>.svg" alt="<?php echo esc_attr($code); ?>" style="width: 16px; height: 12px; border-radius: 2px; object-fit: cover;"><?php endif; ?>
                                        <span><?php echo esc_html($code); ?></span>
                                    </span>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>

                            <!-- Custom Currency Selection (shown when Custom is selected) -->
                            <div id="cwc-display-custom-selection" style="display: <?php echo $display_preset === 'custom' ? 'block' : 'none'; ?>; margin-bottom: 12px;">
                                <!-- Search box -->
                                <input type="text" id="cwc-display-search" placeholder="<?php esc_attr_e('Search currencies...', 'currency-converter-widget'); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; margin-bottom: 12px;">

                                <!-- All currencies for selection (scrollable) -->
                                <div id="cwc-display-all-currencies" style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <?php
                                        foreach ($currencies as $code => $currency_data) :
                                            $is_selected = in_array($code, $display_currencies);
                                            $flag = $currency_data['flag'] ?? '';
                                        ?>
                                        <button type="button"
                                                class="cwc-display-currency-pill <?php echo $is_selected ? 'selected' : ''; ?>"
                                                data-currency="<?php echo esc_attr($code); ?>"
                                                style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: <?php echo $is_selected ? '#3B82F6' : '#fff'; ?>; color: <?php echo $is_selected ? '#fff' : '#374151'; ?>; border: 1px solid <?php echo $is_selected ? '#3B82F6' : '#e2e8f0'; ?>; border-radius: 16px; font-size: 12px; cursor: pointer;">
                                            <?php if ($flag) : ?><img src="https://cdn.currency.wiki/flags/<?php echo esc_attr($flag); ?>.svg" alt="<?php echo esc_attr($code); ?>" style="width: 16px; height: 12px; border-radius: 2px; object-fit: cover;"><?php endif; ?>
                                            <span><?php echo esc_html($code); ?></span>
                                            <?php if ($is_selected) : ?><span style="margin-left: 2px;">✓</span><?php endif; ?>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <p style="margin: 12px 0 0 0; font-size: 11px; color: #94a3b8; text-align: center;"><?php echo sprintf(__('%d currencies available', 'currency-converter-widget'), count($currencies)); ?></p>
                                </div>
                            </div>

                            <!-- Info message (for styles with 10 currency limit) -->
                            <div id="cwc-display-currencies-info" style="display: flex; gap: 8px; padding: 12px; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px;">
                                <span style="color: #3B82F6; flex-shrink: 0;">ℹ️</span>
                                <p style="margin: 0; font-size: 12px; color: #1e40af; line-height: 1.5;">
                                    <?php esc_html_e('Select up to 10 currencies.', 'currency-converter-widget'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Add Currency Dropdown Section (only for multi-converter) -->
                        <div id="cwc-add-currency-dropdown-section" class="cwc-add-currency-dropdown-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: none;">
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px;">
                                    <?php esc_html_e('Add Currency Dropdown', 'currency-converter-widget'); ?>
                                </label>
                                <p style="margin: 0; font-size: 12px; color: #64748b;">
                                    <?php esc_html_e('Currencies available in "+ Add Currency" dropdown', 'currency-converter-widget'); ?>
                                </p>
                            </div>

                            <!-- Add Currency Dropdown Preset Buttons -->
                            <div class="cwc-add-dropdown-preset-buttons" style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <?php
                                $add_dropdown_preset = isset($options['add_dropdown_preset']) ? $options['add_dropdown_preset'] : 'top10';
                                ?>
                                <button type="button" class="cwc-add-dropdown-preset <?php echo $add_dropdown_preset === 'top10' ? 'active' : ''; ?>" data-preset="top10" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $add_dropdown_preset === 'top10' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $add_dropdown_preset === 'top10' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('Top 10', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-add-dropdown-preset <?php echo $add_dropdown_preset === 'top20' ? 'active' : ''; ?>" data-preset="top20" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $add_dropdown_preset === 'top20' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $add_dropdown_preset === 'top20' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('Top 20', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-add-dropdown-preset <?php echo $add_dropdown_preset === 'all' ? 'active' : ''; ?>" data-preset="all" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $add_dropdown_preset === 'all' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $add_dropdown_preset === 'all' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('All (170+)', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-add-dropdown-preset <?php echo $add_dropdown_preset === 'custom' ? 'active' : ''; ?>" data-preset="custom" style="padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 6px; background: <?php echo $add_dropdown_preset === 'custom' ? '#3B82F6' : '#fff'; ?>; color: <?php echo $add_dropdown_preset === 'custom' ? '#fff' : '#374151'; ?>; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    <?php esc_html_e('Custom', 'currency-converter-widget'); ?>
                                </button>
                            </div>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" name="cwc_widget_options[add_dropdown_preset]" id="cwc_add_dropdown_preset" value="<?php echo esc_attr($add_dropdown_preset); ?>">
                            <input type="hidden" name="cwc_widget_options[add_dropdown_currencies]" id="cwc_add_dropdown_currencies" value="<?php echo esc_attr(isset($options['add_dropdown_currencies']) ? $options['add_dropdown_currencies'] : 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN'); ?>">

                            <!-- Currency Info Text for Add Dropdown -->
                            <div class="cwc-add-dropdown-info" style="padding: 10px 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
                                <p id="cwc-add-dropdown-info-text" style="margin: 0; font-size: 12px; color: #64748b;">
                                    <?php
                                    if ($add_dropdown_preset === 'top10') {
                                        esc_html_e('Top 10 currencies: USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, INR, MXN', 'currency-converter-widget');
                                    } elseif ($add_dropdown_preset === 'top20') {
                                        esc_html_e('Top 20 currencies including BRL, KRW, SGD, HKD, NZD, etc.', 'currency-converter-widget');
                                    } elseif ($add_dropdown_preset === 'all') {
                                        esc_html_e('All 170+ currencies with search functionality', 'currency-converter-widget');
                                    } else {
                                        esc_html_e('Custom selection of currencies', 'currency-converter-widget');
                                    }
                                    ?>
                                </p>
                            </div>

                            <!-- Custom Currency Selection for Add Dropdown (shown when Custom is selected) -->
                            <div id="cwc-add-dropdown-custom-selection" style="display: <?php echo $add_dropdown_preset === 'custom' ? 'block' : 'none'; ?>;">
                                <!-- Search box -->
                                <input type="text" id="cwc-add-dropdown-search" placeholder="<?php esc_attr_e('Search currencies...', 'currency-converter-widget'); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; margin-bottom: 12px;">

                                <!-- All currencies for selection (scrollable) -->
                                <div id="cwc-add-dropdown-all-currencies" style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <?php
                                        $add_dropdown_currencies = isset($options['add_dropdown_currencies']) ? explode(',', $options['add_dropdown_currencies']) : ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'MXN'];
                                        foreach ($currencies as $code => $currency_data) :
                                            $is_selected = in_array($code, $add_dropdown_currencies);
                                            $flag = $currency_data['flag'] ?? '';
                                        ?>
                                        <button type="button"
                                                class="cwc-add-dropdown-currency-pill <?php echo $is_selected ? 'selected' : ''; ?>"
                                                data-currency="<?php echo esc_attr($code); ?>"
                                                style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: <?php echo $is_selected ? '#3B82F6' : '#fff'; ?>; color: <?php echo $is_selected ? '#fff' : '#374151'; ?>; border: 1px solid <?php echo $is_selected ? '#3B82F6' : '#e2e8f0'; ?>; border-radius: 16px; font-size: 12px; cursor: pointer;">
                                            <?php if ($flag) : ?><img src="https://cdn.currency.wiki/flags/<?php echo esc_attr($flag); ?>.svg" alt="<?php echo esc_attr($code); ?>" style="width: 16px; height: 12px; border-radius: 2px; object-fit: cover;"><?php endif; ?>
                                            <span><?php echo esc_html($code); ?></span>
                                            <?php if ($is_selected) : ?><span style="margin-left: 2px;">✓</span><?php endif; ?>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <p style="margin: 12px 0 0 0; font-size: 11px; color: #94a3b8; text-align: center;"><?php echo sprintf(__('%d currencies available', 'currency-converter-widget'), count($currencies)); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Menu Currencies -->
                        <div class="cwc-dropdown-currencies-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 12px;">
                                <?php esc_html_e('Dropdown Menu Currencies', 'currency-converter-widget'); ?>
                            </label>

                            <!-- Preset Buttons -->
                            <div class="cwc-preset-buttons" style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <?php
                                $current_preset = isset($options['dropdown_preset']) ? $options['dropdown_preset'] : 'top10';
                                ?>
                                <button type="button" class="cwc-dropdown-preset <?php echo $current_preset === 'top10' ? 'active' : ''; ?>" data-preset="top10">
                                    <?php esc_html_e('Top 10', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-dropdown-preset <?php echo $current_preset === 'top20' ? 'active' : ''; ?>" data-preset="top20">
                                    <?php esc_html_e('Top 20', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-dropdown-preset <?php echo $current_preset === 'custom' ? 'active' : ''; ?>" data-preset="custom">
                                    <?php esc_html_e('Custom', 'currency-converter-widget'); ?>
                                </button>
                                <button type="button" class="cwc-dropdown-preset <?php echo $current_preset === 'all' ? 'active' : ''; ?>" data-preset="all">
                                    <?php esc_html_e('All (170+)', 'currency-converter-widget'); ?>
                                </button>
                            </div>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" name="cwc_widget_options[dropdown_preset]" id="cwc_dropdown_preset" value="<?php echo esc_attr($current_preset); ?>">
                            <input type="hidden" name="cwc_widget_options[dropdown_currencies]" id="cwc_dropdown_currencies" value="<?php echo esc_attr(isset($options['dropdown_currencies']) ? $options['dropdown_currencies'] : 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN'); ?>">

                            <!-- Currency Info Text -->
                            <div class="cwc-currency-info" style="padding: 10px 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
                                <p id="cwc-currency-info-text" style="margin: 0; font-size: 12px; color: #64748b;">
                                    <?php esc_html_e('Most popular currencies: USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, INR, MXN', 'currency-converter-widget'); ?>
                                </p>
                            </div>

                            <!-- Search box for custom mode (moved above pills) -->
                            <div id="cwc-currency-search-container" style="display: <?php echo $current_preset === 'custom' ? 'block' : 'none'; ?>; margin-bottom: 12px;">
                                <input type="text" id="cwc-currency-search" placeholder="<?php esc_attr_e('Search currencies...', 'currency-converter-widget'); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
                            </div>

                            <!-- Currency Pills (shows all currencies for custom selection) - scrollable container -->
                            <div id="cwc-currency-pills-wrapper" style="display: <?php echo $current_preset === 'custom' ? 'block' : 'none'; ?>; max-height: 300px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                                <div class="cwc-currency-pills" id="cwc-currency-pills" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php
                                    $top10 = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'MXN'];
                                    $selected_currencies = isset($options['dropdown_currencies']) ? explode(',', $options['dropdown_currencies']) : $top10;

                                    // Show ALL currencies, not just top10
                                    foreach ($currencies as $code => $currency_data) :
                                        $is_selected = in_array($code, $selected_currencies);
                                        $flag = $currency_data['flag'] ?? '';
                                    ?>
                                    <button type="button"
                                            class="cwc-currency-pill <?php echo $is_selected ? 'selected' : ''; ?>"
                                            data-currency="<?php echo esc_attr($code); ?>">
                                        <?php if ($flag) : ?><img src="https://cdn.currency.wiki/flags/<?php echo esc_attr($flag); ?>.svg" alt="<?php echo esc_attr($code); ?>" style="width: 16px; height: 12px; border-radius: 2px; object-fit: cover;"><?php endif; ?>
                                        <span class="cwc-pill-code"><?php echo esc_html($code); ?></span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                                <p style="margin: 12px 0 0 0; font-size: 11px; color: #94a3b8; text-align: center;"><?php echo sprintf(__('%d currencies available', 'currency-converter-widget'), count($currencies)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance -->
                <div class="cwc-card">
                    <div class="cwc-card-header">
                        <h2><span class="dashicons dashicons-art"></span> <?php esc_html_e('Appearance', 'currency-converter-widget'); ?></h2>
                    </div>
                    <div class="cwc-card-body">
                        <div class="cwc-form-row">
                            <div class="cwc-form-group">
                                <label><?php esc_html_e('Theme', 'currency-converter-widget'); ?></label>
                                <div class="cwc-theme-options">
                                    <label class="cwc-theme-option <?php echo $options['theme'] === 'light' ? 'selected' : ''; ?>">
                                        <input type="radio" name="cwc_widget_options[theme]" value="light" <?php checked($options['theme'], 'light'); ?>>
                                        <span class="dashicons dashicons-admin-appearance"></span>
                                        <?php esc_html_e('Light', 'currency-converter-widget'); ?>
                                    </label>
                                    <label class="cwc-theme-option <?php echo $options['theme'] === 'dark' ? 'selected' : ''; ?>">
                                        <input type="radio" name="cwc_widget_options[theme]" value="dark" <?php checked($options['theme'], 'dark'); ?>>
                                        <span class="dashicons dashicons-admin-customizer"></span>
                                        <?php esc_html_e('Dark', 'currency-converter-widget'); ?>
                                    </label>
                                    <label class="cwc-theme-option <?php echo $options['theme'] === 'auto' ? 'selected' : ''; ?>">
                                        <input type="radio" name="cwc_widget_options[theme]" value="auto" <?php checked($options['theme'], 'auto'); ?>>
                                        <span class="dashicons dashicons-desktop"></span>
                                        <?php esc_html_e('Auto', 'currency-converter-widget'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="cwc-form-row">
                            <div class="cwc-form-group">
                                <label><?php esc_html_e('Accent Color', 'currency-converter-widget'); ?></label>
                                <?php
                                $cwc_preset_accents   = array_keys($accent_colors);
                                $cwc_is_custom_accent = ! in_array($options['accent'], $cwc_preset_accents, true);
                                $cwc_custom_accent    = $cwc_is_custom_accent ? $options['accent'] : '2563eb';
                                ?>
                                <div class="cwc-color-options">
                                    <?php foreach ($accent_colors as $color_val => $color_name) : ?>
                                        <label class="cwc-color-option <?php echo $options['accent'] === $color_val ? 'selected' : ''; ?>" style="background-color: #<?php echo esc_attr($color_val); ?>;">
                                            <input type="radio" name="cwc_widget_options[accent]" value="<?php echo esc_attr($color_val); ?>" <?php checked($options['accent'], $color_val); ?>>
                                            <span class="screen-reader-text"><?php echo esc_html($color_name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                    <label class="cwc-color-option cwc-color-custom <?php echo $cwc_is_custom_accent ? 'selected' : ''; ?>"
                                           title="<?php esc_attr_e('Custom color', 'currency-converter-widget'); ?>"
                                           style="<?php echo $cwc_is_custom_accent ? 'background-color:#' . esc_attr($cwc_custom_accent) . ';background-image:none;' : ''; ?>">
                                        <input type="radio" name="cwc_widget_options[accent]" value="<?php echo esc_attr($cwc_is_custom_accent ? $cwc_custom_accent : ''); ?>" class="cwc-accent-custom-radio" <?php checked($cwc_is_custom_accent); ?>>
                                        <input type="color" class="cwc-accent-custom-picker" value="#<?php echo esc_attr($cwc_custom_accent); ?>" aria-label="<?php esc_attr_e('Custom color', 'currency-converter-widget'); ?>">
                                        <span class="screen-reader-text"><?php esc_html_e('Custom color', 'currency-converter-widget'); ?></span>
                                    </label>
                                </div>
                                <input type="text" class="cwc-accent-custom-hex" maxlength="7" spellcheck="false"
                                       placeholder="#2563EB"
                                       value="<?php echo $cwc_is_custom_accent ? '#' . esc_attr(strtoupper($cwc_custom_accent)) : ''; ?>"
                                       aria-label="<?php esc_attr_e('Custom color', 'currency-converter-widget'); ?>">
                            </div>
                            <div class="cwc-form-group">
                                <label for="cwc_language"><?php esc_html_e('Widget Language', 'currency-converter-widget'); ?></label>
                                <select name="cwc_widget_options[language]" id="cwc_language">
                                    <?php foreach ($languages as $lang_code => $lang_name) : ?>
                                        <option value="<?php echo esc_attr($lang_code); ?>" <?php selected($options['language'], $lang_code); ?>>
                                            <?php echo esc_html($lang_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display Options -->
                <div class="cwc-card">
                    <div class="cwc-card-header">
                        <h2><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Display Options', 'currency-converter-widget'); ?></h2>
                    </div>
                    <div class="cwc-card-body">
                        <div class="cwc-checkbox-grid">
                            <label class="cwc-checkbox-label">
                                <input type="checkbox" name="cwc_widget_options[show_flags]" value="1" <?php checked($options['show_flags'], true); ?>>
                                <?php esc_html_e('Show country flags', 'currency-converter-widget'); ?>
                            </label>
                            <label class="cwc-checkbox-label" id="cwc-show-labels-option">
                                <input type="checkbox" name="cwc_widget_options[show_labels]" value="1" <?php checked($options['show_labels'], true); ?>>
                                <?php esc_html_e('Show labels (From/To)', 'currency-converter-widget'); ?>
                            </label>
                            <label class="cwc-checkbox-label">
                                <input type="checkbox" name="cwc_widget_options[show_swap]" value="1" <?php checked($options['show_swap'], true); ?>>
                                <?php esc_html_e('Show swap button', 'currency-converter-widget'); ?>
                            </label>
                        </div>

                        <!-- Currency Display Mode (ISO vs Symbol) -->
                        <div class="cwc-currency-display-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 12px;">
                                <?php esc_html_e('Currency Display Format', 'currency-converter-widget'); ?>
                            </label>
                            <div class="cwc-currency-display-options" style="display: flex; gap: 8px;">
                                <label class="cwc-display-mode-option <?php echo $options['currency_display'] === 'iso' ? 'selected' : ''; ?>" style="flex: 1; display: flex; flex-direction: column; align-items: center; padding: 16px 12px; border: 2px solid <?php echo $options['currency_display'] === 'iso' ? '#3B82F6' : '#e2e8f0'; ?>; border-radius: 8px; cursor: pointer; background: <?php echo $options['currency_display'] === 'iso' ? '#eff6ff' : '#fff'; ?>; transition: all 0.2s;">
                                    <input type="radio" name="cwc_widget_options[currency_display]" value="iso" <?php checked($options['currency_display'], 'iso'); ?> style="display: none;">
                                    <span style="font-size: 20px; font-weight: 700; color: #374151; margin-bottom: 4px;">USD</span>
                                    <span style="font-size: 12px; color: #64748b;"><?php esc_html_e('ISO Code', 'currency-converter-widget'); ?></span>
                                </label>
                                <label class="cwc-display-mode-option <?php echo $options['currency_display'] === 'symbol' ? 'selected' : ''; ?>" style="flex: 1; display: flex; flex-direction: column; align-items: center; padding: 16px 12px; border: 2px solid <?php echo $options['currency_display'] === 'symbol' ? '#3B82F6' : '#e2e8f0'; ?>; border-radius: 8px; cursor: pointer; background: <?php echo $options['currency_display'] === 'symbol' ? '#eff6ff' : '#fff'; ?>; transition: all 0.2s;">
                                    <input type="radio" name="cwc_widget_options[currency_display]" value="symbol" <?php checked($options['currency_display'], 'symbol'); ?> style="display: none;">
                                    <span style="font-size: 20px; font-weight: 700; color: #374151; margin-bottom: 4px;">$</span>
                                    <span style="font-size: 12px; color: #64748b;"><?php esc_html_e('Symbol', 'currency-converter-widget'); ?></span>
                                </label>
                            </div>
                            <p style="margin: 10px 0 0 0; font-size: 11px; color: #94a3b8;">
                                <?php esc_html_e('Choose how currencies are displayed in the widget. ISO codes (USD, EUR) are universal, while symbols ($, €) may be more familiar to users.', 'currency-converter-widget'); ?>
                            </p>
                        </div>

                        <!-- Branding Link Toggle -->
                        <div class="cwc-branding-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                            <div class="cwc-toggle-row" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <span style="font-size: 14px; color: #374151;"><?php esc_html_e('Show "Powered by Currency.Wiki" link', 'currency-converter-widget'); ?></span>
                                <label class="cwc-toggle-switch">
                                    <input type="checkbox" name="cwc_widget_options[show_branding_link]" value="1" <?php checked($options['show_branding_link'], true); ?> id="cwc_branding_link">
                                    <span class="cwc-toggle-slider"></span>
                                </label>
                                <!-- Hidden field to ensure branding always shows -->
                                <input type="hidden" name="cwc_widget_options[show_branding]" value="1">
                            </div>
                            <div class="cwc-info-box" style="display: flex; gap: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px;">
                                <svg style="width: 16px; height: 16px; color: #2563eb; flex-shrink: 0; margin-top: 2px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p style="font-size: 12px; color: #1e40af; margin: 0; line-height: 1.5;">
                                    <?php esc_html_e('"Powered by Currency.Wiki" will always be displayed. Disabling this option removes only the clickable link.', 'currency-converter-widget'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="cwc-form-row" style="margin-top: 20px;">
                            <div class="cwc-form-group">
                                <label for="cwc_format"><?php esc_html_e('Number Format', 'currency-converter-widget'); ?></label>
                                <select name="cwc_widget_options[number_format]" id="cwc_format">
                                    <option value="auto" <?php selected($options['number_format'], 'auto'); ?>><?php esc_html_e('Auto (Browser)', 'currency-converter-widget'); ?></option>
                                    <option value="us" <?php selected($options['number_format'], 'us'); ?>>US (1,234.56)</option>
                                    <option value="eu" <?php selected($options['number_format'], 'eu'); ?>>EU (1.234,56)</option>
                                    <option value="french" <?php selected($options['number_format'], 'french'); ?>>French (1 234,56)</option>
                                    <option value="indian" <?php selected($options['number_format'], 'indian'); ?>>Indian (1,23,456.78)</option>
                                </select>
                            </div>
                            <div class="cwc-form-group">
                                <label for="cwc_decimals"><?php esc_html_e('Decimal Places', 'currency-converter-widget'); ?>: <span id="cwc_decimals_val"><?php echo esc_html($options['decimals']); ?></span></label>
                                <input type="range" name="cwc_widget_options[decimals]" id="cwc_decimals" min="0" max="4" value="<?php echo esc_attr($options['decimals']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Settings (for chart widgets) -->
                <div class="cwc-card" id="cwc-chart-settings-section" style="display: none;">
                    <div class="cwc-card-header">
                        <h2><span class="dashicons dashicons-chart-line"></span> <?php esc_html_e('Chart Settings', 'currency-converter-widget'); ?></h2>
                    </div>
                    <div class="cwc-card-body">
                        <div class="cwc-form-row">
                            <div class="cwc-form-group">
                                <label><?php esc_html_e('Period', 'currency-converter-widget'); ?></label>
                                <input type="hidden" name="cwc_widget_options[chart_period]" id="cwc_chart_period" value="<?php echo esc_attr($options['chart_period'] ?? '7d'); ?>">
                                <div class="cwc-period-buttons" style="display: flex; gap: 8px; margin-top: 8px;">
                                    <?php
                                    $periods = [
                                        '7d' => '7D',
                                        '14d' => '14D',
                                        '30d' => '30D',
                                        '90d' => '90D'
                                    ];
                                    $currentPeriod = $options['chart_period'] ?? '7d';
                                    foreach ($periods as $value => $label) :
                                        $isActive = $currentPeriod === $value;
                                    ?>
                                    <button type="button" class="cwc-period-btn <?php echo $isActive ? 'active' : ''; ?>" data-period="<?php echo esc_attr($value); ?>" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.2s; <?php echo $isActive ? 'background: #3B82F6; color: #fff; border-color: #3B82F6;' : 'background: #fff; color: #374151;'; ?>">
                                        <?php echo esc_html($label); ?>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="cwc-submit-row">
                    <?php submit_button(__('Save Settings', 'currency-converter-widget'), 'primary large', 'submit', false); ?>
                </div>
            </form>

            <!-- Shortcode (moved to left panel) -->
            <div class="cwc-card" style="margin-top: 24px;">
                <div class="cwc-card-header">
                    <h2><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e('Shortcode', 'currency-converter-widget'); ?></h2>
                </div>
                <div class="cwc-card-body">
                    <p class="cwc-info-text"><?php esc_html_e('Copy this shortcode and paste it into any post, page, or widget area:', 'currency-converter-widget'); ?></p>
                    <div class="cwc-shortcode-box">
                        <code id="cwc-shortcode">[currencywiki_converter]</code>
                        <button type="button" class="button cwc-copy-btn" data-copy="cwc-shortcode">
                            <span class="dashicons dashicons-admin-page"></span>
                            <?php esc_html_e('Copy', 'currency-converter-widget'); ?>
                        </button>
                    </div>

                    <!-- Theme Compatibility Notice -->
                    <div class="cwc-theme-notice" style="margin-top: 16px; padding: 12px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px;">
                        <div style="display: flex; gap: 10px;">
                            <span style="font-size: 16px; flex-shrink: 0;">💡</span>
                            <div>
                                <p style="font-size: 12px; color: #854d0e; margin: 0 0 8px 0; line-height: 1.5;">
                                    <strong><?php esc_html_e('Theme Compatibility:', 'currency-converter-widget'); ?></strong>
                                    <?php esc_html_e('Some WordPress themes may conflict with the widget script. If you experience display issues, try using iframe mode:', 'currency-converter-widget'); ?>
                                </p>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <code id="cwc-iframe-shortcode" style="font-size: 11px; background: #fff; padding: 4px 8px; border-radius: 4px; color: #92400e; flex: 1;">[currencywiki_converter embed="iframe"]</code>
                                    <button type="button" class="button button-small cwc-copy-btn" data-copy="cwc-iframe-shortcode" style="padding: 2px 8px; min-height: 24px;">
                                        <span class="dashicons dashicons-admin-page" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Iframe Limitations -->
                    <div class="cwc-iframe-limitations" style="margin-top: 12px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <p style="font-size: 12px; color: #64748b; margin: 0 0 6px 0; font-weight: 600;">
                            <?php esc_html_e('Iframe Limitations:', 'currency-converter-widget'); ?>
                        </p>
                        <ul style="font-size: 11px; color: #64748b; margin: 0; padding-left: 16px; line-height: 1.6;">
                            <li><?php esc_html_e('Dropdown menus are contained within the widget area', 'currency-converter-widget'); ?></li>
                            <li><?php esc_html_e('Long currency lists may require scrolling inside the widget', 'currency-converter-widget'); ?></li>
                            <li><?php esc_html_e('Best suited for locked currency displays or viewer-style widgets', 'currency-converter-widget'); ?></li>
                        </ul>
                    </div>

                    <div class="cwc-shortcode-examples" style="margin-top: 16px;">
                        <h4><?php esc_html_e('Shortcode Examples', 'currency-converter-widget'); ?></h4>
                        <div class="cwc-example">
                            <label><?php esc_html_e('Basic:', 'currency-converter-widget'); ?></label>
                            <code>[currencywiki_converter]</code>
                        </div>
                        <div class="cwc-example">
                            <label><?php esc_html_e('Short alias:', 'currency-converter-widget'); ?></label>
                            <code>[currencywiki]</code>
                        </div>
                        <div class="cwc-example">
                            <label><?php esc_html_e('Custom currencies:', 'currency-converter-widget'); ?></label>
                            <code>[currencywiki_converter from="EUR" to="GBP"]</code>
                        </div>
                        <div class="cwc-example">
                            <label><?php esc_html_e('Dark theme:', 'currency-converter-widget'); ?></label>
                            <code>[currencywiki theme="dark"]</code>
                        </div>
                        <div class="cwc-example">
                            <label><?php esc_html_e('Mini style:', 'currency-converter-widget'); ?></label>
                            <code>[currencywiki style="mini"]</code>
                        </div>
                        <div class="cwc-example">
                            <label><?php esc_html_e('With chart:', 'currency-converter-widget'); ?></label>
                            <code>[currencywiki style="mini-chart" chart="1"]</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Embed Code (for non-WordPress sites) -->
            <div class="cwc-card">
                <div class="cwc-card-header">
                    <h2><span class="dashicons dashicons-editor-code"></span> <?php esc_html_e('Embed Code (External Sites)', 'currency-converter-widget'); ?></h2>
                </div>
                <div class="cwc-card-body">
                    <p class="cwc-info-text"><?php esc_html_e('For embedding on non-WordPress websites (HTML, Shopify, Wix, etc.), use the code below:', 'currency-converter-widget'); ?></p>

                    <!-- Embed Type Toggle - Script is now primary/default -->
                    <div class="cwc-embed-type-toggle" style="margin-bottom: 16px;">
                        <div style="display: flex; gap: 8px; padding: 4px; background: #f1f5f9; border-radius: 8px;">
                            <button type="button" class="cwc-embed-type-btn active" data-type="script" style="flex: 1; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                <span class="dashicons dashicons-editor-code" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;"></span>
                                <?php esc_html_e('Script (Recommended)', 'currency-converter-widget'); ?>
                            </button>
                            <button type="button" class="cwc-embed-type-btn" data-type="iframe" style="flex: 1; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; background: transparent;">
                                <span class="dashicons dashicons-align-center" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;"></span>
                                <?php esc_html_e('Iframe (Fallback)', 'currency-converter-widget'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Script Embed Info (shown by default) -->
                    <div id="cwc-embed-info-script" class="cwc-embed-info" style="display: flex; gap: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                        <svg style="width: 20px; height: 20px; color: #059669; flex-shrink: 0; margin-top: 2px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p style="font-size: 13px; font-weight: 600; color: #047857; margin: 0 0 4px 0;"><?php esc_html_e('Script Embed (Recommended)', 'currency-converter-widget'); ?></p>
                            <p style="font-size: 12px; color: #10b981; margin: 0; line-height: 1.5;">
                                <?php esc_html_e('Full-featured widget with dropdown menus that display in front of surrounding content. Best for interactive currency selection.', 'currency-converter-widget'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Iframe Embed Info (hidden by default) -->
                    <div id="cwc-embed-info-iframe" class="cwc-embed-info" style="display: none; gap: 12px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                        <svg style="width: 20px; height: 20px; color: #ca8a04; flex-shrink: 0; margin-top: 2px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p style="font-size: 13px; font-weight: 600; color: #854d0e; margin: 0 0 4px 0;"><?php esc_html_e('Iframe Embed (Fallback Option)', 'currency-converter-widget'); ?></p>
                            <p style="font-size: 12px; color: #a16207; margin: 0 0 8px 0; line-height: 1.5;">
                                <?php esc_html_e('Use this if the script embed causes conflicts with your website. Works in isolated sandbox.', 'currency-converter-widget'); ?>
                            </p>
                            <p style="font-size: 11px; color: #92400e; margin: 0; font-weight: 600;"><?php esc_html_e('Limitations:', 'currency-converter-widget'); ?></p>
                            <ul style="font-size: 11px; color: #92400e; margin: 4px 0 0 0; padding-left: 16px; line-height: 1.5;">
                                <li><?php esc_html_e('Dropdown menus are contained within the widget area', 'currency-converter-widget'); ?></li>
                                <li><?php esc_html_e('Long currency lists require scrolling inside the widget', 'currency-converter-widget'); ?></li>
                                <li><?php esc_html_e('Best suited for locked currencies or viewer-style widgets', 'currency-converter-widget'); ?></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Embed Code Box -->
                    <div class="cwc-shortcode-box" style="background: #0f172a;">
                        <code id="cwc-embed-code" style="color: #94a3b8; white-space: pre-wrap; word-break: break-all;"></code>
                        <button type="button" class="button cwc-copy-btn" data-copy="cwc-embed-code" style="margin-top: 8px;">
                            <span class="dashicons dashicons-admin-page"></span>
                            <?php esc_html_e('Copy', 'currency-converter-widget'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Gutenberg Block Info -->
            <div class="cwc-card">
                <div class="cwc-card-header">
                    <h2><span class="dashicons dashicons-block-default"></span> <?php esc_html_e('Gutenberg Block', 'currency-converter-widget'); ?></h2>
                </div>
                <div class="cwc-card-body">
                    <p class="cwc-info-text"><?php esc_html_e('You can also add the widget using the Gutenberg block editor:', 'currency-converter-widget'); ?></p>
                    <ol class="cwc-steps">
                        <li><?php esc_html_e('Open the block editor in any post or page', 'currency-converter-widget'); ?></li>
                        <li><?php esc_html_e('Click the + button to add a new block', 'currency-converter-widget'); ?></li>
                        <li><?php esc_html_e('Search for "Currency Converter"', 'currency-converter-widget'); ?></li>
                        <li><?php esc_html_e('Configure the widget in the block settings sidebar', 'currency-converter-widget'); ?></li>
                    </ol>
                </div>
            </div>

            <!-- Support -->
            <div class="cwc-card cwc-support-card">
                <div class="cwc-card-body">
                    <div class="cwc-support-content">
                        <span class="dashicons dashicons-heart"></span>
                        <div>
                            <h3><?php esc_html_e('Need Help?', 'currency-converter-widget'); ?></h3>
                            <p><?php esc_html_e('Visit our documentation or contact support for assistance.', 'currency-converter-widget'); ?></p>
                            <a href="https://currency.wiki/tools/widget-builder/doc" target="_blank" class="button button-secondary">
                                <?php esc_html_e('View Documentation', 'currency-converter-widget'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Panel (right side - sticky) -->
        <div class="cwc-preview-panel">
            <!-- Live Preview -->
            <div class="cwc-card cwc-sticky">
                <div class="cwc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Live Preview', 'currency-converter-widget'); ?></h2>
                    <span id="cwc-preview-dimensions" style="font-size: 12px; color: #64748b;"><?php echo esc_html($widget_styles[$options['style']]['width'] ?? 300); ?> × <?php echo esc_html($widget_styles[$options['style']]['height'] ?? 180); ?>px</span>
                </div>
                <div class="cwc-card-body">
                    <div class="cwc-preview-container" id="cwc-preview-container">
                        <iframe
                            id="cwc-preview-iframe"
                            src="https://widget.currency.wiki/v3/embed?style=<?php echo esc_attr($options['style']); ?>&theme=<?php echo esc_attr($options['theme']); ?>&accent=<?php echo esc_attr($options['accent']); ?>&from=<?php echo esc_attr($options['from']); ?>&to=<?php echo esc_attr($options['to']); ?>&amount=<?php echo esc_attr($options['amount']); ?>&lang=<?php echo esc_attr($options['language']); ?>&flags=<?php echo $options['show_flags'] ? '1' : '0'; ?>&labels=<?php echo $options['show_labels'] ? '1' : '0'; ?>&swap=<?php echo $options['show_swap'] ? '1' : '0'; ?>&branding=1&brandinglink=<?php echo $options['show_branding_link'] ? '1' : '0'; ?>"
                            width="<?php echo esc_attr($widget_styles[$options['style']]['width'] ?? 300); ?>"
                            height="<?php echo esc_attr($widget_styles[$options['style']]['height'] ?? 180); ?>"
                            frameborder="0"
                            style="border: none;"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Pass size options and currencies to JavaScript
var cwcSizeOptions = <?php echo json_encode($size_options); ?>;
var cwcCurrencies = <?php echo json_encode($currencies); ?>;
</script>
