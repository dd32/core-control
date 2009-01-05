<?php
/*
Plugin Name: WP-Cron Module
Version: 0.7-dev
Description: Core Control Cron module, This allows you to manually run WordPress Cron Jobs and to diagnose Cron issues.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

class core_control_cron {

	function core_control_cron() {
		add_action('core_control-cron', array(&$this, 'the_page'));

		$_SERVER['REQUEST_URI'] = remove_query_arg(array('module_action', 'job'));
		
		$this->settings = array();

		$this->settings = get_option('core_control-cron', $this->settings);

		add_action('admin_post_core_control-cron', array(&$this, 'handle_posts'));

	}

	function has_page() {
		return true;
	}

	function menu() {
		return array('http', 'WP-Cron');
	}
	

	function handle_posts() {
		$option =& $this->settings;
		
		$module_action = isset($_REQUEST['module_action']) ? $_REQUEST['module_action'] : '';
		$transport = isset($_REQUEST['transport']) ? $_REQUEST['transport'] : '';
		switch ( $module_action ) {
			case 'disabletransport':
				$option[ $transport ]['enabled'] = false;
				break;
			case 'enabletransport':
				$option[ $transport ]['enabled'] = true;
				break;
		}
		
		update_option('core_control-cron', $option);
		wp_redirect(admin_url('options-general.php?page=core-control&module=cron'));
	}
	
	function the_page() {
		echo '<h3>WordPress Cron Jobs</h3>';
		echo '<div style="margin-left: 2em; margin-bottom: 3em;">';
			
		echo '<table class="widefat">';
		echo '<col style="text-align: left" width="20%" />
			  <col width="10%" />
			  <col style="text-align:left" />
			  <thead>
			  <tr>
			  	<th>Transport</th>
				<th>Status</th>
				<th>Actions</th>
				<th></th>
			  </tr>
			  </thead>
			  <tbody>
			  ';

		$primary_get = WP_Http::_getTransport();
		$primary_get_nonblocking = WP_Http::_getTransport(array('blocking' => false));
		$primary_post = WP_Http::_postTransport();
		$primary_post_nonblocking = WP_Http::_postTransport(array('blocking' => false));
		foreach ( array('primary_get', 'primary_post', 'primary_get_nonblocking', 'primary_post_nonblocking') as $var )
			$$var = strtolower(get_class(${$var}[0]));
		
		foreach ( array('exthttp' => 'PHP HTTP Extension', 'curl' => 'cURL', 'streams' => 'PHP Streams', 'fopen' => 'PHP fopen()', 'fsockopen' => 'PHP fsockopen()' ) as $transport => $text ) {
			$class = "WP_Http_$transport";
			$class = new $class;
			$useable = $class->test();
			$disabled = $this->settings[$transport]['enabled'] === false;
			$colour = $useable ? '#e7f7d3' : '#ee4546';
			if ( $useable && $disabled ) {
				$colour = '#e7804c';
			}
			
			$status = $disabled ? 'Disabled' : ($useable ? 'Available' : 'Not Available');
			
			//This may look messy, But it works well and doesnt mean too many IF branches
			$extra = '';
			foreach ( array('primary_get', 'primary_post', 'primary_get_nonblocking', 'primary_post_nonblocking') as $var ) {
				if ( strtolower("WP_Http_$transport") == $$var ) {
					$var = substr($var, 8);
					$extra .= 'Primary ';
					if ( 'g' == $var{0} )
						$extra .= 'GET';
					else
						$extra .= 'POST';
					if ( strpos($var, '_') )
						$extra .= '(non-blocking)';
					$extra .= '<br />';
				}
			}

			echo '<tr style="background-color: ' . $colour . ';">';
				echo '<th style="text-shadow: none !important;">' . $text . '</th>';
				echo '<td>' . $status . '</td>';
				echo '<td>';
				if ( $useable ) {
					$actions = array();
					if ( $disabled )
						$actions[] = '<a href="admin-post.php?action=core_control-http&module_action=enabletransport&transport=' . $transport . '">Enable Transport</a>';
					else
						$actions[] = '<a href="admin-post.php?action=core_control-http&module_action=disabletransport&transport=' . $transport . '">Disable Transport</a>';
					$actions[] = '<a href="' . add_query_arg(array('module_action' => 'testtransport', 'transport' => $transport)) . '">Test Transport</a>';
					
					echo implode(' | ', $actions);
				}
				echo '</td>';
				echo '<td>' . $extra . '</td>';
			echo '</tr>';
			//Do the testing.
			if ( isset($_GET['module_action']) && 'testtransport' == $_GET['module_action'] && $transport == $_GET['transport'] ) {
				echo '<tr><td colspan="4" style="background-color: #fffeeb;">';
					echo "<p>Please wait...</p>";
					$url = 'http://tools.dd32.id.au/wordpress/core-control.php';
					$result = $class->request($url, array('timeout' => 10));
					if ( is_wp_error($result) ) {
						echo '<p><strong>An Error has occured:</strong> ' . $result->get_error_message() . '</p>';
					} elseif ( '1563' === $result['body'] ) { //1563 is just a random number which was chosen to indicate successful retrieval
						printf('<p>Successfully retrieved &amp; verified document from %s</p>', $url);
					} else {
						printf('<p>Whilst an error was not returned, The server returned an unexpected result: <em>%s</em>, HTTP result: %s %s', htmlentities($result['body']), $result['response']['code'], $result['response']['message']);
					}
				echo '</td></tr>';
			}
		}
		echo '</tbody></table>';
		
		echo '</div>';
	}
}
?>