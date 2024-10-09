# Magento 2 Patch AutoUpdater Module

## Overview

The **Patch AutoUpdater** module for Magento 2 automates the process of checking and applying patches. It integrates directly with Magento’s patch management system, reducing the need for manual intervention, ensuring your store remains secure and up-to-date.

## Features

- **Automated Patch Lookup**: Automatically checks for the latest Magento patches.
- **Patch Application**: Applies patches directly via CLI or automated cron jobs.
- **Custom CLI Command**: Use `bin/magento patch:update` to manually check and apply patches.
- **Cron Job Support**: Easily set up cron jobs to automate patch updates on a regular schedule.
- **Logging**: Keep track of all applied patches for auditing and rollback if needed.

## Installation

### Install the Module
    composer require aliuosio/magento-autopatcher
    bin/magento setup:upgrade

### Patch the System (automatic per cron or manually)
    # manually
    bin/magento patch:update

    # automatic
    crontab -e as root
    * * * * * /usr/bin/php /path/to/magento/bin/magento patch:update



