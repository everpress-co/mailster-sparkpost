# Mailster SparkPost Integration

Contributors: everpress, mailster, xaverb  
Tags: sparkpost, mailster, deliverymethod, newsletter, mailsteresp, email
Requires at least: 6.0  
Tested up to: 6.5  
Stable tag: 1.8.1  
License: GPLv2 or later

## Description

Uses SparkPost to deliver emails for the [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SparkPost).

Read the [Setup Guide](https://kb.mailster.co/send-your-newsletters-via-sparkpost/?utm_campaign=wporg&utm_source=SparkPost&utm_medium=readme) to get started.

## Installation

1. Upload the entire `mailster-sparkpost` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings => Newsletter => Delivery and select the `SparkPost` tab
4. Enter your API Key and save the settings
5. Send a testmail

## Screenshots

### 1. Option Interface

![Option Interface.](https://ps.w.org/mailster-sparkpost/assets/screenshot-1.png)

## Changelog

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

## Additional Info

This Plugin requires [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SparkPost)
