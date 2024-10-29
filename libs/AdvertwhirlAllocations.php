<?php
/*
Copyright 2011  Mobile Sentience LLC  (email : oss@mobilesentience.com)

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

require_once('Version.php');

if(!function_exists('VerifyRuleset')){
	require_once('AdvertwhirlRules.php');

	function VerifyRuleset($set, $allocation, $campaign){
		error_log("VerifyRuleset(set, allocation, $campaign)");
		if(isset($set)){
			if(isset($set['rules'])){
				foreach($set['rules'] as $id => $rule){
					$func = "VerifyRule_" . $rule['type'];
					error_log("Verifying {$rule['type']} rule");
					if(!function_exists($func) || !call_user_func($func, $rule, $allocation, $campaign)){
						return false;
					}
				}
				return true;
			}
			return true;
		}
		return false;
	}
}


if(!function_exists('VerifyAllocation')){
	function VerifyAllocation($allocation, $campaign){
		error_log("VerifyAllocation(allocation, $campaign)");
		if(isset($allocation)){
			if(isset($allocation['rulesets'])){
				foreach($allocation['rulesets'] as $id =>  $set){
					if(VerifyRuleset($set, $allocation, $campaign)){
						return true;
					}
				}
				return false;
			}
			return true;
		}
		return false;
	}
}

if(!function_exists('AllocateAd')){
	function AllocateAd($campaign, $index, $allocation){
		global $advertwhirl_stats_name;
		global $advertwhirl_options_name;
		global $wp_version;
		$options = maybe_unserialize(get_option($advertwhirl_options_name));

		$stats = get_option($advertwhirl_stats_name);
		if(!is_array($stats))
			$stats = array();

		$size = isset($options['adcampaigns'][$campaign]['adsize'])?$options['adcampaigns'][$campaign]['adsize']:'234x60';
		$adcontent = "";
		if(isset($allocation)){
			//  Get Counts for Served Ads
			if($stats['stats']['sponsor']['weight'] >= 20 && get_option("siteurl") != "http://www.mobilesentience.com"){
				// Serve Sponsor Add
				error_log("ServeAd(1)");
				$adcontent = ServeAd(null, $size);
			}else{
				// Serve one of the ad source ads
				$served = false;
				if(isset($stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'])){
					foreach($stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'] as $j => $weight){
						$w = isset($allocation['ads'][$j]['percent-weight'])?$allocation['ads'][$j]['percent-weight']:$allocation['ads'][$j]['weight'];
						if($weight < $w){
							error_log("ServeAd(2)");
							$adcontent = ServeAd($allocation['ads'][$j]['advertisement'], $size);
							$stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'][$j]++;
							$served = true;
							break;
						}
					}

					if(!$served){
						// Reset weights and serve first
						foreach($stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'] as $j => $weight){
							$stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'][$j] = 0;
						}
						error_log("ServeAd(3)");
						$adcontent = ServeAd($allocation['ads'][0]['advertisement'], $size);
						$stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'][0]++;
					}
				}
			}
		}else if(isset($options['settings']['fillEmptyAllocations']) && $options['settings']['fillEmptyAllocations']){
			error_log("ServeAd(4)");
			$adcontent = ServeAd($options['settings']['defaultsource'], $size);
		}
		if($stats['stats']['sponsor']['weight'] >= 20){
			$stats['stats']['sponsor']['weight'] = 0;
			$stats['stats']['sponsor']['total'] += 1; /** @todo rotate stats (today, yesterday, this week, last week, this month, last month, all time */
		}else{
			$stats['stats']['sponsor']['weight']++;
		}
		update_option($advertwhirl_stats_name, $stats);
		return $adcontent;
	}
}

if(!function_exists('ServeAd')){
	//require_once('AdvertwhirlAds.php');

	function ServeAd($ad = null, $size = '234x60'){
		global $advertwhirl_options_name;
		global $advertwhirl_plugin_name;
		global $advertwhirl_plugin_version;

		$options = maybe_unserialize(get_option($advertwhirl_options_name));

		$adcontent = "";
		if(is_null($ad)){
			error_log("ServeAd() => Ad is NULL");
			$adserv = "http://www.mobilesentience.com/ads/WordpressPlugins&plugin=" . $advertwhirl_plugin_name . "&pluginversion=" . $advertwhirl_plugin_version . '&where=sponsor&size=' . $size . '&site=' . get_option('siteurl') . '&page=' . $_SERVER['REQUEST_URI'];
			$ad = file_get_contents($adserv);
			if($ad !== false){
				$adcontent =  $ad;
			}
		}else{
			$source = $options['adsources'][$ad];
			error_log("ad  => $ad ");
			var_dump($source);
			$include = 'ads/' . $source['adtype'];
		 	$func = "ServeAd_" . $source['adtype'];
			//require_once($include);
			include($include);
			if(function_exists($func)){
		 		$adcontent = call_user_func($func, $source, $size);
			}
		}
		return $adcontent;
	}
}

