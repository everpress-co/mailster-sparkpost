# Mailster SparkPost Integration

Contributors: everpress, mailster, xaverb  
Tags: sparkpost, mailster, newsletter, email, mailsteresp  
Requires at least: 6.0  
Tested up to: 7.1  
Stable tag: 1.9.0  
License: GPLv2 or later

Deliver Mailster newsletters with SparkPost via WEB API or SMTP, with EU endpoint, subaccounts, and bounce syncing.

## Description

**Mailster SparkPost Integration** connects [SparkPost](https://www.sparkpost.com/) to the [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SparkPost) so you can send campaigns and transactional mail through SparkPost instead of your web host’s mail server.

Use it when you want scalable newsletter delivery on SparkPost while keeping Mailster’s campaign editor, lists, and automation.

> This plugin requires [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SparkPost) **4.0** or newer and a [SparkPost](https://www.sparkpost.com/) account with a Master API key and a verified sending domain. When SparkPost is the active delivery method, outgoing email content and delivery metadata are sent to SparkPost (US or EU API, depending on your endpoint setting).

### Why send Mailster newsletters with SparkPost?

- **SparkPost delivery for Mailster** — Registers SparkPost as a delivery method under **Newsletter → Settings → Delivery**.
- **WEB API or SMTP API** — Send via the SparkPost Transmissions (WEB) API (recommended), or use SparkPost SMTP with TLS on port **587** or **2525**.
- **US or EU endpoint** — Choose the default SparkPost API/SMTP hosts or the **EU** endpoint (`api.eu.sparkpost.com` / `smtp.eu.sparkpostmail.com`).
- **API key verification** — Verifies your Master API key against the SparkPost account and can sync Mailster’s send limit from SparkPost usage.
- **Sending domains and subaccounts** — Lists verified sending domains and optional SparkPost subaccounts from your account.
- **Open and click tracking in SparkPost** — Optionally enable SparkPost open/click tracking (account defaults, none, opens, clicks, or both).
- **IP pool and tags** — Set a dedicated IP pool ID and comma-separated tags attached to each transmission.
- **Bounce and complaint sync** — Reads SparkPost message events and applies bounces, complaints, and unsubscribes in Mailster (SparkPost handles soft delays).

### How it works

1. Install and activate **Mailster SparkPost Integration** alongside Mailster 4.0+.
2. Create a SparkPost account, verify your sending domain (DNS), and create a **Master** API key with permissions for Account, Message Events, Sending Domains, Subaccounts, Transmissions, and (if using SMTP) Send via SMTP.
3. Open **Newsletter → Settings → Delivery**, select the **SparkPost** tab, enter your API key, choose the endpoint if needed, and save.
4. Select WEB API or SMTP, configure tracking, IP pool, and tags if desired, then send a test email from a verified From domain.
5. Run your Mailster campaigns through SparkPost.

Setup walkthrough: [Send your newsletters via SparkPost](https://kb.mailster.co/send-your-newsletters-via-sparkpost/?utm_campaign=wporg&utm_source=SparkPost&utm_medium=readme).

## Installation

1. Upload the entire `mailster-sparkpost` folder to the `/wp-content/plugins/` directory, or install from the WordPress plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Make sure [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SparkPost) **4.0** or newer is installed and active.
4. In SparkPost, verify your sending domain and create a Master API key with the permissions described in the [setup guide](https://kb.mailster.co/send-your-newsletters-via-sparkpost/?utm_campaign=wporg&utm_source=SparkPost&utm_medium=readme).
5. Go to **Settings → Newsletter → Delivery**, open the **SparkPost** tab, enter your API key, choose Default or EU endpoint, and save.
6. Choose WEB API (recommended) or SMTP API, configure optional tracking / IP pool / tags, and send a test email using a verified From address.

## Frequently Asked Questions

### Does this plugin work without Mailster?

No. This is a delivery addon for the [Mailster Newsletter Plugin](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SparkPost) and requires Mailster **4.0** or newer.

### Do I need a SparkPost account?

Yes. You need a SparkPost account, a verified sending domain (or verified From address), and a Master API key. SparkPost is a paid email delivery service; see SparkPost for current plans.

### What is the difference between WEB API and SMTP?

**WEB API** (recommended) sends through SparkPost’s Transmissions API and usually returns clearer errors. **SMTP API** relays via `smtp.sparkpostmail.com` (or the EU host) on port 587 or 2525 with TLS. Both are available in the SparkPost delivery settings.

### Why Master API key?

Mailster needs Account read access (and related permissions) to verify the key, list sending domains and subaccounts, read message events for bounces, and send transmissions. A restricted key without those permissions will not work fully.

### Can I use SparkPost EU?

Yes. Choose the **EU** endpoint in the SparkPost delivery settings to use the EU API and SMTP hosts.

### How are bounces handled?

When SparkPost is the active delivery method, bounce handling is driven by SparkPost message events synced into Mailster. Mailster’s own bounce mailbox settings are ignored for this delivery method.

### Is email data sent to a third party?

Yes. When this delivery method is active, Mailster sends email content and related delivery metadata to **SparkPost** so messages can be delivered. Bounce and engagement events are read back from SparkPost’s APIs.

### Where can I get support?

For Mailster and this addon, see [mailster.co](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SparkPost) and the [SparkPost setup guide](https://kb.mailster.co/send-your-newsletters-via-sparkpost/?utm_campaign=wporg&utm_source=SparkPost&utm_medium=readme). For SparkPost account, billing, and API questions, use [SparkPost documentation](https://developers.sparkpost.com/).

## Screenshots

### 1. Option Interface

![Option Interface.](https://ps.w.org/mailster-sparkpost/assets/screenshot-1.png)

## Changelog

### 1.9.0

- fixed: missing $status argument in bounce checking

### 1.8.1

- fixed: waringin on PHP >8.1

### 1.8

- Update for Mailster 3.0

### 1.7

- fixed: issue with invalid data type with WP 5.5

### 1.6

- fixed: subaccounts were not displayed on the settings screen

### 1.5.1

- fixed: using correct host for EU endpoint

### 1.5

- updated: applied SparkPost API endpoint changes

### 1.4.2

- fixed: issue with Mailster 2.3.16+ and reply_to headers

### 1.4.1

- endpoint selection is now available without API key

### 1.4

- added option to choose EU endpoint

### 1.3

- added: option to define IP_POOL

### 1.2

- fixed: issue if embedded images is used multiple times

### 1.1

- fixed: issue when campaign titles have more than 64 characters
- fixed "Track in SparkPost" option now working as expected

### 1.0

- initial release
