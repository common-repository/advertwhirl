<?php
/*  Copyright 2010
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 http://code.google.com/p/google-adsense-dashboard/
Pagelift
 http://www.pagelift.com/
*/

if(!class_exists("AdsenseStats")){


// put this line inside a function, 
// presumably in response to something the user does
// otherwise it will schedule a new event on every page visit

wp_schedule_single_event(time()+3600, 'my_new_event');

// time()+3600 = one hour from now.

	class AdsenseStats {
		private $curl;
		private $classFile;
		private $classDir;
		private $cookieFile;
		private $loggedin;
		private $errormsg;
		private $username;
		private $password;

		/** URLs */
		private $Ga3tURL = 'https://www.google.com/accounts/ServiceLoginBox?service=adsense&ltmpl=login&ifr=true&rm=hide&fpui=3&nui=15&alwf=true&passive=true&continue=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&followup=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&hl=en_US';
		private $LoginURL = 'https://www.google.com/accounts/ServiceLoginBoxAuth';
		private $VerifyLoginURL = 'https://www.google.com/accounts/CheckCookie?continue=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&followup=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&hl=en_US&service=adsense&ltmpl=login&chtml=LoginDoneHtml';
		private $ReportBase = 'https://www.google.com/adsense/report/overview?timePeriod=';
		private $AdUnitsUrl = 'https://www.google.com/adsense/adslots';
		private $AdUnitCodeBase = 'https://www.google.com/adsense/adslot-get-code?adSlotChannel.id=';
		private $AdUnitDetailBase = 'https://www.google.com/adsense/adslot-edit?adSlotChannel.id=';

		/** sprintf formated strings */
		private $LoginPostBase = 'continue=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&followup=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&service=adsense&nui=15&fpui=3&ifr=true&rm=hide&ltmpl=login&hl=en_US&alwf=true&ltmpl=login&';
		private $LoginPostReplace = "GA3T=%s&GALX=%s&Email=%s&Passwd=%s";

		/** Regular Expressions for matching data */
		private $Ga3tExpression = '/<input type=\"hidden\" name=\"GA3T\" value=\"(.*?)\"/';
		private $GalxExpression = '/<input type=\"hidden\" name=\"GALX\" value=\"(.*?)\"/';
		private $ReportExpression = '/<td nowrap valign=\"top\" style=\"text-align\:right\" class=\"\">(.*?)<\/td>/';
		private $AdUnitSummaryExpression = '/<a class=\"metalink\" href=\"\/adsense\/adslot-get-code\?adSlotChannel.id=([0-9]+)\">Code<\/a>/';
		private $AdUnitNameExpression = '/<h1>AdSense unit: (.*?)<\/h1>/';
		private $AdUnitCodeExpression = '/<textarea id=\"code\" .*>(.+)<\/textarea>/s';
		private $AdUnitSizeExpression = '/<tr><td><b>Size:<\/b><\/td>\s*<td>([0-9]+x[0-9]+)<\/td><\/tr>/s';
		private $AdUnitSlotIdExpression = '/<tr><td><b>ID:<\/b><\/td>\s*<td>([0-9]+)<\/td><\/tr>/s';

		/** Field arrays */
		private $ReportPeriods = array('today' => 'Today', 'yesterday' => 'Yesterday', 'last7days' => 'Last 7 Days', 'thismonth' => 'This Month', 'lastmonth' => 'Last Month', 'alltime' => 'All Time');
		private $ReportTypes = array( 'impressions' => 'Impressions', 'clicks' => 'Clicks', 'ctr' => 'CTR', 'ecpm' => 'ECPM', 'earnings' => 'Earnings');
		private $AdCategories = array( 'content' => 'Content', 'search' => 'Search', 'feeds' => 'Feeds', 'mobile' => 'Mobile', 'domains' => 'Domains');

		private $name = "Advertwhirl_adsense";
		private $options;

		/**
		  * Compatible php4 constructor
		  */
		function AdsenseStats(){
			$this->__construct();
		}

		/**
		  * PHP5 constructor
		  */
		function __construct(){
			$this->classFile = __FILE__;
			if(empty($this->classDir)) $this->classDir = dirname($this->classFile);
			$this->cookieFile = $this->classDir . "/AdsenseStatsCookies";
			$this->loggedin = false;

			$this->options = maybe_unserialize(get_option($this->name . "_options"));
		}

		function __destruct(){
			//if($this->curl) {
			//	curl_close($this->curl);
			//}
		}

		public function SetUsername($username){
			$this->username = $username;
		}

		public function SetPassword($password){
			$this->password = $password;
		}

		public function GetAdsCache(){
			return $this->options['ads-cache'];
		}

		public function SetAdsCache($cache){
			$this->options['ads-cache'] = $cache;
		}

		public function GetStatsCache($cache){
			return $this->options['stats-cache'];
		}

		public function SetStatsCache($cache){
			$this->options['stats-cache'] = $cache;
		}

		public function LoggedIn(){
			return $this->loggedin;
		}

		public function ErrorMessage(){
			return $this->errormsg;
		}

		public function Logout(){
			if($this->curl){
				curl_close($this->curl);
			}
		}

		public function GetReportPeriods(){
			return $this->ReportPeriods;
		}
		
		public function GetReportTypes(){
			return $this->ReportTypes;
		}
		
		public function GetAdCategories(){
			return $this->AdCategories;
		}
		
		/**
		  * Login to Adsense - required to get the stats
		  */
		public function Login(){
			$this->curl = curl_init();
			/* Get the GA3T value for login */
			$data = $this->curl_get($this->Ga3tURL);

			/* DEBUGGING */
			//$f = fopen($this->classDir . "/data-1.txt", "w"); 
			//fwrite($f, $data); 
			//fclose($f);

			preg_match($this->Ga3tExpression, $data, $ga3t);
			preg_match($this->GalxExpression, $data, $galx);

			$this->loggedin = true;

			/* Login to AdSense GA3T */
			$ga3tValue = isset($ga3t[1])?$ga3t[1]:"";
			$galxValue = isset($galx[1])?$galx[1]:"";
			$post = $this->LoginPostBase . sprintf($this->LoginPostReplace, $ga3tValue, $galxValue, $this->username, $this->password);
			$data = $this->curl_post($this->LoginURL, $post);
			
			/* DEBUGGING */
			//$f = fopen($this->classDir . "/data-2.txt", "w"); 
			//fwrite($f, $data); 
			//fclose($f);

			if(strpos($data, 'Username and password do not match.')) {
				$this->loggedin = false;
				$this->errormsg = 'Username and password do not match.';

				@unlink($this->cookieFile);
				return $this->loggedin;
			}

			/** Verify Login attempt */
			$data = $this->curl_get($this->VerifyLoginURL);

			/* DEBUGGING */
			//$f = fopen($this->classDir . "/data-3.txt", "w"); 
			//fwrite($f, $data); 
			//fclose($f);

			/** Verify cookie functionality */
			if(strpos($data, 'cookie functionality is turned off.')) {
				$this->loggedin = false;
				$this->errormsg = 'Cookies functionality is turned off.';

				@unlink($this->cookieFile);
				return $this->loggedin;
			}else if(strpos($data, 'unable to grant you access to the AdSense homepage at this time.')) {
				$this->loggedin = false;
				$this->errormsg = 'Unable to grant you access to the AdSense homepage at this time.';

				@unlink($this->cookieFile);
				return $this->loggedin;
			}

			return $this->loggedin;

		}

		/**
		  * This is the curl get function to fetch get data
		  */
		private function curl_get($url) {
			//$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_URL, $url);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			$this->data = curl_exec($this->curl);
			//curl_close($this->curl);
			return $this->data;
		}

		/**
		  * This is the curl function to fetch the data
		  */
		private function curl_post($url, $post) {
			//$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_URL, $url);
			curl_setopt($this->curl, CURLOPT_POST, 1);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
			curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			$this->data = curl_exec($this->curl);
			//curl_close($this->curl);
			return $this->data;
		}

		public function GetTotalEarnings($period){
			$total = 0;
			foreach(array_keys($this->AdCategories) as $category){
				$total += $this->GetCategoryEarnings($period, $category);
			}
			return $total;
		}

		public function GetCategoryEarnings($period, $category){
			
			return $this->GetStat($period, 'earnings', $category);
		}

		public function GetTotalImpressions($period){
			$total = 0;
			foreach(array_keys($this->AdCategories) as $category){
				$total += $this->GetCategoryImpressions($period, $category);
			}
			return $total;
		}

		public function GetCategoryImpressions($period, $category){
			return $this->GetStat($period, 'impressions', $category);
		}

		public function GetTotalClicks($period){
			$total = 0;
			foreach(array_keys($this->AdCategories) as $category){
				$total += $this->GetCategoryClicks($period, $category);
			}
			return $total;
		}

		public function GetCategoryClicks($period, $category){
			return $this->GetStat($period, 'clicks', $category);
		}

		public function GetTotalCtr($period){
			$total = 0;
			foreach(array_keys($this->AdCategories) as $category){
				$total += $this->GetCategoryCtr($period, $category);
			}
			return $total;
		}

		public function GetCategoryCtr($period, $category){
			return $this->GetStat($period, 'ctr', $category);
		}

		public function GetTotalEcpm($period){
			$total = 0;
			foreach(array_keys($this->AdCategories) as $category){
				$total += $this->GetCategoryEcpm($period, $category);
			}
			return $total;
		}

		public function GetCategoryEcpm($period, $category){
			return $this->GetStat($period, 'ecpm', $category);
		}

		public function GetStat($period, $report, $category){
			return $this->options['stats-set']? $this->options['stats'][$category][$report][$period]:0;
		}

		public function GetStatTables($periods = array('today' => true, 'yesterday' => false, 'last7days' => false, 'thismonth' => false, 'lastmonth' => false, 'alltime' => false),
		                              $types = array( 'impressions' => true, 'clicks' => true, 'ctr' => true, 'ecpm' => true, 'earnings' => true),
									  $categories = array( 'content' => true, 'search' => true, 'feeds' => true, 'mobile' => true, 'domains' => true),
									  $tableClass = "", $titleClass = "", $rowClass = "", $columnClass = "", $theadClass = "", $theadRowClass = "", $theadColumnClass = "", $tbodyClass = ""){
			foreach($this->ReportPeriods as $pkey => $label){
				if(isset($periods[$pkey]) && $periods[$pkey]){
					$this->GetPeriodTable($pkey, $types, $categories, $tableClass, $titleClass, $rowClass, $columnClass, $theadClass, $theadRowClass, $theadColumnClass, $tbodyClass);
				}
			}
		}

		public function GetPeriodTable($period,
		                               $types = array( 'impressions' => true, 'clicks' => true, 'ctr' => true, 'ecpm' => true, 'earnings' => true),
									   $categories = array( 'content' => true, 'search' => true, 'feeds' => true, 'mobile' => true, 'domains' => true),
		                               $tableClass = "", $titleClass = "", $rowClass = "", $columnClass = "", $theadClass = "", $theadRowClass = "", $theadColumnClass = "", $tbodyClass = "", $tbodyHeaderClass = ""){
			$colCount = 1;
			$colString = "";
			foreach($this->ReportTypes as $rkey => $report){
				if(isset($types[$rkey]) && $types[$rkey]){
					$colString .= '		<th class="' . $theadColumnClass . '">' . $report . '</th>' . "\n";
					$colCount++;
				}
			}
			echo '<table class="' . $tableClass . '">' . "\n";
			echo '	<thead class="' . $theadClass . '">' . "\n";
			echo '		<tr class="' . $theadRowClass . '">' . "\n";
			echo '			<th class="' . $titleClass . '" colspan="' . $colCount . '">' . $this->ReportPeriods[$period] . '</th>' . "\n";
			echo '		</tr>' . "\n";
			echo '		<tr class="' . $theadRowClass . '">' . "\n";
			echo '			<th class="' . $theadColumnClass . '"></th>' . "\n";
			echo $colString;
			echo '		</tr">' . "\n";
			echo '	</thead>' . "\n";
			echo '	<tbody class="' . $tbodyClass . '">' . "\n";
			foreach($this->AdCategories as $ckey => $category){
				if(isset($categories[$ckey]) && $categories[$ckey]){
					echo '		<tr class="' . $rowClass . '">' . "\n";
					echo '			<th class="' . $tbodyHeaderClass . '">' . $category . '</th>' . "\n";
					foreach($this->ReportTypes as $rkey => $report){
						if(isset($types[$rkey]) && $types[$rkey]){
							echo '			<td class="' . $columnClass . '">' . $this->options['stats'][$ckey][$rkey][$period] . '</td>' . "\n";
						}
					}
					echo '		</tr>' . "\n";
				}
			}
			echo '	</tbody>' . "\n";
			echo '</table>' . "\n";
		}

		public function LoadStats($force = false) {
			$loadTime = $this->options['stats-loadtime'];
			$cacheTime = $this->options['stats-cache'];

			if(($loadTime + $cacheTime <= time()) || $forced){
				if(!$this->LoggedIn()){
					$this->Login();
					if(!$this->LoggedIn()){
						error_log("Advertwhirl Adsense Error: " . $this->ErrorMessage());
						return;	
					}
				}

				$this->options['stats'] = array();
				$this->options['stats-set'] = false;
				foreach($this->ReportPeriods as $pkey => $time) {
					$i = 0;
					$data = $this->curl_get($this->ReportBase . $pkey);
					preg_match_all($this->ReportExpression, $data, $match);

					foreach($this->AdCategories as $ckey => $category){
						$j = 0;
						foreach($this->ReportTypes as $rkey => $report){
							$value = isset($match[1][$i + $j])?$match[1][$i + $j]:0;
							$this->options['stats'][$ckey][$rkey][$pkey] = $value;
							$j++;
						}
						$i += 5;
					}
				}
				$this->options['stats-set'] = true;
				$this->options['stats-loadtime'] = time();
				update_option($this->name . "_options", $this->options);
				$this->Logout();
			}
		}

		public function GetAdName($id){
			return $this->GetAdField($id, 'name');
		}

		public function GetAdFormat($id){
			return $this->GetAdField($id, 'format');
		}

		public function GetAdSize($id){
			return $this->GetAdField($id, 'size');
		}

		public function GetAdSlot($id){
			return $this->GetAdField($id, 'slotId');
		}

		public function GetAdCode($id){
			return $this->GetAdField($id, 'code');
		}

		public function GetAdField($id, $field){
			if($this->options['ads-set'] && isset($this->adUnits[$id][$field]))
				return $this->options['adUnits'][$id][$field];
			return null;
		}

		public function GetAdUnit($id){
			if($this->options['ads-set'] && isset($this->options['adUnits'][$id]))
				return $this->options['adUnits'][$id];
			return null;
		}

		public function GetAdUnits(){
			return $this->options['adUnits'];
		}

		public function LoadAdUnits($forced = false){
			$loadTime = $this->options['ads-loadtime'];
			$cacheTime = $this->options['ads-cache'];

			if($this->LoggedIn() && ($loadTime + $cacheTime <= time()) || $forced){
				if(!$this->LoggedIn()){
					$this->Login();
					if(!$this->LoggedIn()){
						error_log("Advertwhirl Adsense Error: " . $this->ErrorMessage());
						return;	
					}
				}

				$this->options['adUnits'] = array();
				$this->options['ads-set'] = false;
				$data = $this->curl_get($this->AdUnitsUrl);
				preg_match_all($this->AdUnitSummaryExpression, $data, $matches, PREG_SET_ORDER);
				// Get the ad units
				foreach($matches as $match){
					$id = $match[1];

					$url = $this->AdUnitCodeBase . $id;
					$data = $this->curl_get($url);

					preg_match_all($this->AdUnitNameExpression, $data, $nameMatch, PREG_SET_ORDER);
					$name = $nameMatch[0][1];

					preg_match_all($this->AdUnitCodeExpression, $data, $codeMatch, PREG_SET_ORDER);
					$code = $codeMatch[0][1];

					$url = $this->AdUnitDetailBase . $id;
					$data = $this->curl_get($url);

					preg_match_all($this->AdUnitSizeExpression, $data, $sizeMatch, PREG_SET_ORDER);
					$size = $sizeMatch[0][1];

					preg_match_all($this->AdUnitSlotIdExpression, $data, $slotIdMatch, PREG_SET_ORDER);
					$slotId = $slotIdMatch[0][1];

					/** @TODO: Replace the rest of the regex matching with DOM processing */
					$dom = new DOMDocument();
					$dom->loadHTML($data);
					$typeSelect = $dom->getElementById('type');
					$typeOptions = $typeSelect->getElementsByTagName('option');
					foreach($typeOptions as $type){
						if($type->hasAttribute('selected'))
							$format = trim(str_replace('(default)', '', $type->nodeValue));
					}

					$this->options['adUnits'][$id]['name'] = $name;
					$this->options['adUnits'][$id]['format'] = $format;
					$this->options['adUnits'][$id]['size'] = $size;
					$this->options['adUnits'][$id]['slotId'] = $slotId;
					$this->options['adUnits'][$id]['code'] = $code;

				}
				$this->options['ads-set'] = true;
				$this->options['ads-loadtime'] = time();
				update_option($this->name . "_options", $this->options);
				$this->Logout();
			}
		}
	}
}

?>
