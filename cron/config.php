<?php

/**
 * Config file for GlobalStats
 * @ingroup Extensions
 * @author Josef Martiňák
 * @license MIT
 * @file
 */

# Bot accounts
$user = "Botaccount";
$pass = "????????";


/**
 * Watched sites
 * array(URL,stat_filename,custom_stats_page)
 * custom_stats_page - not part of mediawiki core
 */
$wikis[0] = array("www.wikiskripta.eu","WikiSkripta.csv","WikiSkripta:Statistiky");
$wikis[1] = array("www.wikilectures.eu","WikiLectures.csv","WikiLectures:Statistics");
$wikis[2] = array("www.statest.cz","StaTest.csv","");

?>