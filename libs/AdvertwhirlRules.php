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

if(!function_exists('CompareByOperator')){
	function CompareByOperator($lhs, $op, $rhs){
		if(!isset($lhs) || !isset($op) || !isset($rhs) || strlen($op) == 0)
			return false;

		switch($op){
			case '==': // is equal to
				if(is_numeric($rhs)){
					return $lhs == $rhs;
				}else{
					return strcmp($lhs, $rhs) === 0;
				}
				break;
			case '==i': // case insensitive string compare
				return strcasecmp($lhs, $rhs) === 0;
				break;
			case '!=': // is equal to
				if(is_numeric($rhs)){
					return $lhs != $rhs;
				}else{
					return strcmp($lhs, $rhs) !== 0;
				}
				break;
			case '!=i': // is not equal to
				return strcasecmp($lhs, $rhs) === 0;
				break;
			case '>': // is greater than
				if(is_numeric($rhs)){
					return $lhs > $rhs;
				}else{
					return strcmp($lhs, $rhs) > 0;
				}
				break;
			case '<': // is less than
				if(is_numeric($rhs)){
					return $lhs < $rhs;
				}else{
					return strcmp($lhs, $rhs) < 0;
				}
				break;
			case '>=': // is greater than or equal to
				if(is_numeric($rhs)){
					return $lhs >= $rhs;
				}else{
					return strcmp($lhs, $rhs) >= 0;
				}
				break;
			case '<=': // is less than or equal to
				if(is_numeric($rhs)){
					return $lhs <= $rhs;
				}else{
					return strcmp($lhs, $rhs) <= 0;
				}
				break;
			case '~=': // matches regular expression
				return preg_match('/' . $rhs . '/', $lhs, $matches);
				break;
			case '!~': // does not match regular expression
				return !preg_match('/' . $rhs . '/', $lhs, $matches);
				break;
		}

	 	return false;

	}
}

if(!function_exists('VerifyRule_author')){
	function VerifyRule_author($rule, $allocation, $campaign){
		if(!isset($rule['author']))
			return false;
		global $wp_version;
		if ($wp_version > "2.8")
			return $rule['author'] === get_the_author_meta('ID');
	 	return $rule['author'] === get_the_author_ID();
	}
}

if(!function_exists('VerifyRule_posttype')){
	function VerifyRule_posttype($rule, $allocation, $campaign){
		if(!isset($rule['posttype']))
			return false;
	 	return $rule['posttype'] === get_post_type();
	}
}


if(!function_exists('post_is_in_descendant_category')){
	/**
	  * Tests if any of a post's assigned categories are descendants of target categories
	  *
	  * @param int|array $cats The target categories. Integer ID or array of integer IDs
	  * @param int|object $_post The post. Omit to test the current post in the Loop or main query
	  * @return bool True if at least 1 of the post's categories is a descendant of any of the target categories
	  * @see get_term_by() You can get a category by name or slug, then pass ID to this function
	  * @uses get_term_children() Passes $cats
	  * @uses in_category() Passes $_post (can be empty)
	  * @version 2.7
	  * @link http://codex.wordpress.org/Function_Reference/in_category#Testing_if_a_post_is_in_a_descendant_category
	  */
	function post_is_in_descendant_category($cats, $_post = null){
		foreach ((array)$cats as $cat){
			// get_term_children() accepts integer ID only
			$descendants = get_term_children( (int) $cat, 'category');
			if($descendants && in_category($descendants, $_post))
				return true;
		}
		return false;
	}
}

if(!function_exists('VerifyRule_category')){
	function VerifyRule_category($rule, $allocation, $campaign){
		$op = $rule['catop'];
		$id = $rule['catid'];
		if(!isset($op) || !isset($id))
			return false;
		switch($op){
			case 'isa': // is the category or a child of the category
				return in_category($id) || post_is_in_descendant_category($id);
				break;
			case 'isnota': // is not the category or a child of the category
				return !in_category($id) && !post_is_in_descendant_category($id);
				break;
		}
		return false;
	}
}

if(!function_exists('VerifyRule_tag')){
	function VerifyRule_tag($rule, $allocation, $campaign){
		$op = $rule['tagop'];
		$id = $rule['tagid'];
		if(!isset($op) || !isset($id))
			return false;

		$posttags = get_the_tags();
		switch($op){
			case 'isa': // has the tag
				$match = true;
				break;
			case 'isnota': // does not have the tag
				$match = false;
				break;
		}
		if($posttags){
			foreach($posttags as $tag) {
				if($tag == $id)
					return $match;
			}
		}
		return !$match;
	}
}

if(!function_exists('VerifyRule_getargument')){
	function VerifyRule_getargument($rule, $allocation, $campaign){
		return CompareByOperator($_GET[$rule['argname']], $rule['argop'], $rule['argvalue']);

	}
}

if(!function_exists('VerifyRule_cookie')){
	function VerifyRule_cookie($rule, $allocation, $campaign){
		return CompareByOperator($_COOKIES[$rule['cookiename']], $rule['cookieop'], $rule['cookievalue']);

	}
}

if(!function_exists('VerifyRule_customfield')){
	function VerifyRule_customfield($rule, $allocation, $campaign){
		return CompareByOperator(get_post_meta($post->ID, $rule['customfieldname'], true), $rule['customfieldop'], $rule['customfieldvalue']);

	}
}

if(!function_exists('VerifyRule_remoteip')){
	function VerifyRule_remoteip($rule, $allocation, $campaign){
		return CompareByOperator($_SERVER['REMOTE_ADDR'], $rule['remoteipop'], $rule['remoteipvalue']);
	}
}

if(!function_exists('VerifyRule_remotehostname')){
	function VerifyRule_remotehostname($rule, $allocation, $campaign){
		return CompareByOperator($_SERVER['REMOTE_HOST'], $rule['remotehostnameop'], $rule['remotehostnamevalue']);
	}
}

if(!function_exists('VerifyRule_referrer')){
	function VerifyRule_referrer($rule, $allocation, $campaign){
		return CompareByOperator($_SERVER['HTTP_REFERER'], $rule['referrerop'], $rule['referrervalue']);
	}
}

if(!function_exists('VerifyRule_adsize')){
	function VerifyRule_size($rule, $allocation, $campaign){
		if($campaign == null)
			return false;
		return CompareByOperator($campaign['adsize'], $rule['adsizeop'], $rule['adsizevalue']);
	}
}

if(!function_exists('VerifyRule_cvar')){
	function VerifyRule_cvar($rule, $allocation, $campaign){
		// Verify that we have all the arguments that we need and that the GET argument is set
		foreach(get_option('active_plugins') as $index => $plugin){
			if($plugin == 'virtual-theme/VirtualTheme.php'){
				require_once(WP_PLUGIN_DIR . "/" . $plugin);
				$var = VirtualTheme::GetVirtualVariable($_GET['virtualpath'], $rule['cvarname']);
				return CompareByOperator($var, $rule['cvarop'], $rule['cvarvalue']);
			}
		}
		return false;
	}
}

if(!function_exists('VerifyRule_vpath')){
	function VerifyRule_vpath($rule, $allocation, $campaign){
		// Verify that we have all the arguments that we need and that the GET argument is set
		return CompareByOperator($_GET["virtualpath"], $rule['vpathop'], $rule['vpath']);

	}
}

if(!function_exists('VerifyRule_geoip')){
	require_once('Version.php');

	function VerifyRule_geoip_GetGeoLocation($ip, $apiKey){
		$ipLite = new ip2location_lite;
		$ipLite->setKey($apiKey);
		$visitorGeoLocation = $ipLite->getCity($ip);
	  
		if($visitorGeoLocation['statusCode'] == 'OK'){
			$data = base64_encode(serialize($visitorGeoLocation));
			setcookie("geolocation", $data, time()+3600*24*7); //set cookie for 1 week
		}
		return $visitorGeoLocation;
	}

	function VerifyRule_geoip($rule, $allocation, $campaign){
		// Verify that we have all the arguments that we need and that the GET argument is set
		include_once('ip2locationlite.class.php');

		global $advertwhirl_options_name;

		//Set geolocation cookie
		$visitorGeoLocation = null;
		if($_COOKIE["geolocation"]){
			$visitorGeoLocation = unserialize(base64_decode($_COOKIE["geolocation"]));
		}

		if(!isset($visitorGeoLocation) || $visitorGeoLocation['ipAddress'] != $_SERVER['REMOTE_ADDR']){
			$options = maybe_unserialize(get_option($advertwhirl_options_name));
			$apiKey = $options['settings']['ipinfodbkey'];
			if(!isset($apiKey) || strlen($apiKey) == 0)
				return false;
			$visitorGeoLocation = VerifyRule_geoip_GetGeoLocation($_SERVER['REMOTE_ADDR'], $apiKey);
		}

		if(!isset($visitorGeoLocation) || $visitorGeoLocation['statusCode'] !=  "OK")
			return false;

		$lhs = $visitorGeoLocation[$rule['geolocation']];
		return CompareByOperator($lhs, $rule['geoop'], $rule['geovalue']);

	}
}

