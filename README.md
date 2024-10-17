# Magento Auto Patch (WORK IN PROGRESS)

## Overview

The **Patch AutoUpdater** module for Magento 2 automates the process of checking and applying **minor patches (example: 2.4.6 - 2.4.6-p2)**. It integrates directly with Magento’s patch management system, reducing the need for manual intervention, ensuring your store remains secure and up-to-date.
> tested with 2.4.0 - 2.4.7 
> PHP versions: PJP 7.4 - PHP 8.3

## Features
- **Automated Patch Lookup**: Automatically checks for the latest Magento patches.
- **Notifies you** before and after the update
- **Patch Application**: Applies patches directly via CLI or automated cron jobs.
- **Custom CLI Command**: Use `bin/magento patch:update` to manually check and apply patches.
- **Logging**: log errors `auto-patch.log`. This get sent on failure with the after patch notification

## Installation

### Install the Module
    composer require aliuosio/magento-autopatch
    bin/magento setup:upgrade

### Enable the Module
    stores -> configuration -> Osio -> Auto Patcher -> enable

### Patch the System (automatic per cron or manually)
    # manually
    bin/magento patch:update

#### Todos
* ~~add command implementation: Feedback loop for processes~~
* ~~add tested on magento versions to README~~
* ~~refactor process class usage~~
* ~~add deploy modes handling~~
* ~~add error handling in extra module log~~
* ~~add separate log file~~
* ~~Add backend Dialog and command implementation: Enable switch~~
* Fix Error setting Production Mode when not in production modea
* Add backend Dialog and command implementation: notification per mail of available patch
* Add backend Dialog and command implementation: Patch automatically or not (comment cron has to bee set up to use)
* Add backend Dialog and command implementation: notification per mail of after auto-patch
* Add ACL
* add animated GIF to demo tool
