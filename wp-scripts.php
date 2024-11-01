<?php
/*
Plugin Name: WP-Scripts
Version: 1.1
Plugin URI: http://takuhii.x10hosting.com/
Description: load popular javascripts on blog head using 'wp_head'. this contains prototype, mootools, scriptaculous, fat, behaviour, sweetTitles, lightbox. you can select script on option panel. Javascripts can be compressed by <a href="http://www.phpclasses.org/browse/package/3158.html">JsComppressor</a>.
Author: Darren Mackintosh
Author URI: http://takuhii.x10hosting.com/
*/
/* Library
jscompressor : http://www.phpclasses.org/browse/package/3158.html
/* Included scripts
prototype.js : http://prototype.conio.net/
scriptaculous : scriptaculous.js, effects.js, slider.js, dragdrop.js(http://script.aculo.us/)
mootools : http://mootools.net/
fat.js : fade anything(http://www.axentric.com/aside/fat/)
behaviour.js : http://bennolan.com/behaviour/
sweetTitles.js : http://www.dustindiaz.com/
lightbox.js: http://www.huddletogether.com/projects/lightbox2/
litebox.js: http://www.doknowevil.net/litebox/
*/

if(!class_exists('wpInsertScripts')) :
class wpInsertScripts {
	var $options;
	var $pluginURL;
	var $version = '1.0';
	var $error;
	
	function wpInsertScripts() {
		if(!get_option('wp_scripts')) {
			$op['selected_js'] = 'mootools.addons';
			add_option('wp_scripts', $op);
		}
		if(isset($_GET['activate']) && $_GET['activate'] == 'true') 
			add_action('init', array(&$this, '_setup'));
		$this->initOptions();
		$this->pluginURL = get_settings('siteurl') . '/wp-content/plugins/wp-scripts';
		add_action('admin_menu', array(&$this, 'add_page'));
		add_action('wp_head', array(&$this, 'wp_head_js'),0);
		if(in_array('lightbox', $this->options['js_set']) || in_array('litebox', $this->options['js_set'])) 
			add_filter('the_content', array(&$this, 'lightbox_filter'));
	}

	function initOptions() {
		$this->options = get_settings('wp_scripts');
		$this->options['js_set'] = ($this->options['baselib'] == 'mootools')?get_settings('wp_scripts_moo'):get_settings('wp_scripts_proto');
		$this->options['other_list'] = get_settings('wp_scripts_other');
		//php5 fix
		if(!is_array($this->options['js_set'])) $this->options['js_set'] = array();
		if(!is_array($this->options['other_list'])) $this->options['other_list'] = array();
	}

	function _setup(){
		if(!get_settings('wp_scripts')) { 
			$op = array();
			$op['jscomp'] = 'none';
			$op['baselib'] = 'none';
			add_option('wp_scripts', $op);
		}
		if(!get_settings('wp_scripts_moo')) add_option('wp_scripts_moo', array());
		if(!get_settings('wp_scripts_proto')) add_option('wp_scripts_proto', array());
		if(!get_settings('wp_scripts_other')) add_option('wp_scripts_other', array());
	}

	function option_page() {
		load_plugin_textdomain('wp-scripts', '/wp-content/plugins/wp-scripts/lang');
		$op = get_option('wp_scripts');
		if(isset($_POST['update_options'])) {
			$op['jscomp'] = trim($_POST['jscomp']);
			$op['baselib'] = trim($_POST['baselib']);
			$this->options = $op;//reload options
			update_option('wp_scripts', $op);
			if(isset($_POST['moo_list'])) update_option('wp_scripts_moo', $_POST['moo_list']);
			if(isset($_POST['proto_list']))update_option('wp_scripts_proto', $_POST['proto_list']);
			update_option('wp_scripts_other', $_POST['other_list']);
			$this->initOptions();
			echo '<div class="updated"><p>'.__('Options saved.').'</p></div>';
			if($op['jscomp'] !== 'none') {
				$_write_javascripts = $this->_writeJs();
				if($_write_javascripts !== 0) echo '<div class="updated"><p>'.__('Updated compressed javascript', 'wp_scripts').'</p></div>';
				else echo '<div class="updated"><p>'.$this->error.'</p></div>';
			}
		}
		$jschecked = $this->get_selected_js();
?>
<div class="wrap"> 
  <h2>Wp-Scripts</h2>
  <form name="wp_scripts_option" method="post" action=""> 
	<input type="hidden" name="action" value="update" />

		<fieldset class="options">
			<table width="100%" cellspacing="2" cellpadding="5" class="editform" id="optiontable"> 
      <tr valign="top">
        <th width="13%" scope="row"><?php _e('Base:', 'wp-scripts'); ?></th> 
          <td colspan="4"><select name="baselib">
          <option value="none"<?php if ($op['baselib'] =='none') { ?> selected="selected"<?php } ?>><?php _e('None', 'wp-scripts'); ?></option>
          <option value="mootools"<?php if ($op['baselib'] =='mootools') { ?> selected="selected"<?php } ?>>MooTools</option>
          <option value="prototype"<?php if ($op['baselib'] == 'prototype') { ?> selected="selected"<?php } ?>>Prototype</option>
          </select></td>
      </tr>
      <tr valign="top" style="color:#888;">
        <th width="13%" scope="row">&mdash;</th> 
        <td colspan="4"><?php _e('Select javascript library you want to use.', 'wp-scripts'); ?><br />
			Prototype.js : <a href="http://prototype.conio.net/">http://prototype.conio.net/</a><br />
		  mootools.js : <a href="http://mootools.net/">http://mootools.net/</a> (Moo.js, Native, Addons, Window.js)</td> 
      </tr>
<?php if($op['baselib'] == 'prototype') { ?>
      <tr valign="top">
        <th width="13%" scope="row">Scriptaculous:</th> 
        <td colspan="4"><input name="proto_list[]" id="scriptaculous" type="checkbox" value="scriptaculous" <?php checked(true, $jschecked['scriptaculous']); ?> /> -  
		  <label for="effects">scriptaculous.js</label> (only select this if you need the enitre Scriptaculous Framework loaded.)</td> 
      </tr>
      
      <tr valign="top">
        <td rowspan="2" align="center" style="font-size: 12px; color: #999;">(only select these if you require individual elements of the Scriptaculous Framework)</td>
        <td width="21%"><input name="proto_list[]" id="effects" type="checkbox" value="effects" <?php checked(true, $jschecked['effects']); ?> /> -  
		  <label for="effects">effects.js</label></td> 
        <td width="22%"><input name="proto_list[]" id="slider" type="checkbox" value="slider" <?php checked(true, $jschecked['slider']); ?> /> -
		  <label for="slider">slider.js</label></td> 
        <td width="22%"><input name="proto_list[]" id="dragdrop" type="checkbox" value="dragdrop" <?php checked(true, $jschecked['dragdrop']); ?> /> -  
		  <label for="dragdrop">dragdrop.js</label></td> 
        <td width="22%"><input name="proto_list[]" id="builder" type="checkbox" value="builder" <?php checked(true, $jschecked['builder']); ?> /> -  
		  <label for="builder">builder.js</label></td> 
      </tr>
      <tr valign="top">
        <td><input name="proto_list[]" id="controls" type="checkbox" value="controls" <?php checked(true, $jschecked['controls']); ?> /> -  
		  <label for="controls">controls.js</label></td> 
        <td><input name="proto_list[]" id="unittest" type="checkbox" value="unittest" <?php checked(true, $jschecked['unittest']); ?> /> -  
		  <label for="unittest">unittest.js</label></td> 
        <td><input name="proto_list[]" id="lightbox" type="checkbox" value="lightbox" <?php checked(true, $jschecked['lightbox']); ?> /> -
		  <label for="lightbox">lightbox.js</label></td>
		<td style="font-size: 12px; color: #999;">(lightbox will only work if you select the Scriptaculous Framework, or select the effects.js)</td>
      </tr>
      <tr valign="top">
        <th width="13%" scope="row">&mdash;</th> 
        <td colspan="4" style="color:#888;">scriptaculous.js : <a href="http://script.aculo.us/">http://script.aculo.us/</a><br />
				lightbox.js : <a href="http://www.huddletogether.com/projects/lightbox2/">http://www.huddletogether.com/projects/lightbox2/</a></td> 
      </tr>
<?php } elseif ($op['baselib'] == 'mootools') { ?>
      <tr valign="top">
        <th width="13%" scope="row">Mootools:</th> 
        <td colspan="4"><input name="moo_list[]" id="litebox" type="checkbox" value="litebox" <?php checked(true, $jschecked['litebox']); ?> /> -  
		  <label for="litebox">litebox.js</label></td>
			</tr>
      <tr valign="top">
        <th width="13%" scope="row">&mdash;</th> 
        <td colspan="4" style="color:#888;"><?php _e('You can use these scripts with mootools as plugin', 'wp-scripts'); ?><br />
				litebox.js : <a href="http://www.doknowevil.net/litebox/">http://www.doknowevil.net/litebox/</a></td> 
      </tr>
<?php } ?>
      <tr valign="top">
        <th width="13%" scope="row"><?php _e('Others:', 'wp-scripts'); ?></th> 
        <td><input name="other_list[]" id="fat" type="checkbox" value="fat" <?php checked(true, $jschecked['fat']); ?> /> -
		  <label for="fat">fat.js</label></td> 
				<td><input name="other_list[]" id="behaviour" type="checkbox" value="behaviour" <?php checked(true, $jschecked['behaviour']); ?> /> -  
		  <label for="behaviour">behaviour.js</label></td> 
        <td><input name="other_list[]" id="sweetTitles" type="checkbox" value="sweetTitles" <?php checked(true, $jschecked['sweetTitles']); ?> /> -  
		  <label for="sweetTitles">sweetTitles.js</label></td> 
        <td><input name="other_list[]" id="site" type="checkbox" value="site.custom" <?php checked(true, $jschecked['site.custom']); ?> /> -  
		  <label for="site">your custom</label></td> 
      </tr>
      <tr valign="top" style="color:#888;">
        <th width="13%" scope="row">&mdash;</th> 
        <td colspan="4">
				fat.js : <a href="http://www.axentric.com/aside/fat/">http://www.axentric.com/aside/fat/</a><br />
				behaviour.js : <a href="http://ripcord.co.nz/behaviour/">http://ripcord.co.nz/behaviour/</a><br />
				sweetTitles.js : <a href="http://www.dustindiaz.com/">http://www.dustindiaz.com/</a><br />
				site.custom.js : <a href="<?php bloginfo('home'); ?>">Your custom</a> (<?php _e('Edit site.custom.js file', 'wp-scripts'); ?>)<br />				</td> 
      </tr>
			<tr valign="top">
				<th width="13%" scope="row"><?php _e('Compress:', 'wp-scripts'); ?></th>
        <td colspan="4"><select name="jscomp">
          <option value="none"<?php if ($op['jscomp'] == 'none') { ?> selected="selected"<?php } ?>>
		  <?php _e('None', 'wp-scripts'); ?></option>
          <option value="standard"<?php if ($op['jscomp'] == 'standard') { ?> selected="selected"<?php } ?>>
		  <?php _e('Standard', 'wp-scripts'); ?></option>
          <option value="high"<?php if ($op['jscomp'] == 'high') { ?> selected="selected"<?php } ?>>
		  <?php _e('High', 'wp-scripts'); ?></option>
          </select> - ( <?php _e('Select javascript compress method', 'wp-scripts') ?> )</td> 
			</tr>
		</table>
		</fieldset>
    <p class="submit">
    <input type="submit" name="update_options" value="<?php _e('Update Options'); ?> &raquo;" />
    </p>
  </form> 
</div>
<?php
	}

	function get_selected_js() {
		$jschecked = array();
		$lists = array_merge($this->options['js_set'], $this->options['other_list']);
		foreach($lists as $js) {
			$jschecked[$js] = true;
		}
		return $jschecked;
	}

	function add_page()	{
		if (function_exists('add_options_page')) {
			add_options_page("Wp-Scripts", "Wp-Scripts", 9, __FILE__, array(&$this, 'option_page'));
		}
	}
	
	function wp_head_js() {
		$r = "\t".'<!-- Added by Wp-Scripts '.$this->version.' -->'."\n";
		$before = "\t".'<script type="text/javascript" src="'.$this->pluginURL.'/';
		$after = '"></script>'."\n";
		if(in_array('lightbox', $this->options['js_set']) || in_array('litebox', $this->options['js_set'])) {
			$r .= "\t".'<link rel="stylesheet" type="text/css" media="screen" href="'.$this->pluginURL.'/css/lightbox.css" />'."\n";
		}
		if(in_array('sweetTitles', $this->options['other_list'])) {
			$r .= "\t".'<link rel="stylesheet" type="text/css" media="screen" href="'.$this->pluginURL.'/css/sweetTitles.css" />'."\n";
		}
		if($this->options['jscomp'] == 'none') {
			if($this->options['baselib'] !== 'none') {
				$r .= $before.'js/base/'.$this->options['baselib'].'.js'.$after;
				$r .= ($this->options['baselib'] == 'prototype')?$before.'js/base/selector.js'.$after:'';
				foreach($this->options['js_set'] as $js) {
					if('' !== $js) {
						$r .= $before.'js/rel_'.$this->options['baselib'].'/'.$js.'.js'.$after;
					}
				}
			}
			foreach($this->options['other_list'] as $other) {
				if('' !== $other) {
					$r .= $before.'js/'.$other.(($this->options['baselib'] !== 'none' && $other == 'sweetTitles')?'_'.$this->options['baselib']:'').'.js'.$after;
				}
			}
		} else {
			$r .= $before.'cache/wp-scripts.js'.$after;
		}
		$r .= "\n";
		echo $r;
	}

	function _writeJs($comp=false) {
		$files = array();
		$baseFile = '';
		$path = realpath(dirname(__FILE__));
		$data_otherjs = '';
		$data_basejs = '';
		if(!class_exists('JavaScriptCompressor')){
			require_once($path."/lib/JavaScriptCompressor.class.php");
		}
		$jscomp = new JavaScriptCompressor();

		if($this->options['baselib'] !== 'none') {
			$baseFile = file_get_contents($path.'/js/base/'.$this->options['baselib'].'.js');
			foreach($this->options['js_set'] as $js) {
				if('' !== $js) $files[] = file_get_contents($path.'/js/rel_'.$this->options['baselib'].'/'.$js.'.js');
			}
		}
		foreach($this->options['other_list'] as $other) {
			if('' !== $other) $files[] = file_get_contents($path.'/js/'.$other.(($this->options['baselib'] !=='none' && $other == 'sweetTitles')?'_'.$this->options['baselib']:'').'.js');
		}
		switch($this->options['jscomp']) {
			case 'standard':
				$data_basejs .= ($baseFile !== '')?$jscomp->getClean(array($baseFile)):'';
				$data_otherjs .= $jscomp->getClean($files);
			break;
			case 'high': 
			default:
				$data_basejs .= ($baseFile !== '')?$jscomp->getPacked(array($baseFile)):'';
				$data_otherjs .= $jscomp->getPacked($files);
			break;
		}
		$data_basejs .= ($this->options['baselib'] == 'prototype')?"\n".file_get_contents($path.'/js/base/selector.js'):'';
		$data = $data_basejs."\n".$data_otherjs;
		$cache_file = $path.'/cache/wp-scripts.js';
		$fp = @fopen( $cache_file, 'w' );
		if ( ! $fp ) {
			$this->error = "Cache unable to open file for writing: $cache_file";
		  return 0;
		}
		fwrite($fp, $data);
		fclose($fp);
		return $cache_file;
	}

	//powered by WP lightbox 2 (http://zeo.unic.net.my/2006/03/29/lightbox-js-version-20/)
	function lightbox_filter($the_content) {
		global $post;
		$preg = '/(<a(.*?)href="([^"]*.)(bmp|gif|jpeg|jpg|png)"(.*?)><img)/ie';
		$repl = '(strstr("\2\5","rel=") ? "\1" : "<a\2href=\"\3\4\"\5 rel=\"lightbox['.$post->ID.']\"><img")';
		return preg_replace($preg,$repl,$the_content);
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new wpInsertScripts();
		}
		return $instance[0];
	}

}//end of class
endif;

$WpScripts =& wpInsertScripts::get_instance();
?>
