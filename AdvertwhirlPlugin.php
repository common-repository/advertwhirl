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

require_once('libs/AdsenseStats.php');
require_once('libs/SiteDB.php');
require_once('libs/Version.php');
require_once('libs/Compatibility.php');
require_once(ABSPATH . '/wp-admin/includes/plugin.php');

class AdvertwhirlPlugin {
	private $options;

	private $adserv;
	private $prodid = "advertwhirl";
	private $version;
	private $name;

	private $pluginDir;
	private $pluginFile;
	private $pluginPage;
	private $pageURL;
	private $imagePath;
	private $libraryPath;
	private $handbookPath;

	private $adsense;
	private $db;

	// Flags
	private $activating = false;
	private $deactivating = false;
	private $virtualThemeInstalled = false;
	private $initialized = false;
	private $rewriteHooked = false;

	private $PromoteText = '<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">Advertwhirl <input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="U9SUYMTEKZJDY"><input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/scr/pixel.gif" width="1" height="1"> <a target="_blank" href="http://twitter.com/#!/MobileSentience">Follow Mobile Sentience</a><iframe src="http://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FMobile-Sentience%2F184752614894685%3Fsk%3Dapp_136488953086266&amp;layout=button_count&amp;show_faces=false&amp;width=200&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe></form>';

	private $DonateImage = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="U9SUYMTEKZJDY"><input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/scr/pixel.gif" width="1" height="1"></form>';
	private $DonateText = '<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U9SUYMTEKZJDY">Donate</a>';

	private $TwitterFollow = '<a target="_blank" href="http://twitter.com/#!/MobileSentience">Follow Mobile Sentience</a>';
	private $FacebookFollow = '<iframe src="http://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FMobile-Sentience%2F184752614894685%3Fsk%3Dapp_136488953086266&amp;layout=button_count&amp;show_faces=false&amp;width=200&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>';

	private $eturi = 'http://www.mobilesentience.com/et/wptracker.php';

	private $AdminTabs = array(
		'campaigns-tab' => array('name' => 'Campaigns', 'method_base' => 'CampaignListTab'),
		'sources-tab' => array('name' => 'Local Ads', 'method_base' => 'SourceListTab'),
		'settings-tab' => array('name' => 'Settings', 'method_base' => 'SettingsTab'),
		'handbook-tab' => array('name' => 'Tutorials', 'method_base' => 'HandbookTab'),
		'support-tab' => array('name' => 'Support', 'method_base' => 'SupportTab'),
		'about-tab' => array('name' => 'About', 'method_base' => 'AboutTab')
	);

	private $AllocationMap = array(
		'name' => 'alname-',
		'description' => 'aldescription-',
		'displayed' => 'alpaneldisplayed-',
		'ads' => array(
			'advertisement' => 'alad-',
			'weight' => 'aladweight-',
			'percent-weight' => 'aladpercentweight-'
		),
		'rulesets' => array(
			'id' => 'alrulesetid-',
			'rules' => array(
				'type' => 'alruletype-',
				'author' => 'alruleauthor-',
				'posttype' => 'alruleposttype-',
				'tag' => 'alruletag-',
				'category' => 'alrulecategory-',
				'post' => 'alrulepost-',
				'page' => 'alrulepage-',
				'argname' => 'alruleargumentname-',
				'argvalue' => 'alruleargumentvalue-',
				'argop' => 'alruleargumentoperator-',
				'catid' => 'alrulecategoryid-',
				'catop' => 'alrulecategoryoperator-',
				'tagid' => 'alruletagid-',
				'tagop' => 'alruletagoperator-',
				'vpath' => 'alrulevpath-',
				'vpathop' => 'alrulevpathoperator-',
				'cvarname' => 'alrulecvarname-',
				'cvarop' => 'alrulecvaroperator-',
				'cvarvalue' => 'alrulecvarvalue-',
				'geolocation' => 'alrulegeolocation-',
				'geoop' => 'alrulegeoop-',
				'geovalue' => 'alrulegeovalue-',
				'cookiename' => 'alrulecookiename-',
				'cookievalue' => 'alrulecookievalue-',
				'cookieop' => 'alrulecookieoperator-',
				'customfieldname' => 'alrulecustomfieldname-',
				'customfieldvalue' => 'alrulecustomfieldvalue-',
				'customfieldop' => 'alrulecustomfieldoperator-',
				'remoteipvalue' => 'alruleremoteipvalue-',
				'remoteipop' => 'alruleremoteipoperator-',
				'remotehostnamevalue' => 'alruleremotehostnamevalue-',
				'remotehostnameop' => 'alruleremotehostnameoperator-',
				'referrervalue' => 'alrulereferrervalue-',
				'referrerop' => 'alrulereferreroperator-',
				'adsizevalue' => 'alruleadsizevalue-',
				'adsizeop' => 'alruleadsizeoperator-'
			)
		)
	);

	private $AdTypes = array (
		'adsense' => 'Adsense Ad Unit',
		'url' => 'Ad Unit Loaded From URL',
		'inline' => 'Inline HTML Ad',
		'banner' => 'Local Banner Ad'
	);

	private $RuleTypes = array (
		'author' => 'Match an author',
		'posttype' => 'Match the post type',
		'category' => 'Match a category',
		'tag' => 'Match a tag ',
		'geoip' => 'Match a location',
		'getargument' => 'Match link arguments',
		'cookie' => 'Match a cooke value',
		'customfield' => 'Match a posts custom fields',
		'remoteip' => 'Match the visitors ip address',
		'remotehostname' => 'Match the visitors hostname',
		'referrer' => 'Match the referring site',
		'adsize' => 'Match the Ad Unit size'
	);

	private $VirtualThemeRules = array (
		'vpath' => 'Match a Virtual Path',
		'cvar' => 'Match a Virtual Theme Custom Variable'
	);

	private $CacheTimes = array(
		'0' => 'Never Cache',
		'1' => 'Save loaded data for 1 hour',
		'24' => 'Save loaded data for 1 day',
		'168' => 'Save loaded data for 1 week'
	);

	private $PostTypes = array(
		'homepage' => 'Place Campaign Ads on Home Page',
		'page' => 'Place Campaign Ads in Pages',
		'post' => 'Place Campaign Ads in Posts',
	);

	private $AdPlacementAlignment = array(
		'0' => 'On the left',
		'1' => 'On the right',
		'2' => 'Alternating on the left then on the right',
		'3' => 'Alternating on the right then on the left'
	);

	private $AdUnitSizes = array(
		'Medium Rectangle - (300x250)' => '300x250',
		'Large Rectangle - (336x280)' => '336x280',
		'Leaderboard - (728x90)' => '728x90',
		'Wide Skyscraper - (160x600)' => '160x600',
		'Banner - (468x60)' => '468x60',
		'Half Banner - (234x60)' => '234x60',
		'Skyscraper - (120x600)' => '120x600',
		'Vertical Banner - (120x240)' => '120x240',
		'Square - (250x250)' => '250x250',
		'Small Square - (200x200)' => '200x200',
		'Small Rectangle - (180x150)' => '180x150',
		'Button - (125x125)' => '125x125',
		'(728x15)' => '728x15',
		'(468x15)' => '468x15',
		'(200x90)' => '200x90',
		'(180x90)' => '180x90',
		'(160x90)' => '160x90',
		'(120x90)' => '120x90'
	);

	// Protected constructor for singleton
	//private function __construct(){
	public function __construct(){
		/** Uncomment for testing, forces loading of plugin stylesheets and javascripts */
		global $wp_version;
		global $advertwhirl_plugin_version;
		global $advertwhirl_plugin_name;
		global $advertwhirl_stats_name;
		global $advertwhirl_options_name;
		$this->version = $advertwhirl_plugin_version;
		$this->name = $advertwhirl_plugin_name;
		$this->options_name = $advertwhirl_options_name;
		$this->stats_name = $advertwhirl_stats_name;

		$this->adserv = 'http://www.mobilesentience.com/ads/WordpressPlugins&plugin=' . $this->name . '&pluginversion=' . $this->version . '&where=admin';
		$this->pluginFile = __FILE__;
		$this->pageURL = '?page=' . $this->getRightMost(__FILE__, 'plugins/');
		$this->imagePath = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "", plugin_basename(__FILE__)) . '/images/';
		$this->externalAdsPath = str_replace(site_url(), '', WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "", plugin_basename(__FILE__))) . '/ServeAd.php?campaign=';
		if(empty($this->pluginDir)) $this->pluginDir = dirname($this->pluginFile);
		$this->libraryPath = $this->pluginDir . '/libs/';
		$this->handbookPath = $this->pluginDir . '/handbook.txt';

		$this->adsense = new AdsenseStats();
		$this->db = new SiteDB();

		//Language Setup
		$locale = get_locale();
		$mo = dirname(__FILE__) . "/languages/" . $this->name . "-".$locale.".mo";
		load_textdomain($this->name, $mo);

		//add_action('init', array(&$this, 'InitializePlugin'));

		/* Get plugin data */
		$this->options = maybe_unserialize(get_option($this->options_name));
		if(!isset($this->options['settings'])){
			$this->options['settings'] = $this->DefaultSettings();
			update_option($this->options_name, $this->options);
		}

		/** This can be removed once everyone is using 1.0.5+ */
		if(!isset($this->options['settings']['defaulttab']))
			$this->options['settings']['defaulttab'] = 'handbook-tab';

		/* Hook the actions */
		$this->HookActions();

		/* Add the shortcodes */
		$this->AddShortcodes();

		if(gettype($this->options)!="array"){
			$this->options = array();
		}

		$this->adsense->SetUsername($this->options['settings']['adsense']['username']);
		$this->adsense->SetPassword($this->options['settings']['adsense']['password']);
	}

	public function PingBack($version, $previousVersion, $state){
		if(function_exists('curl_init')){
			global $wp_version;
			global $advertwhirl_options_name;
			global $advertwhirl_plugin_version;
			global $advertwhirl_plugin_name;
			$options = maybe_unserialize(get_option($advertwhirl_options_name));
	
			//private function curl_post($url, $post) {
			$post['plugin'] = $advertwhirl_plugin_name;
			$post['version'] = $advertwhirl_plugin_version;
			$post['preversion'] = $previousVersion;
			$post['state'] = $state;
			$post['phpversion'] = PHP_VERSION;
			$post['wpversion'] = get_bloginfo('version');
			$post['site'] = get_site_url();
			$post['server'] = $_SERVER['SERVER_SOFTWARE'];
	
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->eturi);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_exec($curl);
			curl_close($curl);
		}
	}

	/** Called as a static from ActivatePlugin hook */
	public function ActivatePlugin() {
		global $advertwhirl_options_name;
		global $advertwhirl_plugin_name;
		global $advertwhirl_plugin_version;
		$options = maybe_unserialize(get_option($advertwhirl_options_name));

		$options['flags']['deactivating'] = false;
		update_option($advertwhirl_options_name, $options);

		// Ad the rules for exteranl adds back to .htaccess
		$inst = new AdvertwhirlPlugin();
		$inst->FlushRewrites();
		/* Check the version info */
		$state = "active";
		if(!isset($this->options['settings']['installed_version'])){
			// This is a new install
			$options['settings']['installed_version'] = $advertwhirl_plugin_version;
			update_option($advertwhirl_options_name, $options);
		}else{
			// There is a current install
			switch(version_compare($advertwhirl_plugin_version, $options['settings']['installed_version'])){
				case -1:
					// Version downgrade
					$options['settings']['previous_version'] = $options['settings']['installed_version'];
					$options['settings']['installed_version'] = $advertwhirl_plugin_version;
					update_option($advertwhirl_options_name, $options);
				break;
				case 0:
					// No version change, just a simple re-activation
				break;
				case 1:
					// Version upgrade
					$options['settings']['previous_version'] = $options['settings']['installed_version'];
					$options['settings']['installed_version'] = $advertwhirl_plugin_version;
					update_option($advertwhirl_options_name, $options);
				break;
			}
		}
		$cversion = $options['settings']['installed_version'];
		$pversion = isset($options['settings']['previous_version'])?$options['settings']['previous_version']:'na';
		$inst->PingBack($cversion, $pversion, $state);
	}

	public function DeactivatePlugin() {
		global $wp_version;
		global $advertwhirl_options_name;
		$options = maybe_unserialize(get_option($advertwhirl_options_name));
		$inst = new AdvertwhirlPlugin();
		/* Remove the shortcodes */
		$inst->RemoveShortcodes();

		/** Register Admin Style Sheet*/
		wp_deregister_style('advertwhirl-plugin-admin-css');

		/** Register Admin Javascript*/
		wp_deregister_script('advertwhirl-plugin-admin-scripts');

		//  If external ads are set flush the rules to remove the external ad rules
		$options['flags']['deactivating'] = true;
		update_option($advertwhirl_options_name, $options);
		$inst->FlushRewrites();
		$state = "inactive";
		$cversion = $options['settings']['installed_version'];
		$pversion = isset($options['settings']['previous_version'])?$options['settings']['previous_version']:'na';
		$inst->PingBack($cversion, $pversion, $state);
	}

	// Protected cloner for singleton
	//private function __clone(){}

	public function InitializePlugin(){
		if(!$this->initialized){
			/* Get plugin data */
			global $wp_version;
			$this->options = maybe_unserialize(get_option($this->options_name));
			if(!isset($this->options['settings'])){
				$this->options['settings'] = $this->DefaultSettings();
				update_option($this->options_name, $this->options);
			}

			/** This can be removed once everyone is using 1.0.5+ */
			if(!isset($this->options['settings']['defaulttab']))
				$this->options['settings']['defaulttab'] = 'handbook-tab';

			/* Hook the actions */
			$this->HookActions();

			/* Add the shortcodes */
			$this->AddShortcodes();

			if(gettype($this->options)!="array"){
				$this->options = array();
			}

			$this->adsense->SetUsername($this->options['settings']['adsense']['username']);
			$this->adsense->SetPassword($this->options['settings']['adsense']['password']);
			$this->initialized = true;
		}
	}

	private function DefaultSettings(){
		$settings = array();
		$settings['externalAdsEnabled'] = false;
		$settings['adUrlPrefix'] = 'advertwhirl';
		$settings['defaulttab'] = 'handbook-tab';

		/* Default Settings for Adsense */
		$adsenseSettings = array();
		$adsenseSettings['username'] = "";
		$adsenseSettings['password'] = "";
		$adsenseSettings['ads-cache'] = 8640;
		$adsenseSettings['stats-cache'] = 8640;
		$settings['adsense'] = $adsenseSettings;

		$analyticsSettings = array();
		$analyticsSettings['enabled'] = false;
		$analyticsSettings['username'] = "";
		$analyticsSettings['password'] = "";
		$analyticsSettings['VariablesEnabled'] = true;
		$settings['analytics'] = $analyticsSettings;

		return $settings;
	}

	public function HookActions() {
		add_action('admin_menu', array(&$this, 'DisplayAdminMenu')); /* Add the admin menu */
		add_action('wp_dashboard_setup', array(&$this, 'AddDashboardWidgets')); /* Add the stats widget to the dashboard */

		if(!isset($this->rewriteHooked) || !$this->rewriteHooked){
			add_action('generate_rewrite_rules', array(&$this, 'RewriteExternalAds'));
			$this->rewriteHooked = true;
		}
		add_action('admin_init', array(&$this, 'AdminInit'));

		// Hooks and filters for auto insertion of ads
		if($this->options['settings']['display']['hook_content'])
			add_filter("the_content", array(&$this, "FilterContent"), 1000);

		if($this->options['settings']['display']['hook_header'])
			add_action('wp_head', array(&$this, 'InsertHeaderAds'));

		if($this->options['settings']['display']['hook_footer'])
			add_action('wp_footer', array(&$this, 'InsertFooterAds'));
	}

	public function FilterContent($content){
		$type = get_post_type();
		if($type == 'page' && is_front_page()){
			$type = 'homepage';
		}

		$precontent = "";
		$postcontent = "";
		$midcontent = "";
		$useMidContent = false;
		foreach($this->options['adcampaigns'] as $name => $campaign){
			// Get the display settings for this campaign
			if($campaign['display']['enabled']){
				//  Insert an add before the content if required
				if($campaign['display'][$type]['before-content']){
					$precontent .= advertwhirl_get_ad($name);
				}

				if($campaign['display'][$type]['in-content']){
					$useMidContent = true;
					$maxAds = $campaign['display'][$type]['maxads'];
					$adsEvery = $campaign['display'][$type]['every'];
					$offset = $campaign['display'][$type]['offset'] - 1;
					$align = $campaign['display'][$type]['align'];

					$adon = 0;  //this is a variable so you don't show ads more than once.
					$midcontent .= '<div>';
					$tcount = 0; //this is count for number of <p> blocks

					/** This switch is broke up weird for improved performance.  the modules
					  * doesn't need to be calculated for the content of every post/page if
					  * ads are aligned on the left or right
					  */
					switch($align){
						case '0':
							// 'On the left'
							$float = "float:left;";
						break;
						case '1':
							// 'On the right'
							$float = "float:right;";
						break;
						case '2':
							// 'Alternating on the left then on the right'
							$even = "float:left;";
							$odd = "float:right;";
						break;
						case '3':
							// 'Alternating on the right then on the left'
							$even = "float:right;";
							$odd = "float:left;";
						break;
					}

					$dimensions = explode('x', $campaign['adsize']);
					if(is_array($dimensions) && count($dimensions) == 2){
						$size = 'width:' . $dimensions[0] . 'px;height:' . $dimensions[1] . 'px;';
					}else{
						$size = '';
					}
					$paragraphs = explode("</p>", $content);  //Separate the content into <p> blocks
					foreach($paragraphs as $paragraph) {
						if(preg_match('/<p> /',$paragraph) == 0 && $tcount >= $offset && ($tcount - $offset) % $adsEvery == 0 && $adon < $maxAds){
							if($align > 1){
								/** This switch is broke up weird for improved performance.  the modules
								  * doesn't need to be calculated for the content of every post/page if
								  * ads are aligned on the left or right
								  */
								$float = $adon % 2 == 0?$even:$odd;
							}
							$midcontent .= '<div style="' . $size . $float . 'padding: 9px 9px 9px 9px;">';
							$midcontent .= advertwhirl_get_ad($name);
							$midcontent .= '</div>';
							$adon++;
						}
						$midcontent .=  $paragraph;  //print the <p> block
						$midcontent .= "</p>";
						$tcount++;
					}
					$midcontent .= '</div>';
				}
	
				//  Insert an add after the content if required
				if($campaign['display'][$type]['after-content']){
					$postcontent .= advertwhirl_get_ad($name);
				}
			}
		}

		if($useMidContent)
			$content = $precontent . $midcontent . $postcontent;
		else
			$content = $precontent . $content . $postcontent;

		return $content;
	}

	public function InsertHeaderAds(){
		$type = get_post_type();
		if($type == 'page' && is_front_page()){
			$type = 'homepage';
		}
		foreach($this->options['adcampaigns'] as $name => $campaign){
			// Get the display settings for this campaign
			if($campaign['display'][$type]['header']){
				// Place an ad from this campaign in the header
				echo advertwhirl_get_ad($name);
			}
		}
	}

	public function InsertFooterAds(){
		$type = get_post_type();
		if($type == 'page' && is_front_page()){
			$type = 'homepage';
		}
		foreach($this->options['adcampaigns'] as $name => $campaign){
			// Get the display settings for this campaign
			if($campaign['display'][$type]['footer']){
				// Place an ad from this campaign in the footer
				echo advertwhirl_get_ad($name);
			}
		}
	}

	public function AddShortcodes(){
		add_shortcode('advertwhirl', array(&$this, 'HandleShortcode_advertwhirl'));
	}

	public function RemoveShortcodes(){
		remove_shortcode('advertwhirl', array(&$this, 'HandleShortcode_advertwhirl'));
	}

	public function AdminInit(){
		foreach(get_option('active_plugins') as $index => $plugin){
			if($plugin == 'virtual-theme/VirtualTheme.php'){
				$this->virtualThemeInstalled = true;
				$this->RuleTypes = array_merge($this->RuleTypes, $this->VirtualThemeRules);
				break;
			}
		}
	}

	public function FlushRewrites(){
		global $wp_rewrite;
		global $advertwhirl_options_name;
		$options = maybe_unserialize(get_option($advertwhirl_options_name));

		$rewriteHooked = isset($options['flags']['rewriteHooked'])?$options['flags']['rewriteHooked']:false;
		if(!isset($options['flags']['rewriteHooked']) || !$options['flags']['rewriteHooked']){
			add_action('generate_rewrite_rules', array(&$this, 'RewriteExternalAds'));
			$options['flags']['rewriteHooked'] = true;
			update_option($advertwhirl_options_name, $options);
		}
		$wp_rewrite->flush_rules();
	}

	public function RewriteExternalAds() {
		global $wp_rewrite;
		global $advertwhirl_options_name;
		$options = maybe_unserialize(get_option($advertwhirl_options_name));

		$prefix = $options['settings']['adUrlPrefix'];
		$externalAdsPath = str_replace(get_site_url(), '', WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "", plugin_basename(__FILE__))) . '/ServeAd.php?campaign=';

		//RewriteRule ^ads/(.*) /wordpress/wp-content/plugins/advertwhirl//ServeAd.php?campaign=$1 [QSA,L]
		//RewriteRule ^(vpath)/ads/(.*) /wordpress/wp-content/plugins/advertwhirl//ServeAd.php?campaign=$2&virtualpath=$1 [QSA,L]
		if($options['settings']['externalAdsEnabled'] && !$options['flags']['deactivating']){
			$newRules = array(
				$prefix . '/(.*)' => $externalAdsPath . '$1'
			);
			// If Virtual Theme is installed add a rule for each vpath
			foreach(get_plugins() as $plugin => $data){
				if($plugin == 'virtual-theme/VirtualTheme.php'){
					require_once(WP_PLUGIN_DIR . "/" . $plugin);
					if(VirtualThemeActive()){
						$paths = VirtualTheme::GetVirtualPaths();
						foreach($paths as $path){
							if(strpos($path, '/') === 0)
								$path = substr($path, 1);
							$newRules['(' . $path . ')/' . $prefix . '/(.*)'] = $externalAdsPath . '$2&virtualpath=/$1';
						}
					}
				}
			}
			$wp_rewrite->non_wp_rules = $newRules + $wp_rewrite->non_wp_rules;
		}
	}

	private function GetTechnicalSpecs(){
		global $wp_version;
		$specs .= "Plugin Specs\n";
		$specs = "========================================\n";
		$specs .= "Plugin: $this->name\n";
		$specs .= "Plugin Version: $this->version\n";
		$specs .= "Plugin Directory: $this->pluginDir\n\n";


		$specs .= "Server Specs\n";
		$specs = "========================================\n";
		$specs .= "Server Address: " . $_SERVER['SERVER_ADDR'] . "\n";
		$specs .= "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
		$specs .= "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n\n";

		$specs .= "Wordpress Specs\n";
		$specs = "========================================\n";
		$specs .= "Blog Name: " . get_option('blogname') . "\n";
		$specs .= "Wordpress Version: $wp_version\n";
		$specs .= "Wordpress Siteurl: " . get_option("siteurl") . "\n";
		$specs .= "Wordpress Home: " . get_option("home") . "\n";
		$specs .= "\tActive Plugins\n";
		$specs .= "\t========================================\n";
		foreach(get_option('active_plugins') as $index => $plugin){
			if(strlen($plugin)>0)
				$specs .= "\t$plugin\n";
		}

		/** PHP Info - @TODO: outputs html and needs to be parsed */
		//ob_start();
		//phpinfo(INFO_GENERAL or INFO_CONFIGURATION or INFO_MODULES);
		//$specs .= ob_get_contents();
		//ob_get_clean();

		return $specs;
	}

	function HandleShortcode_advertwhirl($atts, $content = null) {
		extract(shortcode_atts(array('campaign' => null), $atts));
		if(isset($campaign)){
			echo advertwhirl_get_ad($campaign);
		}
	}

	// Add the stats widget to the dashboard
	public function AddDashboardWidgets() {
		// Add statistics widget to dashboard
		if(isset($this->options['settings']['displayDashboard']) && $this->options['settings']['displayDashboard'])
			wp_add_dashboard_widget("AdvertwhirlStatsWidget", "Advertwhirl", array(&$this, "DisplayAdvertwhirlStatsWidget"));
	} 

	public function DisplayAdsenseStats(){
		$periods = array();
		foreach($this->adsense->GetReportPeriods() as $key => $value){
			if(isset($this->options['settings']['adsense']['period'][$key])){
				$periods[$key] = $this->options['settings']['adsense']['period'][$key];
			}else{
				$periods[$key] = false;
			}
		}

		$types = array();
		foreach($this->adsense->GetReportTypes() as $key => $value){
			if(isset($this->options['settings']['adsense']['type'][$key])){
				$types[$key] = $this->options['settings']['adsense']['type'][$key];
			}else{
				$types[$key] = false;
			}
		}

		$categories = array();
		foreach($this->adsense->GetAdCategories() as $key => $value){
			if(isset($this->options['settings']['adsense']['category'][$key])){
				$categories[$key] = $this->options['settings']['adsense']['category'][$key];
			}else{
				$categories[$key] = false;
			}
		}

		$this->adsense->LoadStats();
		$this->adsense->GetStatTables($periods, $types, $categories);
	}

	public function DisplayAdvertwhirlStatsWidget(){
		echo '<div class="wrap">';
			if($this->options['settings']['adsense']['dashboard'])
				$this->DisplayAdsenseStats();
		echo '</div>';
	}

    public function DisplayAdminMenu(){
		$this->pluginPage = add_options_page('Advertwhirl Options', 'Advertwhirl', 'manage_options', __FILE__, array(&$this, 'CreateAdminPanel'));

		add_action('admin_print_styles-' . $this->pluginPage, array(&$this, 'LinkAdminStylesheet'));
		add_action('admin_print_scripts-' . $this->pluginPage, array(&$this, 'LinkAdminScripts'));
    }

	function LinkAdminStylesheet() {
		/** Register plugin admin stylesheet*/
		wp_register_style('advertwhirl-plugin-admin-css', plugins_url('css/styles.css', __FILE__), false, $this->version);

		/** Enqueue plugin admin stylesheet*/
		wp_enqueue_style('advertwhirl-plugin-admin-css');
	}

	function LinkAdminScripts() {
		/** Register plugin admin javascript*/
		wp_register_script('advertwhirl-plugin-admin-scripts', plugins_url('advertwhirl.js', __FILE__), false, $this->version);

		/** Enqueue plugin admin stylesheet*/
		wp_enqueue_script('advertwhirl-plugin-admin-scripts');
	}

	function DisplayMobileSentienceAd(){
		$ad = file_get_contents($this->adserv);
		if($ad !== false){
			echo '<center>' . $ad . '</center>';
		}
	}

	public function DisplayAdminBanner($activeTab){
		echo '<h2>' . $this->PromoteText . '</h2>';
	}

	function ShiftArrayElement(&$a, $index, $delta){
		if(isset($a) && is_array($a)){
			$nindex = $index + $delta;
			if($nindex < 0){
				$nindex = 0;
			}else if($nindex >= sizeof($a)){
				$nindex = sizeof($a) - 1;
			}

			$tmp = $a[$index];
			for($i = $index; $i != $nindex; $i += $delta){
				$a[$i] = $a[$i + $delta];
			}
			$a[$nindex] = $tmp;
		}


	}
	
	function ExtractPostArray($map, $postfix = ""){
		$sets = null;
		$i = 0;
		$found = true;
		while($found){
			$set = null;
			$found = false;
			foreach($map as $dkey => $pkey){
				if(is_array($pkey)){
					$post = $postfix . $i . '-';
					$sub = $this->ExtractPostArray($pkey, $post);
					if(isset($sub)){
						if(!isset($set)){
							$set = array();
						}
						$set[$dkey] = $sub;
						$found = true;
					}
				}else if(isset($_POST[$pkey . $postfix . $i])){
					if(!isset($set)){
						$set = array();
					}
					$set[$dkey] = $_POST[$pkey . $postfix . $i];
					$found = true;
				}
			}
			if(isset($set)){
				if(!isset($sets)){
					$sets = array();
				}
				$sets[] = $set;
			}
			$i++;
		}

		return $sets;
	}

	function CalculateWeights($campaign, &$allocations){
		// Setup weight stats
		$success = true;
		$totalWeights = 0;
		$totalPercents = 0;

		foreach($allocations as $index => $allocation){
			$percents = array();
			foreach($allocation['ads'] as $i => $ad){
				$weight = strlen($ad['weight']) > 0?$ad['weight']:0;
				$weight = str_replace(' ', '', $weight);
				if(is_numeric($weight)){ /* Weight */
					$w = intVal($weight);
					$totalWeights += $w;
					if($w < 0){
						$_POST['aladweighterror-' . $index . '-' . $i] = "*Allocations can't have a negative weight";
						$success = false;
					}else if(isset($ad['percent-weight'])){
						unset($ad['percent-weight']);
					}
				}else if (strpos($weight, '%') !== false){ /* Percent */
					$weight = str_replace('%', '', $weight);
					$percents["$i"] = floatVal($weight)/100;
					if($percents["$i"] < 0){
						$_POST['aladweighterror-' . $index . '-' . $i] = "*Allocations can't have a negative weight";
						$success = false;
					}
					$totalPercents += $percents["$i"];
				}else{
					// set error code
					$_POST['aladweighterror-' . $index . '-' . $i] = "*Invalid weight value";
					$success = false;
				}
			}
			if($totalPercents > 1 || ($totalPercents == 1 && $totalWeights > 0)){
				$_POST['aladweighterror-' . $index] = "*Ad slots over-allocated";
				$success = false;
			}else if($totalPercents < 1 && $totalWeights == 0){
				$_POST['aladweighterror-' . $index] = "*Ad slots under-allocated, percentage only allocations must total 100%";
				$success = false;
			}

			if(!$success)
				return $success;

			// Calculate percent weights
			$weightWithPercents = $totalWeights == 0 && $totalPercents > 0?1000:$totalWeights/(1 - $totalPercents);
			$percentWeightsOnly = $totalWeights == 0 && $totalPercents > 0?true:false;
			if($percentWeightsOnly){
				require_once('libs/Math.php');
				if(count($percents) == 1){
						$allocation['ads'][$i]['percent-weight'] = intval($w);
				}else{
					$values = array();
					foreach($percents as $i => $percent){
						$w = $weightWithPercents * $percent;
						$values[] = intval($w);
					}
					$gcf = get_gcf($values);
					foreach($values as $i => $value){
						$allocation['ads'][$i]['percent-weight'] =  $gcf !== false?$value / $gcf:$value;
					}
				}
			} else{
				foreach($percents as $i => $percent){
					$w = $weightWithPercents * $percent;
					$allocation['ads'][$i]['percent-weight'] = ceil($w);
				}
			}
		}

		$stats = maybe_unserialize(get_option($this->stats_name));

		if(!is_array($stats))
			$stats = array();

		unset($stats['stats']['adcampaigns'][$campaign]['allocations']);
		foreach($allocations as $index => $allocation){
			$stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'] = array();
			foreach($allocation['ads'] as $i => $ad){
				$stats['stats']['adcampaigns'][$campaign]['allocations'][$index]['sourceweights'][] = 0;
			}
		}

		update_option($this->stats_name, $stats);

		return $success;
	}

	function CampaignListTab_update($tab, $action){
		// Check for allocation buttons
		$postAllocations = $this->ExtractPostArray($this->AllocationMap);
		if(isset($postAllocations) && sizeof($postAllocations) > 0){
			$edit = false;
			$count = sizeof($postAllocations);
			for($i = 0; $i < $count; $i++){
				if(isset($_POST['aldown-' . $i])){
					$this->ShiftArrayElement($postAllocations, $i, 1);
					$edit = true;
				}else if(isset($_POST['alup-' . $i])){
					$this->ShiftArrayElement($postAllocations, $i, -1);
					$edit = true;
				}else if(isset($_POST['alremove-' . $i])){
					unset($postAllocations[$i]);
					$edit = true;
				}else if(isset($_POST['add-allocation-source-' . $i])){
					$postAllocations[$i]['ads'][] = $this->GetDefaultAllocationAd();
					$edit = true;
				}else if(isset($_POST['add-allocation-ruleset-' . $i])){
					$postAllocations[$i]['rulesets'][] = array();
					$postAllocations[$i]['rulesets'][0]['id'] = $i . '-' . sizeof($postAllocations[$i]['rulesets']);
					$edit = true;
				}
				$adcount = sizeof($postAllocations[$i]['ads']);
				for($j = 0; $j < $adcount; $j++){
					if(isset($_POST['aladremove-' . $i . '-' . $j])){
						unset($postAllocations[$i]['ads'][$j]);
						$edit = true;
					}
				}
				for($j = 0; $j < sizeof($postAllocations[$i]['rulesets']); $j++){
					if(isset($_POST['add-allocation-rule-' . $i . '-' . $j])){
						$postAllocations[$i]['rulesets'][$j]['rules'][] = $this->GetDefaultAllocationRule($_POST['new-rule-type-' . $i . '-' . $j]);
						$edit = true;
					}
					$rulecount = sizeof($postAllocations[$i]['rulesets'][$j]['rules']);
					for($k = 0; $k < $rulecount; $k++){
						if(isset($_POST['alruleremove-' . $i . '-' . $j . '-' . $k])){
							unset($postAllocations[$i]['rulesets'][$j]['rules'][$k]);
							$edit = true;
						}
					}
				}
			}

			if($edit){
				if(isset($_POST['current-action'])){
					$action = $_POST['current-action'];
				}
				if(isset($_POST['add-set']) && $_POST['add-set']){
					$_POST['add'] = true;;
				}
				if(isset($_POST['edit-set']) && $_POST['edit-set']){
					$_POST['edit'] = true;;
				}
				return $this->CampaignListTab_edit($tab, $action, $postAllocations);
			}
		}

		if(isset($_POST['current-action'])){
			$action = $_POST['current-action'];
		}
		if(isset($_POST['add-set']) && $_POST['add-set']){
			$_POST['add'] = true;;
		}
		if(isset($_POST['edit-set']) && $_POST['edit-set']){
			$_POST['edit'] = true;;
		}

		if(isset($_POST['add-allocation'])){
			$postAllocations[] = $this->GetDefaultAllocation();

			return $this->CampaignListTab_edit($tab, $action, $postAllocations);
		} else if(isset($_POST['add']) || isset($_POST['update'])){
			if(!isset($_POST['name']) || $_POST['name'] == ""){
				$_POST['name_error'] = "*Name is a required field";
				return $this->CampaignListTab_edit($tab, $action);
			}
			$name = $_POST['name'];

			if(!$this->CalculateWeights($name, $postAllocations)){
				return $this->CampaignListTab_edit($tab, $action);
			}

			if(isset($_POST['update']) && $_POST['origname'] != $name){
				$oname = $_POST['origname'];
				unset($this->options['adcampaigns'][$oname]);
			}

			$this->options['adcampaigns'][$name]['allocations'] = $postAllocations;
			$this->options['adcampaigns'][$name]['description'] = $_POST['description'];
			$this->options['adcampaigns'][$name]['adsize'] = $_POST['adsize'];

			foreach($this->PostTypes as $type => $label){
				$this->options['adcampaigns'][$name]['display'][$type]['header'] = isset($_POST['display-' . $type . '-header']);
				$this->options['adcampaigns'][$name]['display'][$type]['footer'] = isset($_POST['display-' . $type . '-footer']);
				$this->options['adcampaigns'][$name]['display'][$type]['before-content'] = isset($_POST['display-' . $type . '-before-content']);
				$this->options['adcampaigns'][$name]['display'][$type]['after-content'] = isset($_POST['display-' . $type . '-after-content']);
				$this->options['adcampaigns'][$name]['display'][$type]['in-content'] = isset($_POST['display-' . $type . '-in-content']);
				$this->options['adcampaigns'][$name]['display'][$type]['align'] = $_POST['display-' . $type . '-align'];
				$this->options['adcampaigns'][$name]['display'][$type]['maxads'] = $_POST['display-' . $type . '-maxads'];
				$this->options['adcampaigns'][$name]['display'][$type]['every'] = $_POST['display-' . $type . '-every'];
				$this->options['adcampaigns'][$name]['display'][$type]['offset'] = $_POST['display-' . $type . '-offset'];
			}

			/** Loop through all the campaigns and set the ad insertion flags */
			$this->options['settings']['display']['hook_content'] = false;
			$this->options['settings']['display']['hook_footer'] = false;
			$this->options['settings']['display']['hook_header'] = false;
			foreach($this->options['adcampaigns'] as $name => $campaign){
				foreach($this->PostTypes as $type => $label){
					$this->options['settings']['display']['hook_header'] = $campaign['display'][$type]['header'] || $this->options['settings']['display']['hook_header'];
					$this->options['settings']['display']['hook_footer'] = $campaign['display'][$type]['footer'] || $this->options['settings']['display']['hook_footer'];
					$this->options['settings']['display']['hook_content'] = $this->options['settings']['display']['hook_content'] || $campaign['display'][$type]['before-content'] || $campaign['display'][$type]['after-content'] || $campaign['display'][$type]['in-content'];
				}
				$this->options['adcampaigns'][$name]['display']['enabled'] = $campaign['display'][$type]['header'] || $campaign['display'][$type]['footer'] || $campaign['display'][$type]['before-content'] || $campaign['display'][$type]['after-content'] || $campaign['display'][$type]['in-content'];
			}

			update_option($this->options_name, $this->options);
			return $this->CampaignListTab_view($tab, $action);
		} else if(isset($_POST['delete'])){
			if(!isset($_POST['name']) || $_POST['name'] == ""){
				return $this->CampaignListTab_view($tab, $action);
			}
			$name = $_POST['name'];
			unset($this->options['adcampaigns'][$name]);
			update_option($this->options_name, $this->options);
			return $this->CampaignListTab_view($tab, $action);
		} else if(isset($_POST['cancel'])){
			return $this->CampaignListTab_view($tab, $action);
		}
	}

	function GetAllocationRuleForm_author($i, $j, $k, $rule){
		$authors = $this->db->GetAuthorsList();
		$content .= '
																			<select name="alruleauthor-' . $i . '-' . $j . '-' . $k . '" style="width:99%;" >';

		foreach($authors as $id => $author){
			$selected = $rule['author'] == $id?"selected":"";
			$content .= '
																				<option value="' . $id . '" ' . $selected . '>' . $author['lastname'] . ', ' . $author['firstname'] . '</option>';
		}
		$content .= '
																			</select>';
		return $content;
	}

	function GetAllocationRuleForm_posttype($i, $j, $k, $rule){
		$content .= '
																			<select name="alruleposttype-' . $i . '-' . $j . '-' . $k . '" style="width:99%;">';

		$post_types=get_post_types('','names'); 
		foreach ($post_types as $type ) {
			$selected = $rule['posttype'] == $type?"selected":"";
			$content .= '
																				<option value="' . $type . '" ' . $selected . '>' . $type . '</option>';
  		}
		$content .= '
																			</select>';
		return $content;
	}

	function GetAllocationRuleForm_getargument($i, $j, $k, $rule){
		$argumentOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
			'>' => 'is greater than',
			'<' => 'is less than',
			'>=' => 'is greater than or equal to',
			'<=' => 'is less than or equal to',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		$content .= '
																			<label for="alruleargumentname-' . $i . '-' . $j . '-' . $k . '">Argument</label><input type="text" name="alruleargumentname-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['argname'] . '" />';
		$content .= '
																			<select name="alruleargumentoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($argumentOperators as $value => $label) {
			$selected = $rule['argop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<input type="text" name="alruleargumentvalue-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['argvalue'] . '" />';

		return $content;
	}

	function GetAllocationRuleForm_cookie($i, $j, $k, $rule){
		$cookieOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
			'>' => 'is greater than',
			'<' => 'is less than',
			'>=' => 'is greater than or equal to',
			'<=' => 'is less than or equal to',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		$content .= '
																			<label for="alrulecookiename-' . $i . '-' . $j . '-' . $k . '">Cookie</label><input type="text" name="alrulecookiename-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['cookiename'] . '" />';
		$content .= '
																			<select name="alrulecookieoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($cookieOperators as $value => $label) {
			$selected = $rule['cookieop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<input type="text" name="alrulecookievalue-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['cookievalue'] . '" />';

		return $content;
	}

	function GetAllocationRuleForm_customfield($i, $j, $k, $rule){
		$customfieldOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
			'>' => 'is greater than',
			'<' => 'is less than',
			'>=' => 'is greater than or equal to',
			'<=' => 'is less than or equal to',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		$content .= '
																			<label for="alrulecustomfieldname-' . $i . '-' . $j . '-' . $k . '">Custom Field</label><input type="text" name="alrulecustomfieldname-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['customfieldname'] . '" />';
		$content .= '
																			<select name="alrulecustomfieldoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($customfieldOperators as $value => $label) {
			$selected = $rule['customfieldop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<input type="text" name="alrulecustomfieldvalue-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['customfieldvalue'] . '" />';

		return $content;
	}

	function GetAllocationRuleForm_remoteip($i, $j, $k, $rule){
		$remoteipOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		$content .= '
																			<label for="alruleremoteipname-' . $i . '-' . $j . '-' . $k . '">Visitors IP Address</label>';
		$content .= '
																			<select name="alruleremoteipoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($remoteipOperators as $value => $label) {
			$selected = $rule['remoteipop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<input type="text" name="alruleremoteipvalue-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['remoteipvalue'] . '" />';

		return $content;
	}

	function GetAllocationRuleForm_remotehostname($i, $j, $k, $rule){
		$remotehostnameOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		$content .= '
																			<label for="alruleremotehostname-' . $i . '-' . $j . '-' . $k . '">Visitors Hostname</label>';
		$content .= '
																			<select name="alruleremotehostnameoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($remotehostnameOperators as $value => $label) {
			$selected = $rule['remotehostnameop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<input type="text" name="alruleremotehostnamevalue-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['remotehostnamevalue'] . '" />';

		return $content;
	}

	function GetAllocationRuleForm_referrer($i, $j, $k, $rule){
		$referrerOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		$content .= '
																			<label for="alrulereferrer-' . $i . '-' . $j . '-' . $k . '">Referring Site</label>';
		$content .= '
																			<select name="alrulereferreroperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($referrerOperators as $value => $label) {
			$selected = $rule['referrerop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<input type="text" name="alrulereferrervalue-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['referrervalue'] . '" />';

		return $content;
	}

	function GetAllocationRuleForm_adsize($i, $j, $k, $rule){
		$adsizeOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
		);

		$content .= '
																			<label for="alruleadsize-' . $i . '-' . $j . '-' . $k . '">Ad Unit Size</label>';
		$content .= '
																			<select name="alruleadsizeoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($adsizeOperators as $value => $label) {
			$selected = $rule['adsizeop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<select name="alruleadsizevalue-' . $i . '-' . $j . '-' . $k . '" >';
		foreach ($this->AdUnitSizes as $label => $value) {
			$selected = $rule['adsizevalue'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}
		$content .= '
																			</select>';

		return $content;
	}

	// 'geoip' => 'alrulegeoip-'

	function GetAllocationRuleForm_cvar($i, $j, $k, $rule){
		$argumentOperators = array (
			'==' => 'is equal to',
			'!=' => 'is not equal to',
			'>' => 'is greater than',
			'<' => 'is less than',
			'>=' => 'is greater than or equal to',
			'<=' => 'is less than or equal to',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		if($this->virtualThemeInstalled){
			require_once(WP_PLUGIN_DIR . "/virtual-theme/VirtualTheme.php");
			$vars = VirtualTheme::GetCustomVariables();
			$content .= '
																			<select name="alrulecvarname-' . $i . '-' . $j . '-' . $k . '" >';
			foreach($vars as $var){
				$selected = $rule['cvarname'] == $var?"selected":"";
				$content .= '
																				<option value="' . $var . '" ' . $selected . '>' . $var . '</option>';
			}
			$content .= '
																			</select>
																			<select name="alrulecvaroperator-' . $i . '-' . $j . '-' . $k . '" >';

			foreach ($argumentOperators as $op => $label) {
				$selected = $rule['cvarop'] == $op?"selected":"";
				$content .= '
																				<option value="' . $op . '" ' . $selected . '>' . $label . '</option>';
	  		}

			$content .= '
																			</select>
																			<input type="text"  name="alrulecvarvalue-' . $i . '-' . $j . '-' . $k . '" value="' . $rule['cvarvalue'] . '" />';
			return $content;
		}
		return "";
	}

	function GetAllocationRuleForm_geoip($i, $j, $k, $rule){
		//http://api.ipinfodb.com/v3/ip-city/?key=0ff0aab59531b725b5da5b874f6acadc4d0ea0b667fe19d94150b9520451e120&ip=71.237.2.63
		//OK;;71.237.2.63;US;UNITED STATES;COLORADO;DENVER;80002;39.7761;-105.015;-07:00
		$argumentOperators = array (
			'==i' => 'is',
			'!=i' => 'is not',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);
		$locations = array(
			'countryName' => 'Country',
			'regionName' => 'State/Province',
			'cityName' => 'City',
			'zipCode' => 'Postal Code'
		);

		$content .= '
																			<select name="alrulegeolocation-' . $i . '-' . $j . '-' . $k . '" >';
		foreach ($locations as $value => $label) {
			$selected = $rule['geolocation'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<select name="alrulegeoop-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($argumentOperators as $value => $label) {
			$selected = $rule['geoop'] == $value?"selected":"";
			$content .= '
																				<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<input type="text" name="alrulegeovalue-' . $i . '-' . $j . '-' . $k . '" value="'. $rule['geovalue'] . '" />';

		return $content;
	}

	function GetAllocationRuleForm_vpath($i, $j, $k, $rule){
		$argumentOperators = array (
			'==' => 'is',
			'!=' => 'is not',
			'~=' => 'matches regular expression',
			'!~' => 'does not match regular expression',
		);

		if($this->virtualThemeInstalled){
			require_once(WP_PLUGIN_DIR . "/virtual-theme/VirtualTheme.php");
			$content .= '
																			<label for="alrulevpathoperator-' . $i . '-' . $j . '-' . $k . '">Virtual Path</label>
																			<select name="alrulevpathoperator-' . $i . '-' . $j . '-' . $k . '" >';

			foreach ($argumentOperators as $op => $label) {
				$selected = $rule['vpathop'] == $op?"selected":"";
				$content .= '
																				<option value="' . $op . '" ' . $selected . '>' . $label . '</option>';
	  		}

			$content .= '
																			</select>
																			<select name="alrulevpath-' . $i . '-' . $j . '-' . $k . '" >';
			$paths = VirtualTheme::GetVirtualPaths();
			foreach($paths as $path){
				$selected = $rule['vpath'] == $path?"selected":"";
				$content .= '
																				<option value="' . $path . '" ' . $selected . '>' . $path . '</option>';
			}
			$content .= '
																			</select>';
			return $content;
		}
		return "";
	}

	function GetAllocationRuleForm_tag($i, $j, $k, $rule){
		$argumentOperators = array (
			'isa' => 'is a',
			'isnota' => 'is not a',
		);

		$content .= '
																			<label for="alruletagoperator-' . $i . '-' . $j . '-' . $k . '">Post/Page</label>
																			<select name="alruletagoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($argumentOperators as $op => $label) {
			$selected = $rule['tagop'] == $op?"selected":"";
			$content .= '
																				<option value="' . $op . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>
																			<select name="alruletagid-' . $i . '-' . $j . '-' . $k . '" >';
		$tags = get_tags(array('hide_empty' => 0));
		foreach($tags as $tag){
			$selected = $rule['tagid'] == $tag->term_id?"selected":"";
			$content .= '
																				<option value="' . $tag->term_id . '" ' . $selected . '>' . $tag->name . '</option>';
		}
		$content .= '
																			</select>';

		return $content;
	}

	function GetAllocationRuleForm_category($i, $j, $k, $rule){
		$argumentOperators = array (
			'isa' => 'is a',
			'isnota' => 'is not a',
		);

		$content .= '
																			<label for="alrulecategoryoperator-' . $i . '-' . $j . '-' . $k . '">Post/Page</label>
																			<select name="alrulecategoryoperator-' . $i . '-' . $j . '-' . $k . '" >';

		foreach ($argumentOperators as $op => $label) {
			$selected = $rule['catop'] == $op?"selected":"";
			$content .= '
																				<option value="' . $op . '" ' . $selected . '>' . $label . '</option>';
  		}

		$content .= '
																			</select>';

		$selected = isset($rule['catid'])?$rule['catid']:0;
		$content .= wp_dropdown_categories(array('echo' => 0, 'hide_empty' => 0, 'name' => 'alrulecategoryid-' . $i . '-' . $j . '-' . $k, 'hierarchical' => true, 'selected' => $selected));

		return $content;
	}

	function GetAllocationRuleForm($i, $j, $k, $rule){
		$func = "GetAllocationRuleForm_" . $rule['type'];
		if(method_exists($this, $func)){
			$content = '
															<tr class="gradient2">
																<th scope="col" nowrap>' . $this->RuleTypes[$rule['type']] . '</th>
																	<td valign="middle" style="width:1%;" nowrap>
																		<input type="hidden"  name="alruletype-' . $i . '-' . $j . '-' . $k . '" value="' . $rule['type'] . '"/>';

			$content .= call_user_func(array(&$this, $func), $i, $j, $k, $rule);

			$content .= '
																		</td>
																		<td align="left" style="width:1%" nowrap>
																			<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $i . '\')" type="submit" class="button" name="alruleremove-' . $i . '-' . $j . '-' . $k . '" value="Remove" />
																		</td>
																	</tr>';
		}

		return $content;
	}

	function GetAllocationAdForm($i, $index, $ad, $displayRemove){
		if($displayRemove){
			$labelWidth = "10%";
			$adWidth = "40%";
			$weightWidth = "20%";
		}else{
			$labelWidth = "10%";
			$adWidth = "50%";
			$weightWidth = "30%";
		}
		$content .= ' 
							<tr class="gradient4">
								<td colspan="2">
									<table id="aladform-' . $i . '-' . $index . '" width="100%">
										<tr class="gradient2">
											<th scope="col" style="width:' . $labelWidth . ';" nowrap="nowrap">Ad Source</th>
											<td valign="middle" style="width:' . $adWidth . ';">
												<select name="alad-' . $i . '-' . $index . '" style="width:99%;">';
		if(isset($this->options['adsources'])){
			foreach($this->options['adsources'] as $name => $source){
				if($name == $ad['advertisement']){
					$content .= '
													<option value="' . $name .'" selected>local ad - ' . $name . '</option>' . "\n";
				}else{
					$content .= '
													<option value="' . $name .'" >local ad - ' . $name . '</option> . "\n"';
				}
			}
		}

		if(isset($this->options['settings']['adsense']['username']) && isset($this->options['settings']['adsense']['password'])){
				$this->adsense->LoadAdUnits();
				$adUnits = $this->adsense->GetAdUnits();
				if(isset($adUnits) && sizeof($adUnits) > 0){
					foreach($adUnits as $id => $unit){
						if ('adsense-' . $id == $ad['advertisement']){
							$content .= '
													<option value="adsense-' . $id .'" selected>adsense ad - ' . $unit['name'] . ' - ' .  $unit['size'] . ' ' . $unit['format'] . '</option>';
						}else{
							$content .= '
													<option value="adsense-' . $id .'" >adsense ad - ' . $unit['name'] . ' - ' .  $unit['size'] . ' ' . $unit['format'] . '</option>';
						}
					}
				}
		}


		$origweight = isset($_POST['aladoriginalweight-' . $i . '-' . $index])?$_POST['aladoriginalweight-' . $i . '-' . $index]:$ad['weight'];
		$percentweight = isset($_POST['aladoriginalweight-' . $i . '-' . $index])?$_POST['aladoriginalweight-' . $i . '-' . $index]:$ad['weight'];

		$content .= '
												</select>
											</td>
											<th scope="col" style="width:' . $labelWidth . ';">Weight</th>
											<td valign="middle" style="width:' . $weightWidth . ';">
												<input name="aladoriginalweight-' . $i . "-" . $index . '" type="hidden" value="' . $origweight . '" style="width:99%;"/>
												<strong> ' . $_POST['aladweighterror-' . $i . '-' . $index] . '</strong><input name="aladweight-' . $i . "-" . $index . '" type="text" value="' . $ad['weight'] . '" style="width:99%;"/>';
	if(isset($ad['percent-weight'])){
		$content .= '
												<input name="aladpercentweight-' . $i . "-" . $index . '" type="hidden" value="' . $ad['percent-weight'] . '" />';
	}

	$content .= '
											</td>';
			if($displayRemove){
				$content .= '
											<td valign="middle" style="width:20%;" align="center">
												<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $i . '\')" type="submit" class="button" name="aladremove-' . $i . '-' . $index . '" value="Remove" />
											</td>';
			}
			$content .= '
										</tr>
									</table>
								</td>
							</tr>';
		return $content;
	}

	function GetAllocationForm($i, $allocation, $totalAllocations){
		// Calculate what buttons if any to display
		$displayButtonRow = $totalAllocations > 1;
		$displayMoveUp = $i > 0;
		$displayMoveDown = $i < $totalAllocations - 1;
		$adsupdated = isset($_POST['adsupdated-' . $i])?$_POST['adsupdated-' . $i]:false; /** @todo Verify this can be removed, only refernced here and belore */

		$id = $i + 1;

		$caption = isset($allocation['description']) && strlen($allocation['description']) > 0?"{$allocation['description']} - ":"";
		$caption .= "Allocation $id";
		$chevron = $allocation['displayed'] == 'none'?'expand.gif':'collapse.gif';

		$content = '
<a name="allocation-' . $i .'" ></a>
<div class="collapsiblepanelcaption" name="allocation-' . $i . '_caption" style="height:20px; cursor: pointer;" onclick="togglePanelAnimatedStatus(\'allocation-' . $i . '\', 50, 50, \'alpaneldisplayed-' . $i . '\')">
	<div style="float:left; position:relative; width:80%; left:+10%; text-align:center;">' . $caption . '</div>
	<div style="float: right; vertical-align: middle; ">
		<img name="allocation-' . $i . '_chevron" src="' . $this->imagePath . '/' . $chevron . '" width="13" height="14" border="0" alt="Show/Hide" title="Show/Hide" />
	</div>
</div>
<div class="collapsiblepanelcontent" name="allocation-' . $i . '_content" style="padding:0px; display:' . $allocation['displayed'] . ';">
	<input type="hidden" name="alpaneldisplayed-' . $i .'" value="' . $allocation['displayed'] . '" />
	<table width="100%" id="alform-' . $i . '">
		<tbody>
			<tr class="gradient3">
				<th><div>' . $id . '</div></th>
				<td style="width:99%;" colspan="2">
					<table width="100%" id="alform-' . $i . '">
						<tbody>
							<tr class="gradient2">
								<th scope="col" style="width:1%">Description</th>
								<td valign="middle">
									<input name="aldescription-' . $i . '" type="text" value="' . $allocation['description'] . '" style="width:99%;"/>
									<input name="adsupdated-' . $i . '" type="hidden" value="' . $adsupdated . '"/>
								</td>
							</tr>
							<!--<tr class="gradient2">
								<td colspan="2"></td>
							</tr>-->
							<tr class="gradient2">
								<th style="text-align:center;" colspan="2">Ads</th>
							</tr>';

	if(isset($_POST['aladweighterror-' . $i])){
			$content .= '
							<tr class="gradient2">
								<td colspan="2"><strong>' . $_POST['aladweighterror-' . $i] . '</strong></td>
							</tr>
							<a name="add-allocation-source-' . $i . '"></a>';
		}

		if(isset($allocation['ads'])){
			foreach($allocation['ads'] as $index => $ad){
				$content .= $this->GetAllocationAdForm($i, $index, $ad, sizeof($allocation['ads']) > 1);
			}
		}

		$content .= '
							<tr class="gradient2">
								<td align="center" colspan="2">
									<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $i . '\')" type="submit" class="button" name="add-allocation-source-' . $i . '" value="Add Advertisement Source" />
								</td>
							</tr>
							<!--<tr class="gradient2">
								<td colspan="2"></td>
							</tr>-->
							<tr class="gradient2">
								<th style="text-align:center;" colspan="2">Rules</th>
							</tr>';

		if(isset($allocation['rulesets'])){
			$content .= '
							<tr>
								<td colspan="2">
									<table id="alrulesets-' . $i . '" width="100%">
										<tbody>';
			foreach($allocation['rulesets'] as $id => $set){
				$content .= '
											<tr class="gradient4">
												<th><div> ' . ($id + 1) . '</div></th>
												<td style="width:99%;" colspan="2">
													<input type="hidden" value="' . $set['id'] . '" name="alrulesetid-' . $i . '-' . $id .'"/>
													<table id="alrulesets-' . $i . '-' . $id .'" width="100%">
														<tbody>';

				if(isset($set['rules'])){
					foreach($set['rules'] as $j => $rule){
						$content .= $this->GetAllocationRuleForm($i, $id, $j, $rule);
					}
				}

				$content .= '
																<tr class="gradient2">
																	<td align="center" colspan="3">
																		<select name="new-rule-type-' . $i . '-' . $id . '">';

				foreach($this->RuleTypes as $value => $name){
					$content .= '
																			<option value="' . $value . '">' . $name . '</option>';
				}

			$content .= '
																		</select>
																		<a name="add-allocation-rule-' . $i . '"></a>
																			<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $i . '\')" type="submit" class="button" name="add-allocation-rule-' . $i . '-' . $id . '" value="Add Rule" />
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>';
			}

			$content .= '
											</tbody>
										</table>
									</td>
								</tr>';
		}

		$content .= '
								<tr class="gradient2">
									<td align="center" colspan="2">
										<a name="add-allocation-ruleset-' . $i . '"></a>
										<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $i . '\')" type="submit" class="button" name="add-allocation-ruleset-' . $i . '" value="Add Rule Set" />
									</td>
								</tr>';
		if($displayButtonRow){
			$content .= '
								<tr class="gradient2">
									<td align="center" colspan="2">';
			if($displayMoveDown){
				$j = $i + 1;
				$content .= '										
										<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $j . '\')" type="submit" button class="button" name="aldown-' . $i . '" value="Move Down" />';
			}

			if($displayMoveUp){
				$j = $i - 1;
				$content .= '
										<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $j . '\')" type="submit" class="button" name="alup-' . $i . '" value="Move Up" />';
			}

			$j = $i > 0?$i - 1:0;
			$content .= '
										<input onClick="SetActionAnchor(document.campaignform, \'allocation-' . $j . '\')" type="submit" class="button" name="alremove-' . $i . '" value="Remove" />
									</td>
								</tr>';
		}

		$content .= '
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>';
		return $content;
	}

	function GetDefaultAllocationRule($type = null){
		$rule = array();
		$rule['type'] = $type;
		$rule['author'] = "";
		$rule['posttype'] = "";
		$rule['tag'] = "";
		$rule['category'] = "";
		$rule['post'] = "";
		$rule['page'] = "";
		return $rule;
	}

	function GetDefaultAllocationAd(){
		$ad = array();
		$ad['advertisement'] = "";
		$ad['weight'] = "1";
		return $ad;
	}

	function GetDefaultAllocation(){
		$allocation = array();
		$allocation['name'] = "";
		$allocation['description'] = "";
		$allocation['ads'][] = $this->GetDefaultAllocationAd();
		return $allocation;
	}

	function CampaignListTab_edit($tab, $action, $postAllocations = null){
		$baseurl = $this->pageURL . '&tab=' . $tab . '&action=';
		$cancelurl = $this->pageURL . '&tab=' . $tab . '&action=view';
		//verify data
		$add = isset($_POST['add']);
		$addAllocation = isset($_POST['add-allocation']);
		$edit = isset($_POST['edit']);
		$delete = isset($_POST['delete']);
		if($add){
			$title = "Create new ad campaign";
			$submitName = "add";
			$submitValue = "Save Campaign";
			$origname = "";
			
			$name = isset($_POST['name'])?$_POST['name']:"";
			$description = isset($_POST['description'])?$_POST['description']:"";
			$adsize = isset($_POST['adsize'])?$_POST['adsize']:"";

			foreach($this->PostTypes as $typeName => $typeLabel){
				// Auto ad placement flags
				$displayHeader[$typeName] = isset($_POST['display-' . $typeName . '-header']) && $_POST['display-' . $typeName . '-header']?'checked':'';
				$displayFooter[$typeName] = isset($_POST['display-' . $typeName . '-footer']) && $_POST['display-' . $typeName . '-footer']?'checked':'';
				$displayBeforeContent[$typeName] = isset($_POST['display-' . $typeName . '-before-content']) && $_POST['display-' . $typeName . '-before-content']?'checked':'';
				$displayAfterContent[$typeName] = isset($_POST['display-' . $typeName . '-after-content']) && $_POST['display-' . $typeName . '-after-content']?'checked':'';
				$displayInContent[$typeName] = isset($_POST['display-' . $typeName . '-in-content']) && $_POST['display-' . $typeName . '-in-content']?'checked':'';
				$displayAlign[$typeName] = isset($_POST['display-' . $typeName . '-align'])?$_POST['display-' . $typeName . '-align']:0;
				$displayMaxads[$typeName] = isset($_POST['display-' . $typeName . '-maxads'])?$_POST['display-' . $typeName . '-maxads']:2;
				$displayEvery[$typeName] = isset($_POST['display-' . $typeName . '-every'])?$_POST['display-' . $typeName . '-every']:4;
				$displayOffset[$typeName] = isset($_POST['display-' . $typeName . '-offset'])?$_POST['display-' . $typeName . '-offset']:1;
			}

		}else if($edit){
			$title = "Edit ad campaign";
			$name = $_POST['name'];
			$submitName = "update";
			$submitValue = "Save Campaign";

			if(isset($_POST['origname'])){
				$origname = $_POST['origname'];
			}else{
				$origname = $name;
			}

			$description = isset($_POST['description'])?$_POST['description']:$this->options['adcampaigns'][$origname]['description'];
			$adsize = isset($_POST['adsize'])?$_POST['adsize']:$this->options['adcampaigns'][$origname]['adsize'];

			foreach($this->PostTypes as $typeName => $typeLabel){
				// Auto ad placement flags
				$displayHeader[$typeName] = isset($_POST['display-' . $typeName . '-header'])?$_POST['display-' . $typeName . '-header']?'checked':'':$this->options['adcampaigns'][$origname]['display'][$typeName]['header']?'checked':'';
				$displayFooter[$typeName] = isset($_POST['display-' . $typeName . '-footer'])?$_POST['display-' . $typeName . '-footer']?'checked':'':$this->options['adcampaigns'][$origname]['display'][$typeName]['footer']?'checked':'';
				$displayBeforeContent[$typeName] = isset($_POST['display-' . $typeName . '-before-content'])?$_POST['display-' . $typeName . '-before-content']?'checked':'':$this->options['adcampaigns'][$origname]['display'][$typeName]['before-content']?'checked':'';
				$displayAfterContent[$typeName] = isset($_POST['display-' . $typeName . '-after-content'])?$_POST['display-' . $typeName . '-after-content']?'checked':'':$this->options['adcampaigns'][$origname]['display'][$typeName]['after-content']?'checked':'';
				$displayInContent[$typeName] = isset($_POST['display-' . $typeName . '-in-content'])?$_POST['display-' . $typeName . '-in-content']?'checked':'':$this->options['adcampaigns'][$origname]['display'][$typeName]['in-content']?'checked':'';
				$displayAlign[$typeName] = isset($_POST['display-' . $typeName . '-align'])?$_POST['display-' . $typeName . '-align']:$this->options['adcampaigns'][$origname]['display'][$typeName]['align'];
				$displayMaxads[$typeName] = isset($_POST['display-' . $typeName . '-maxads'])?$_POST['display-' . $typeName . '-maxads']:$this->options['adcampaigns'][$origname]['display'][$typeName]['maxads'];
				$displayEvery[$typeName] = isset($_POST['display-' . $typeName . '-every'])?$_POST['display-' . $typeName . '-every']:$this->options['adcampaigns'][$origname]['display'][$typeName]['every'];
				$displayOffset[$typeName] = isset($_POST['display-' . $typeName . '-offset'])?$_POST['display-' . $typeName . '-offset']:$this->options['adcampaigns'][$origname]['display'][$typeName]['offset'];
			}

		} else if($delete){
			return $this->CampaignListTab_update($tab, $action);
		}

		echo
'<div class="wrap">
	<br class="clear" />
	<form name="campaignform" action="' . $baseurl . 'update" method="post">';
	echo '
	<input name="current-action" type="hidden" value="' . $action . '"/>
	<input name="add-set" type="hidden" value="' . $add . '"/>
	<input name="edit-set" type="hidden" value="' . $edit . '"/>
	<input name="origname" type="hidden" value="' . $origname . '"/>
	<table class="widefat">
		<thead>
			<tr class="gradient">
				<th colspan="3" scope="col" style="text-align:center;">' . $title . '</th>
			</tr>
		</thead>
		<tbody id="campaign-detail" >
			<tr class="gradient2">
				<th scope="col" style="width:1%" colspan="2">Name</th>
				<td valign="middle"><strong>' . $_POST['name_error'] . '</strong><input name="name" type="text" value="' . $name . '" style="width:99%;"/></td>
			</tr>
			<tr class="gradient2">
				<th scope="col" style="width:1%" colspan="2">Description</th>
				<td valign="middle"><input name="description" type="text" value="' . $description . '" style="width:99%;"/></td>
			</tr>
			<tr class="gradient2">
				<th scope="col" style="width:1%" colspan="2">Ad Unit Size</th>
				<td valign="middle">
					<select name="adsize">';
		foreach($this->AdUnitSizes as $unit => $value){
			$selected = $adsize == $value?"selected":"";
			echo '
						<option value="' . $value . '" ' . $selected . '>' . $unit . '</option>';
		}
		echo '
					</select>
				</td>
			</tr>';

		foreach($this->PostTypes as $typeName => $typeLabel){
			$disabled = $displayInContent[$typeName]?"":"DISABLED";
			echo '
			<tr class="gradient2">
				<th style="text-align:center;" colspan="3">' . $typeLabel . '</th>
			</tr>
			<tr class="gradient2">
				<td valign="middle" nowrap colspan="3">
					<center>
						<input type="checkbox" name="display-' . $typeName . '-header" value="1" ' . $displayHeader[$typeName] . '/><label for="display-' . $typeName . '-header">Place in header</label>
						<input type="checkbox" name="display-' . $typeName . '-footer" value="1" ' . $displayFooter[$typeName] . '/><label for="display-' . $typeName . '-footer">Place in footer</label>
						<input type="checkbox" name="display-' . $typeName . '-before-content" value="1" ' . $displayBeforeContent[$typeName] . '/><label for="display-' . $typeName . '-before-content">Place before content</label>
						<input type="checkbox" name="display-' . $typeName . '-after-content" value="1" ' . $displayAfterContent[$typeName] . '/><label for="display-' . $typeName . '-after-content">Place after content</label>
					</center>
				</td>
			</tr>
			<tr class="gradient2">
				<td valign="middle" nowrap colspan="3">
					<center>
						<input type="checkbox" name="display-' . $typeName . '-in-content" value="1" ' . $displayInContent[$typeName] . ' onClick="enableContentPlacementFields(\'' . $typeName . '\');" /><label for="display-' . $typeName . '-in-content">Place in content</label>
						<label for="display-' . $typeName . '-maxads"> at most</label><select name="display-' . $typeName . '-maxads" ' . $disabled .'>';
			for($c = 1; $c < 11; $c++){
				$selected = $displayMaxads[$typeName] == $c?"selected":"";
				echo '
						<option value="' . $c . '" ' . $selected . '>' . $c . '</option>';
			}
			echo '
						</select>
						<label for="display-' . $typeName . '-every">ads every <select name="display-' . $typeName . '-every" ' . $disabled .'>';
			for($c = 1; $c < 11; $c++){
				$selected = $displayEvery[$typeName] == $c?"selected":"";
				echo '
							<option value="' . $c . '" ' . $selected . '>' . $c . '</option>';
			}
			$selected = $displayOffset[$typeName] == -1?"selected":"";
			echo '
						</select>
						paragraphs</label>
						<label for="display-' . $typeName . '-offset">starting with paragraph</label><select name="display-' . $typeName . '-offset" ' . $disabled .'>
							<option value="-1" ' . $selected . '>No limit</option>';
			for($c = 1; $c < 11; $c++){
				$selected = $displayOffset[$typeName] == $c?"selected":"";
				echo '
							<option value="' . $c . '" ' . $selected . '>' . $c . '</option>';
			}
			echo '
						</select>
						<label for="display-' . $typeName . '-align">on the</label><select name="display-' . $typeName . '-align" ' . $disabled .'>';

			foreach($this->AdPlacementAlignment as $alignkey => $alignlabel){
				$selected = $displayAlign[$typeName] == $alignkey?"selected":"";
				echo '
							<option value="' . $alignkey . '" ' . $selected . '>' . $alignlabel . '</option>';
			}
			echo '
						</select>
					</center>
				</td>
			</tr>';

		}

		echo '
			<tr class="gradient">
				<th colspan="3" scope="col" style="text-align:center;">Advertisement Allocation Schedules</th>
			</tr>
			<tr class="gradient3">
				<td style="width:100%;" colspan="3">';

		if(!isset($postAllocations)){
			$postAllocations = $this->ExtractPostArray($this->AllocationMap);
		}

		if(!isset($postAllocations)){
			$list = $this->options['adcampaigns'][$name]['allocations'];
		}else{
			$list = $postAllocations;
		}
		
		if(sizeof($list) == 0){
			$list[0] = $this->GetDefaultAllocation();
		}

		foreach($list as $index =>  $allocation){
			echo $this->GetAllocationForm($index, $allocation, sizeof($list));
		}

		echo '			
			<tr class="gradient2">
				<td align="center" colspan="3"><a name="bottom"><input type="submit" class="button" name="add-allocation" value="Add Allocation" onClick="SetActionAnchor(document.campaignform, \'bottom\')" /></a></td>
			</tr>
			<tr class="gradient2">
				<td align="center" colspan="3"><input type="submit" button class="button" name="cancel" value="Cancel" /><input type="submit" class="button" name="' . $submitName . '" value="' . $submitValue . '" /></td>
			</tr>
		</tbody>
	</table>
	</form>
</div>';

	}

	function CampaignListTab_view($tab, $action){
		// Create the  Custom variable list
		$baseurl = $this->pageURL . '&tab=' . $tab . '&action=';
		$adUrlPrefix = $this->options['settings']['adUrlPrefix'];
		echo
		'<div class="wrap">
				<br class="clear" />
				<table class="widefat">
					<thead>
						<tr class="gradient">
							<th scope="col">Name</th>
							<th scope="col">Description</th>';
		if($this->options['settings']['externalAdsEnabled']){
			echo '
							<th scope="col">External Ad Path</th>';
		}
		echo '
							<th scope="col">Action</th>
						</tr>
					</thead>
					<tbody id="the-list" class="list:url">';
		if(isset($this->options['adcampaigns'])){
			$i=0;
			foreach($this->options['adcampaigns'] as $key => $value){
				echo '<form action="' . $baseurl . 'edit" method="post">';
				echo'<tr id="' . $key .'" ';
				if (!fmod($i,2)){
					echo 'class="alternate">';
				}else{
					echo '>';
				}
				echo
				'	<td valign="middle"><input name="name" type="hidden" value="' . $key . '" />' . $key . '</td>
					<td valign="middle">' . $this->options['adcampaigns'][$key]['description'] . '</td>';

				if($this->options['settings']['externalAdsEnabled']){
					echo '
							<td valign="middle"><a href="' . get_site_url() . '/' . $adUrlPrefix . '/' . $key . '" target="_blank" alt="External ad path for ' . $key . ' campaign.">' . get_site_url() . '/' . $adUrlPrefix . '/' . $key . '/</a></td>';
				}

				echo '
					<td>
							<input type="submit" value="Edit" name="edit" class="button" />
							<input type="submit" value="Delete" name="delete" class="button-secondary delete" onClick="return confirmDeleteSource(\'' . $key . '\');" />
					</td>
				</tr></form>';
				$i++;
			}
		}
		echo'<tr id="new" ';
			if (!fmod($i,2)){
				echo 'class="alternate">';
			}else{
				echo '>';
		}

		if( !isset($this->options['adsources']) && (!isset($this->options['settings']['adsense']['username']) || !isset($this->options['settings']['adsense']['password']))){
			$onClick = 'onClick="alert(\'You need to configure an ad source before you can setup a campaign.  You can configure your own local ad sources under Ad Sources or import your Adsense Ad Units as ad sources by configuring Adsense in Settings.\'); return false;"';
		}
		echo '
		<form action="' . $baseurl . 'edit" method="post">
			<td align="center" colspan="3"><input type="submit" class="button" name="add" value="Add Campaign" ' . $onClick . '/></td></form>
		</tr>
		</tbody></table></div>';
	}

	function SourceListTab_update($tab, $action){
		//verify data
		if($_POST['cancel']){
			return $this->SourceListTab_view($tab, $action);
		}
		$add = isset($_POST['add']);
		$delete = isset($_POST['delete']) && isset($_POST['name']) && isset($this->options['adsources'][$_POST['name']]);
		$update = isset($_POST['edit']) && isset($_POST['name']) && isset($this->options['adsources'][$_POST['name']]);

		if ($add){
			if(!isset($_POST['name']) || strlen($_POST['name']) < 1 ){
				$_POST['name_error'] = '*Name required';
				return $this->SourceListTab_edit($tab, $action);
			}
			if(isset($this->options['adsources'][$_POST['name']])){
				$_POST['name_error'] = '*Name must be unique';
				return $this->SourceListTab_edit($tab, $action);
			}
		}
		
		if($delete){
			$name = $_POST['name'];
			unset($this->options['adsources'][$name]);

			update_option($this->options_name, $this->options);
			echo '<H1>Deleted Source ' . $name . '</H1>';
		}else{
			$name = $_POST['name'];
			$this->options['adsources'][$name]['adtype'] = $_POST['adtype'];
			if($this->options['adsources'][$name]['adtype'] == "url"){
				$this->options['adsources'][$name]['url'] = $_POST['url'];
				$this->options['adsources'][$name]['wrap_url'] = isset($_POST['wrap_url']);
				$this->options['adsources'][$name]['code'] = "";
			} else {
				$this->options['adsources'][$name]['url'] = "";
				$this->options['adsources'][$name]['wrap_url'] = false;
				$this->options['adsources'][$name]['code'] = stripslashes($_POST['code']);
			}
			$this->options['adsources'][$name]['description'] = $_POST['description'];

			update_option($this->options_name, $this->options);
			if($add){
				echo '<H1>Added Source ' . $name . '</H1>';
			}else{
				echo '<H1>Updated Source ' . $name . '</H1>';
			}
		}

		$_POST['name'] = '';
		$_POST['adtype'] = '';
		$_POST['url'] = '';
		$_POST['wrap_url'] = '';
		$_POST['code'] = '';
		$_POST['description'] = '';

		//add to options
		return $this->SourceListTab_view($tab, $action);
	}

	function SourceListTab_edit($tab, $action){
		$baseurl = $this->pageURL . '&tab=' . $tab . '&action=update';
		$cancelurl = $this->pageURL . '&tab=' . $tab . '&action=view';
		//verify data
		$add = isset($_POST['add']);
		$edit = isset($_POST['edit']);
		$delete = isset($_POST['delete']);
		if ($add){
			if(!isset($_POST['new-ad-type'])){
				return $this->SourceListTab_view($tab, $action);
			}
			$type = $_POST['new-ad-type'];
			$include = 'libs/ads/' . $type . '.php';
			$func = "EditAd_" . $type;

			$title = "Create new " . $this->AdTypes[$type];
			$submitName = "add";
			$submitValue = "Add";

			$name = "";
			$descriptions = "";
			$ad = NULL;
		}else if($edit){
			$name = $_POST['name'];
			$type = $this->options['adsources'][$name]['adtype'];
			$include = 'libs/ads/' . $type . '.php';
			$func = "EditAd_" . $type;

			$title = "Edit " . $this->AdTypes[$type];
			$submitName = "edit";
			$submitValue = "Save";

			$ad = $this->options['adsources'][$name];
			$description = $ad['description'];

			$url = $this->options['adsources'][$name]['url'];
			$wrapURL = isset($this->options['adsources'][$name]['wrap_url'])?"checked":"";
			$code = $this->options['adsources'][$name]['code'];
		} else if($delete){
			return $this->SourceListTab_update($tab, $action);
		}
		require_once($include);
		if(!function_exists($func)){
			return $this->SourceListTab_view($tab, $action);
		}
		echo
		'<div class="wrap">
				<br class="clear" />
				<table class="widefat">
					<thead>
						<tr class="gradient">
							<th style="text-align:center" scope="col" colspan="2">' . $title . '</th>
						</tr>
					</thead>
					<form id="editform" action="' . $baseurl . '" method="post">
					<tbody id="source-detail">
						<tr class="gradient2">
							<th scope="col" style="width:1%">Name</th>
							<td valign="middle"><strong>' . $_POST['name_error'] . '</strong><input name="name" type="text" value="' . $name . '" style="width:99%;"/></td>
						</tr>
						<tr class="gradient2">
							<th scope="col" style="width:1%">Description</th>
							<td valign="middle"><input name="description" type="text" value="' . $description . '" style="width:99%;"/></td>
						</tr>';

		require_once($include);
		if(function_exists($func)){
			call_user_func($func, true, $this->options);
		}

		echo '
						<tr class="gradient2">
							<td align="center" colspan="2"><input type="submit" button class="button" name="cancel" value="Cancel" /><input type="submit" class="button" name="' . $submitName . '" value="' . $submitValue . '" /></td></form>
						</tr>
					</tbody>
				</table>
			</div>';
		//add to options
		//$this->SourceListTab_view($tab, $action);
	}

	function SourceListTab_view($tab, $action){
		// Create the  Custom variable list
		$baseurl = $this->pageURL . '&tab=' . $tab . '&action=';
		echo
		'<div class="wrap">
				<br class="clear" />
				<table class="widefat">
					<thead>
						<tr class="gradient" style="text-align:center;">
							<th scope="col">Name</th>
							<th scope="col">Description</th>
							<th scope="col">Action</th>
						</tr>
					</thead>
					<tbody id="the-list" class="list:url">';
		if(isset($this->options['adsources'])){
			$i=0;
			foreach($this->options['adsources'] as $key => $value){
				echo '<form action="' . $baseurl . 'edit" method="post">';
				echo'<tr valign="middle" id="' . $key .'" ';
				if (!fmod($i,2)){
					echo 'class="alternate">';
				}else{
					echo '>';
				}
				echo
				'	<td valign="middle" style="width:1%;" nowrap><input name="name" type="hidden" value="' . $key . '" />' . $key . '</td>
					<td valign="middle">' . $this->options['adsources'][$key]['description'] . '</td>
					<td style="width:1%;" nowrap>
							<input type="submit" class="button" name="edit" value="Edit" />
							<input type="submit" value="Delete" name="delete" class="button-secondary delete" onClick="return confirmDeleteSource(\'' . $key . '\');" />
					</td>
				</tr></form>';
				$i++;
			}
		}
		echo'<tr id="new" ';
			if (!fmod($i,2)){
				echo 'class="alternate">';
			}else{
				echo '>';
		}
		echo
		'<form action="' . $baseurl . 'edit" method="post">
			<td align="center" colspan="3">
				<input type="submit" class="button" name="add" value="Add" />
				<select name="new-ad-type">';

				foreach($this->AdTypes as $value => $name){
					echo '
					<option value="' . $value . '">' . $name . '</option>';
				}

			echo '
				</select>
			</td>
		</form>
		</tbody></table></div>';
	}

	function DisplaySettingsGroup($name, $content, $open){
		$ps = $open?'':'style="display:none;"';
		$chevron = $open?'collapse.gif':'expand.gif';

		$id = str_replace(' ', '', $name);
		echo '
			<div class="settingspanel">
				<div class="collapsiblepanelcaption" name="' . $id . '_caption" style="height:20px; cursor: pointer;" onclick="togglePanelAnimatedStatus(\'' . $id . '\' , 50, 50)">
					<div style="float: left">' . $name . '</div>
					<div style="float: right; vertical-align: middle">
						<img src="' . $this->imagePath . '/'. $chevron . '" name="' . $id . '_chevron" width="13" height="14" border="0" alt="Show/Hide" title="Show/Hide" />
					</div>
				</div>
				<div class="collapsiblepanelcontent" name="' . $id . '_content" ' . $ps . '>' . $content . '</div>
			</div>' . "\n";

	}

	function GetAdsenseAdminForm(){
		$content =
'<table class="widefat">
	<tbody id="adsense-settings">
		<tr class="gradient2">
			<th scope="col" colspan="5" style="text-align:center;">Adsense Login</th>
		</tr>
		<tr class="gradient2">
			<th scope="col" style="width:1%;" colspan="2">Username</th>
			<td><input type="text" name="adsenseUsername" value="'. $this->options['settings']['adsense']['username'] . '" style="width:99%;"></td>
			<th scope="col" style="width:1%;">Password</th>
			<td><input type="password" name="adsensePassword" value="" autocomplete="off" style="width:99%;"></td>
		</tr>
		<tr>
			<th scope="col" colspan="5" style="text-align:center;"></th>
		</tr>
		<tr class="gradient2">
			<th scope="col" colspan="5" style="text-align:center;">Caching</th>
		</tr>
		<tr class="gradient2">
			<td colspan="5">
				<div style="text-align:left !important;">How long should Advertwhirl save data it loads from Adsense.  Loading data such as reports and ad lists can be slow, turning on caching will make the Advertwhirl control panels more responsive but new settings made at the Adsense website will not be seen immediatly by Advertwhirl.  Ad code from is always cached to insure ads are served as quickly as possible.  If you make a change to an Adsense Ad Slot that you are using as an ad source you will need to manually update the adsense cache.</div>
			</td>
		</tr>
		<tr class="gradient2">
			<th scope="col" colspan="2" >Ad Caching</th>
			<td colspan="3">
				<select name="adsense-ads-cache-time">';
		$cacheTime = isset($this->options['settings']['adsense']['ads-cache'])?$this->options['settings']['adsense']['ads-cache']:24;
		foreach($this->CacheTimes as $value => $name){
			$selected = $cacheTime == $value?"selected":"";
			$content .= '
					<option value="' . $value . '" ' . $selected . '>' . $name . '</option>';
		}

		$content .= '
				</select>
				<input type="submit" class="button" name="reload-adsense-ads-cache" value="Manually Reload Ad Units" />
			</td>
		</tr> 
		<tr class="gradient2">
			<th scope="col" colspan="2" >Reports Caching</th>
			<td colspan="3">
				<select name="adsense-stats-cache-time">';
		$cacheTime = isset($this->options['settings']['adsense']['stats-cache'])?$this->options['settings']['adsense']['stats-cache']:24;
		foreach($this->CacheTimes as $value => $name){
			$selected = $cacheTime == $value?"selected":"";
			$content .= '
					<option value="' . $value . '" ' . $selected . '>' . $name . '</option>';
		}

		$content .= '
				</select>
				<input type="submit" class="button" name="reload-adsense-stats-cache" value="Manually Reload Reports" />
			</td>
		</tr>
		<tr>
			<th scope="col" colspan="5" style="text-align:center"></th>
		</tr>
		<tr class="gradient2">
			<th scope="col" colspan="5" style="text-align:center">Adsense Reports</th>
		</tr>
		<tr class="gradient2">
			<th colspan="2" scope="col" style="width:1%;" nowrap>Get reports for</th>
			<td colspan="4">';

		foreach($this->adsense->GetReportPeriods() as $key => $value){
			$checked = $this->options['settings']['adsense']['period'][$key]?'checked':'';
			$content .= '
				<input type="checkbox" name="adsense_report_period_' . $key . '" value="' . $value . '" ' . $checked . ' /><label>' . $value . '</label>';
		}

		$content .= '
			</td>
		</tr>
		<tr class="gradient2">
			<th colspan="2" scope="col" style="width:1%;" nowrap>Statistics to report</th>
			<td colspan="4">';

		foreach($this->adsense->GetReportTypes() as $key => $value){
			$checked = 	$this->options['settings']['adsense']['type'][$key]?'checked':'';
			$content .= '
				<input type="checkbox" name="adsense_report_type_' . $key . '" value="' . $value . '" ' . $checked . ' /><label>' . $value . '</label>';
		}

		$content .= '
			</td>
		</tr>
		<tr class="gradient2">
			<th colspan="2" scope="col" style="width:1%;" nowrap>Get statistics for ad categories</th>
			<td colspan="4">';

		foreach($this->adsense->GetAdCategories() as $key => $value){
			$checked = 	$this->options['settings']['adsense']['category'][$key]?'checked':'';
			$content .= '
				<input type="checkbox" name="adsense_report_category_' . $key . '" value="' . $value . '" ' . $checked . ' /><label>' . $value . '</label>';
		}

		$content .= '
			</td>
		</tr>
		<tr><td colspan="5"><input type="submit" class="button" name="update" value="Update" /></td></tr>
	</tbody>
</table>' . "\n";
	return $content;
	}

	function GetGeneralSettingsAdminForm(){
		$fillAllocation = $this->options['settings']['fillEmptyAllocations']?"checked":"";
		$displayDashboard = $this->options['settings']['displayDashboard']?"checked":"";
		$adsenseDashboard = $this->options['settings']['adsense']['dashboard']?'checked':'';
		$externalAdsEnabled = $this->options['settings']['externalAdsEnabled']?"checked":"";
		$adUrlPrefix = $this->options['settings']['adUrlPrefix'];
		$gaEnabled = $this->options['settings']['analytics']['enabled'] && strlen($this->options['settings']['analytics']['username']) > 0 && strlen($this->options['settings']['analytics']['password']) > 0?"":"disabled";
		$gaVariablesEnabled = $this->options['settings']['analytics']['VariablesEnabled']?"checked":"";
		$defaultTab = $this->options['settings']['defaulttab'];

		if(isset($this->options['settings']['adsense']['username']) && isset($this->options['settings']['adsense']['password'])){
			$this->adsense->LoadAdUnits();
			$adUnits = $this->adsense->GetAdUnits();
		}

		$defaultAdsDisabled = (!isset($this->options['adsources']) || count($this->options['adsources']) < 1) && (!isset($adUnits) || count($adUnits) < 1)?'disabled':'';

		$content =
'<table class="widefat">
	<thead>
		<tr class="gradient2">
			<th scope="col" colspan="4" style="text-align:center;">Ad Sources</th>
		</tr>
	</thead>
	<tbody id="adsense-settings">
		<tr class="gradient2">
			<th scope="col" style="width:1%;" nowrap>Empty Sources</th>
			<td nowrap style="width:1%;"><input type="checkbox" name="fillEmptyAllocations" value="'. $this->options['settings']['fillEmptyAllocations'] . '" ' . $fillAllocation . ' ' . $defaultAdsDisabled . '/><label>Fill empty ad slots with Adsource</label></td>
			<td colspan="2">
				<select name="defaultsource" style="width:99%;" ' . $defaultAdsDisabled . '>
					<option value="">Select default ad source</option>' . "\n";
		if(isset($this->options['adsources'])){
			foreach($this->options['adsources'] as $name => $source){
				if ($name == $this->options['settings']['defaultsource']){
					$content .= '					<option value="' . $name .'" selected>local ad - ' . $name . '</option>' . "\n";
				}else{
					$content .= '					<option value="' . $name .'" >local ad - ' . $name . '</option> . "\n"';
				}
			}
		}

		if(isset($this->options['settings']['adsense']['username']) && isset($this->options['settings']['adsense']['password'])){
			if(isset($adUnits) && sizeof($adUnits) > 0){
				foreach($adUnits as $id => $ad){
					if ('adsense-' . $id == $this->options['settings']['defaultsource']){
						$content .= '					<option value="adsense-' . $id .'" selected>adsense ad - ' . $ad['name'] . ' - ' .  $ad['size'] . ' ' . $ad['format'] . '</option>' . "\n";
					}else{
						$content .= '					<option value="adsense-' . $id .'" >adsense ad - ' . $ad['name'] . ' - ' .  $ad['size'] . ' ' . $ad['format'] . '</option> . "\n"';
					}
				}
			}
		}

		$content .= '
				</select>
			</td>
		</tr>
		<tr class="gradient2">
			<th scope="col" style="width:1%;" nowrap>Tab to Start On</th>
			<td colspan="3">
				<select name="defaulttab" style="width:99%;">';

		foreach($this->AdminTabs as $name => $tab){
			$selected = $defaultTab == $name?"selected":"";
			$content .= '
					<option value="' . $name . '" ' . $selected . '>' . $this->AdminTabs[$name]['name'] . '</option>';
		}

		$content .= '
				</select>
			</td>
		</tr>
		<tr><td colspan="4"></td></tr>
		<tr class="gradient2">
			<th scope="col" colspan="4" style="text-align:center;">External Ads</th>
		</tr>
		<tr class="gradient2">
			<td colspan="4">
				<div style="text-align:left !important;">Ad campaigns can be accessed from external sites with a permalink style url if external ads are enabled.  As these ads are being loaded outside of your wordpress site any of the rules which rely on wordpress variables, posts, pages etc... will be marked false.  Even if the external site is also running wordpress, all wordpress rules will be marked false. </div>
			</td>
		</tr>
		<tr class="gradient2">
			<th scope="col" style="width:1%;" nowrap>Enable External Ads</th>
			<td style="text-align:left">
				<input type="checkbox" name="externalAdsEnabled" value="1" '.  $externalAdsEnabled . '/>
			</td>
			<th scope="col" style="width:1%;" nowrap>External URL</th>
			<td style="text-align:left">
				<i>' . get_site_url() . '/<input type="text" name="adUrlPrefix" value="' . $adUrlPrefix . '" />/your_campaign/
			</td>
		</tr>
		<tr class="gradient2">
			<td colspan="4">
				<div style="text-align:left !important;">
					If Google Analytics is enabled extra path information after the name of an ad campaign can be added to your analytics code as custom variables.
				</div>
			</td>
		</tr>
		<tr class="gradient2">
			<th scope="col" style="width:1%;" nowrap>Add Google Anayltic Variables</th>
			<td style="text-align:left" colspan="3">
				<input type="checkbox" name="gaVariablesEnabled" value="1" ' . $gaEnabled . ' '.  $gaVariablesEnabled . '/>
			</td>
		</tr>
		<tr class="gradient2">
			<td colspan="4">
				<div style="text-align:left !important;">
					To use location rules you need a free api key from the IP Info Database.  You can <a target="_blank" href="http://www.ipinfodb.com/register.php">request your key here</a> it is a simple form and they will email the key in a few minutes time.
				</div>
			</td>
		</tr>
		<tr class="gradient2">
			<th scope="col" style="width:1%;" nowrap>IP Info Database Key</th>
			<td style="text-align:left" colspan="3">
				<input type="text" name="ipinfodbkey" value="' . $this->options['settings']['ipinfodbkey'] . '" style="width:99%;"/>
			</td>
		</tr>
		<tr><td colspan="4"></td></tr>
		<tr class="gradient2">
			<th scope="col" colspan="4" style="text-align:center;">Dashboard</th>
		</tr>
		<tr class="gradient2">
			<td colspan="4">
				<input type="checkbox" name="displayDashboard" value="1" '.  $displayDashboard . '/><label>Display Dashboard widget</label>
				<input type="checkbox" name="adsenseDashboard" value="1" ' . $adsenseDashboard . ' /><label>Add Adsense to Dashboard</label>
			</td>
		</tr>
		<tr><td colspan="4"><input type="submit" class="button" name="update" value="Update" /></td></tr>
	</tbody>
</table>' . "\n";

		return $content;
	}

	function GetCommissionJunctionAdminForm(){
		$content = "<span>Commission Junction integration still under development</span>";
		return $content;
	}

	function GetGoogleAnalyticsAdminForm(){
		$content = "<span>Google Anayltics integration still under development</span>";
		return $content;
	}

	function SettingsTab_view($tab, $action){
		$baseurl = $this->pageURL . '&tab=' . $tab . '&action=';
		echo '<form action="' . $baseurl . 'update" method="post">' . "\n";

		/** Panel for general settings */
		$groupOpen = true;
		$groupName = "General Settings";
		$groupContent = $this->GetGeneralSettingsAdminForm();
		$this->DisplaySettingsGroup($groupName, $groupContent, $groupOpen);

		/** Panel for Adsense settings */
		$groupOpen = true;
		$groupName = "Adsense Settings";
		$groupContent = $this->GetAdsenseAdminForm();
		$this->DisplaySettingsGroup($groupName, $groupContent, $groupOpen);

		/** Panel for Google Analytics settings */
		$groupOpen = false;
		$groupName = "Google Anayltics";
		$groupContent = $this->GetGoogleAnalyticsAdminForm();
		$this->DisplaySettingsGroup($groupName, $groupContent, $groupOpen);

		/** Panel for Commission Junction settings */
		$groupOpen = false;
		$groupName = "Commission Junction";
		$groupContent = $this->GetCommissionJunctionAdminForm();
		$this->DisplaySettingsGroup($groupName, $groupContent, $groupOpen);

		echo '</form>' . "\n";
    }

	function SettingsTab_update($tab, $action){
		//verify data
		$update = isset($_POST['update']);
		$reloadAdsenseAdsCache = isset($_POST['reload-adsense-ads-cache']);
		$reloadAdsenseStatsCache = isset($_POST['reload-adsense-stats-cache']);
		if ($reloadAdsenseAdsCache){
			if(isset($this->options['settings']['adsense']['username']) && isset($this->options['settings']['adsense']['password'])){
				$this->adsense->LoadAdUnits(true);
    	    	$this->adsense->Logout();
			}
		}

		if ($reloadAdsenseStatsCache){
			if(isset($this->options['settings']['adsense']['username']) && isset($this->options['settings']['adsense']['password'])){
				$this->adsense->LoadStats(true);
    	    	$this->adsense->Logout();
			}
		}

		if ($update){
			/** Update Adsense Settings */
			$this->options['settings']['adsense']['username'] = $_POST['adsenseUsername'];
			$this->adsense->SetUsername($this->options['settings']['adsense']['username']);
			if(isset($_POST['adsensePassword']) && strlen($_POST['adsensePassword']) > 1){
				$this->options['settings']['adsense']['password'] = $_POST['adsensePassword'];
				$this->adsense->SetPassword($this->options['settings']['adsense']['password']);
			}

			foreach($this->adsense->GetReportPeriods() as $key => $value){
				$this->options['settings']['adsense']['period'][$key] = isset($_POST['adsense_report_period_' . $key]);
			}

			foreach($this->adsense->GetReportTypes() as $key => $value){
				$this->options['settings']['adsense']['type'][$key] = isset($_POST['adsense_report_type_' . $key]);
			}

			foreach($this->adsense->GetAdCategories() as $key => $value){
				$this->options['settings']['adsense']['category'][$key] = isset($_POST['adsense_report_category_' . $key]);
			}

			if(isset($_POST['adsense-ads-cache-time'])){
				$this->options['settings']['adsense']['ads-cache'] = $_POST['adsense-ads-cache-time'];
				$this->adsense->SetAdsCache($_POST['adsense-ads-cache-time'] * 360);
			}

			if(isset($_POST['adsense-stats-cache-time'])){
				$this->options['settings']['adsense']['stats-cache'] = $_POST['adsense-stats-cache-time'];
				$this->adsense->SetStatsCache($_POST['adsense-stats-cache-time'] * 360);
			}

			/** Update General Settings */
			$this->options['settings']['fillEmptyAllocations'] = isset($_POST['fillEmptyAllocations']);
			if(isset($_POST['defaultsource']))
				$this->options['settings']['defaultsource'] = $_POST['defaultsource'];

			if(isset($_POST['defaulttab']))
				$this->options['settings']['defaulttab'] = $_POST['defaulttab'];

			$this->options['settings']['displayDashboard'] = isset($_POST['displayDashboard']);
			$this->options['settings']['adsense']['dashboard'] = isset($_POST['adsenseDashboard']);

			if($this->options['settings']['externalAdsEnabled'] != isset($_POST['externalAdsEnabled'])){
				$this->options['settings']['externalRulesUpdated'] = true;
			} else if($this->options['settings']['adUrlPrefix'] != $_POST['adUrlPrefix']){
				$this->options['settings']['externalRulesUpdated'] = true;
			}else{
				$this->options['settings']['externalRulesUpdated'] = false;
			}

			$this->options['settings']['externalAdsEnabled'] = isset($_POST['externalAdsEnabled']);
			$this->options['settings']['adUrlPrefix'] = $_POST['adUrlPrefix'];
			$this->options['settings']['analytics']['VariablesEnabled'] = isset($_POST['gaVariablesEnabled']);
			$this->options['settings']['ipinfodbkey'] = $_POST['ipinfodbkey'];

			/** Save the updated settings */
			update_option($this->options_name, $this->options);
			if($this->options['settings']['externalRulesUpdated'])
				$this->FlushRewrites();
		}

		//add to options
		$this->SettingsTab_view($tab, $action);
	}

	function DisplayHandbookSection($name, $content){
		$open = $name == "Getting Started";
		$style = 'style="text-align:left;';
		$style .= $open ?'"':'display:none;"';
		$chevron = $open?'collapse.gif':'expand.gif';

		echo '
		<div class="settingspanel">
			<div class="collapsiblepanelcaption" name="' . $name . '_caption" style="height:20px; cursor: pointer;" onclick="togglePanelAnimatedStatus(\''. $name . '\' , 50, 50)">
				<div style="float:left; position:relative; width:20%; left:+40%; text-align:center;">' . $name . '</div>
				<div style="float: right; vertical-align: middle">
					<img src="' . $this->imagePath . '/' . $chevron . '" name="' . $name . '_chevron" width="13" height="14" border="0" alt="Show/Hide" title="Show/Hide" />
				</div>
			</div>
			<div class="collapsiblepanelcontent" name="' . $name . '_content" ' . $style . '>' . $content . '</div>
		</div>' . "\n";

	}

	function MarkupHandbookSection($section){
		$content = '<div class="handbooksection">' . "\n";
		//$content .= str_replace("\n+", '<br/>', $section);
		$content .= $section;
		$content .= '</div>' . "\n";

		return $content;
	}

	function GetHandbookSections($path){
		$handbookText = file_get_contents($path, true);
		//split('= section =', $handbookText);

		$chunks = preg_split('/<-- ([^>]+) -->/i', $handbookText, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		$sections = array();
		for($i = 0; $i < count($chunks); $i++){
			$sections[$chunks[$i]] = $this->MarkupHandbookSection($chunks[$i + 1]);
			$i++;
		}

		return $sections;
	}

	function HandbookTab_view($tab, $action){

		$sections = $this->GetHandbookSections($this->handbookPath);
		foreach($sections as $name => $content){
			if(isset($name) && isset($content))
				$this->DisplayHandbookSection($name, $content);
		}
    }

	function AboutTab_view($tab, $action){
		echo '<H2><a href="http://wordpress.org/extend/plugins/advertwhirl">Advertwhirl version ' . $this->version . '</a></H2><br/>' . "\n";
		echo '<b>Advertwhirl</b> <i>(pronounced Adver-twhirl)</i> is a wordpress plugin that lets you set up advertising campaigns for your site.  Each campaign has 1 or more allocation strategies which can be made active manually or by schedule.  Each allocation strategy defines what ad-servers the campaign uses and how ads are rotated between them.  Campaigns can be accessed through a shortcode.<br/><br/>' . "\n";
		echo '<b>Example shortcode</b><br/>' . "\n";
		echo '<i>[­advertwhirl campaign=CAMPAIGN]</i><br/><br/>' . "\n";
		echo '&copy; Copyright 2011  Mobile Sentience LLC<br/>' . "\n";
		echo 'Written by <a href="mailto:oss@mobilesentience.com?subject=' . $this->name . ' version ' . $this->version . '">Max Jonathan Spaulding</a> - <a href="http://www.mobilesentience.com">Mobile Sentience LLC</a><br/>' . "\n";
		echo '<br/>' . "\n";
		echo 'This program is free software; you can redistribute it and/or modify<br/>' . "\n";
		echo 'it under the terms of the GNU General Public License as published by<br/>' . "\n";
		echo 'the Free Software Foundation; either version 2 of the License, or<br/>' . "\n";
		echo '(at your option) any later version.<br/>' . "\n";
		echo '<br/>' . "\n";
		echo 'This program is distributed in the hope that it will be useful,<br/>' . "\n";
		echo 'but WITHOUT ANY WARRANTY; without even the implied warranty of<br/>' . "\n";
		echo 'MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br/>' . "\n";
		echo 'GNU General Public License for more details.<br/>' . "\n";
		echo '<br/>' . "\n";
		echo 'You should have received a copy of the GNU General Public License<br/>' . "\n";
		echo 'along with this program; if not, write to the Free Software<br/>' . "\n";
		echo 'Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA<br/><br/>' . "\n";
	}

	function SupportTab_view($tab, $action){
		$defaultSubject = 'Bug Report: ' . $this->name . ' Version ' . $this->version;
		$defaultMessage = "\n\n\n\n\n\n" . $this->GetTechnicalSpecs();

		include_once($this->libraryPath . "/captcha/shared.php");
		include_once($this->libraryPath . "/captcha/captcha_code.php");
		$wpf_captcha = new CaptchaCode();
		$wpf_code = wpf_str_encrypt($wpf_captcha->generateCode(6));

		echo '
<p><a href="http://www.mobilesentience.com">Mobile Sentience LLC</a> actively supports Advertwhirl if you have any questions or problems you can fill out the form below.  The message is pre-filled with some technical specs which will help us troubleshoot any issues.  <a href="http://www.mobilesentience.com">Mobile Sentience</a> provides all support through a public forum, this helps us manage our support costs as well as providing a searchable reference of past problems and questions that other users of encountered.  A great place to start if you have a simple question is to see if it has already been asked and answered on the <a href="http://www.mobilesentience.com/support/?mingleforumaction=viewforum&f=12.0">Advertwhirl Support Forum</a></p><br/>
<table class="widefat">
	<thead>
		<tr class="gradient">
			<th scope="col" colspan="2" style="text-align:center;">Report a problem</th>
		</tr>
	</thead>
	<tbody id="adsense-settings">
		<form action="http://www.mobilesentience.com/wp-content/plugins/mingle-forum/wpf-insert.php" name="addform" method="post" target="_blank">
		<tr class="gradient2">
			<th>Subject</th>
			<td><input style="width:99%;" type="text" name="add_topic_subject" value="' . $defaultSubject . '"></td>
		</tr>
		<tr class="gradient2">
			<th valign="top" >Message</th>
			<td><textarea rows="20" cols="80" name="message" >' . $defaultMessage . '</textarea></td>
		</tr>
		<tr class="gradient2">
			<th>Security Code</th>
			<td>
				<img alt="" src="http://www.mobilesentience.com/wp-content/plugins/mingle-forum/captcha/captcha_images.php?width=120&amp;height=40&amp;code=' . $wpf_code . '">
				<input type="hidden" name="wpf_security_check" value="' . $wpf_code . '">
				<input id="wpf_security_code" name="wpf_security_code" type="text">
			</td>
		<tr>
			<td colspan="2" align="center"><input type="submit" id="wpf-post-submit" name="add_topic_submit" value="Submit" class="button"></td>
			<input type="hidden" name="add_topic_forumid" value="12">
			<input type="hidden" name="add_topic_plink" value="http://www.mobilesentience.com/support/">
		</tr>
		</form>
	</tbody>
</table>' . "\n";

	}

	function getRightMost($sSrc, $sSrch) { 
		for ($i = strlen($sSrc); $i >= 0; $i = $i - 1) {
			$f = strpos($sSrc, $sSrch, $i);
			if ($f !== FALSE) {
				return substr($sSrc,$f + strlen($sSrch), strlen($sSrc));
			}
		}
		return $sSrc;
	}

	public function DisplayAdminTabs($activeTab){
		echo '<div class="tabBox" style="clear:both;">' . "\n";
		echo '	<div class="tabArea">' . "\n";
		foreach(array_keys($this->AdminTabs) as $tab){
			$url = $this->pageURL . "&tab=" . $tab;
			if($activeTab == $tab){
				echo '<a class="activetab" href="' . $url . '">' . $this->AdminTabs[$tab]['name'] . '</a>' . "\n";
			}else{
				echo '<a class="tab" href="' . $url . '">' . $this->AdminTabs[$tab]['name'] . '</a>' . "\n";
			}
		}
		echo '	</div>' . "\n";
	}

	public function DisplayAdminContent($tab, $action){
		echo '<div class="tabMain">' . "\n";
		echo '<div class="tabContent" id="tabContent" >' . "\n";
		if(isset($this->AdminTabs[$tab])){
			$func = $this->AdminTabs[$tab]['method_base'] . "_" . $action;
			if(!method_exists($this, $func)){
				$func = $this->AdminTabs[$tab]['method_base'] . "_view";
			}
			if(method_exists($this, $func)){
				call_user_func(array(&$this, $func), $tab, $action);
			}
		}
		echo '</div>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	}

	// Create the administration panel
	public function CreateAdminPanel(){

		$activeTab = $this->options['settings']['defaulttab'];
		if(isset($_GET['tab'])){
			if(isset($this->AdminTabs[$_GET['tab']])){
				$activeTab =  $_GET['tab'];
			}
		}

		$action = "view";
		if(isset($_GET['action'])){
			$action =  $_GET['action'];
		}

		//if(get_option('siteurl') != 'http://www.mobilesentience.com') $this->DisplayMobileSentienceAd();
		$this->DisplayAdminBanner($activeTab);
		$this->DisplayAdminTabs($activeTab);
		$this->DisplayAdminContent($activeTab, $action);
    }    

}
?>
