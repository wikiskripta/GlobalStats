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
                    'siprop' => 'statistics',
                    'format' => 'json' );
    $json = $bot->callApi($query);  

    $row = $total = $good = $edits = $admins = $images = $users = $activeusers = $checked = $valid_checked = 0;
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

    // Check articles (WikiSkripta + Wikilectures)
    if($info[2]) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $info[0] . "/index.php?title=" . urlencode($info[2]));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($ch);
        if( preg_match("/\<span id=\"actualCheckedArt\"\>([0-9]*)\<\/span\>/",$response,$matches) ) {
            $valid_checked = $matches[1];
        }
        if( preg_match("/\<span id=\"allCheckedArt\"\>([0-9]*)\<\/span\>/",$response,$matches) ) {
            $checked = $matches[1];
        }
    }
    $row .= ";$valid_checked;$checked";
    
    fwrite($statfile,$row . "\n");
    fclose($statfile);
    unset($statfile);
    unset($row);
    $bot->logout();
}