=== Advertwhirl Advertising Manager and Ad Rotator ===
Contributors: mobilesentience
Donate link: http://www.mobilesentience.com/software/oss/advertwhirl/
Tags: advertising, advertisement, ad, ad rotator, ads, rotate, whirl, campaign, manager, allocation, adsense, analytics, google analytics, commission junction, cj, affiliate, banner, guest blogging, geo location, location, geoip, virtual theme, branding, automatic ad placement
Requires at least: 2.2
Tested up to: 3.1
Stable tag: 1.0.13

Advertwhirl the ultimate WordPress advertising campaign manager WordPress.  Control exactly which ads are displayed and how.

== Description ==

Advertwhirl(pronounced Adver-Twirl) is the ultimate WordPress plugin to manage advertising campaigns for your site.  Advertwhirl not only allows you to manage exactly how and when ads are displayed on your site but allows you to serve your ads to external sites and smartphone apps.  Manage ads for your guest posts and get a return on your hard work.  Incentivize your guest bloggers to create great content by giving them a share of their posts ad slots.  Advertwhirl is being actively developed and supported by Mobile Sentience.  Already the most comprehensive advertising manager for WordPress with true integration with Adsense, Groupon and others Advertwhirl is updated frequently with many exciting and new features to come.  If Advertwhirl is missing something that you think is a must have, it is likely already on our todo list but give us a shout anyway and see if we can move it up on our release schedule.

= Advertwhirl development and support are ad sponsored, for every twenty of your ads it serves it serves one of its own. =

Advertwhirl's advertisement manager allows even the most complex ad rotation scenarios to be realized.  Providing total control of how and when your advertisements are displayed.  At the moment Advertwhirl can import all of your Adsense ad units automatically(along with your Adsense reports), creates Groupon ads based on your criteria(advertisement size and geographic location), and it's custom ad code feature allows you to create an ad for ad broker, affiliate, direct advertiser, or guest blogger along with your own local banner or text ads.  Advertwhirl is quite flexible and lets you manage your advertising campaigns as simply or complexly as you require, if there is an ad management scenario which you can't figure out how to accomplish with Advertwhirl ask about it on the [Advertwhirl support forum](http://www.mobilesentience.com/support/?mingleforumaction=viewforum&f=12.0 "Advertwhirl Support Forum"), we can help you configure your ad campaign.  Or if there is a rule that you think is missing let us know, we may just add it.

Advertwhirl can automatically place your ads where you want of your front page, posts and other pages.  Ads can be placed in the header, footer, before/after the content of a page/post and in the content of a page post(wrapping the text of the post/page around the ad).  All without ever touching your theme template files.

Advertwhirl ad campaigns can now be accessed in five ways.

1. Automatically insert advertisements in your posts and pages (without having to edit your theme)
1. In posts and pages via short code ie… advertisement for campaign your_campaign
1. In theme template files via php functions ie… advertwhirl_get_ad($campaign); advertwhirl_print_ad($campaign);
1. On external sites and none-Wordpress pages via permalink ie… http://www.yoursite.com/ads/yourcampaign
1. Through a widget in the side bar

= Known Issues =
* A bug in install prevents the plugin being activated on WordPress older than 3.0 this is fixed in 1.0.11
* advertwhirl_get_ad() no longer prints the ad but returns it, advertwhirl_print_ad() as introduced in 1.0.6 - if you are updating Advertwhirl from version 1.0.5 or older you need to change <?php advertwhirl_get_ad('Your_Campaign'); ?> to either <?php echo advertwhirl_get_ad('Your_Campaign'); ?> or <?php advertwhirl_print_ad('Your_Campaign'); ?>
* there are a few bugs (priority of the_content filter changes from system to system) for auto ad placement in version 1.0.8 that lead to some post/page content not being displayed, or ads not being displayed.  This has been fixed in 1.0.9, it is recommended that you upgrade

= Roadmap =
= Version 1.0.13 (Scheduled for release May 22th) =
* Keyword advertising, automatically insert affiliate links that match keywords in your post.
* OS rule targets ads based on the visitors Operating System(Windows, MAC, Linux, Android, iphone/ios, etc...)
* User Agent rule targets ads based on the visitors browser(Chrome, Firefox, IE...)
* Add Ad Source Groups to allow several advertisements to share a single weight(as in they total served ads of all advertisements in the ad group are counted together when determining weights for ad rotation)
* Add Advertwhirl Content Menu to simplify access
* Add a quick configuration wizard
* Export/Import Advertwhirl configurations

= Version 1.0.14 (Scheduled for release May 24th) =
* Customize ads with css and html before or after ad placement
* Formating for iframe wrapped advertisements
* Add TinyMCE ad widget to automate insertion of advertisements in posts
* New Banner Ad Source for speedy code free creation of local banner advertisements
* New Post Ad Source create your own advertisements using the post editor

= Version 1.0.15 (TBD) =
* New tool to track ad placement statistics
* Permalink for auotgenerated statistics xml file for easy importation in external apps
* Google Analytics Integration

= Version 1.0.16 (TBD) =
* Finish Groupon API integration to automatically pull down Groupon's newest advertisements
* Automatically selct Groupon Ad to run based on visitors location

= Version 1.0.17 (TBD) =
* Commission Junction Integration

= And soon your advertising campaigns will be easily available in smartphone apps. =
* Advertwhirl advertisement panel for Android Apps (coming May 25th, 2011)
* Advertwhirl advertisement panel for iOS Apps (coming June, 2011)

= Features =
* Ads can be allocated based on weight, percent or a mix.
* Fill empty ad slots with a default ad
* Define your own rules for which an advertisement is picked
* Advertwhirl Adsense integration imports both your reports and your ad units.
* Automatically inserts ads where you want them in posts and pages, complete with wrapping of post content around ads.

= Coming Soon =
* Insert Google Analytics tracking code
* View Google Analytics statistics
* Commission Junction integration to download affiliate ads and commission reports

== Installation ==

Through WordPress plugin gallery using built-in installer (preferred)

1. Login as an admin
1. Go to “Plugins” and click “Add New”
1. Enter “Advertwhirl” in the search terms and click “Search”
1. Click “Install Now”
1. When prompted “Are you sure you want to install this plugin” click “Ok”
1. Fill out your “Connection Information” and click “Proceed”
1. When WordPress is finished installing the plugin click “Activate”
1. Go to the Advertwhirl settings menu setup your campaigns

Through WordPress built-in installer if you already have the file downloaded

1. After plugin zip file is downloaded Login as an admin
1. Go to “Plugins” and click “Add New”
1. Click “Upload”
1. Click “Choose File” and find the downloaded plugin zip file
1. Click “Install Now”
1. Fill out your “Connection Information” and click “Proceed”
1. When WordPress is finished installing the plugin click “Activate”
1. Go to the Advertwhirl settings menu setup your campaigns

Manual method

1. Download plugin zip file from WordPress directory or the [Mobile Sentience Advertwhirl page](http://www.mobilesentience.com/software/oss/advertwhirl/)
1. Unzip plugin file
1. Upload the ‘advertwhirl’ directory and all of its files to the ‘/wp-content/plugins/’ directory of your WordPress site
1. Activate the plugin through the ‘Plugins’ menu in WordPress
1. Go to the Advertwhirl settings menu to setup your campaigns

== Frequently Asked Questions ==

= Is Advertwhirl free? =
Sort of.  You can download, install and use Advertwhirl free of charge with no limits.  However, Advertwhirl is ad sponsored.  That for every 20 ads it serves up for your site it will serve 1 of its own.

= Is Advertwhirl open source? =
Yes.  All plugins in the WordPress plugin directory are required to have a GPL V2 compatible license.  Advertwhirl is licensed under the GPLv2

= What ads can I display with Advertwhirl? =
Any you want.  At the moment Advertwhirl can import all of your Adsense ad units automatically(along with your Adsense reports), creates Groupon ads based on your criteria(advertisement size and geographic location), and it's custom ad code feature allows you to create an ad for ad broker, affiliate, direct advertiser, or guest blogger along with your own local banner or text ads.

= How much control do I have over which ads are served? =
Total control.  Advertwhirl's advertisement manager allows even the most complex ad rotation scenarios to be realized.  If there is an ad management scenario which you can't figure out how to accomplish with Advertwhirl ask about it on the [Advertwhirl support forum](http://www.mobilesentience.com/support/?mingleforumaction=viewforum&f=12.0 "Advertwhirl Support Forum"), we can help you configure your ad campaign.  Or ff there is a rule that you think is missing let us know, we may just add it.

= Advertwhirl seems complex, can it do simple ad sharing with a guest blogger? =
Yes.  Advertwhirl is quite flexible and lets you manage your advertising campaigns as simply or complexly as you require.  Ad sharing for guest bloggers is one of the easiest configurations you can do with Advertwhirl.

1. Set up your ads and your guest bloggers under Ad Sources.
1. Create a new campaign.
1. Add an ad source to the default allocation(Allocation 1).
1. Select your ad unit for one of the ad sources and your guest bloggers for the other.
1. Set the weights or percents to whatever the agreed upon share is.
1. Add a single rule set and a single author rule.
1. Select the author is and the author you wish to use this allocation for.
1. Add another allocation(Allocation 2)
1. Select your ad unit as the single ad source

Thats it.  Any posts written by the guest blogger will rotate ads based on the wieghts you configured.  Any other post will serve only your ad unit.  If you have multiple guest bloggers the process is the same.  Just add an allocation for each that is configured the same as Allocation 1, just change the weights, the authors ad unit and who the author is.  Make sure to leave the Allocation with no rules and only your Ad Unit as the last allocation.

= How focused can I target my ads to a particular visitor? =
The degree to which an Advertwhirl advertisement can be targeted to a particular visitor is dependent on the type of Ad unit served.  Adsense and Groupon both do their own user tracking and can pretty accurately target ads based on visitor.  Local banners, direct advertisers, and other affiliate ads can be selected based on the visitors location or the value of a cookie or get argument for external ads.

= How does Advertwhirl determine a visitor's location? =
Advertwhirl uses ipinfodb.com and the visitors ip address to determine location.  Geo location/GeoIP lookups from ipinfodb.com are free but require that you get a unique api key.  [Requesting an API Key](http://www.ipinfodb.com/register.php "IP Info DB Key Registration") is a simple and quick process, it should take less then 2 minutes of your time.  With the GeoIP data your ads can be targeted to visitors based on their country, state/region, postal code(or partial postal code) and city.  Great for focusing direct advertisements that are location specific.

ipinfodb.com GeoIP lookups also can provide location information for a visitors longitude, latitude and time zone.  Currently Advertwhirl doesn't allow you to target ads based on this, but it is a feature that may be added if users of Advertwhirl require it for managing their advertising campaigns.

= Where can I place my ads with Advertwhirl? =
With the Advertwhirl ad campaign manager you can place your ads anywhere in your blog you choose.

Without any modifications to your template you can place advertisements automatically in the header, footer, before the content, in the content or after the content of posts and pages.  If you want more control over where an ad is placed in a particular post/page you can use a short code in the built-in editor ie... [advertwhirl campaign=your_campaign].

You can use the Advertwhirl widget to place an advertisement anywhere you can place a WordPress sidebar widget.

Finally if you need precise control over ad placement Advertwhirl provides php functions you can call in any of your themes php template files for more direct management over where your advertisements are placed.  For instance you could replace the_content() in a theme's single.php file with the following code to automatically place ads in all of its blog posts.

<?php
    //the_content();
    $content = apply_filters('the_content', $post->post_content);  //get the post content store in $content
    $paragraphs = explode("</p>", $content);  //Separate the content into <p> blocks
    $tcount = 0;   //this is count for number of <p> blocks
    $adsEvery = 4; // How many <p> blocks between each of your ads.  The smaller the number the more ads, the larger the fewer
    $maxAds = 2;   // The maximum number of ads to put in a post.  There is no maximum if $maxAds is -1
    $adon = 0;     //this is a variable to keep track of how many ads have been put in the post
    echo '<div>';
    foreach($paragraphs as $paragraph) {
        if(preg_match('/<p> /',$paragraph) == 0 && $tcount % $adsEvery == 0 && ($adon < $maxAds || $maxAds == -1)){
            $float = $adon % 2 == 0?"float:left":"float:right";
            echo '<div style="width:300px;height:250px;' . $float . ';padding: 9px 9px 9px 9px;">';
            advertwhirl_print_ad('Your_Campaign');  // This is what places the Advertwhirl advertisement
            echo '</div>';
            $adon++;
        }
        echo $paragraph;  //print the <p> block
        echo "</p>";
        $tcount++;
    }
    echo '</div>';
?>

if you just want to place an ad before or after the post content you could use the much simplified code in single.php

<?php
    echo advertwhirl_get_ad('Your_Campaign');  // This advertisement will be before your post content
    the_content();
    $after_ad = advertwhirl_get_ad('Your_Campaign');  // This ad will be after your post content
	echo '<div>' . $after_ad . '</div>';
?>

the functions are 'advertwhirl_get_ad()' and 'advertwhirl_print_ad()'.  They both take a single argument, the name of the campaign you wish to allocate an ad from.  advertwhirl_get_ad() returns the content of the Ad Unit, but does not output it, you will need to save the content to a variablei(and do something with it) or echo it for the ad to actually be placed on the page.  advertwhirl_print_ad() outputs the ad directly to the output stream when it is called.  Calling advertwhirl_print_ad() will output the ad exactly where it is called while advertwhirl_get_ad() will give you the ad code but will leave the outputting of the ad code up to you.

The last way you can display ads for your advertising campaign is with a permalink.  The power of this last method may not be immediately obvious but can manage your ads and take control of your advertising revenue in a very meaningful way.  External advertisements via permalinks allows you to provide a URL to any content source outside of your blog and maintain control and manage how your ads appear on that content source. Become your own advertisement broker with Advertwhirl external ads.  Mobile Sentience uses Advertwhirl to serve its affiliate ads in all of its ad supported products allowing use to keep our ads fresh and relevant without the users of our products having to update versions or make any changes.  Real-time on the fly changing of your ads on sites outside of your blog.

Do you guest blog and get a share of the ad slots for your posts?  Advertwhirl external advertisements are for you.  Simply have the host blog/site pull your ads from the permalink for your guest blogging campaign and manage your own ads without having to wait for the host blogs admin to make changes.

Looking at having a smartphone app built for your site, or already have one?  Mobile Sentience is releasing advertisement panels for android and iOS soon.  Seamlessly integrate your external ads with your smartphone app and manage what ads are displayed on the fly direct from your WordPress admin panel.

If you need help creating a smartphone app for your blog [Mobile Sentience offers custom consulting services](http://www.mobilesentience.com/consulting/ "Custom Consulting by Mobile Sentience")

* Advertwhirl advertisement panel for Android Apps (coming May 15th)
* Advertwhirl advertisement panel for iOS Apps (coming June 2011)

= What kind of reporting does Advertwhirl have? =
Advertwhirl has real integration with Google Adsense and pulls down your Adsense reports and displays them in a dashboard widget.  Advertwhirl also collects statistics on the ads it displays, though they aren't currently reported that feature is being activily developed.  As is integration with Commission Junctions reporting api and Google Analytics.  Finally integration with Mobile Sentience's "Mobile Analytics" Android application is under active development providing a handy on stop place to get all your analytics on the go.

= Is there a version available which contains no sponsored ads? =
Mobile Sentience provides [custom consulting services](http://www.mobilesentience.com/consulting/ "Custom Consulting by Mobile Sentience").  Feel free to contact us to learn more about this.

== Screenshots ==
1. View ad campaigns
2. Edit ad campaign
3. View local ads
4. Edit local ad
5. Settings panel
6. Bug reporting and technical support

== Roadmap ==
= Version 1.0.13 (Scheduled for release May 22th) =
* Keyword advertising, automatically insert affiliate links that match keywords in your post.
* OS rule targets ads based on the visitors Operating System(Windows, MAC, Linux, Android, iphone/ios, etc...)
* User Agent rule targets ads based on the visitors browser(Chrome, Firefox, IE...)
* Search engine rule matches if the visitor came from a search engine
* Add Get code button if external ads are located to easily get the code needed to access a campaign from an external site
* Add Ad Source Groups to allow several advertisements to share a single weight(as in they total served ads of all advertisements in the ad group are counted together when determining weights for ad rotation)
* Add Advertwhirl Content Menu to simplify access
* Add a quick configuration wizard
* Export/Import Advertwhirl configurations

= Version 1.0.14 (Scheduled for release May 24th) =
* Customize ads with css and html before or after ad placement
* Formating for iframe wrapped advertisements
* Add TinyMCE ad widget to automate insertion of advertisements in posts
* New Banner Ad Source for speedy code free creation of local banner advertisements
* New Post Ad Source create your own advertisements using the post editor

= Version 1.0.15 (TBD) =
* New tool to track ad placement statistics
* Permalink for auotgenerated statistics xml file for easy importation in external apps
* Google Analytics Integration

= Version 1.0.16 (TBD) =
* Finish Groupon API integration to automatically pull down Groupon's newest advertisements
* Automatically selct Groupon Ad to run based on visitors location

= Version 1.0.17 (TBD) =
* Commission Junction Integration

= And soon your advertising campaigns will be easily available in smartphone apps. =
* Advertwhirl advertisement panel for Android Apps (coming May 25th, 2011)
* Advertwhirl advertisement panel for iOS Apps (coming June, 2011)

= Features =
* Ads can be allocated based on weight, percent or a mix.
* Fill empty ad slots with a default ad
* Define your own rules for which an advertisement is picked
* Advertwhirl Adsense integration imports both your reports and your ad units.
* Automatically inserts ads where you want them in posts and pages, complete with wrapping of post content around ads.

== Changelog ==
= 1.0.12 =
Bug fixes to support WordPress installs older than Version 3.0

= 1.0.11 =
Bug fixes to support WordPress installs older than Version 3.0

= 1.0.10 =
* Cookie rule targets ads based on the value of a cookie
* Custom field rule targets ads based on the value of a custom field in the post/page
* IP rule targets ads based on the visitors IP address
* Hostname rule targets ads based on the visitors hostname(reverse lookups must be enabled on your web server)
* Referrer rule targets ads based on the site that referred the visitor
* Ad size rule targets ads based on the requested ad size.

= 1.0.9 =
* Fixed bugs with auto placement of ad campaigns

= 1.0.8 =
* Fixed some bugs
* Added auto placement of ads to campaigns

= 1.0.7 =
* Fixed static bug for older php installs

= 1.0.6 =
* Fixed bug on settings page that spit out a lot of warnings
* Fixed bug that broke editing of allocations in campaigns tab.

= 1.0.5 =
* Can set the size of sponsor advertisements per Ad Slot/Campaign, so they match the other ads
* Automatic insertion of advertisements

= 1.0.4 =
* Bug fixs for Virtual Theme integration

= 1.0.3 =
* Fixed bug in shortcode's - removed debug code

= 1.0.2 =
* Added rule for matching post/page tags
* Added rule for matching post/page categories
* Added rule for matching [Virtual Theme](http://WordPress.org/extend/plugins/virtual-theme/ "Virtual Theme Plugin") virtual paths
* Added rule for matching the visitors location
* Fixed bug with allocations stats when an allocation has been moved up/down
* Finished sidebar widget
* Fixed IE CSS bug with rotated text

= 1.0.1 =
* Added missing files from release 1.0.0

= 1.0.0 =
* Initial release

== Upgrade Notice ==
= 1.0.12 =
Bug fixes to support WordPress installs older than Version 3.0

= 1.0.11 =
Bug fixes to support WordPress installs older than Version 3.0

= 1.0.10 =
Added new rules and a few minor bug fixes.

= 1.0.9 = 
* Recommended update - bug fixes.  See Known Issues for notes regarding advertwhirl_get_ad()

= 1.0.8 =
* Bug Fixes
* Auto placement of ads

= 1.0.7 =
* Bug Fixes

= 1.0.6 =
* Bug Fixes

= 1.0.5 =
* New features

= 1.0.4 =
* Bug Fixes

= 1.0.3 =
* Bug Fixes

= 1.0.2 =
* New matching rules
* Bug Fixes

= 1.0.1 =
* Fixes bug with missing libs/Version.php file.

== Support ==
If you have any problems or usage questions you can report them [here](http://www.mobilesentience.com/support/?mingleforumaction=viewforum&f=12.0 "Advertwhirl Support Forum")

== Tutorials ==
[Automatic Insertion of Advertisements in WordPress Posts](http://mobilesentience.com/?p=http://mobilesentience.com/?p=496)
