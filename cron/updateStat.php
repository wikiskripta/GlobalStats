<?php

/**
 * CRON script for updating CSV
 * Check every day wiki's stats and add row to csv file
 * @ingroup Extensions
 * @author Josef Martiňák
 * @license MIT
 * @file
 */


require 'bot.class.php';
require 'config-bot.php';


foreach($wikis as $info){

    // Skip if web not accessible
    if( !(file_get_contents($info[0]."/api.php") ) ) continue;

    // Create stat file if not exists
    $fpath = __DIR__ . "../data/" . $info[1];
    $statfile = fopen($fpath, "r+");
    if(!filesize($fpath)) fwrite($statfile,"date;total;good;views;edits;users;admins;images;activeusers;valid_checked;checked\n");	// columns

    // One record a day check
    $today = date("Y-m-d");
    $updated = false;
    while (!feof($statfile)) {
        $buffer = fgets($statfile);
        if(strpos($buffer,$today)!==false) {
            fclose($statfile);
            unset($statfile);
            exit;
        }
    }

    // Bot login
    $bot = new Bot($info[0], $botUser, $botPassword);
    $bot->login();

    // Get stats from wiki
    $query = array( 'action' => 'query',
                    'meta' => 'siteinfo',
                    'prop' => 'statistics',
                    'format' => 'json' );
    $json = $bot->callApi($query);
    $row = $total = $good = $edits = $views = $admins = $images = $users = $activeusers = $views = $checked = $valid_checked = 0;
    if( !isset($json->error) ) {
        $total = $json->statistics->pages;
        $good = $json->statistics->articles;
        $edits = $json->statistics->edits;
        $admins = $json->statistics->admins;
        $images = $json->statistics->images;
        $users = $json->statistics->users;
        $activeusers = $json->statistics->activeusers;
    }

    // Get number of views (HitCounters)
    if( $stathc = file_get_contents($info[0] . "/index.php?title=Special:Statistics" ) ) {
        if(preg_match("/id=\"mw-hitcounters-statistics-views-total\"><td>[^<]*<\/td><td class=\"mw-statistics-numbers\">([^<]*)/",$stathc,$matches) ) {
            $views = preg_replace("/\s+/u","",urldecode($matches[1]));
        }
    }

    $row = "$today;$total;$good;$views;$edits;$users;$admins;$images;$activeusers";

    // Check articles (WikiSkripta + Wikilectures)
    if($info[2]) {
        $pagecontent = file_get_contents($info[0]."/w/$info[2]");
        $tmp = preg_match("/\<span id=\"actualCheckedArt\"\>([0-9]*)\<\/span\>/",$pagecontent,$matches);
        $valid_checked = $matches[1].";";
        $tmp = preg_match("/\<span id=\"allCheckedArt\"\>([0-9]*)\<\/span\>/",$pagecontent,$matches);
        $checked = $matches[1];
        $row .= ";$valid_checked;$checked";
    }
    
    fwrite($statfile,$row . "\n");
    fclose($statfile);
    unset($statfile);
    unset($row);
    $bot->logout();
}