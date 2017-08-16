# GlobalStats

Mediawiki extension.

## Description

* Version 1.2.0
* _GlobalStats_ collects everyday stats for wiki.
* See special page _Special:GlobalStats_ for results.


## Installation

* Make sure you have MediaWiki 1.25+ installed.
* Download and place the extension's folder to your /extensions/ folder.
* Add the following code to your LocalSettings.php: `wfLoadExtension( 'GlobalStats' )`;
* Add file with name _$wgSitename.csv_ (see LocalSettings.php) to the extension's folder.
* In case od wikifarm more files like this can be added.
* Create bot account at wiki.
* Set variables in _cron/config.php_.
* Set CRON job for _cron/updateStat.php_ ... every day.


## Details

* Running a couple of times (with some delay) recommended. Server can be down at this exact moment.
* Stats are stored in CSV file. New line is appended only once a day.
* In case of Wikifarm with one shared _extensions_ folder, we can create CSV files for all sites.


## SpecialPage

_Special:GlobalStats_ - browsing and exporting statistics.


## Internationalization
This extension is available in English and Czech language. For other languages, just edit files in /i18n/ folder.


## Authors and license

* [Josef Martiòák](https://bitbucket.org/josmart/)
* MIT License, Copyright (c) 2017 First Faculty of Medicine, Charles University




