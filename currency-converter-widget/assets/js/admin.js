/**
 * Currency Converter Widget - Admin JavaScript
 * Version: 3.0.0
 */

(function($) {
    'use strict';

    // Preset configurations
    const presets = {
        simple: {
            style: 'compact',
            theme: 'light',
            accent: '2563eb',
            show_flags: true,
            show_labels: true,
            show_swap: true
        },
        minimal: {
            style: 'mini',
            theme: 'light',
            accent: '475569',
            show_flags: false,
            show_labels: false,
            show_swap: false
        },
        sidebar: {
            style: 'tall',
            theme: 'light',
            accent: '059669',
            show_flags: true,
            show_labels: true,
            show_swap: true
        },
        multi: {
            style: 'multi-fixed',
            theme: 'light',
            accent: '7c3aed',
            show_flags: true,
            show_labels: false,
            show_swap: false
        },
        chart: {
            style: 'mini-chart',
            theme: 'light',
            accent: '2563eb',
            show_flags: true,
            show_labels: true,
            show_swap: true
        },
        dark: {
            style: 'compact',
            theme: 'dark',
            accent: '7c3aed',
            show_flags: true,
            show_labels: true,
            show_swap: true
        }
    };

    // Currency presets for dropdown menu
    const currencyPresets = {
        top10: ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'MXN'],
        top20: ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'MXN', 'BRL', 'KRW', 'SGD', 'HKD', 'NOK', 'SEK', 'DKK', 'NZD', 'ZAR', 'RUB'],
        all: [] // Will be populated with all currencies
    };

    // Current embed type (script is now default/primary, iframe is fallback)
    let currentEmbedType = 'script';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initStyleSelection();
        initSizeSelection();
        initThemeSelection();
        initColorSelection();
        initPresets();
        initDecimalSlider();
        initCopyButton();
        initLivePreview();
        initFormChanges();
        initReviewNotice();
        initSearchableCurrencyDropdowns();
        initDropdownCurrencies();
        initDisplayCurrencies();
        initEmbedCodeToggle();
        initChartPeriodButtons();
        initCurrencyDisplayMode();
        initAddCurrencyDropdown();
        initToastNotifications();
    });

    // Review Notice Handlers
    function initReviewNotice() {
        // Rate Now button - opens WordPress plugin review page
        $('#cwc-rate-now').on('click', function(e) {
            e.preventDefault();
            // Open review page in new tab
            window.open('https://wordpress.org/support/plugin/currency-converter-widget/reviews/?filter=5#new-post', '_blank');
            // Dismiss the notice after clicking
            dismissReviewNotice('rated');
        });

        // Remind Later button
        $('#cwc-remind-later').on('click', function(e) {
            e.preventDefault();
            dismissReviewNotice('remind');
        });

        // Dismiss X button
        $('#cwc-dismiss-review').on('click', function(e) {
            e.preventDefault();
            dismissReviewNotice('dismiss');
        });
    }

    // Dismiss review notice via AJAX
    function dismissReviewNotice(action) {
        $.ajax({
            url: cwcAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cwc_dismiss_review',
                dismiss_action: action,
                nonce: cwcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Animate and remove the notice
                    $('.cwc-review-notice').slideUp(300, function() {
                        $(this).remove();
                    });
                }
            }
        });
    }

    // Style Selection
    function initStyleSelection() {
        $('.cwc-style-option').on('click', function() {
            $('.cwc-style-option').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);

            // Update size dropdown options when style changes - reset to default size
            updateSizeOptions(true);

            // Show/hide Display Currencies section for multi-converter style
            updateDisplayCurrenciesVisibility();

            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });

        // Initial visibility check
        updateDisplayCurrenciesVisibility();
    }

    // Show/hide Display Currencies section based on style
    function updateDisplayCurrenciesVisibility() {
        const style = $('input[name="cwc_widget_options[style]"]:checked').val() || 'compact';
        // Styles that use Display Currencies instead of Dropdown Menu Currencies
        const displayCurrencyStyles = ['multi-expandable', 'multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];
        // Styles that have chart settings
        const chartStyles = ['mini-chart', 'rates-viewer', 'rates-viewer-compact'];
        // Styles that use Show Labels (only "+ Classic" widgets)
        const labelStyles = ['tall', 'multi-fixed'];
        // Styles that have 10 currency limit (multi-expandable and Display Charts styles)
        const limitedStyles = ['multi-expandable', 'rates-viewer', 'rates-viewer-compact'];
        // Styles that don't need "To Currency" dropdown (they use Display Currencies for results)
        const viewerStyles = ['multi-expandable', 'multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];

        if (displayCurrencyStyles.includes(style)) {
            $('#cwc-display-currencies-section').slideDown(200);
            // Hide Dropdown Menu Currencies for these styles
            $('.cwc-dropdown-currencies-section').slideUp(200);

            // Show "Max 10" label and info message for limited styles (multi-expandable, rates-viewer, rates-viewer-compact)
            if (limitedStyles.includes(style)) {
                $('#cwc-max-currencies-label').show();
                $('#cwc-display-currencies-info').show();
                $('#cwc-display-preset-all').hide();
                // Show "Add Currency Dropdown" section only for multi-expandable (it has add/remove feature)
                if (style === 'multi-expandable') {
                    $('#cwc-add-currency-dropdown-section').slideDown(200);
                } else {
                    $('#cwc-add-currency-dropdown-section').slideUp(200);
                }
            } else {
                // For other viewer styles (multi-fixed, rates-compact) - no limit, show "All" button
                $('#cwc-max-currencies-label').hide();
                $('#cwc-display-currencies-info').hide();
                $('#cwc-display-preset-all').show();
                // Hide "Add Currency Dropdown" section for non-expandable multi styles
                $('#cwc-add-currency-dropdown-section').slideUp(200);
            }
        } else {
            $('#cwc-display-currencies-section').slideUp(200);
            // Hide "Add Currency Dropdown" section for non-multi styles
            $('#cwc-add-currency-dropdown-section').slideUp(200);
            // Show Dropdown Menu Currencies for other styles
            $('.cwc-dropdown-currencies-section').slideDown(200);
        }

        // Show/hide "To Currency" for viewer styles
        // Viewer styles show a list of currencies from Display Currencies, so "To Currency" is not needed
        if (viewerStyles.includes(style)) {
            $('#cwc-to-currency-group').slideUp(200);
        } else {
            $('#cwc-to-currency-group').slideDown(200);
        }

        // Show/hide Chart Settings section
        if (chartStyles.includes(style)) {
            $('#cwc-chart-settings-section').slideDown(200);
        } else {
            $('#cwc-chart-settings-section').slideUp(200);
        }

        // Show/hide Show Labels option (only for + Classic widgets: tall, multi-fixed)
        if (labelStyles.includes(style)) {
            $('#cwc-show-labels-option').show();
        } else {
            $('#cwc-show-labels-option').hide();
        }
    }

    // Size Selection
    function initSizeSelection() {
        // Handle size dropdown change
        $('#cwc_size').on('change', function() {
            const size = $(this).val();
            const $selected = $(this).find('option:selected');

            // Show/hide custom dimensions
            if (size === 'custom') {
                $('#cwc-custom-dimensions').slideDown(200);
            } else {
                $('#cwc-custom-dimensions').slideUp(200);
                // Update custom dimensions to preset values
                const width = $selected.data('width');
                const height = $selected.data('height');
                $('#cwc_custom_width').val(width);
                $('#cwc_custom_height').val(height);
            }

            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });

        // Handle custom width/height changes
        $('#cwc_custom_width, #cwc_custom_height').on('input change', function() {
            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });
    }

    // Update size dropdown options based on current style
    // resetToDefault: if true, always reset to default size (used when switching styles)
    function updateSizeOptions(resetToDefault = false) {
        const style = $('input[name="cwc_widget_options[style]"]:checked').val() || 'compact';
        const $sizeSelect = $('#cwc_size');
        const currentSize = $sizeSelect.val();

        // Get size options for current style from PHP-passed variable
        const sizeOptions = typeof cwcSizeOptions !== 'undefined' ? cwcSizeOptions[style] : null;

        if (!sizeOptions) return;

        // Clear current options
        $sizeSelect.empty();

        // Add new options
        let firstKey = null;
        for (const [key, data] of Object.entries(sizeOptions)) {
            if (!firstKey) firstKey = key;
            const $option = $('<option>')
                .val(key)
                .text(data.label)
                .attr('data-width', data.width)
                .attr('data-height', data.height);
            $sizeSelect.append($option);
        }

        // When switching styles, always reset to default (first option)
        // Otherwise try to keep the same size if available
        if (resetToDefault || !sizeOptions[currentSize]) {
            $sizeSelect.val(firstKey);
            // Hide custom dimensions if showing
            $('#cwc-custom-dimensions').slideUp(200);
        } else {
            $sizeSelect.val(currentSize);
        }

        // Trigger change to update custom dimensions display
        $sizeSelect.trigger('change');
    }

    // Theme Selection
    function initThemeSelection() {
        $('.cwc-theme-option').on('click', function() {
            $('.cwc-theme-option').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });
    }

    // Color Selection
    function initColorSelection() {
        $('.cwc-color-option').on('click', function() {
            $('.cwc-color-option').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });
    }

    // Presets
    function initPresets() {
        $('.cwc-preset-btn').on('click', function() {
            const presetName = $(this).data('preset');
            const preset = presets[presetName];

            if (!preset) return;

            // Update style
            $('input[name="cwc_widget_options[style]"]').val([preset.style]);
            $('.cwc-style-option').removeClass('selected');
            $('.cwc-style-option input[value="' + preset.style + '"]').closest('.cwc-style-option').addClass('selected');

            // Update size options for new style - reset to default
            updateSizeOptions(true);

            // Update theme
            $('input[name="cwc_widget_options[theme]"]').val([preset.theme]);
            $('.cwc-theme-option').removeClass('selected');
            $('.cwc-theme-option input[value="' + preset.theme + '"]').closest('.cwc-theme-option').addClass('selected');

            // Update accent color
            $('input[name="cwc_widget_options[accent]"]').val([preset.accent]);
            $('.cwc-color-option').removeClass('selected');
            $('.cwc-color-option input[value="' + preset.accent + '"]').closest('.cwc-color-option').addClass('selected');

            // Update checkboxes
            $('input[name="cwc_widget_options[show_flags]"]').prop('checked', preset.show_flags);
            $('input[name="cwc_widget_options[show_labels]"]').prop('checked', preset.show_labels);
            $('input[name="cwc_widget_options[show_swap]"]').prop('checked', preset.show_swap);

            // Highlight active preset button
            $('.cwc-preset-btn').removeClass('active');
            $(this).addClass('active');

            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });
    }

    // Decimal Slider
    function initDecimalSlider() {
        $('#cwc_decimals').on('input', function() {
            $('#cwc_decimals_val').text($(this).val());
            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });
    }

    // Copy Button
    function initCopyButton() {
        $(document).on('click', '.cwc-copy-btn', function() {
            const targetId = $(this).data('copy');
            const $target = $('#' + targetId);
            const text = $target.text();
            const $btn = $(this);
            const originalHtml = $btn.html();

            // Try modern clipboard API first, fall back to execCommand
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    $btn.html('<span class="dashicons dashicons-yes"></span> Copied!');
                    setTimeout(function() {
                        $btn.html(originalHtml);
                    }, 2000);
                }).catch(function() {
                    // Fallback if clipboard API fails
                    fallbackCopy(text, $btn, originalHtml);
                });
            } else {
                // Fallback for older browsers
                fallbackCopy(text, $btn, originalHtml);
            }
        });
    }

    // Fallback copy method using textarea
    function fallbackCopy(text, $btn, originalHtml) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            $btn.html('<span class="dashicons dashicons-yes"></span> Copied!');
            setTimeout(function() {
                $btn.html(originalHtml);
            }, 2000);
        } catch (err) {
            console.error('Copy failed:', err);
            alert('Copy failed. Please select and copy manually.');
        }
        document.body.removeChild(textarea);
    }

    // Live Preview
    function initLivePreview() {
        updatePreview();
    }

    // Form Changes
    function initFormChanges() {
        // Listen for change events (dropdowns, checkboxes, radio buttons)
        $('#cwc-settings-form').on('change', 'select, input', function() {
            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });

        // Listen for input events (real-time typing in text/number fields)
        $('#cwc-settings-form').on('input', 'input[type="text"], input[type="number"]', function() {
            updatePreview();
            updateShortcode();
            updateEmbedCode();
        });
    }

    // Get current dimensions based on size selection
    function getCurrentDimensions() {
        const size = $('#cwc_size').val() || 'medium';

        if (size === 'custom') {
            return {
                width: parseInt($('#cwc_custom_width').val()) || 300,
                height: parseInt($('#cwc_custom_height').val()) || 180
            };
        }

        const $selected = $('#cwc_size option:selected');
        return {
            width: parseInt($selected.data('width')) || 300,
            height: parseInt($selected.data('height')) || 180
        };
    }

    // Update Preview
    function updatePreview() {
        const $iframe = $('#cwc-preview-iframe');
        const style = $('input[name="cwc_widget_options[style]"]:checked').val() || 'compact';
        const theme = $('input[name="cwc_widget_options[theme]"]:checked').val() || 'auto';
        const accent = $('input[name="cwc_widget_options[accent]"]:checked').val() || '2563eb';
        const from = $('#cwc_from').val() || 'USD';
        const to = $('#cwc_to').val() || 'EUR';
        const amount = $('#cwc_amount').val() || '1';
        const language = $('#cwc_language').val() || 'en';
        const flags = $('input[name="cwc_widget_options[show_flags]"]').is(':checked') ? '1' : '0';
        const labels = $('input[name="cwc_widget_options[show_labels]"]').is(':checked') ? '1' : '0';
        const swap = $('input[name="cwc_widget_options[show_swap]"]').is(':checked') ? '1' : '0';
        // Branding is always shown (hidden field value=1), brandinglink is a toggle
        const branding = $('input[name="cwc_widget_options[show_branding]"]').val() || '1';
        const brandinglink = $('input[name="cwc_widget_options[show_branding_link]"]').is(':checked') ? '1' : '0';
        const lock = $('input[name="cwc_widget_options[lock_currencies]"]').is(':checked') ? '1' : '0';
        const format = $('#cwc_format').val() || 'auto';
        const decimals = $('#cwc_decimals').val() || '2';
        const dropdownCurrencies = $('#cwc_dropdown_currencies').val() || 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
        const displayCurrencies = $('#cwc_display_currencies').val() || 'EUR,GBP,JPY';
        const chartPeriod = $('#cwc_chart_period').val() || '7d';
        const currencyDisplay = $('input[name="cwc_widget_options[currency_display]"]:checked').val() || 'iso';
        const addDropdownCurrencies = $('#cwc_add_dropdown_currencies').val() || 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';

        // Get dimensions based on size selection
        const dimensions = getCurrentDimensions();

        // Multi-currency styles - determine which currencies param to use
        const displayCurrencyStyles = ['multi-expandable', 'multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];
        // Viewer styles (non multi-expandable) that should sync dropdown with display currencies
        const viewerStyles = ['multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];
        let currenciesParam = dropdownCurrencies;
        let dropdownParam = dropdownCurrencies;

        if (displayCurrencyStyles.includes(style)) {
            // Multi-currency widgets use display currencies
            currenciesParam = displayCurrencies;

            if (style === 'multi-expandable') {
                // For multi-expandable, the "Dropdown Menu Currencies" section is hidden.
                // Use "Add Currency Dropdown" setting for the base currency selector instead.
                const addPreset = $('#cwc_add_dropdown_preset').val();
                if (addPreset === 'all') {
                    dropdownParam = 'all';
                } else {
                    dropdownParam = addDropdownCurrencies;
                }
            } else if (viewerStyles.includes(style)) {
                // For viewer styles, sync dropdown with display currencies selection
                // If "all" preset is selected, dropdown should also be "all"
                const displayPreset = $('#cwc_display_preset').val();
                if (displayPreset === 'all') {
                    dropdownParam = 'all';
                } else {
                    // Sync dropdown with display currencies for viewer styles
                    dropdownParam = displayCurrencies;
                }
            }
        }

        // Build URL
        const params = new URLSearchParams({
            style: style,
            theme: theme,
            accent: accent,
            from: from,
            to: to,
            amount: amount,
            lang: language,
            flags: flags,
            labels: labels,
            swap: swap,
            branding: branding,
            brandinglink: brandinglink,
            lock: lock,
            format: format,
            decimals: decimals,
            currencies: currenciesParam,
            dropdown: dropdownParam,
            chartperiod: chartPeriod,
            display: currencyDisplay,
            adddropdown: style === 'multi-expandable' ? addDropdownCurrencies : '',
            w: dimensions.width,
            h: dimensions.height
        });

        // Add cache-busting parameter to force iframe reload
        const url = 'https://widget.currency.wiki/v3/embed?' + params.toString() + '&_t=' + Date.now();

        // Update iframe dimensions - add extra height for dropdown visibility in preview
        const previewHeight = dimensions.height + 150; // Extra space for dropdown

        // Scale down wide widgets (like inline 480px) to fit preview container
        const maxPreviewWidth = 400;
        let scale = 1;
        if (dimensions.width > maxPreviewWidth) {
            scale = maxPreviewWidth / dimensions.width;
        }

        $iframe.attr({
            src: url,
            width: dimensions.width,
            height: previewHeight
        }).css({
            'transform': scale < 1 ? 'scale(' + scale + ')' : 'none',
            'transform-origin': 'top center'
        });

        // Update dimensions display text (show actual widget dimensions, not preview height)
        $('#cwc-preview-dimensions').text(dimensions.width + ' × ' + dimensions.height + 'px');
    }

    // Update Shortcode
    function updateShortcode() {
        const style = $('input[name="cwc_widget_options[style]"]:checked').val() || 'compact';
        const theme = $('input[name="cwc_widget_options[theme]"]:checked').val() || 'auto';
        const from = $('#cwc_from').val() || 'USD';
        const to = $('#cwc_to').val() || 'EUR';
        const size = $('#cwc_size').val() || 'medium';
        const dimensions = getCurrentDimensions();

        let shortcode = '[currencywiki_converter';
        let iframeShortcode = '[currencywiki_converter embed="iframe"';

        // Only add non-default values
        if (style !== 'compact') {
            shortcode += ' style="' + style + '"';
            iframeShortcode += ' style="' + style + '"';
        }
        if (theme !== 'auto') {
            shortcode += ' theme="' + theme + '"';
            iframeShortcode += ' theme="' + theme + '"';
        }
        if (from !== 'USD') {
            shortcode += ' from="' + from + '"';
            iframeShortcode += ' from="' + from + '"';
        }
        if (to !== 'EUR') {
            shortcode += ' to="' + to + '"';
            iframeShortcode += ' to="' + to + '"';
        }

        // Only add custom dimensions if using custom size
        // Note: size attribute is not supported in shortcode, only width/height for custom
        if (size === 'custom') {
            shortcode += ' width="' + dimensions.width + '" height="' + dimensions.height + '"';
            iframeShortcode += ' width="' + dimensions.width + '" height="' + dimensions.height + '"';
        }

        shortcode += ']';
        iframeShortcode += ']';

        $('#cwc-shortcode').text(shortcode);
        $('#cwc-iframe-shortcode').text(iframeShortcode);
    }

    // Searchable Currency Dropdowns with Flags
    function initSearchableCurrencyDropdowns() {
        // Initialize for both From and To currency selects
        initSearchableDropdown('#cwc_from', 'From Currency');
        initSearchableDropdown('#cwc_to', 'To Currency');
    }

    function initSearchableDropdown(selectId, label) {
        const $select = $(selectId);
        if (!$select.length) return;

        const currencies = typeof cwcCurrencies !== 'undefined' ? cwcCurrencies : {};
        const $wrapper = $select.parent();

        // Create custom dropdown HTML
        const $customDropdown = $(`
            <div class="cwc-searchable-dropdown">
                <button type="button" class="cwc-dropdown-toggle">
                    <span class="cwc-selected-currency">
                        <img class="cwc-flag" src="" alt="">
                        <span class="cwc-code"></span>
                        <span class="cwc-name"></span>
                    </span>
                    <svg class="cwc-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                <div class="cwc-dropdown-menu" style="display: none;">
                    <div class="cwc-search-wrapper">
                        <input type="text" class="cwc-currency-search" placeholder="Search currencies...">
                    </div>
                    <div class="cwc-currency-list"></div>
                </div>
            </div>
        `);

        // Hide original select
        $select.hide();
        $wrapper.append($customDropdown);

        const $toggle = $customDropdown.find('.cwc-dropdown-toggle');
        const $menu = $customDropdown.find('.cwc-dropdown-menu');
        const $search = $customDropdown.find('.cwc-currency-search');
        const $list = $customDropdown.find('.cwc-currency-list');

        // Populate currency list
        function populateList(filter = '') {
            $list.empty();
            const filterLower = filter.toLowerCase();

            $select.find('option').each(function() {
                const code = $(this).val();
                const currency = currencies[code] || {};
                const name = currency.name || code;
                const flag = currency.flag || code.substring(0, 2).toLowerCase();

                if (filter && !code.toLowerCase().includes(filterLower) && !name.toLowerCase().includes(filterLower)) {
                    return;
                }

                const $item = $(`
                    <div class="cwc-currency-item" data-code="${code}">
                        <img class="cwc-flag" src="https://cdn.currency.wiki/flags/${flag}.svg" alt="${code}" onerror="this.style.display='none'">
                        <span class="cwc-code">${code}</span>
                        <span class="cwc-name">${name}</span>
                    </div>
                `);

                if ($select.val() === code) {
                    $item.addClass('selected');
                }

                $list.append($item);
            });
        }

        // Update selected display
        function updateSelected() {
            const code = $select.val();
            const currency = currencies[code] || {};
            const name = currency.name || code;
            const flag = currency.flag || code.substring(0, 2).toLowerCase();

            $toggle.find('.cwc-code').text(code);
            $toggle.find('.cwc-name').text(name);
            $toggle.find('.cwc-flag').attr('src', `https://cdn.currency.wiki/flags/${flag}.svg`);
        }

        // Initial population and selection
        populateList();
        updateSelected();

        // Toggle dropdown
        $toggle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Close other dropdowns
            $('.cwc-dropdown-menu').not($menu).hide();
            $('.cwc-searchable-dropdown').not($customDropdown).removeClass('open');

            $menu.toggle();
            $customDropdown.toggleClass('open');

            if ($menu.is(':visible')) {
                $search.val('').focus();
                populateList();
            }
        });

        // Search functionality
        $search.on('input', function() {
            populateList($(this).val());
        });

        // Select currency
        $list.on('click', '.cwc-currency-item', function() {
            const code = $(this).data('code');
            $select.val(code).trigger('change');
            $menu.hide();
            $customDropdown.removeClass('open');
            updateSelected();
            populateList();
        });

        // Close on outside click
        $(document).on('click', function(e) {
            if (!$customDropdown.is(e.target) && $customDropdown.has(e.target).length === 0) {
                $menu.hide();
                $customDropdown.removeClass('open');
            }
        });

        // Close on escape
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $menu.hide();
                $customDropdown.removeClass('open');
            }
        });
    }

    // Embed Code Toggle
    function initEmbedCodeToggle() {
        // Initialize embed code on page load
        updateEmbedCode();

        // Handle toggle button clicks
        $('.cwc-embed-type-btn').on('click', function() {
            const type = $(this).data('type');
            currentEmbedType = type;

            // Update button styles
            $('.cwc-embed-type-btn').removeClass('active').css({
                'background': 'transparent',
                'box-shadow': 'none'
            });
            $(this).addClass('active').css({
                'background': '#fff',
                'box-shadow': '0 1px 2px rgba(0,0,0,0.05)'
            });

            // Show/hide info boxes
            if (type === 'iframe') {
                $('#cwc-embed-info-iframe').css('display', 'flex');
                $('#cwc-embed-info-script').hide();
            } else {
                $('#cwc-embed-info-iframe').hide();
                $('#cwc-embed-info-script').css('display', 'flex');
            }

            // Update embed code
            updateEmbedCode();
        });
    }

    // Update Embed Code
    function updateEmbedCode() {
        const style = $('input[name="cwc_widget_options[style]"]:checked').val() || 'compact';
        const theme = $('input[name="cwc_widget_options[theme]"]:checked').val() || 'auto';
        const accent = $('input[name="cwc_widget_options[accent]"]:checked').val() || '2563eb';
        const from = $('#cwc_from').val() || 'USD';
        const to = $('#cwc_to').val() || 'EUR';
        const amount = $('#cwc_amount').val() || '1';
        const language = $('#cwc_language').val() || 'en';
        const flags = $('input[name="cwc_widget_options[show_flags]"]').is(':checked') ? '1' : '0';
        const labels = $('input[name="cwc_widget_options[show_labels]"]').is(':checked') ? '1' : '0';
        const swap = $('input[name="cwc_widget_options[show_swap]"]').is(':checked') ? '1' : '0';
        const branding = $('input[name="cwc_widget_options[show_branding]"]').val() || '1';
        const brandinglink = $('input[name="cwc_widget_options[show_branding_link]"]').is(':checked') ? '1' : '0';
        const lock = $('input[name="cwc_widget_options[lock_currencies]"]').is(':checked') ? '1' : '0';
        const format = $('#cwc_format').val() || 'auto';
        const decimals = $('#cwc_decimals').val() || '2';
        const dropdownCurrencies = $('#cwc_dropdown_currencies').val() || 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
        const displayCurrencies = $('#cwc_display_currencies').val() || 'EUR,GBP,JPY';
        const chartPeriod = $('#cwc_chart_period').val() || '7d';
        const currencyDisplay = $('input[name="cwc_widget_options[currency_display]"]:checked').val() || 'iso';
        const addDropdownCurrencies = $('#cwc_add_dropdown_currencies').val() || 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';

        const dimensions = getCurrentDimensions();

        // Multi-currency styles - determine which currencies param to use
        const displayCurrencyStyles = ['multi-expandable', 'multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];
        // Viewer styles (non multi-expandable) that should sync dropdown with display currencies
        const viewerStyles = ['multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];
        let currenciesParam = dropdownCurrencies;
        let dropdownParam = dropdownCurrencies;

        if (displayCurrencyStyles.includes(style)) {
            // Multi-currency widgets use display currencies
            currenciesParam = displayCurrencies;

            if (style === 'multi-expandable') {
                // For multi-expandable, the "Dropdown Menu Currencies" section is hidden.
                // Use "Add Currency Dropdown" setting for the base currency selector instead.
                const addPreset = $('#cwc_add_dropdown_preset').val();
                if (addPreset === 'all') {
                    dropdownParam = 'all';
                } else {
                    dropdownParam = addDropdownCurrencies;
                }
            } else if (viewerStyles.includes(style)) {
                // For viewer styles, sync dropdown with display currencies selection
                // If "all" preset is selected, dropdown should also be "all"
                const displayPreset = $('#cwc_display_preset').val();
                if (displayPreset === 'all') {
                    dropdownParam = 'all';
                } else {
                    // Sync dropdown with display currencies for viewer styles
                    dropdownParam = displayCurrencies;
                }
            }
        }

        // Build params
        const params = new URLSearchParams({
            style: style,
            theme: theme,
            accent: accent,
            from: from,
            to: to,
            amount: amount,
            lang: language,
            flags: flags,
            labels: labels,
            swap: swap,
            branding: branding,
            brandinglink: brandinglink,
            lock: lock,
            format: format,
            decimals: decimals,
            currencies: currenciesParam,
            dropdown: dropdownParam,
            chartperiod: chartPeriod,
            display: currencyDisplay,
            width: dimensions.width,
            height: dimensions.height
        });

        // Add adddropdown parameter for multi-expandable style
        if (style === 'multi-expandable' && addDropdownCurrencies) {
            params.set('adddropdown', addDropdownCurrencies);
        }

        // Use production widget subdomain URL
        const baseUrl = 'https://widget.currency.wiki';

        let embedCode;

        if (currentEmbedType === 'script') {
            // Script embed
            const widgetId = 'cww-' + Math.random().toString(36).substr(2, 9);
            embedCode = `<div id="${widgetId}"></div>\n<script src="${baseUrl}/v3/script.js?${params.toString()}&container=${widgetId}"></script>`;
        } else {
            // Iframe embed
            embedCode = `<iframe
  src="${baseUrl}/v3/embed?${params.toString()}"
  width="${dimensions.width}"
  height="${dimensions.height}"
  frameborder="0"
  style="border-radius: 12px; overflow: hidden;"
></iframe>`;
        }

        $('#cwc-embed-code').text(embedCode);
    }

    // Initialize Dropdown Menu Currencies
    function initDropdownCurrencies() {
        const $presetButtons = $('.cwc-dropdown-preset');
        const $currencyPills = $('.cwc-currency-pill');
        const $presetInput = $('#cwc_dropdown_preset');
        const $currenciesInput = $('#cwc_dropdown_currencies');
        const $infoText = $('#cwc-currency-info-text');
        const $pillsContainer = $('#cwc-currency-pills');
        const $pillsWrapper = $('#cwc-currency-pills-wrapper');
        const $searchContainer = $('#cwc-currency-search-container');
        const $searchInput = $('#cwc-currency-search');

        // Info text for different presets
        const infoTexts = {
            top10: 'Most popular currencies: USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, INR, MXN',
            top20: 'Top 20 currencies by trading volume',
            custom: 'Select currencies to show in the dropdown menu',
            all: 'All 170+ currencies will be available in the dropdown'
        };

        // Handle search input
        $searchInput.on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $currencyPills.each(function() {
                const code = $(this).data('currency').toLowerCase();
                if (code.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Handle preset button clicks
        $presetButtons.on('click', function() {
            const preset = $(this).data('preset');

            // Update active state
            $presetButtons.removeClass('active');
            $(this).addClass('active');

            // Update hidden input
            $presetInput.val(preset);

            // Update info text
            $infoText.text(infoTexts[preset] || '');

            // Clear search
            $searchInput.val('');
            $currencyPills.show();

            // Update currency pills based on preset
            if (preset === 'top10') {
                $currencyPills.each(function() {
                    const code = $(this).data('currency');
                    if (currencyPresets.top10.includes(code)) {
                        $(this).addClass('selected');
                    } else {
                        $(this).removeClass('selected');
                    }
                });
                $currenciesInput.val(currencyPresets.top10.join(','));
                $pillsWrapper.hide();
                $searchContainer.hide();
            } else if (preset === 'top20') {
                $currencyPills.each(function() {
                    const code = $(this).data('currency');
                    if (currencyPresets.top20.includes(code)) {
                        $(this).addClass('selected');
                    } else {
                        $(this).removeClass('selected');
                    }
                });
                $currenciesInput.val(currencyPresets.top20.join(','));
                $pillsWrapper.hide();
                $searchContainer.hide();
            } else if (preset === 'custom') {
                // Keep current selection, show pills and search for custom editing
                $pillsWrapper.show();
                $searchContainer.show();
            } else if (preset === 'all') {
                // Select all and hide pills
                $currencyPills.addClass('selected');
                $currenciesInput.val('all');
                $pillsWrapper.hide();
                $searchContainer.hide();
            }

            // Trigger preview update
            updatePreview();
            updateEmbedCode();
        });

        // Handle currency pill clicks (only in custom mode)
        $currencyPills.on('click', function() {
            const currentPreset = $presetInput.val();

            // If not in custom mode, switch to custom
            if (currentPreset !== 'custom') {
                $presetButtons.removeClass('active');
                $presetButtons.filter('[data-preset="custom"]').addClass('active');
                $presetInput.val('custom');
                $infoText.text(infoTexts.custom);
                $searchContainer.show();
                $pillsWrapper.show();
            }

            // Toggle selection
            $(this).toggleClass('selected');

            // Update hidden input with selected currencies
            const selectedCurrencies = [];
            $currencyPills.filter('.selected').each(function() {
                selectedCurrencies.push($(this).data('currency'));
            });
            $currenciesInput.val(selectedCurrencies.join(','));

            // Trigger preview update
            updatePreview();
            updateEmbedCode();
        });
    }

    // Display Currencies Presets (for Multi-Converter and viewer styles)
    const displayCurrencyPresets = {
        top3: ['EUR', 'GBP', 'JPY'],
        top10: ['EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'MXN', 'BRL'],
        all: [] // Will be populated dynamically with all available currencies
    };

    // Initialize Display Currencies
    function initDisplayCurrencies() {
        const $presetButtons = $('.cwc-display-preset');
        const $presetInput = $('#cwc_display_preset');
        const $currenciesInput = $('#cwc_display_currencies');
        const $pillsContainer = $('#cwc-display-currencies-pills');
        const $customSelection = $('#cwc-display-custom-selection');
        const $searchInput = $('#cwc-display-search');

        // Handle preset button clicks
        $presetButtons.on('click', function() {
            const preset = $(this).data('preset');

            // Update active state and styles
            $presetButtons.removeClass('active').css({
                'background': '#fff',
                'color': '#374151'
            });
            $(this).addClass('active').css({
                'background': '#3B82F6',
                'color': '#fff'
            });

            // Update hidden input
            $presetInput.val(preset);

            // Show/hide custom selection
            if (preset === 'custom') {
                $customSelection.slideDown(200);
            } else {
                $customSelection.slideUp(200);
            }

            // Update currencies based on preset
            let currencies = [];
            if (preset === 'top3') {
                currencies = displayCurrencyPresets.top3;
            } else if (preset === 'top10') {
                currencies = displayCurrencyPresets.top10;
            } else if (preset === 'all') {
                // Get all available currencies from cwcCurrencies
                if (typeof cwcCurrencies !== 'undefined') {
                    currencies = Object.keys(cwcCurrencies);
                }
            } else if (preset === 'custom') {
                // Keep current selection for custom
                currencies = $currenciesInput.val().split(',').filter(c => c);
            }

            if (preset !== 'custom') {
                $currenciesInput.val(currencies.join(','));
                updateDisplayPills(currencies);
                updateDisplayCurrencyPillsSelection(currencies);
            }

            // Trigger preview update
            updatePreview();
            updateEmbedCode();
        });

        // Handle custom currency pill clicks
        $(document).on('click', '.cwc-display-currency-pill', function() {
            const $pill = $(this);
            const currency = $pill.data('currency');
            let selectedCurrencies = $currenciesInput.val().split(',').filter(c => c);

            if ($pill.hasClass('selected')) {
                // Remove currency
                selectedCurrencies = selectedCurrencies.filter(c => c !== currency);
                $pill.removeClass('selected').css({
                    'background': '#fff',
                    'color': '#374151',
                    'border-color': '#e2e8f0'
                });
                $pill.find('span:last').text() === '✓' && $pill.find('span:last').remove();
            } else {
                // Add currency (max 10 for multi-expandable, rates-viewer, rates-viewer-compact styles)
                const currentStyle = $('input[name="cwc_widget_options[style]"]:checked').val() || 'compact';
                const limitedStyles = ['multi-expandable', 'rates-viewer', 'rates-viewer-compact'];
                if (limitedStyles.includes(currentStyle) && selectedCurrencies.length >= 10) {
                    alert('Maximum 10 currencies allowed for this widget style');
                    return;
                }
                selectedCurrencies.push(currency);
                $pill.addClass('selected').css({
                    'background': '#3B82F6',
                    'color': '#fff',
                    'border-color': '#3B82F6'
                });
                if ($pill.find('span:contains("✓")').length === 0) {
                    $pill.append('<span style="margin-left: 2px;">✓</span>');
                }
            }

            // Update hidden input and pills display
            $currenciesInput.val(selectedCurrencies.join(','));
            updateDisplayPills(selectedCurrencies);

            // Trigger preview update
            updatePreview();
            updateEmbedCode();
        });

        // Handle search filtering
        $searchInput.on('input', function() {
            const query = $(this).val().toLowerCase();
            $('.cwc-display-currency-pill').each(function() {
                const currency = $(this).data('currency').toLowerCase();
                const currencyData = typeof cwcCurrencies !== 'undefined' ? cwcCurrencies[$(this).data('currency')] : {};
                const name = (currencyData && currencyData.name ? currencyData.name : '').toLowerCase();

                if (currency.includes(query) || name.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

    // Update display currency pills selection state
    function updateDisplayCurrencyPillsSelection(selectedCurrencies) {
        $('.cwc-display-currency-pill').each(function() {
            const $pill = $(this);
            const currency = $pill.data('currency');
            const isSelected = selectedCurrencies.includes(currency);

            // Remove existing checkmark
            $pill.find('span').each(function() {
                if ($(this).text() === '✓') {
                    $(this).remove();
                }
            });

            if (isSelected) {
                $pill.addClass('selected').css({
                    'background': '#3B82F6',
                    'color': '#fff',
                    'border-color': '#3B82F6'
                });
                $pill.append('<span style="margin-left: 2px;">✓</span>');
            } else {
                $pill.removeClass('selected').css({
                    'background': '#fff',
                    'color': '#374151',
                    'border-color': '#e2e8f0'
                });
            }
        });
    }

    // Update display currency pills
    function updateDisplayPills(currencies) {
        const $container = $('#cwc-display-currencies-pills');
        const allCurrencies = typeof cwcCurrencies !== 'undefined' ? cwcCurrencies : {};

        $container.empty();

        currencies.forEach(code => {
            const currency = allCurrencies[code] || {};
            const flag = currency.flag || '';

            const $pill = $(`
                <span class="cwc-display-pill" data-currency="${code}" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; font-size: 12px;">
                    ${flag ? '<img src="https://cdn.currency.wiki/flags/' + flag + '.svg" alt="' + code + '" style="width: 16px; height: 12px; border-radius: 2px; object-fit: cover;">' : ''}
                    <span>${code}</span>
                </span>
            `);

            $container.append($pill);
        });
    }

    // Initialize Add Currency Dropdown (for multi-expandable)
    function initAddCurrencyDropdown() {
        const $presetButtons = $('.cwc-add-dropdown-preset');
        const $presetInput = $('#cwc_add_dropdown_preset');
        const $currenciesInput = $('#cwc_add_dropdown_currencies');
        const $infoText = $('#cwc-add-dropdown-info-text');
        const $customSelection = $('#cwc-add-dropdown-custom-selection');
        const $searchInput = $('#cwc-add-dropdown-search');
        const $currencyPills = $('.cwc-add-dropdown-currency-pill');

        // Info text for different presets
        const infoTexts = {
            top10: 'Top 10 currencies: USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, INR, MXN',
            top20: 'Top 20 currencies including BRL, KRW, SGD, HKD, NZD, etc.',
            all: 'All 170+ currencies with search functionality',
            custom: 'Custom selection of currencies'
        };

        // Handle preset button clicks
        $presetButtons.on('click', function() {
            const preset = $(this).data('preset');

            // Update active state and styles
            $presetButtons.removeClass('active').css({
                'background': '#fff',
                'color': '#374151',
                'border-color': '#e2e8f0'
            });
            $(this).addClass('active').css({
                'background': '#3B82F6',
                'color': '#fff',
                'border-color': '#3B82F6'
            });

            // Update hidden input
            $presetInput.val(preset);

            // Update info text
            $infoText.text(infoTexts[preset] || '');

            // Show/hide custom selection
            if (preset === 'custom') {
                $customSelection.slideDown(200);
            } else {
                $customSelection.slideUp(200);
            }

            // Update currencies based on preset
            if (preset === 'top10') {
                $currenciesInput.val(currencyPresets.top10.join(','));
                updateAddDropdownPillsSelection(currencyPresets.top10);
            } else if (preset === 'top20') {
                $currenciesInput.val(currencyPresets.top20.join(','));
                updateAddDropdownPillsSelection(currencyPresets.top20);
            } else if (preset === 'all') {
                $currenciesInput.val('all');
                // Select all pills visually
                $currencyPills.addClass('selected').each(function() {
                    $(this).css({
                        'background': '#3B82F6',
                        'color': '#fff',
                        'border-color': '#3B82F6'
                    });
                    if ($(this).find('span:contains("✓")').length === 0) {
                        $(this).append('<span style="margin-left: 2px;">✓</span>');
                    }
                });
            }
            // For 'custom', keep current selection

            // Trigger preview update
            updatePreview();
            updateEmbedCode();
        });

        // Handle currency pill clicks (for custom mode)
        $currencyPills.on('click', function() {
            const currentPreset = $presetInput.val();

            // If not in custom mode, switch to custom
            if (currentPreset !== 'custom') {
                $presetButtons.removeClass('active').css({
                    'background': '#fff',
                    'color': '#374151',
                    'border-color': '#e2e8f0'
                });
                $presetButtons.filter('[data-preset="custom"]').addClass('active').css({
                    'background': '#3B82F6',
                    'color': '#fff',
                    'border-color': '#3B82F6'
                });
                $presetInput.val('custom');
                $infoText.text(infoTexts.custom);
                $customSelection.slideDown(200);
            }

            // Toggle selection
            const $pill = $(this);
            const isSelected = $pill.hasClass('selected');

            // Remove existing checkmark
            $pill.find('span').each(function() {
                if ($(this).text() === '✓') {
                    $(this).remove();
                }
            });

            if (isSelected) {
                $pill.removeClass('selected').css({
                    'background': '#fff',
                    'color': '#374151',
                    'border-color': '#e2e8f0'
                });
            } else {
                $pill.addClass('selected').css({
                    'background': '#3B82F6',
                    'color': '#fff',
                    'border-color': '#3B82F6'
                });
                $pill.append('<span style="margin-left: 2px;">✓</span>');
            }

            // Update hidden input with selected currencies
            const selectedCurrencies = [];
            $currencyPills.filter('.selected').each(function() {
                selectedCurrencies.push($(this).data('currency'));
            });
            $currenciesInput.val(selectedCurrencies.join(','));

            // Trigger preview update
            updatePreview();
            updateEmbedCode();
        });

        // Handle search filtering
        $searchInput.on('input', function() {
            const query = $(this).val().toLowerCase();
            $currencyPills.each(function() {
                const currency = $(this).data('currency').toLowerCase();
                if (currency.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

    // Update add dropdown currency pills selection state
    function updateAddDropdownPillsSelection(selectedCurrencies) {
        $('.cwc-add-dropdown-currency-pill').each(function() {
            const $pill = $(this);
            const currency = $pill.data('currency');
            const isSelected = selectedCurrencies.includes(currency);

            // Remove existing checkmark
            $pill.find('span').each(function() {
                if ($(this).text() === '✓') {
                    $(this).remove();
                }
            });

            if (isSelected) {
                $pill.addClass('selected').css({
                    'background': '#3B82F6',
                    'color': '#fff',
                    'border-color': '#3B82F6'
                });
                $pill.append('<span style="margin-left: 2px;">✓</span>');
            } else {
                $pill.removeClass('selected').css({
                    'background': '#fff',
                    'color': '#374151',
                    'border-color': '#e2e8f0'
                });
            }
        });
    }

    // Chart Period Buttons
    function initChartPeriodButtons() {
        $('.cwc-period-btn').on('click', function() {
            const period = $(this).data('period');

            // Update hidden input
            $('#cwc_chart_period').val(period);

            // Update button styles
            $('.cwc-period-btn').removeClass('active').css({
                'background': '#fff',
                'color': '#374151',
                'border-color': '#e2e8f0'
            });
            $(this).addClass('active').css({
                'background': '#3B82F6',
                'color': '#fff',
                'border-color': '#3B82F6'
            });

            // Update preview
            updatePreview();
            updateEmbedCode();
        });
    }

    // Currency Display Mode (ISO vs Symbol)
    function initCurrencyDisplayMode() {
        $('.cwc-display-mode-option').on('click', function() {
            const $label = $(this);
            const value = $label.find('input[type="radio"]').val();

            // Update visual selection
            $('.cwc-display-mode-option').removeClass('selected').css({
                'border-color': '#e2e8f0',
                'background': '#fff'
            });
            $label.addClass('selected').css({
                'border-color': '#3B82F6',
                'background': '#eff6ff'
            });

            // Check the radio button
            $label.find('input[type="radio"]').prop('checked', true);

            // Update preview and embed code
            updatePreview();
            updateEmbedCode();
        });
    }

    // Toast Notification System
    function initToastNotifications() {
        // Create toast container if it doesn't exist
        if ($('#cwc-toast-container').length === 0) {
            $('body').append(`
                <div id="cwc-toast-notification" class="cwc-toast-notification">
                    <div class="cwc-toast-icon">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                    <div class="cwc-toast-content">
                        <p class="cwc-toast-title">Settings Saved</p>
                        <p class="cwc-toast-message">Your widget settings have been updated successfully.</p>
                    </div>
                    <button type="button" class="cwc-toast-close">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            `);
        }

        // Check for WordPress admin notices on page load and show toast
        const $wpNotices = $('.cwc-admin-wrap').siblings('.notice, .updated, .error').add($('.cwc-admin-wrap .notice, .cwc-admin-wrap .updated, .cwc-admin-wrap .error'));

        if ($wpNotices.length > 0) {
            $wpNotices.each(function() {
                const $notice = $(this);
                const isError = $notice.hasClass('notice-error') || $notice.hasClass('error');
                const message = $notice.find('p').text() || $notice.text();

                // Only show toast for settings-related messages
                if (message.toLowerCase().includes('saved') || message.toLowerCase().includes('updated') || message.toLowerCase().includes('settings')) {
                    showToast(isError ? 'error' : 'success', message);
                }

                // Hide the original WordPress notice
                $notice.hide();
            });
        }

        // Also check for URL parameter (for settings saved redirect)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('settings-updated') === 'true') {
            showToast('success', 'Your widget settings have been saved successfully.');
        }

        // Close button handler
        $(document).on('click', '.cwc-toast-close', function() {
            hideToast();
        });

        // Auto-hide after clicking outside
        $(document).on('click', function(e) {
            const $toast = $('#cwc-toast-notification');
            if ($toast.hasClass('show') && !$(e.target).closest('#cwc-toast-notification').length) {
                // Don't auto-hide on outside click, let it auto-dismiss
            }
        });
    }

    // Show toast notification
    function showToast(type, message) {
        const $toast = $('#cwc-toast-notification');

        // Update toast content based on type
        $toast.removeClass('success error').addClass(type);

        if (type === 'success') {
            $toast.find('.cwc-toast-icon .dashicons').attr('class', 'dashicons dashicons-yes-alt');
            $toast.find('.cwc-toast-title').text('Settings Saved');
        } else {
            $toast.find('.cwc-toast-icon .dashicons').attr('class', 'dashicons dashicons-warning');
            $toast.find('.cwc-toast-title').text('Error');
        }

        $toast.find('.cwc-toast-message').text(message || (type === 'success' ? 'Your settings have been saved.' : 'Something went wrong.'));

        // Show toast with animation
        setTimeout(function() {
            $toast.addClass('show');
        }, 100);

        // Auto-hide after 4 seconds
        setTimeout(function() {
            hideToast();
        }, 4000);
    }

    // Hide toast notification
    function hideToast() {
        $('#cwc-toast-notification').removeClass('show');
    }

})(jQuery);
