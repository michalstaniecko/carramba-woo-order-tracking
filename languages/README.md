# Translations

This directory contains translation files for the Carramba WooCommerce Order Tracking plugin.

## Available Translations

- **Norwegian Bokmål** (nb_NO) - Complete translation

## How to Use

WordPress will automatically load the appropriate translation based on your site's language settings (Settings > General > Site Language).

## Translation Files

- `.pot` - Template file containing all translatable strings
- `.po` - Human-readable translation file
- `.mo` - Compiled translation file used by WordPress

## Contributing Translations

To contribute a new translation:

1. Copy `carramba-woo-order-tracking.pot` to `carramba-woo-order-tracking-{locale}.po`
2. Translate all strings in the `.po` file
3. Compile the `.po` file to `.mo` using: `msgfmt carramba-woo-order-tracking-{locale}.po -o carramba-woo-order-tracking-{locale}.mo`
4. Submit a pull request

## Need Help?

For translation questions or to request a new language, please open an issue on the [GitHub repository](https://github.com/michalstaniecko/carramba-woo-order-tracking).
