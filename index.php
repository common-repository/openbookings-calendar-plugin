<?php
/*
Plugin Name: OpenBookings Calendar
Plugin URI: http://wordpress.org/extend/plugins/OpenBookings_Calendar/
Description: A simple to show OpenBookings calendar.
Version: 1.0
Author: eGwada
Author URI: http://egwada.fr
*/
if (!class_exists('OpenBookings')) {
	class OpenBookings {
		var $name = 'OpenBookings';
		var $tag = 'openbookings';
		var $options = array();
		var $year, $start, $end, $table, $gite;
		function OpenBookings()
		{
			global $wpdb;
			$this->table = $wpdb->prefix.$this->tag;
			if ($options = get_option($this->tag)) {
				$this->options = $options;
				$this->start = $this->year = gmdate('Y', current_time('timestamp'));
				$this->end = $this->start + $this->options['years'] - 1;
			}
			if (isset($_GET['y']) && is_numeric($_GET['y'])) {
				if ($_GET['y'] <= $this->start) {
					$this->year = $this->start;
				} else if ($_GET['y'] >= $this->end) {
					$this->year = $this->end;
				} else {
					$this->year = $_GET['y'];
				}
			}
			if (is_admin()) {
				register_activation_hook(__FILE__, array(&$this, 'activate'));
			} else {
				add_action('get_header', array(&$this, 'css'));
				add_shortcode($this->tag, array(&$this, 'shortcode'));
//				add_filter('OpenBookings_cal', array(&$this, 'dropdown'), 1, -1);
			}			
		}
		function activate()
		{
			if (!isset($this->options['years'])) {
				$this->options['years'] = 5;
			}
			update_option($this->tag, $this->options);
		}
		function css()
		{
			global $post;
			if ($post && strstr($post->post_content, '['.$this->tag.' gite')) {
				wp_enqueue_style(
					$this->tag,
					WP_PLUGIN_URL.'/'.$this->tag.'/style.css',
					false,
					false,
					'screen'
				);
			}
		}
		function shortcode($atts)
		{
			global $gite;
			extract(shortcode_atts(array(
				'display' => false, 'gite' => -1
			), $atts));
			switch ($display) {
				default:					
					return $this->output('calendar.php');
				break;
			}
		}
		function booked()
		{
			global $wpdb, $gite;
		
			$sql = "SELECT `book_id`, book_start, book_end,".
				" DATE_FORMAT(`book_start`,'%Y') start_year,".
				" DATE_FORMAT(`book_start`,'%j') start_day,".
				" DATE_FORMAT(`book_end`,'%Y') end_year,".
				" DATE_FORMAT(`book_end`,'%j') end_day".
				" FROM  `rs_data_bookings`".
				" WHERE book_start > DATE_SUB( CURDATE( ) , INTERVAL 31 DAY )".
				" AND `validated`=1".
				" AND `object_id`=".$gite.
				" order by start_year, start_day";

  			$result = $wpdb->get_results($sql);
  			$booked = array();
			if (count($result) > 0) {
				foreach ($result AS $date) {
					//DEBUG echo "<p><b>Book</b> ".$date->book_id;
					if ($date->start_year == $date->end_year) // même année
					{
						//DEBUG echo "meme annee ".$date->start_day." ".$date->end_day." start :".$date->book_start." end :".$date->book_end;
						for ($i=intval($date->start_day); $i<=$date->end_day; $i++)
						{
							$booked[$date->start_year][$i]=$date->book_id;
							//DEBUG echo "<br>ADD ".$i;
						}
					}
					else
					{
						//DEBUG echo "DIFF annee ".$date->start_day." ".$date->end_day." start :".$date->book_start." end :".$date->book_end;
						$dernierjour = date('z', mktime(0, 0, 0, 12, 31, $date->start_year))+1;
						for ($i=intval($date->start_day); $i<=$dernierjour; $i++)
						{
							$booked[$date->start_year][$i]=$date->book_id;
						}
						for ($i=1; $i<=$date->end_day; $i++)
						{
							$booked[$date->end_year][$i]=$date->book_id;
						}
					}
				}
			}
			return $booked;
		}

		function output($file)
		{
			global $booked;
			ob_start();
			$booked = $this->booked();
			include(basename($file));
			$content = ob_get_contents(); ob_end_clean();
			return $content;
		}
	}
	$OpenBookings = new OpenBookings();
	if (isset($OpenBookings)) {
		function OpenBookings_cal($b='',$a='',$d=true, $e) {
			return apply_filters('OpenBookings_cal', array('before'=>$b,'after'=>$a,'display'=>$d, 'gite'=>$e));
		}
	}
}
