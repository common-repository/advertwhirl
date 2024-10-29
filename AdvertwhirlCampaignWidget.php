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

require_once("libs/Version.php");

/**
  * AdvertwhirlCampaignWidget - Sideboard widget to display an Ad Campaign
  */
class AdvertwhirlCampaignWidget extends WP_Widget {
	/** constructor */
	function AdvertwhirlCampaignWidget() {
		parent::WP_Widget(false, $name = 'Advertwhirl Campaign Widget');	
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {		
		require_once('Advertwhirl.php');
		extract( $args );
		echo $before_widget;
		echo advertwhirl_get_ad($instance['adcampaign']);
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['adcampaign'] = $new_instance['adcampaign'];

		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {
		global $wp_version;
		global $advertwhirl_options_name;
		$this->options = maybe_unserialize(get_option($advertwhirl_options_name));

		$defaults = array('adcampaign' => '');
		$instance = wp_parse_args((array) $instance, $defaults);
		$campaign = esc_attr($instance['adcampaign']);

		?>
<p>
<label for="<?php echo $this->get_field_id('adcampaign');?>">Ad Campaign:</label> 
	<select class="widefat" name="<?php echo $this->get_field_name('adcampaign'); ?>" id="<?php echo $this->get_field_id('adcampaign'); ?>">
		<option value="">Select ad campaign</option>
<?php 
	foreach($this->options['adcampaigns'] as $key => $value){
		$selected = $key == $campaign?"selected":"";
		echo '		<option value="' . esc_attr($key) .'" ' . $selected . '>' . $key . ' - ' . $value['description'] . '</option>';
	}
?>
	</select>
</p>
<?php 
	}

} // class AdvertwhirlCampaignWidget 
