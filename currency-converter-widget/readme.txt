=== Currency Converter Widget ===
Contributors: currencywiki
Tags: currency converter, exchange rates, currency calculator, forex, money converter
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 4.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free, fast, and beautiful currency converter widget with 170+ currencies, live exchange rates, and 11 widget styles.

== Description ==

**Currency Converter Widget** is the easiest way to add a professional currency converter to your WordPress site. Powered by [Currency.wiki](https://currency.wiki), it offers real-time exchange rates for 170+ world currencies.

[View Full Documentation](https://currency.wiki/tools/wordpress/doc)

Convert currencies on the go with our free Android app: [Currency Converter App](https://play.google.com/store/apps/details?id=com.currencywiki.app)

= Key Features =

* **11 Widget Styles** - Mini, Square, Tall, Inline, Compact, Mini Chart, Multi Currency, and more
* **170+ Currencies** - All major world currencies with live exchange rates
* **Gutenberg Block** - Native WordPress block editor support
* **Shortcode Support** - Use [currencywiki_converter] anywhere
* **Customizable** - Themes, colors, languages, and display options
* **Responsive** - Works perfectly on all devices
* **Fast & Lightweight** - Loads asynchronously, no impact on page speed
* **Multi-language** - 78 languages supported
* **Historical Charts** - Show rate history (selected styles)

= Widget Styles =

1. **Mini** (250x140) - Minimal footprint
2. **Square** (250x250) - Perfect for sidebars
3. **Tall** (200x280) - Vertical layout
4. **Inline** (480x56) - Single line, great for headers
5. **Compact** (280x200) - Default balanced style
6. **Mini Chart** (250x260) - With historical rate chart
7. **Multi Expandable** (300x400) - Multiple currency converter
8. **Multi Fixed** (300x340) - Fixed multi-currency view
9. **Rates Compact** (220x300) - Exchange rates table
10. **Rates Viewer** (300x500) - Full rates display
11. **Rates Viewer Compact** (300x400) - Compact rates table

= Customization Options =

* **Theme** - Light, Dark, or Auto (system preference)
* **Accent Colors** - Blue, Green, Purple, Red, Orange, Slate
* **Default Currencies** - Set from/to currencies
* **Display Options** - Flags, labels, swap button, branding
* **Number Format** - US, European, French, Swiss styles
* **Decimal Places** - 0 to 6 decimal precision
* **Lock Currencies** - Prevent user changes
* **Language** - 78 languages including English, Spanish, French, German, Italian, Portuguese, Russian, Japanese, Korean, Chinese, Arabic, Hindi, Thai, Vietnamese, Turkish, Polish, Dutch, Swedish, and many more

= Usage =

**Gutenberg Block:**
Search for "Currency Converter" in the block inserter and customize in the sidebar.

**Shortcode:**
`[currencywiki_converter]`

**Short Alias:**
`[currencywiki]`

**With Options:**
`[currencywiki_converter style="compact" theme="light" from="USD" to="EUR" amount="100"]`

**All Shortcode Attributes:**

* `style` - Widget style (mini, square, tall, inline, compact, mini-chart, multi-expandable, multi-fixed, rates-compact, rates-viewer, rates-viewer-compact)
* `theme` - Theme (auto, light, dark)
* `accent` - Accent color hex without # (2563eb, 059669, 7c3aed, dc2626, ea580c, 475569)
* `from` - Source currency code (USD, EUR, GBP, etc.)
* `to` - Target currency code
* `amount` - Default amount
* `lang` - Language code - 78 supported (en, es, fr, de, it, pt, pt-br, ru, ja, ko, zh, zh-tw, ar, hi, th, vi, tr, pl, nl, sv, and more)
* `flags` - Show flags (1/0)
* `labels` - Show labels (1/0)
* `swap` - Show swap button (1/0)
* `branding` - Show branding (1/0)
* `lock` - Lock currency selection (1/0)
* `format` - Number format (auto, en-US, de-DE, fr-FR, de-CH)
* `decimals` - Decimal places (0-6)

== Installation ==

1. Upload the `currency-converter-widget` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Currency Converter to customize defaults
4. Use the Gutenberg block or shortcode to add the widget

== Frequently Asked Questions ==

= Is this widget free? =

Yes! The widget is completely free to use with full functionality.

= How often are rates updated? =

Exchange rates are updated frequently from reliable financial data sources.

= Can I use multiple widgets on one page? =

Yes, you can add as many currency converter widgets as needed.

= Does it work with page builders? =

Yes! Use the shortcode [currencywiki_converter] or [currencywiki] in any page builder.

= Is the widget GDPR compliant? =

The widget does not collect any personal user data. It only fetches exchange rates.

= Can I disable the branding? =

You can disable the clickable link, but the branding text is always shown.

* **Settings**: Toggle "Branding Link" off
* **Shortcode**: `brandinglink="0"` (shows text only, no link)

== Screenshots ==

1. Widget Compact Style - Light Theme
2. Widget Mini Chart Style - With historical data
3. Multi Currency Converter
4. Admin Settings Dashboard
5. Gutenberg Block Editor - Block Selection
6. Gutenberg Block Editor - Settings Panel

== Changelog ==
= 4.1.2 =
* Fixed: Smoother live preview in the admin when switching widget styles (no more resize flicker)

= 4.1.1 =
* Fixed: Block editor preview now displays the widget at its true size (removed the extra empty space below the widget)

= 4.1.0 =
* Added: Custom accent color — choose any hex color in addition to the preset swatches, in both the Advanced Builder and the WordPress dashboard
* Improved: Generated shortcode now carries your accent color when it differs from the default

= 4.0.1 =
* Fixed: Embed code now properly reflects all settings changes (theme, accent color, style, size, decimals)
* Fixed: Currency flag images now display correctly in admin dashboard currency pills
* Fixed: Multi-Expandable base currency dropdown now syncs with Add Currency Dropdown setting
* Fixed: Widget flags no longer show blank for currencies outside the dropdown preset

= 4.0.0 =
* Complete rebuild with new modern widget engine
* Added 11 beautiful widget styles
* Added native Gutenberg block support
* Added multi-language support (78 languages)
* Added historical rate charts
* Added number format options
* Added decimal precision control
* New admin dashboard with live preview
* Performance improvements
* Backwards compatible with legacy [currency_bcc] shortcode
* Backwards compatible with legacy sidebar widget

= 3.0.3 =
* Bug fixes and improvements

= 2.0.0 =
* Added dark mode support
* Added more currency options
* Improved mobile responsiveness

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 4.0.0 =
Major update with completely redesigned widgets, Gutenberg block support, and many new features. Your existing widgets will continue to work - backwards compatibility included!
