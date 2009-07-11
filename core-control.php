<?php
/*
Plugin Name: Core Control
Version: 0.8
Plugin URI: http://dd32.id.au/wordpress-plugins/?plugin=core-control
Description: Core Control is a set of plugin modules which can be used to control certain aspects of the WordPress control.
Author: Dion Hulse
Author URI: http://dd32.id.au/
*/

$GLOBALS['core-control'] = new core_control();
class core_control {
	
	var $dd32_requires = 5;
	var $basename = '';
	var $folder = '';
	var $version = '0.8';
	
	var $modules = array();
	
	function core_control() {
		//Set the directory of the plugin:
		$this->basename = plugin_basename(__FILE__);
		$this->folder = dirname($this->basename);

		//Set the version of the DD32 library this plugin requires.
		$GLOBALS['dd32_version'] = isset($GLOBALS['dd32_version']) ? max($GLOBALS['dd32_version'], $this->dd32_requires) : $this->dd32_requires;
		add_action('init', array(&$this, 'load_dd32'), 20);
		//load plugins after core class
		add_action('init', array(&$this, 'load_modules'), 25);

		//Register general hooks.
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		register_activation_hook(__FILE__, array(&$this, 'activate'));
		register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
		
	}
	
	function load_dd32() {
		//Load common library
		include 'inc/class.dd32.php';
	}
	
	function admin_init() {
		//Load any translation files needed:
		load_plugin_textdomain('core-control', '', $this->folder . '/langs/');

		//Register our JS & CSS
		//When i implement it and convert over.. yes.
		//wp_register_script('core-control', plugins_url( $this->folder . '/core-control.js' ), array('jquery'), $this->version);
		//wp_register_style ('core-control', plugins_url( $this->folder . '/core-control.css' ), array(), $this->version);

		DD32::add_configure($this->basename, __('Core Control', 'core-control'), admin_url('tools.php?page=core-control'));
		DD32::add_changelog($this->basename, 'http://svn.wp-plugins.org/core-control/trunk/readme.txt');

		//Add actions/filters
		add_action('admin_post_core_control-modules', array(&$this, 'handle_posts'));

		//Add page
		add_action('core_control-default', array(&$this, 'default_page'));
	}
	function admin_menu() {
		add_submenu_page('tools.php', __('Core Control', 'core-control'), __('Core Control', 'core-control'), 'administrator', 'core-control', array(&$this, 'main_page'));
	}

	function activate() {
		global $wp_version;
		if( ! version_compare( $wp_version, '2.7', '>=') ) {
			if( function_exists('deactivate_plugins') )
				deactivate_plugins(__FILE__);
			wp_die(__('<h1>Core Control</h1> Sorry, This plugin requires WordPress 2.7+', 'core-control'));
		}
	}
	
	function deactivate() {

	}

	function load_modules() {
		$modules = get_option('core_control-active_modules', array());
		foreach ( $modules as $module ) {
			include_once WP_PLUGIN_DIR . '/' . $this->folder . '/modules/' . $module;
			$class = basename($module, '.php');
			$this->modules[ $class ] = new $class;
		}
	}

	function is_module_active($module) {
		return in_array( $module, get_option('core_control-active_modules', array()) );
	}
	
	function handle_posts() {
		foreach ( (array)$_POST['checked'] as $module )
			if ( 0 !== validate_file($module) )
				wp_die('I dont trust you, That data looks malformed to me.');

		update_option('core_control-active_modules', (array)$_POST['checked']);
		wp_redirect( admin_url('tools.php?page=core-control') );
	}
	
	function main_page() {
		echo '<div class="wrap">';
		screen_icon('tools');
		echo '<h2>Core Control</h2>';
		
		$module = !empty($_GET['module']) ? $_GET['module'] : 'default';
		
		$menus = array( array('default', 'Main Page') );
		foreach ( $this->modules as $a_module ) {
			if ( ! $a_module->has_page() )
				continue;
			$menus[] = $a_module->menu();
		}
		echo '<ul class="subsubsub">';
		foreach ( $menus as $menu ) {
			$url = 'tools.php?page=core-control';
			if ( 'default' != $menu[0] )
				$url .= '&module=' . $menu[0];
			$title = $menu[1];
			$sep = $menu == end($menus) ? '' : ' | ';
			$current = $module == $menu[0] ? ' class="current"' : '';
			echo "<li><a href='$url'$current>$title</a>$sep</li>";
		}
		echo '</ul>';
		echo '<br class="clear" />';

		do_action('core_control-' . $module);

		echo '</div>';
	}
	function default_page() {
		$files = DD32::find_files( WP_PLUGIN_DIR . '/' . $this->folder . '/modules/', array('pattern' => '*.php', 'levels' => 1, 'relative' => true) );
?>
<p>Welcome to Core Control, Please select the subsection from the above menu which you would like to modify</p>
<p>You may Enable/Disable which modules are loaded by checking them in the following list:
<form method="post" action="admin-post.php?action=core_control-modules">
<table class="widefat">
	<thead>
	<tr>
		<th scope="col" class="check-column"><input type="checkbox" name="check-all" /></th>
		<th scope="col">Module Name</th>
		<th scope="col">Description</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach ( $files as $module ) {
			$details = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->folder . '/modules/' . $module, true, false);
			$active = $this->is_module_active($module);
			$style = $active ? ' style="background-color: #e7f7d3"' : '';
	?>
	<tr<?php echo $style ?>>
		<th scope="row" class="check-column"><input type="checkbox" name="checked[]" value="<?php echo attribute_escape($module) ?>" <?php if ( $active ) echo 'checked="checked"' ?> /></th>
		<td><?php echo $details['Title'] . ' ' . $details['Version'] ?></td>
		<td><?php echo $details['Description'] ?></td>
	</tr>
	<?php
		} //end foreach;
	?>
	</tbody>
</table>
<input type="submit" class="button-secondary" value="Save Module Choices" />
</p>
</form>
<?php
	}

}//end class

?>
