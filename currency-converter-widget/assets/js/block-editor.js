/**
 * Currency Converter Widget - Gutenberg Block
 * Version: 3.0.0
 */

(function(blocks, element, blockEditor, components, i18n) {
    const { registerBlockType } = blocks;
    const { createElement: el, Fragment } = element;
    const { InspectorControls, useBlockProps } = blockEditor;
    const { PanelBody, SelectControl, ToggleControl, RangeControl, TextControl } = components;
    const { __ } = i18n;

    // Widget styles with dimensions (names match WordPress dashboard)
    const widgetStyles = [
        { label: 'Mini', value: 'mini' },
        { label: 'Square', value: 'square' },
        { label: 'Tall Sidebar + Classic', value: 'tall' },
        { label: 'Inline', value: 'inline' },
        { label: 'Compact', value: 'compact' },
        { label: 'Mini + Chart', value: 'mini-chart' },
        { label: 'Multi-Converter', value: 'multi-expandable' },
        { label: 'Exchange Rates + Classic', value: 'multi-fixed' },
        { label: 'Exchange Rates - Compact', value: 'rates-compact' },
        { label: 'Display Charts', value: 'rates-viewer' },
        { label: 'Display Charts - Compact', value: 'rates-viewer-compact' }
    ];

    const styleDimensions = {
        'mini': { width: 250, height: 140 },
        'square': { width: 250, height: 250 },
        'tall': { width: 200, height: 280 },
        'inline': { width: 480, height: 56 },
        'compact': { width: 280, height: 200 },  // Fixed: was swapped
        'mini-chart': { width: 250, height: 260 },
        'multi-expandable': { width: 300, height: 320 },
        'multi-fixed': { width: 300, height: 340 },
        'rates-compact': { width: 220, height: 300 },
        'rates-viewer': { width: 300, height: 500 },
        'rates-viewer-compact': { width: 300, height: 400 }
    };

    // Theme options
    const themeOptions = [
        { label: 'Auto (System)', value: 'auto' },
        { label: 'Light', value: 'light' },
        { label: 'Dark', value: 'dark' }
    ];

    // Accent color options
    const accentColors = [
        { label: 'Blue', value: '2563eb' },
        { label: 'Green', value: '059669' },
        { label: 'Purple', value: '7c3aed' },
        { label: 'Red', value: 'dc2626' },
        { label: 'Orange', value: 'ea580c' },
        { label: 'Slate', value: '475569' }
    ];

    // All supported currencies (170+)
    const currencies = [
        { label: 'United Arab Emirates Dirham (AED)', value: 'AED' },
        { label: 'Afghan Afghani (AFN)', value: 'AFN' },
        { label: 'Albanian Lek (ALL)', value: 'ALL' },
        { label: 'Armenian Dram (AMD)', value: 'AMD' },
        { label: 'Netherlands Antillean Guilder (ANG)', value: 'ANG' },
        { label: 'Angolan Kwanza (AOA)', value: 'AOA' },
        { label: 'Argentine Peso (ARS)', value: 'ARS' },
        { label: 'Australian Dollar (AUD)', value: 'AUD' },
        { label: 'Aruban Florin (AWG)', value: 'AWG' },
        { label: 'Azerbaijani Manat (AZN)', value: 'AZN' },
        { label: 'Bosnia-Herzegovina Convertible Mark (BAM)', value: 'BAM' },
        { label: 'Barbadian Dollar (BBD)', value: 'BBD' },
        { label: 'Bangladeshi Taka (BDT)', value: 'BDT' },
        { label: 'Bulgarian Lev (BGN)', value: 'BGN' },
        { label: 'Bahraini Dinar (BHD)', value: 'BHD' },
        { label: 'Burundian Franc (BIF)', value: 'BIF' },
        { label: 'Bermudan Dollar (BMD)', value: 'BMD' },
        { label: 'Brunei Dollar (BND)', value: 'BND' },
        { label: 'Bolivian Boliviano (BOB)', value: 'BOB' },
        { label: 'Brazilian Real (BRL)', value: 'BRL' },
        { label: 'Bahamian Dollar (BSD)', value: 'BSD' },
        { label: 'Bitcoin (BTC)', value: 'BTC' },
        { label: 'Bhutanese Ngultrum (BTN)', value: 'BTN' },
        { label: 'Botswanan Pula (BWP)', value: 'BWP' },
        { label: 'Belarusian Ruble (BYN)', value: 'BYN' },
        { label: 'Belize Dollar (BZD)', value: 'BZD' },
        { label: 'Canadian Dollar (CAD)', value: 'CAD' },
        { label: 'Congolese Franc (CDF)', value: 'CDF' },
        { label: 'Swiss Franc (CHF)', value: 'CHF' },
        { label: 'Chilean Unit of Account (CLF)', value: 'CLF' },
        { label: 'Chilean Peso (CLP)', value: 'CLP' },
        { label: 'Chinese Yuan Offshore (CNH)', value: 'CNH' },
        { label: 'Chinese Yuan (CNY)', value: 'CNY' },
        { label: 'Colombian Peso (COP)', value: 'COP' },
        { label: 'Costa Rican Colón (CRC)', value: 'CRC' },
        { label: 'Cuban Convertible Peso (CUC)', value: 'CUC' },
        { label: 'Cuban Peso (CUP)', value: 'CUP' },
        { label: 'Cape Verdean Escudo (CVE)', value: 'CVE' },
        { label: 'Czech Republic Koruna (CZK)', value: 'CZK' },
        { label: 'Djiboutian Franc (DJF)', value: 'DJF' },
        { label: 'Danish Krone (DKK)', value: 'DKK' },
        { label: 'Dominican Peso (DOP)', value: 'DOP' },
        { label: 'Algerian Dinar (DZD)', value: 'DZD' },
        { label: 'Egyptian Pound (EGP)', value: 'EGP' },
        { label: 'Eritrean Nakfa (ERN)', value: 'ERN' },
        { label: 'Ethiopian Birr (ETB)', value: 'ETB' },
        { label: 'Euro (EUR)', value: 'EUR' },
        { label: 'Fijian Dollar (FJD)', value: 'FJD' },
        { label: 'Falkland Islands Pound (FKP)', value: 'FKP' },
        { label: 'British Pound Sterling (GBP)', value: 'GBP' },
        { label: 'Georgian Lari (GEL)', value: 'GEL' },
        { label: 'Guernsey Pound (GGP)', value: 'GGP' },
        { label: 'Ghanaian Cedi (GHS)', value: 'GHS' },
        { label: 'Gibraltar Pound (GIP)', value: 'GIP' },
        { label: 'Gambian Dalasi (GMD)', value: 'GMD' },
        { label: 'Guinean Franc (GNF)', value: 'GNF' },
        { label: 'Guatemalan Quetzal (GTQ)', value: 'GTQ' },
        { label: 'Guyanaese Dollar (GYD)', value: 'GYD' },
        { label: 'Hong Kong Dollar (HKD)', value: 'HKD' },
        { label: 'Honduran Lempira (HNL)', value: 'HNL' },
        { label: 'Croatian Kuna (HRK)', value: 'HRK' },
        { label: 'Haitian Gourde (HTG)', value: 'HTG' },
        { label: 'Hungarian Forint (HUF)', value: 'HUF' },
        { label: 'Indonesian Rupiah (IDR)', value: 'IDR' },
        { label: 'Israeli New Sheqel (ILS)', value: 'ILS' },
        { label: 'Isle of Man Pound (IMP)', value: 'IMP' },
        { label: 'Indian Rupee (INR)', value: 'INR' },
        { label: 'Iraqi Dinar (IQD)', value: 'IQD' },
        { label: 'Iranian Rial (IRR)', value: 'IRR' },
        { label: 'Icelandic Króna (ISK)', value: 'ISK' },
        { label: 'Jersey Pound (JEP)', value: 'JEP' },
        { label: 'Jamaican Dollar (JMD)', value: 'JMD' },
        { label: 'Jordanian Dinar (JOD)', value: 'JOD' },
        { label: 'Japanese Yen (JPY)', value: 'JPY' },
        { label: 'Kenyan Shilling (KES)', value: 'KES' },
        { label: 'Kyrgystani Som (KGS)', value: 'KGS' },
        { label: 'Cambodian Riel (KHR)', value: 'KHR' },
        { label: 'Comorian Franc (KMF)', value: 'KMF' },
        { label: 'North Korean Won (KPW)', value: 'KPW' },
        { label: 'South Korean Won (KRW)', value: 'KRW' },
        { label: 'Kuwaiti Dinar (KWD)', value: 'KWD' },
        { label: 'Cayman Islands Dollar (KYD)', value: 'KYD' },
        { label: 'Kazakhstani Tenge (KZT)', value: 'KZT' },
        { label: 'Laotian Kip (LAK)', value: 'LAK' },
        { label: 'Lebanese Pound (LBP)', value: 'LBP' },
        { label: 'Sri Lankan Rupee (LKR)', value: 'LKR' },
        { label: 'Liberian Dollar (LRD)', value: 'LRD' },
        { label: 'Lesotho Loti (LSL)', value: 'LSL' },
        { label: 'Libyan Dinar (LYD)', value: 'LYD' },
        { label: 'Moroccan Dirham (MAD)', value: 'MAD' },
        { label: 'Moldovan Leu (MDL)', value: 'MDL' },
        { label: 'Malagasy Ariary (MGA)', value: 'MGA' },
        { label: 'Macedonian Denar (MKD)', value: 'MKD' },
        { label: 'Myanmar Kyat (MMK)', value: 'MMK' },
        { label: 'Mongolian Tugrik (MNT)', value: 'MNT' },
        { label: 'Macanese Pataca (MOP)', value: 'MOP' },
        { label: 'Mauritanian Ouguiya (MRU)', value: 'MRU' },
        { label: 'Mauritian Rupee (MUR)', value: 'MUR' },
        { label: 'Maldivian Rufiyaa (MVR)', value: 'MVR' },
        { label: 'Malawian Kwacha (MWK)', value: 'MWK' },
        { label: 'Mexican Peso (MXN)', value: 'MXN' },
        { label: 'Malaysian Ringgit (MYR)', value: 'MYR' },
        { label: 'Mozambican Metical (MZN)', value: 'MZN' },
        { label: 'Namibian Dollar (NAD)', value: 'NAD' },
        { label: 'Nigerian Naira (NGN)', value: 'NGN' },
        { label: 'Nicaraguan Córdoba (NIO)', value: 'NIO' },
        { label: 'Norwegian Krone (NOK)', value: 'NOK' },
        { label: 'Nepalese Rupee (NPR)', value: 'NPR' },
        { label: 'New Zealand Dollar (NZD)', value: 'NZD' },
        { label: 'Omani Rial (OMR)', value: 'OMR' },
        { label: 'Panamanian Balboa (PAB)', value: 'PAB' },
        { label: 'Peruvian Nuevo Sol (PEN)', value: 'PEN' },
        { label: 'Papua New Guinean Kina (PGK)', value: 'PGK' },
        { label: 'Philippine Peso (PHP)', value: 'PHP' },
        { label: 'Pakistani Rupee (PKR)', value: 'PKR' },
        { label: 'Polish Zloty (PLN)', value: 'PLN' },
        { label: 'Paraguayan Guarani (PYG)', value: 'PYG' },
        { label: 'Qatari Rial (QAR)', value: 'QAR' },
        { label: 'Romanian Leu (RON)', value: 'RON' },
        { label: 'Serbian Dinar (RSD)', value: 'RSD' },
        { label: 'Russian Ruble (RUB)', value: 'RUB' },
        { label: 'Rwandan Franc (RWF)', value: 'RWF' },
        { label: 'Saudi Riyal (SAR)', value: 'SAR' },
        { label: 'Solomon Islands Dollar (SBD)', value: 'SBD' },
        { label: 'Seychellois Rupee (SCR)', value: 'SCR' },
        { label: 'Sudanese Pound (SDG)', value: 'SDG' },
        { label: 'Swedish Krona (SEK)', value: 'SEK' },
        { label: 'Singapore Dollar (SGD)', value: 'SGD' },
        { label: 'Saint Helena Pound (SHP)', value: 'SHP' },
        { label: 'Sierra Leonean Leone New (SLE)', value: 'SLE' },
        { label: 'Sierra Leonean Leone Old (SLL)', value: 'SLL' },
        { label: 'Somali Shilling (SOS)', value: 'SOS' },
        { label: 'Surinamese Dollar (SRD)', value: 'SRD' },
        { label: 'South Sudanese Pound (SSP)', value: 'SSP' },
        { label: 'São Tomé & Príncipe Dobra Old (STD)', value: 'STD' },
        { label: 'São Tomé & Príncipe Dobra New (STN)', value: 'STN' },
        { label: 'Salvadoran Colón (SVC)', value: 'SVC' },
        { label: 'Syrian Pound (SYP)', value: 'SYP' },
        { label: 'Swazi Lilangeni (SZL)', value: 'SZL' },
        { label: 'Thai Baht (THB)', value: 'THB' },
        { label: 'Tajikistani Somoni (TJS)', value: 'TJS' },
        { label: 'Turkmenistani Manat (TMT)', value: 'TMT' },
        { label: 'Tunisian Dinar (TND)', value: 'TND' },
        { label: 'Tongan Paʻanga (TOP)', value: 'TOP' },
        { label: 'Turkish Lira (TRY)', value: 'TRY' },
        { label: 'Trinidad & Tobago Dollar (TTD)', value: 'TTD' },
        { label: 'New Taiwan Dollar (TWD)', value: 'TWD' },
        { label: 'Tanzanian Shilling (TZS)', value: 'TZS' },
        { label: 'Ukrainian Hryvnia (UAH)', value: 'UAH' },
        { label: 'Ugandan Shilling (UGX)', value: 'UGX' },
        { label: 'United States Dollar (USD)', value: 'USD' },
        { label: 'Uruguayan Peso (UYU)', value: 'UYU' },
        { label: 'Uzbekistan Som (UZS)', value: 'UZS' },
        { label: 'Venezuelan Bolívar Soberano (VES)', value: 'VES' },
        { label: 'Vietnamese Dong (VND)', value: 'VND' },
        { label: 'Vanuatu Vatu (VUV)', value: 'VUV' },
        { label: 'Samoan Tala (WST)', value: 'WST' },
        { label: 'Central African CFA Franc (XAF)', value: 'XAF' },
        { label: 'Silver troy ounce (XAG)', value: 'XAG' },
        { label: 'Gold troy ounce (XAU)', value: 'XAU' },
        { label: 'East Caribbean Dollar (XCD)', value: 'XCD' },
        { label: 'Caribbean Guilder (XCG)', value: 'XCG' },
        { label: 'Special Drawing Rights (XDR)', value: 'XDR' },
        { label: 'West African CFA Franc (XOF)', value: 'XOF' },
        { label: 'Palladium troy ounce (XPD)', value: 'XPD' },
        { label: 'CFP Franc (XPF)', value: 'XPF' },
        { label: 'Platinum troy ounce (XPT)', value: 'XPT' },
        { label: 'Yemeni Rial (YER)', value: 'YER' },
        { label: 'South African Rand (ZAR)', value: 'ZAR' },
        { label: 'Zambian Kwacha (ZMW)', value: 'ZMW' },
        { label: 'Zimbabwe Gold (ZWG)', value: 'ZWG' },
        { label: 'Zimbabwean Dollar (ZWL)', value: 'ZWL' }
    ];

    // Languages (60+ supported)
    const languages = [
        { label: 'Afrikaans', value: 'af' },
        { label: 'Amharic', value: 'am' },
        { label: 'Arabic', value: 'ar' },
        { label: 'Bengali', value: 'bn' },
        { label: 'Bosnian', value: 'bs' },
        { label: 'Bulgarian', value: 'bg' },
        { label: 'Burmese', value: 'my' },
        { label: 'Chinese (Simplified)', value: 'zh-CN' },
        { label: 'Chinese (Traditional)', value: 'zh-TW' },
        { label: 'Croatian', value: 'hr' },
        { label: 'Czech', value: 'cs' },
        { label: 'Danish', value: 'da' },
        { label: 'Dutch', value: 'nl' },
        { label: 'English', value: 'en' },
        { label: 'Estonian', value: 'et' },
        { label: 'Filipino', value: 'fil' },
        { label: 'Finnish', value: 'fi' },
        { label: 'French', value: 'fr' },
        { label: 'Georgian', value: 'ka' },
        { label: 'German', value: 'de' },
        { label: 'Greek', value: 'el' },
        { label: 'Hebrew', value: 'he' },
        { label: 'Hindi', value: 'hi' },
        { label: 'Hungarian', value: 'hu' },
        { label: 'Icelandic', value: 'is' },
        { label: 'Indonesian', value: 'id' },
        { label: 'Irish', value: 'ga' },
        { label: 'Italian', value: 'it' },
        { label: 'Japanese', value: 'ja' },
        { label: 'Kazakh', value: 'kk' },
        { label: 'Khmer', value: 'km' },
        { label: 'Korean', value: 'ko' },
        { label: 'Latvian', value: 'lv' },
        { label: 'Lithuanian', value: 'lt' },
        { label: 'Malay', value: 'ms' },
        { label: 'Maltese', value: 'mt' },
        { label: 'Mongolian', value: 'mn' },
        { label: 'Nepali', value: 'ne' },
        { label: 'Norwegian', value: 'no' },
        { label: 'Persian', value: 'fa' },
        { label: 'Polish', value: 'pl' },
        { label: 'Portuguese', value: 'pt' },
        { label: 'Portuguese (Brazil)', value: 'pt-BR' },
        { label: 'Romanian', value: 'ro' },
        { label: 'Russian', value: 'ru' },
        { label: 'Serbian', value: 'sr' },
        { label: 'Sinhala', value: 'si' },
        { label: 'Slovak', value: 'sk' },
        { label: 'Slovenian', value: 'sl' },
        { label: 'Spanish', value: 'es' },
        { label: 'Swahili', value: 'sw' },
        { label: 'Swedish', value: 'sv' },
        { label: 'Tamil', value: 'ta' },
        { label: 'Telugu', value: 'te' },
        { label: 'Thai', value: 'th' },
        { label: 'Turkish', value: 'tr' },
        { label: 'Ukrainian', value: 'uk' },
        { label: 'Urdu', value: 'ur' },
        { label: 'Uzbek', value: 'uz' },
        { label: 'Vietnamese', value: 'vi' },
        { label: 'Armenian', value: 'hy' }
    ];

    // Number formats (must match admin dashboard values)
    const numberFormats = [
        { label: 'Auto (Browser)', value: 'auto' },
        { label: '1,234.56 (US)', value: 'us' },
        { label: '1.234,56 (EU)', value: 'eu' },
        { label: '1 234,56 (French)', value: 'french' },
        { label: '1,23,456.78 (Indian)', value: 'indian' }
    ];

    // Styles that use Display Currencies (don't need "To Currency")
    const displayCurrencyStyles = ['multi-expandable', 'multi-fixed', 'rates-compact', 'rates-viewer', 'rates-viewer-compact'];

    // Display currency preset options (for viewer styles)
    const displayPresets = [
        { label: 'Top 3 (EUR, GBP, JPY)', value: 'top3' },
        { label: 'Top 10', value: 'top10' },
        { label: 'All Currencies', value: 'all' },
        { label: 'Custom', value: 'custom' }
    ];

    // Dropdown menu currency presets (for classic converter styles)
    const dropdownPresets = [
        { label: 'Top 10', value: 'top10' },
        { label: 'Top 20', value: 'top20' },
        { label: 'All (170+)', value: 'all' },
        { label: 'Custom', value: 'custom' }
    ];

    // Styles that support Show Labels (classic converter styles with From/To labels)
    const labelStyles = ['tall', 'multi-fixed'];

    // Chart period options
    const chartPeriods = [
        { label: __('7 Days', 'currency-converter-widget'), value: '7d' },
        { label: __('14 Days', 'currency-converter-widget'), value: '14d' },
        { label: __('30 Days', 'currency-converter-widget'), value: '30d' },
        { label: __('90 Days', 'currency-converter-widget'), value: '90d' }
    ];

    // Styles that show chart settings
    const chartStyles = ['mini-chart', 'rates-viewer', 'rates-viewer-compact'];

    // Build embed URL
    function buildEmbedUrl(attributes) {
        const isDisplayCurrencyStyle = displayCurrencyStyles.includes(attributes.style);

        // Get display currencies based on preset
        let displayCurrencies = attributes.displayCurrencies || 'EUR,GBP,JPY';
        if (attributes.displayPreset === 'top3') {
            displayCurrencies = 'EUR,GBP,JPY';
        } else if (attributes.displayPreset === 'top10') {
            displayCurrencies = 'EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN,BRL';
        } else if (attributes.displayPreset === 'all') {
            displayCurrencies = 'all';
        }

        // Get dropdown currencies based on preset (for classic converter styles)
        let dropdownCurrencies = attributes.dropdownCurrencies || 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
        if (attributes.dropdownPreset === 'top10') {
            dropdownCurrencies = 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
        } else if (attributes.dropdownPreset === 'top20') {
            dropdownCurrencies = 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN,BRL,KRW,SGD,HKD,NOK,SEK,DKK,NZD,ZAR,RUB';
        } else if (attributes.dropdownPreset === 'all') {
            dropdownCurrencies = 'all';
        }

        // For multi-expandable, dropdownCurrencies is used for the "+ Add Currency" dropdown
        let addDropdownCurrencies = dropdownCurrencies;
        if (attributes.style === 'multi-expandable') {
            if (attributes.dropdownPreset === 'top10') {
                addDropdownCurrencies = 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN';
            } else if (attributes.dropdownPreset === 'top20') {
                addDropdownCurrencies = 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN,BRL,KRW,SGD,HKD,NOK,SEK,DKK,NZD,ZAR,RUB';
            } else if (attributes.dropdownPreset === 'all') {
                addDropdownCurrencies = 'all';
            }
        }

        const params = new URLSearchParams({
            style: attributes.style,
            theme: attributes.theme,
            accent: attributes.accent,
            from: attributes.from,
            to: attributes.to,
            amount: attributes.amount,
            lang: attributes.language,
            flags: attributes.showFlags ? '1' : '0',
            labels: attributes.showLabels ? '1' : '0',
            swap: attributes.showSwap ? '1' : '0',
            branding: attributes.showBranding ? '1' : '0',
            brandinglink: attributes.showBrandingLink ? '1' : '0',
            lock: attributes.lockCurrencies ? '1' : '0',
            format: attributes.numberFormat,
            decimals: attributes.decimals,
            display: attributes.currencyDisplayMode || 'iso',
            currencies: displayCurrencies,
            dropdown: isDisplayCurrencyStyle ? displayCurrencies : dropdownCurrencies,
            chartperiod: attributes.chartPeriod || '7d'
        });

        // Add adddropdown parameter for multi-expandable style
        if (attributes.style === 'multi-expandable') {
            params.append('adddropdown', addDropdownCurrencies);
        }

        // Add cache buster to force iframe reload when attributes change
        params.append('_cb', Date.now());
        return 'https://widget.currency.wiki/v3/embed?' + params.toString();
    }

    // Currency.Wiki brand logo icon SVG
    const brandIcon = el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        viewBox: '0 0 1440 1440',
        width: 24,
        height: 24
    },
        el('path', {
            fill: 'currentColor',
            d: 'M1375.63 731.24c-19.65-63.67-127.53-92.45-278.84-85.18-10.55-114.74-49.02-299.27-102.56-461.98-233.4 108.87-433.17 98.66-637.71 50.53 153.37 193.34 291.21 483.5 316.17 821.94 94.98-20.82 291.22-71.18 421.57-154.97 8.63-77.27 11.48-158.07 2.62-254.86 115.76-2.58 196 20.32 210.91 68.69 17.27 56.06-57.01 133.17-184.78 205.21-.03.03-.09.06-.12.1-.05.02-.17.09-.32.17-.38.2-.99.55-1.89 1.04-.47.3-.93.56-1.43.79-9.44 5.32-19.19 10.55-29.21 15.78.02-.2.05-.41.09-.61-77.57 39.26-280.4 133.81-477.61 157.67-.09 0-.14.02-.24.02-195.19 32.3-344.09 14.45-364.99-53.29-12.97-42.2 25.96-96.34 100.51-151.27 19.93-14.7 42.41-29.45 67.13-44.05-24.87 14.06-48.03 28.37-69.24 42.77-106.33 72.16-164.03 146.6-146.3 204.07 33.44 108.7 323.94 115.58 648.76 15.47 324.83-100.12 560.99-269.36 527.48-378.04zM688 510.44l-24.62 12.95-12.55-23.86c-17.23 8.09-35.93 12.17-48.37 11.65l-7.92-33.34c13.59.23 31.18-2.29 47.5-10.87 14.31-7.53 21.2-18.21 15.91-28.26-5.02-9.54-16.22-11.35-38.11-7.85-31.63 5.1-56.56 2.17-69.38-22.18-11.63-22.1-5.17-47.63 18.93-67.03l-12.55-23.86 24.62-12.95 11.62 22.1c17.23-8.1 30.38-10.53 40.79-10.87l7.64 32.22c-7.99.67-22.58.96-40.67 10.48-16.32 8.6-17.9 18.4-14.19 25.43 4.35 8.29 15.91 8.93 41.5 5.74 35.43-5.17 54.75 2.31 66.64 24.91 11.76 22.35 5.97 49.76-20.27 69.98L688 510.44zM911.41 830l11.44 57.29-45.67 9.1-10.74-53.98c-35.33 5.85-72.38 2.82-95.06-6.6l4.59-55.27c24.25 8.45 57.8 13.95 91.72 7.21 35.09-7 55.58-28.99 50.27-55.74-4.95-25.06-27.67-37.3-71.88-44.17-62.65-9.48-105.83-28.67-116.11-80.47-9.56-47.88 16.58-91.25 70.82-112.46l-10.87-54.56 45.67-9.12 10.3 51.81c35.26-5.85 60.86-2.27 80.44 3.1l-4.82 54.2c-14.58-3.46-41.6-11.37-79.47-3.85-38.97 7.73-48.92 30.56-45.14 49.45 4.56 22.86 27.09 31.7 78.19 41.19 67.03 10.97 100.83 35.51 110.67 85.07 9.44 47.27-15.1 95.59-74.35 117.8z'
        }),
        el('path', {
            fill: 'currentColor',
            d: 'M632.42 1070.23c-9.69-339.25-197.08-613.88-197.08-613.88l-267.96 90.84C341.64 666.36 483.8 798.83 631.92 1071.6c-.54-1.61-1.1-3.21-1.66-4.82 1.38 2.24 2.16 3.45 2.16 3.45zM434.69 630.29c-17.39 10.96-37.82 13.93-56.03 7.07-8.59-3-16.95-8.39-24.47-16.79l-11.53 7.27-7.95-12.59 9.76-6.16c-.55-.89-1.23-1.95-1.9-3.02-1.13-1.77-2.07-3.65-3.18-5.44l-9.93 6.26-7.95-12.59 11.88-7.49c-3.73-11.03-4.62-21.88-3.09-32.02 2.96-17.73 12.89-33.41 29.56-43.94 10.82-6.82 21.78-10.27 29.95-11.45l7.79 23.34c-5.84.96-14.57 3.25-22.37 8.16-8.51 5.38-14.52 13.12-15.77 23.33-.69 4.41-.06 9.72 1.6 15.11l44.17-27.86 7.94 12.59-46.82 29.53c.94 1.89 2.17 3.84 3.3 5.62.67 1.07 1.11 1.77 1.78 2.83l47-29.65 7.94 12.59-44.52 28.08c4.6 4.54 9.13 7.37 13.68 8.72 9.71 2.81 19.72.21 28.59-5.39 8.16-5.15 14.82-13.05 17.31-17.36l16.68 17.06c-3.9 7.18-12.06 17.03-23.42 24.19z'
        }),
        el('path', {
            fill: 'currentColor',
            d: 'M235.02 656.67 105.78 766.52c96.86 42.15 176.05 84.31 239.91 123.22.71.42 1.41.85 2.11 1.28 164.61 100.63 226.47 179.21 226.47 179.21-74.31-206.78-339.25-413.56-339.25-413.56zm54.43 177.16-14.81-7.78c1.06-11.81-3.71-28.07-16.14-34.58-2.57-1.35-4.86-2.32-7.62-3.29l-9.4 17.93-18.84-9.88 7.68-14.63c-5.06-1.48-10.65-3.72-15.59-6.3-23.05-12.09-29.41-36.88-16.55-61.4 5.27-10.07 11.4-16.41 15.71-19.28l18.64 15.6c-3.84 2.42-7.8 6.87-11.35 13.63-6.8 12.99-.5 21.43 8.28 26.04 4.75 2.49 9.43 4.25 14.58 5.55l13.15-25.07 18.84 9.88-11.89 22.68c4.67 2.21 8.68 4.79 12.13 8.46 3.7 4.04 6.75 9.38 8.29 15.79l.37.18 25.61-48.85 22.68 11.89-43.77 83.43z'
        })
    );

    // Register block
    registerBlockType('currency-wiki/converter', {
        title: __('Currency Converter', 'currency-converter-widget'),
        description: __('Display a currency converter widget with live exchange rates.', 'currency-converter-widget'),
        category: 'widgets',
        icon: brandIcon,
        keywords: [
            __('currency', 'currency-converter-widget'),
            __('converter', 'currency-converter-widget'),
            __('exchange', 'currency-converter-widget'),
            __('rates', 'currency-converter-widget'),
            __('money', 'currency-converter-widget')
        ],
        supports: {
            html: false,
            align: ['left', 'center', 'right', 'wide']
        },
        attributes: {
            style: {
                type: 'string',
                default: 'compact'
            },
            theme: {
                type: 'string',
                default: 'auto'
            },
            accent: {
                type: 'string',
                default: '2563eb'
            },
            from: {
                type: 'string',
                default: 'USD'
            },
            to: {
                type: 'string',
                default: 'EUR'
            },
            amount: {
                type: 'string',
                default: '1'
            },
            language: {
                type: 'string',
                default: 'en'
            },
            showFlags: {
                type: 'boolean',
                default: true
            },
            showLabels: {
                type: 'boolean',
                default: true
            },
            showSwap: {
                type: 'boolean',
                default: true
            },
            showBranding: {
                type: 'boolean',
                default: true
            },
            showBrandingLink: {
                type: 'boolean',
                default: true
            },
            lockCurrencies: {
                type: 'boolean',
                default: false
            },
            numberFormat: {
                type: 'string',
                default: 'auto'
            },
            decimals: {
                type: 'number',
                default: 2
            },
            displayPreset: {
                type: 'string',
                default: 'top3'
            },
            displayCurrencies: {
                type: 'string',
                default: 'EUR,GBP,JPY'
            },
            dropdownPreset: {
                type: 'string',
                default: 'top10'
            },
            dropdownCurrencies: {
                type: 'string',
                default: 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN'
            },
            chartPeriod: {
                type: 'string',
                default: '7d'
            },
            size: {
                type: 'string',
                default: 'default'
            },
            customWidth: {
                type: 'string',
                default: ''
            },
            customHeight: {
                type: 'string',
                default: ''
            },
            currencyDisplayMode: {
                type: 'string',
                default: 'iso'
            }
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();
            const defaultDimensions = styleDimensions[attributes.style] || { width: 300, height: 180 };
            // Use custom dimensions if size is 'custom' and values are provided
            const dimensions = attributes.size === 'custom' && attributes.customWidth && attributes.customHeight
                ? { width: parseInt(attributes.customWidth) || defaultDimensions.width, height: parseInt(attributes.customHeight) || defaultDimensions.height }
                : defaultDimensions;
            const embedUrl = buildEmbedUrl(attributes);

            return el(Fragment, {},
                // Inspector Controls (Sidebar)
                el(InspectorControls, {},
                    // Style Panel
                    el(PanelBody, { title: __('Widget Style', 'currency-converter-widget'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Style', 'currency-converter-widget'),
                            value: attributes.style,
                            options: widgetStyles,
                            onChange: function(value) {
                                setAttributes({ style: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Size', 'currency-converter-widget'),
                            value: attributes.size,
                            options: [
                                { label: 'Default', value: 'default' },
                                { label: 'Custom', value: 'custom' }
                            ],
                            onChange: function(value) {
                                setAttributes({ size: value });
                                // When switching to custom, pre-fill with default dimensions
                                if (value === 'custom' && !attributes.customWidth && !attributes.customHeight) {
                                    var dims = styleDimensions[attributes.style] || { width: 300, height: 180 };
                                    setAttributes({ customWidth: String(dims.width), customHeight: String(dims.height) });
                                }
                            }
                        }),
                        // Show width/height fields only when Custom is selected
                        attributes.size === 'custom' && el(TextControl, {
                            label: __('Width (px)', 'currency-converter-widget'),
                            type: 'number',
                            value: attributes.customWidth,
                            onChange: function(value) {
                                setAttributes({ customWidth: value });
                            }
                        }),
                        attributes.size === 'custom' && el(TextControl, {
                            label: __('Height (px)', 'currency-converter-widget'),
                            type: 'number',
                            value: attributes.customHeight,
                            onChange: function(value) {
                                setAttributes({ customHeight: value });
                            }
                        })
                    ),

                    // Currencies Panel
                    el(PanelBody, { title: __('Currencies', 'currency-converter-widget'), initialOpen: true },
                        el(SelectControl, {
                            label: __('From Currency', 'currency-converter-widget'),
                            value: attributes.from,
                            options: currencies,
                            onChange: function(value) {
                                setAttributes({ from: value });
                            }
                        }),
                        // Only show "To Currency" for non-display-currency styles
                        !displayCurrencyStyles.includes(attributes.style) && el(SelectControl, {
                            label: __('To Currency', 'currency-converter-widget'),
                            value: attributes.to,
                            options: currencies,
                            onChange: function(value) {
                                setAttributes({ to: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Default Amount', 'currency-converter-widget'),
                            value: attributes.amount,
                            onChange: function(value) {
                                setAttributes({ amount: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Lock Currency Selection', 'currency-converter-widget'),
                            help: __('Prevent users from changing currencies', 'currency-converter-widget'),
                            checked: attributes.lockCurrencies,
                            onChange: function(value) {
                                setAttributes({ lockCurrencies: value });
                            }
                        }),
                        // Display Currencies section (only for display currency styles)
                        displayCurrencyStyles.includes(attributes.style) && el(Fragment, {},
                            el('div', { style: { marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e0e0e0' } },
                                el('p', { style: { marginBottom: '8px', fontWeight: '600' } }, __('Display Currencies', 'currency-converter-widget')),
                                el(SelectControl, {
                                    label: __('Preset', 'currency-converter-widget'),
                                    value: attributes.displayPreset,
                                    // No "all" option for styles with 10 currency limit (multi-expandable, rates-viewer, rates-viewer-compact)
                                    options: ['multi-expandable', 'rates-viewer', 'rates-viewer-compact'].includes(attributes.style)
                                        ? displayPresets.filter(function(p) { return p.value !== 'all'; })
                                        : displayPresets,
                                    onChange: function(value) {
                                        setAttributes({ displayPreset: value });
                                        if (value === 'top3') {
                                            setAttributes({ displayCurrencies: 'EUR,GBP,JPY' });
                                        } else if (value === 'top10') {
                                            setAttributes({ displayCurrencies: 'EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN,BRL' });
                                        } else if (value === 'all') {
                                            setAttributes({ displayCurrencies: 'all' });
                                        }
                                    }
                                }),
                                attributes.displayPreset === 'custom' && el(TextControl, {
                                    label: __('Custom Currencies (comma-separated)', 'currency-converter-widget'),
                                    help: __('e.g., EUR,GBP,JPY,CAD', 'currency-converter-widget'),
                                    value: attributes.displayCurrencies,
                                    onChange: function(value) {
                                        setAttributes({ displayCurrencies: value.toUpperCase() });
                                    }
                                }),
                                // Show max 10 message for limited styles
                                ['multi-expandable', 'rates-viewer', 'rates-viewer-compact'].includes(attributes.style) && el('p', {
                                    style: { marginTop: '8px', fontSize: '12px', color: '#757575' }
                                }, __('Select up to 10 currencies.', 'currency-converter-widget'))
                            )
                        ),
                        // Dropdown Menu Currencies section (for classic converter styles AND multi-expandable)
                        (!displayCurrencyStyles.includes(attributes.style) || attributes.style === 'multi-expandable') && el(Fragment, {},
                            el('div', { style: { marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e0e0e0' } },
                                el('p', { style: { marginBottom: '8px', fontWeight: '600' } },
                                    attributes.style === 'multi-expandable'
                                        ? __('Add Currency Dropdown', 'currency-converter-widget')
                                        : __('Dropdown Menu Currencies', 'currency-converter-widget')
                                ),
                                attributes.style === 'multi-expandable' && el('p', {
                                    style: { marginBottom: '8px', fontSize: '12px', color: '#757575' }
                                }, __('Currencies available in "+ Add Currency" dropdown', 'currency-converter-widget')),
                                el(SelectControl, {
                                    label: __('Preset', 'currency-converter-widget'),
                                    value: attributes.dropdownPreset,
                                    options: dropdownPresets,
                                    onChange: function(value) {
                                        setAttributes({ dropdownPreset: value });
                                        if (value === 'top10') {
                                            setAttributes({ dropdownCurrencies: 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN' });
                                        } else if (value === 'top20') {
                                            setAttributes({ dropdownCurrencies: 'USD,EUR,GBP,JPY,CAD,AUD,CHF,CNY,INR,MXN,BRL,KRW,SGD,HKD,NOK,SEK,DKK,NZD,ZAR,RUB' });
                                        } else if (value === 'all') {
                                            setAttributes({ dropdownCurrencies: 'all' });
                                        }
                                    }
                                }),
                                attributes.dropdownPreset === 'custom' && el(TextControl, {
                                    label: __('Custom Currencies (comma-separated)', 'currency-converter-widget'),
                                    help: __('e.g., USD,EUR,GBP,JPY,CAD', 'currency-converter-widget'),
                                    value: attributes.dropdownCurrencies,
                                    onChange: function(value) {
                                        setAttributes({ dropdownCurrencies: value.toUpperCase() });
                                    }
                                })
                            )
                        )
                    ),

                    // Chart Settings Panel (only for chart styles)
                    chartStyles.includes(attributes.style) && el(PanelBody, { title: __('Chart Settings', 'currency-converter-widget'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Default Period', 'currency-converter-widget'),
                            value: attributes.chartPeriod,
                            options: chartPeriods,
                            onChange: function(value) {
                                setAttributes({ chartPeriod: value });
                            }
                        })
                    ),

                    // Appearance Panel
                    el(PanelBody, { title: __('Appearance', 'currency-converter-widget'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Theme', 'currency-converter-widget'),
                            value: attributes.theme,
                            options: themeOptions,
                            onChange: function(value) {
                                setAttributes({ theme: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Accent Color', 'currency-converter-widget'),
                            value: attributes.accent,
                            options: accentColors,
                            onChange: function(value) {
                                setAttributes({ accent: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Custom color', 'currency-converter-widget'),
                            value: attributes.accent ? '#' + attributes.accent : '',
                            placeholder: '#F66C3B',
                            onChange: function(value) {
                                setAttributes({ accent: (value || '').replace(/[^0-9a-fA-F]/g, '').toLowerCase().slice(0, 6) });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Language', 'currency-converter-widget'),
                            value: attributes.language,
                            options: languages,
                            onChange: function(value) {
                                setAttributes({ language: value });
                            }
                        })
                    ),

                    // Display Options Panel
                    el(PanelBody, { title: __('Display Options', 'currency-converter-widget'), initialOpen: false },
                        el(ToggleControl, {
                            label: __('Show Flags', 'currency-converter-widget'),
                            checked: attributes.showFlags,
                            onChange: function(value) {
                                setAttributes({ showFlags: value });
                            }
                        }),
                        // Show Labels only for styles that support it (tall, multi-fixed - matching admin dashboard)
                        labelStyles.includes(attributes.style) && el(ToggleControl, {
                            label: __('Show Labels', 'currency-converter-widget'),
                            help: __('Show "From" and "To" labels above currency dropdowns', 'currency-converter-widget'),
                            checked: attributes.showLabels,
                            onChange: function(value) {
                                setAttributes({ showLabels: value });
                            }
                        }),
                        // Show Swap only for styles that have swap button
                        !displayCurrencyStyles.includes(attributes.style) && el(ToggleControl, {
                            label: __('Show Swap Button', 'currency-converter-widget'),
                            checked: attributes.showSwap,
                            onChange: function(value) {
                                setAttributes({ showSwap: value });
                            }
                        }),
                        // Branding link option (branding text is always shown)
                        el(ToggleControl, {
                            label: __('Clickable Branding', 'currency-converter-widget'),
                            help: __('Make "Powered by Currency.Wiki" clickable', 'currency-converter-widget'),
                            checked: attributes.showBrandingLink,
                            onChange: function(value) {
                                setAttributes({ showBrandingLink: value });
                            }
                        }),
                        // Currency Display Format toggle
                        el(ToggleControl, {
                            label: __('Show Currency Symbol', 'currency-converter-widget'),
                            help: __('Display currency symbol instead of ISO code', 'currency-converter-widget'),
                            checked: attributes.currencyDisplayMode === 'symbol',
                            onChange: function(value) {
                                setAttributes({ currencyDisplayMode: value ? 'symbol' : 'iso' });
                            }
                        })
                    ),

                    // Number Format Panel
                    el(PanelBody, { title: __('Number Format', 'currency-converter-widget'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Number Format', 'currency-converter-widget'),
                            value: attributes.numberFormat,
                            options: numberFormats,
                            onChange: function(value) {
                                setAttributes({ numberFormat: value });
                            }
                        }),
                        el(RangeControl, {
                            label: __('Decimal Places', 'currency-converter-widget'),
                            value: attributes.decimals,
                            onChange: function(value) {
                                setAttributes({ decimals: value });
                            },
                            min: 0,
                            max: 4
                        })
                    )
                ),

                // Block Preview
                // Add extra height for dropdown menus in editor preview (iframe can't overflow)
                el('div', blockProps,
                    el('div', {
                        className: 'cwc-block-preview',
                        style: {
                            display: 'flex',
                            justifyContent: 'center',
                            padding: '20px',
                            backgroundColor: '#f8fafc',
                            borderRadius: '8px',
                            border: '1px solid #e2e8f0'
                        }
                    },
                        el('iframe', {
                            src: embedUrl,
                            width: dimensions.width,
                            height: dimensions.height + Math.max(0, 180 - dimensions.height * 0.3), // Dynamic padding: less for taller widgets
                            frameBorder: '0',
                            scrolling: 'no',
                            style: {
                                border: 'none',
                                borderRadius: '8px',
                                boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                            }
                        })
                    )
                )
            );
        },

        save: function() {
            // Rendered via PHP on frontend
            return null;
        }
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
