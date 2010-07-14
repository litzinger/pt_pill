<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


if (! defined('PT_SWITCH_VER'))
{
	// get the version from config.php
	require PATH_THIRD.'pt_pill/config.php';
	define('PT_SWITCH_VER',  $config['version']);
}


/**
 * P&T Pill Fieldtype Class for EE2
 *
 * @package   P&T Pill
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2010 Pixel & Tonic, LLC
 */
class Pt_pill_ft extends EE_Fieldtype {

	var $info = array(
		'name'    => 'P&amp;T Pill',
		'version' => PT_SWITCH_VER
	);

	/**
	 * Fieldtype Constructor
	 */
	function Pt_pill_ft()
	{
		parent::EE_Fieldtype();

		/** ----------------------------------------
		/**  Prepare Cache
		/** ----------------------------------------*/

		if (! isset($this->EE->session->cache['pt_pill']))
		{
			$this->EE->session->cache['pt_pill'] = array('includes' => array());
		}
		$this->cache =& $this->EE->session->cache['pt_pill'];
	}

	// --------------------------------------------------------------------

	/**
	 * Theme URL
	 */
	private function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = $this->EE->config->item('theme_folder_url');
			if (substr($theme_folder_url, -1) != '/') $theme_folder_url .= '/';
			$this->cache['theme_url'] = $theme_folder_url.'third_party/pt_pill/';
		}

		return $this->cache['theme_url'];
	}

	/**
	 * Include Theme CSS
	 */
	private function _include_theme_css($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().$file.'" />');
		}
	}

	/**
	 * Include Theme JS
	 */
	private function _include_theme_js($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().$file.'"></script>');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Insert JS
	 */
	private function _insert_js($js)
	{
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field Settings
	 */
	function display_settings($data)
	{
		// load the language file
		$this->EE->lang->loadfile('pt_pill');

		$options = isset($data['options']) ? $data['options'] : array();

		$this->EE->table->add_row(
			lang('pt_pill_options', 'pt_pill_options') . '<br />'
			. lang('field_list_instructions') . '<br /><br />'
			. lang('option_setting_examples'),

			'<textarea id="pt_pill_options" name="pt_pill_options" rows="6">'.$this->_options_setting($options).'</textarea>'
		);
	}

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		// load the language file
		$this->EE->lang->loadfile('pt_pill');

		$options = isset($data['options']) ? $data['options'] : array();

		return array(array(
			lang('pt_pill_options'),
			'<textarea class="matrix-textarea" name="options" rows="4">'.$this->_options_setting($options).'</textarea>'
		));
	}

	/**
	 * Options Setting Value
	 */
	private function _options_setting($options)
	{
		$r = '';

		foreach($options as $name => $label)
		{
			if ($r !== '') $r .= "\n";
			$r .= $name;
			if ($name != $label) $r .= ' : '.$label;
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field Settings
	 */
	function save_settings($data)
	{
		$post = $this->EE->input->post('pt_pill_options');

		return array(
			'options' => $this->_save_options_setting($post)
		);
	}

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		$settings['options'] = $this->_save_options_setting($settings['options']);

		return $settings;
	}

	/**
	 * Save Options Setting
	 */
	private function _save_options_setting($options = '')
	{
		$r = array();

		$options = preg_split('/[\r\n]+/', $options);
		foreach($options as &$option)
		{
			$option_parts = preg_split('/\s:\s/', $option, 2);
			$option_name  = (string) trim($option_parts[0]);
			$option_value = isset($option_parts[1]) ? (string) trim($option_parts[1]) : $option_name;

			$r[$option_name] = $option_value;
		}

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 */
	function display_field($data, $cell = FALSE)
	{
		$this->_include_theme_css('styles/pt_pill.css');
		$this->_include_theme_js('scripts/pt_pill.js');

		$field_name = $cell ? $this->cell_name : $this->field_name;
		$field_id = str_replace(array('[', ']'), array('_', ''), $field_name);

		if (! $cell)
		{
			$this->_insert_js('new ptPill(jQuery("#'.$field_id.'"));');
		}

		return form_dropdown($field_name, $this->settings['options'], $data, 'id="'.$field_id.'"');
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		$this->_include_theme_js('scripts/matrix2.js');

		return $this->display_field($data, TRUE);
	}
}
