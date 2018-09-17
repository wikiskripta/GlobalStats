# GlobalStats

Mediawiki extension.

## Description

* Version 1.1
* _GlobalStats_ collects everyday stats for wiki.
* See special page _Special:GlobalStats_ for results.


## Installation

* Make sure you have MediaWiki 1.29+ installed.
* Download and place the extension to your /extensions/ folder.
* Add the following code to your LocalSettings.php: `wfLoadExtension( 'GlobalStats' )`;
* Add file with name _$wgSitename.csv_ (see LocalSettings.php) to the /data/ folder.
* In case od wikifarm more files like this can be added.
* Create bot account at wiki.
* Set variables in _cron/config.php_ and _cron/config-bot.php_.
* Set CRON job for _cron/updateStat.php_ ... every day.


## Configuration

### _cron/config.php - example
// array(URL,stat's filename,custom stat's wiki page)
$wikis[0] = array("www.wikiskripta.eu","WikiSkripta.csv","WikiSkripta:Statistiky");
$wikis[1] = array("www.wikilectures.eu","WikiLectures.csv","WikiLectures:Statistics");

### _cron/config-bot.php - example

Create file _cron/config-bot.php_ (if not exist) with following content
```
<?php
$botUser = "Bot_account_name";
$botPassword = "Bot_password";
?>
```
In case of wikifarm, this account should exist on all wikis.


## Details

* Running a couple of times (with some delay) recommended. Server can be down at this exact moment.
* Stats are stored in CSV file. New line is appended only once a day.
* In case of Wikifarm with one shared _extensions_ folder, we can create CSV files for all sites.


## SpecialPage

_Special:GlobalStats_ - browsing and exporting statistics.


## Internationalization
This extension is available in English and Czech language. For other languages, just edit files in /i18n/ folder.


## Authors and license

* [Josef Martiňák](https://bitbucket.org/josmart/), [Petr Kajzar](https://bitbucket.org/petrkajzar/)
* MIT License, Copyright (c) 2018 First Faculty of Medicine, Charles University




