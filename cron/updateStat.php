<?php

/**
 * CRON script for updating CSV
 * Check every day wiki's stats and add row to csv file
 * @ingroup Extensions
 * @author Josef Martiňák
 */

require 'bot.class.php';
require 'config-bot.php';
require 'config.php';

foreach($wikis as $info){

    // Skip if web not accessible
    if( !(file_get_contents($info[0]."/api.php") ) ) continue;

    // Create stat file if not exists
    $fpath = __DIR__ . "/../data/" . $info[1];
    $statfile = fopen($fpath, "r+");
    if(!filesize($fpath)) fwrite($statfile,"date;total;good;edits;users;admins;images;activeusers;valid_checked;checked\n");	// columns

    // One record a day check
    $today = date("Y-m-d");
    $updated = false;
    $alreadyRecent = false;
    while (!feof($statfile)) {
        $buffer = fgets($statfile);
        if(strpos($buffer,$today)!==false) {
            fclose($statfile);
            unset($statfile);
            $alreadyRecent = true;
            break;
        }
    }
    if($alreadyRecent) continue;

    // Bot login
    $bot = new Bot($info[0], $botUser, $botPassword);
    $bot->login();

    // Get stats from wiki
    $query = array( 'action' => 'query',
                    'meta' => 'siteinfo',
                    'siprop' => 'statistics',
                    'format' => 'json' );
    $json = $bot->callApi($query);  

    $row = $total = $good = $edits = $admins = $images = $users = $activeusers = 0;
    if( !isset($json->error) ) {
        $total = $json->query->statistics->pages;
        $good = $json->query->statistics->articles;
        $edits = $json->query->statistics->edits;
        $admins = $json->query->statistics->admins;
        $images = $json->query->statistics->images;
        $users = $json->query->statistics->users;
        $activeusers = $json->query->statistics->activeusers;
    }

    $row = "$today;$total;$good;$edits;$users;$admins;$images;$activeusers";

    fwrite($statfile,$row . "\n");
    fclose($statfile);
    unset($statfile);
    unset($row);
    $bot->logout();

}