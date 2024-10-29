<?php

	define('WP_USE_THEMES', true);

	if(!isset($wp_did_header)){
		$wp_did_header = true;
		//require_once(dirname(__FILE__) . '/../../../wp-load.php');
		require_once('../../../wp-load.php');

		//wp();

		//require_once(ABSPATH . WPINC . '/template-loader.php');
	}

	require_once('./Advertwhirl.php');

	echo advertwhirl_get_ad($_GET['campaign']);

?>
