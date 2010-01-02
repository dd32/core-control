<?php
/*
Plugin Name: HTTP Access Module
Version: 0.9.2
Description: Core Control HTTP module, This allows you to Enable/Disable the different HTTP Access methods which WordPress 2.7+ supports
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

class core_control_http {

	function core_control_http() {
		add_action('core_control-http', array(&$this, 'the_page'));
		
		$this->settings = array('curl' => array('enabled' => true), 'streams' => array('enabled' => true), 'fopen' => array('enabled' => true), 'fsockopen' => array('enabled' => true), 'exthttp' => array('enabled' => true));

		$this->settings = get_option('core_control-http', $this->settings);

		foreach ( $this->settings as $transport => $opts )
			$this->settings[$transport]['filter'] = "use_{$transport}_transport";
		$this->settings['exthttp']['filter'] = 'use_http_extension_transport';
		
		//if ( ! has_filter('use_exthttp_transport') ) //If the function's not deprecated, we'll hook on the old one. see WP#8772
		//	$this->settings['exthttp']['filter'] = 'use_http_extension_transport';

		add_action('admin_post_core_control-http', array(&$this, 'handle_posts'));

		foreach ( $this->settings as $transport => $options )
			if ( $options['enabled'] === false )
				add_filter($options['filter'], array(&$this, 'disable_transport'));
	}

	function has_page() {
		return true;
	}

	function menu() {
		return array('http', 'External HTTP Access');
	}
	
	function disable_transport() {
		return false;
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
		
		update_option('core_control-http', $option);
		wp_redirect(admin_url('tools.php?page=core-control&module=http'));
	}
	
	function the_page() {
		echo '<h3>Manage Transports</h3>';
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
			
			//Before we test, we need to remove any filters we've loaded.
			$filtered = has_filter($this->settings[$transport]['filter'], array(&$this, 'disable_transport'));
			if ( $filtered )
				remove_filter($this->settings[$transport]['filter'], array(&$this, 'disable_transport'));
			$useable = $class->test();
			if ( $filtered )
				add_filter($this->settings[$transport]['filter'], array(&$this, 'disable_transport'));
			$disabled = $this->settings[$transport]['enabled'] === false;
			$colour = $useable ? '#e7f7d3' : '#ee4546';
			if ( $useable && $disabled )
				$colour = '#e7804c';
			
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