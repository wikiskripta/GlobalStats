<?php

	/**
	* CRON script for updating CSV
	* @ingroup Extensions
	* @author Josef Martiňák
	* @license MIT
	* @file
	*/

	require_once('config.php');
	require_once('botclasses.php');
	$mainpath = preg_replace("/\/cron\/updateStat.php$/","", $_SERVER["SCRIPT_FILENAME"]);

	/* kazdy den vzdy v 1,2,3 se zjisti aktualni stav WIKI a ulozi se jako novy radek do souboru statistika.csv */
	/* ovsem pouze v pripade, ze jde o prvni zapis ten den a spojeni je OK */
	foreach($wikis as $info){

		// pokud web není dostupný, přeskoč
		if( !($statistika = file_get_contents("http://".$info[0]."/api.php?action=query&meta=siteinfo&siprop=statistics&format=xml") ) ) continue;

		// over, zda existuje soubor pro ulozeni statistik. Pokud ne, vytvor.
		$soubor = "$mainpath/".$info[1];
		$statfile = fopen($soubor, "r+");
		if(!filesize($soubor)) fwrite($statfile,"date;total;good;views;edits;users;admins;images;activeusers;valid_checked;checked\n");	// nazvy sloupcu
	
		// zjisti, zda uz dnes nebyl radek pridan
		$datum = date("Y-m-d");
		$updated = false;
		// zjisti, zda uz dnes nebyl radek pridan
		while (!feof($statfile)) {
			$buffer = fgets($statfile);
			if(strpos($buffer,$datum)!==false) {
				$updated = true;
				break;
			}
		}
		// pokud soubor nebyl dnes updatovan, pripoji novy radek
		if(preg_match("/error code/",$statistika)){
			// wiki je uzavrena, musime se prihlasit	
			$wiki      = new wikipedia();
			$wiki->url = "http://".$info[0]."/api.php";
			$wiki->login($user,$pass);
			unset($pass);
			$statistika = $wiki->getXML("?action=query&meta=siteinfo&siprop=statistics&format=xml");
		}
		if(!$updated && !preg_match("/error code/",$statistika) && $statistika) {
			// vytvoreni noveho radku do statistiky za aktualni den
			$row = date("Y-m-d").";";
			$tmp = preg_match("/pages=\"([0-9]*)\"/",$statistika,$matches);
			$row .= $matches[1].";";
			$tmp = preg_match("/articles=\"([0-9]*)\"/",$statistika,$matches);
			$row .= $matches[1].";";
			$tmp = preg_match("/views=\"([0-9]*)\"/",$statistika,$matches);
			$tmp2 = "";
			if(isset($matches[1])) {
				// <1.25
				$tmp2 = $matches[1];
			}
			else {
				//>= 1.25 ... s využitím HitCounters
				if( $stathc = file_get_contents("http://".$info[0]."/index.php?title=Special:Statistics" ) ) {
					if(preg_match("/id=\"mw-hitcounters-statistics-views-total\"><td>[^<]*<\/td><td class=\"mw-statistics-numbers\">([^<]*)/",$stathc,$matches) ) {
						$tmp2 = preg_replace("/\s+/u","",urldecode($matches[1]));
					}
					/*<tr class="mw-statistics-hook" id="mw-hitcounters-statistics-views-total"><td>Views total</td><td class="mw-statistics-numbers">191 301 520</td></tr>*/
				}
			}
			$row .= $tmp2.";";
			$tmp = preg_match("/edits=\"([0-9]*)\"/",$statistika,$matches);
			$row .= $matches[1].";";
			$tmp = preg_match("/users=\"([0-9]*)\"/",$statistika,$matches);
			$row .= $matches[1].";";
			$tmp = preg_match("/admins=\"([0-9]*)\"/",$statistika,$matches);
			$row .= $matches[1].";";
			$tmp = preg_match("/images=\"([0-9]*)\"/",$statistika,$matches);
			$row .= $matches[1].";";
			$tmp = preg_match("/activeusers=\"([0-9]*)\"/",$statistika,$matches);
			$row .= $matches[1].";";
			
			// zkontrolovane clanky
			if($info[2]){
				$pagecontent = file_get_contents("http://".$info[0]."/index.php/$info[2]");
				$tmp = preg_match("/\<span id=\"actualCheckedArt\"\>([0-9]*)\<\/span\>/",$pagecontent,$matches);
				$row .= $matches[1].";";
				$tmp = preg_match("/\<span id=\"allCheckedArt\"\>([0-9]*)\<\/span\>/",$pagecontent,$matches);
				$row .= $matches[1]."\n";
			}
			else $row .= ";\n";
			fwrite($statfile,$row);
		}
		fclose($statfile);
		unset($statfile);
		unset($row);
	}

?>
