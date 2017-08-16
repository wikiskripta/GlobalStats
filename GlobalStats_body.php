<?php

/**
 * SpecialPage file for GlobalStats
 * @ingroup Extensions
 * @author Josef Martiňák
 * @license MIT
 * @file
 */

class GlobalStats extends SpecialPage {
	function __construct() {
		parent::__construct( 'GlobalStats' );
	}

	function execute($par) {
		global $wgSitename;
	
		$this->setHeaders();
 		$request = $this->getRequest();
		$out = $this->getOutput();
		
		// styly
		$out->addHTML("
			<style type='text/css'>
				table {
				    border:1px #AAA solid;
				    border-collapse:collapse;
					/*width:700px;*/
				}
				th, td {
				    border:1px #AAA solid;
				    padding:2px;
					text-align:left;
				}	
				form td, form table, form th { border-width:0px; }
				.checkbox { width:100px; }
			</style>
		");
			
		// otevreni souboru se statistikama
		$soubor = preg_replace("/index.php/","extensions/GlobalStats/$wgSitename.csv",$_SERVER["SCRIPT_FILENAME"]);
	
		$statfile = fopen($soubor, "r");
		
		$line = fgets($statfile);
		$line = fgets($statfile);	
		$arr=explode(";",rtrim($line));	// prvni zaznam
		
		if(!$arr[0]) { echo "Empty statistics";	exit; }
		
		$datum = date("Y-m-d");
		$days = array();
		
		// nacteni datumu z tabulky do pole $days
		$days[0] = $arr[0];	// datum prvniho zaznamu
		$i=1;
		$line = fgets($statfile);
		while (!feof($statfile)) {
			if(substr($line,0,10)==$datum) $arr=explode(";",rtrim($line));
			$days[$i] = substr($line,0,10);
			$line = fgets($statfile);
			$i++;
		}
		
		if($arr[0]!=$datum) $arr = array($datum,"?","?","?","?","?","?","?","?","?","?");
			
		// zpracovani vstupu
		if(isset($_POST["from"]) && in_array( $_POST["from"], $days ) ) $from = $_POST["from"]; 
		else {
			// defaultne pouze poslednich 30dni
			if(sizeof($days)<31) $from = $days[0];
			else $from = $days[sizeof($days)-31];
		}
		if(isset($_POST["to"]) && in_array( $_POST["to"], $days ) && $_POST["to"]>=$from) $to = $_POST["to"]; 
		else {
			// posledni zobrazovany den statistiky
			if(sizeof($days)==0) $to = $days[0];
			else $to = $days[sizeof($days)-1];
		}
		if(isset($_POST["chb"])) {
			for($i=0;$i<10;$i++) if(isset($_POST["chb"][$i])) $chb[$i] = 1; else $chb[$i] = 0;
		}
		else $chb = array(1,1,1,1,1,1,1,1,1,1);	// ktere checkboxy budou zatrhnuty
		if(isset($_POST["CSVexport"])) $CSVexport = "export"; else $CSVexport = false;

		// aktualni statistika
		$out->addWikiText("<h2>".$this->msg( 'gs_todaystat' )->escaped()."</h2>");
		$out->addHTML("<table>");
		$out->addHTML("<tr>");
	   		$out->addHTML("<th style='width:40px'>date</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_date' )->escaped()."</td>");
			$out->addHTML("<td>$arr[0]</td>");
	   	$out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>total</th>");
	       	$out->addHTML("<td>".$this->msg( 'gs_total' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[1]</td>");
	    $out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>good</th>");
	       	$out->addHTML("<td>".$this->msg( 'gs_good' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[2]</td>");
	    $out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>views</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_views' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[3]</td>");
	    $out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>edits</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_edits' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[4]</td>");
	    $out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>users</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_users' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[5]</td>");
	    $out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>admins</th>");
	       	$out->addHTML("<td>".$this->msg( 'gs_admins' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[6]</td>");
	    $out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>images</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_images' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[7]</td>");
	    $out->addHTML("</tr>");
	    $out->addHTML("<tr>");
	    	$out->addHTML("<th>activeusers</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_activeusers' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[8]</td>");
	    $out->addHTML("</tr>");
		
		$out->addHTML("<tr>");
	    	$out->addHTML("<th>valid_checked</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_actualCheckedArt' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[9]</td>");
	    $out->addHTML("</tr>");
		$out->addHTML("<tr>");
	    	$out->addHTML("<th>checked</th>");
	        $out->addHTML("<td>".$this->msg( 'gs_allCheckedArt' )->escaped()."</td>");
	        $out->addHTML("<td>$arr[10]</td>");
	    $out->addHTML("</tr>");
		
		$out->addHTML("</table>");
		$out->addHTML("<br/><br/>");
		
		// kompletni statistiky
		$out->addWikiText("<h2>".$this->msg( 'gs_completestat' )->escaped()."</h2>");
		$out->addHTML("<form id='form' name='form' action='' method='post'>");
		$out->addHTML("<fieldset><legend>".$this->msg( 'gs_settings' )->escaped()."</legend>");
    	$out->addHTML("<table>");
        	$out->addHTML("<tr>");
            	$out->addHTML("<th>".$this->msg( 'gs_from' )->escaped().":</th>");
				$out->addHTML("<td>");
					//$out->addHTML("<span title='YYYY-MM-DD ($days[0] ... ".$days[sizeof($days)-1].")'><input type='text' name='from' value='$from' placeholder='YYYY-MM-DD' style='width:90px;'/></span>");
                	$out->addHTML("<select name='from'>");
                    	// vytvoreni polozek <select></select>
						foreach($days as $day) {
							$opts .= "<option value='".$day."'";
							if($day==$from) $opts .= " selected='selected'";
							$opts .= ">".$day."</option>";
						}
						$out->addHTML($opts);
                    $out->addHTML("</select>");
                $out->addHTML("</td>");
                $out->addHTML("<td></td>");
                $out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[0]' ");
				if($chb[0]) $out->addHTML("checked='checked'");
				$out->addHTML("/> total</td>");
                $out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[1]' ");
				if($chb[1]) $out->addHTML("checked='checked'");
				$out->addHTML("/> good</td>");
                $out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[2]' ");
				if($chb[2]) $out->addHTML("checked='checked'");
				$out->addHTML("/> views</td>");
				$out->addHTML("<td class='checkbox' style='width:150px;'><input type='checkbox' name='chb[8]' ");
				if($chb[8]) $out->addHTML("checked='checked'");
				$out->addHTML("/> valid_checked</td>");
            $out->addHTML("</tr>");
            $out->addHTML("<tr>");
            	$out->addHTML("<th>".$this->msg( 'gs_to' )->escaped().":</th>");
                $out->addHTML("<td>");
				//$out->addHTML("<span title='YYYY-MM-DD ($days[0] ... ".$days[sizeof($days)-1].")'><input type='text' name='to' value='$to' placeholder='YYYY-MM-DD' style='width:90px;'/></span>");
                	$out->addHTML("<select name='to'>");
                    	// vytvoreni polozek <select></select>
						foreach($days as $day) {
							$out->addHTML("<option value='$day' ");
							if($day==$to) $out->addHTML("selected='selected'");
							$out->addHTML(">$day</option>\n");
						}
                    $out->addHTML("</select>");
                $out->addHTML("</td>");
                $out->addHTML("<td></td>");
                $out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[4]' ");
				if($chb[4]) $out->addHTML("checked='checked'");
				$out->addHTML("/> users</td>");
                $out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[5]' ");
				if($chb[5]) $out->addHTML("checked='checked'");
				$out->addHTML("/> admins</td>");
                $out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[6]' ");
				if($chb[6]) $out->addHTML("checked='checked'");
				$out->addHTML("/> images</td>");
				$out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[9]' ");
				if($chb[9]) $out->addHTML("checked='checked'");
				$out->addHTML("/> checked</td>");
            $out->addHTML("</tr>");
			$out->addHTML("<tr>");
            	$out->addHTML("<td colspan='3'></td>");
				$out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[7]' ");
				if($chb[7]) $out->addHTML("checked='checked'");
				$out->addHTML("/> activeusers</td>");
				$out->addHTML("<td class='checkbox'><input type='checkbox' name='chb[3]' ");
				if($chb[3]) $out->addHTML("checked='checked'");
				$out->addHTML("/> edits</td>");
            $out->addHTML("</tr>");
            $out->addHTML("<tr><td colspan='7' style='height:30px;vertical-align:bottom;'><input type='submit' name='update' value='".$this->msg( 'gs_refresh' )->escaped()."'/>&nbsp;<input type='submit' name='CSVexport' value='".$this->msg( 'gs_export' )->escaped()."'/></td>");
        	$out->addHTML("</table>");
		//$out->addHTML($this->msg( 'gs_IE_warning' )->escaped());
		$out->addHTML("</fieldset>");
	    $out->addHTML("</form>");
		$out->addHTML("<br/><br/>");
			
		$out->addHTML("<table>");
		$out->addHTML("<tr>");
	    	$out->addHTML("<th>date</th>");
            $export = "date";
	        if($chb[0]) { $out->addHTML("<th>total</th>"); $export .= ";total"; }
		    if($chb[1]) { $out->addHTML("<th>good</th>"); $export .= ";good"; }
	        if($chb[2]) { $out->addHTML("<th>views</th>"); $export .= ";views"; }
	        if($chb[3]) { $out->addHTML("<th>edits</th>"); $export .= ";edits"; }
		    if($chb[4]) { $out->addHTML("<th>users</th>"); $export .= ";users"; }
		    if($chb[5]) { $out->addHTML("<th>admins</th>"); $export .= ";admins"; }
		    if($chb[6]) { $out->addHTML("<th>images</th>"); $export .= ";images"; }
		    if($chb[7]) { $out->addHTML("<th>activeusers</th>"); $export .= ";activeusers"; }
			if($chb[8]) { $out->addHTML("<th>valid_checked</th>"); $export .= ";valid_checked"; }
			if($chb[9]) { $out->addHTML("<th>checked</th>"); $export .= ";checked"; }
			$export .= ";\r\n";
	    $out->addHTML("</tr>");
		
		// nastaveni pointeru na prvni radek statistiky
    	rewind($statfile);
		$line = fgets($statfile);

		// zobrazeni tabulky
		while (!feof($statfile)) {
     	 	$line = fgets($statfile);
			$arr=explode(";",rtrim($line));
			if(sizeof($arr)>1 && $arr[0]>=$from && $arr[0]<=$to) {
				$out->addHTML("<tr>\n");
				$out->addHTML("<td>$arr[0]</td>");
				$export .= $arr[0];
				for($i=1;$i<sizeof($arr);$i++) {
					if($chb[$i-1]) {
						$out->addHTML("<td>".$arr[$i]."</td>");
						$export .= ";".$arr[$i];
					}
				}
				$export .= ";\r\n";
				$out->addHTML("</tr>\n");
			}
	    }
		
		$out->addHTML("</table>");
	
		
		if($CSVexport == "export") {
			header('Content-type: text/csv;');
			header('Content-disposition: attachment; filename="'.$wgSitename.'.csv"');
			print ($export);
			exit;
		}	
				
		
		fclose($statfile);
	        unset($statfile);
 	}
}
