# Magento 2 Patch AutoUpdater

## Overview

The **Patch AutoUpdater** module for Magento 2 automates the process of checking and applying patches. It integrates directly with Magento’s patch management system, reducing the need for manual intervention, ensuring your store remains secure and up-to-date.
> tested with 2.4.5, 2.4.6, 2.4.7

## Features

- **Automated Patch Lookup**: Automatically checks for the latest Magento patches.
- **Patch Application**: Applies patches directly via CLI or automated cron jobs.
- **Custom CLI Command**: Use `bin/magento patch:update` to manually check and apply patches.
- **Cron Job Support**: Easily set up cron jobs to automate patch updates on a regular schedule.

## Installation

### Install the Module
    composer require aliuosio/magento-autopatcher
    bin/magento setup:upgrade

### Enable the Module
    stores -> configuration -> Osio -> Auto Patcher -> enable

### Patch the System (automatic per cron or manually)
    # cron
    crontab -e as root
    * * * * * /path/to/magento/bin/magento patch:update

    # manually
    bin/magento patch:update



#### Todos
* ~~add command implementation: Feedback loop for processes~~
* ~~add tested on magento versions to README~~
* ~~refactor process class usage~~
* ~~add deploy modes handling~~
* add error handling in extra module log
* ~~Add backend Dialog and command implementation: Enable switch~~
* Add backend Dialog and command implementation: notification per mail of available patch
* Add backend Dialog and command implementation: Patch automatically or not (comment cron has to bee set up to use)
* Add ACL
* add animtaed GIF to demo tool
