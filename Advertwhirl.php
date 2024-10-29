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
                                                                 
Plugin Name: Advertwhirl
Plugin URI: http://wordpress.org/extend/plugins/advertwhirl/
Description: Advertwhirl is an advertising campaign manager wordpress.  Schedule and rotate ad campaigns.  Access campaigns through a shortcode.  If you like Advertwhirl consider supporting it. | <a target="_blank" href="http://twitter.com/share?url=http://www.mobilesentience.com/software/oss/advertwhirl/&text=Checkout%20Advertwhirl%20a%20%23Advertising%20%23campaign%20%23manager20for%20%40Wordpress.%20%23wordpress%20%23wp%20%23plugin&via=MobileSentience">Tweet about Advertwhirl.</a> | <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U9SUYMTEKZJDY">Donate</a> | <a target="_blank" href="http://www.mobilesentience.com/software/oss/advertwhirl/">Advertwhirl Support</a> | <a target="_blank" href="http://twitter.com/#!/MobileSentience">Follow Mobile Sentience</a>
Version: 1.0.14b
License: GPL2
Author: Max Jonathan Spaulding - Mobile Sentience LLC
Author URI: http://www.mobilesentience.com
Stable tag: 1.0.13
*/

 
if(!class_exists('AdvertwhirlPlugin')){
	require_once('AdvertwhirlPlugin.php');
	require_once('libs/AdvertwhirlAllocations.php');

	$advertwhirlPlugin = new AdvertwhirlPlugin();
	function_exists('register_activation_hook')?register_activation_hook(__FILE__, array(&$advertwhirlPlugin, 'ActivatePlugin')):add_action('activate_'.__FILE__, array(&$advertwhirlPlugin, 'ActivatePlugin'));
	function_exists('register_deactivation_hook')?register_deactivation_hook(__FILE__, array(&$advertwhirlPlugin, 'DeactivatePlugin')):add_action('deactivate_'.__FILE__, array(&$advertwhirlPlugin, 'DeactivatePlugin'));
}

if(!class_exists('AdvertwhirlCampaignWidget')){
	require_once('AdvertwhirlCampaignWidget.php');
	add_action('widgets_init', create_function('', 'return register_widget("AdvertwhirlCampaignWidget");'));
}

if(!function_exists('advertwhirl_get_ad')){
	require_once("libs/Version.php");
	function advertwhirl_get_ad($campaign){
		global $wp_version;
		global $advertwhirl_options_name;
		$options = maybe_unserialize(get_option($advertwhirl_options_name));

		if(isset($options['adcampaigns'][$campaign])){
			// Get the ad
			$allocations = $options['adcampaigns'][$campaign]['allocations'];
			if(isset($allocations) && sizeof($allocations) > 0){
				foreach($allocations as $index =>  $allocation){
					if(VerifyAllocation($allocation, $campaign)){
						return AllocateAd($campaign, $index, $allocation);
					}
				}
			}
		}
		return AllocateAd($campaign, $index, null);
	}
}

if(!function_exists('advertwhirl_print_ad')){
	function advertwhirl_print_ad($campaign){
		echo advertwhirl_get_ad($campaign);
	}
}

