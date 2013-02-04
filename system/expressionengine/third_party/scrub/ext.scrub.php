<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Scrub Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Iain Urquhart
 * @link		
 */

class Scrub_ext {
	
	public $settings 		= array();
	public $description		= 'Clean your content.';
	public $docs_url		= '';
	public $name			= 'Scrub';
	public $settings_exist	= 'y';
	public $version			= '0.1';
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
		$this->_theme_base_url 	= defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES . 'scrub/' : $this->EE->config->item('theme_folder_url') . '/third_party/scrub/';
	}
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'entry_submission_ready',
			'hook'		=> 'entry_submission_ready',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);			
		
	}	

	// ----------------------------------------------------------------------

	/**
	* Settings Form
	*
	* @param   Array   Settings
	* @return  void
	*/
	function settings_form($current)
	{
		$this->EE->load->helper('form');
		$this->EE->load->library('table');
		$this->EE->load->model('field_model');
		$this->EE->cp->add_js_script('ui', 'sortable'); 
		
		// add our js/css
		$this->EE->cp->add_to_head('
			<link type="text/css" href="'.$this->_theme_base_url.'/css/roland.css" rel="stylesheet" />
			<script src="'.$this->_theme_base_url.'/js/jquery.roland.js"></script>
		');

		// setup some vars
		$vars = $vars['field_groups'] = array();

		// ---------------------------------
		// build our field selector options
		// ---------------------------------
		$vars['field_groups'][''] = lang('select_field');

		$field_groups = $this->EE->field_model->get_field_groups()->result_array();

		foreach($field_groups as $field_group)
		{
			$fg_fields = $this->EE->field_model->get_fields( $field_group['group_id'] )->result_array();
			foreach($fg_fields as $field)
			{
				$vars['field_groups'][ $field_group['group_name'] ][ $field['field_id'] ] 
					= '('.$field_group['group_name'].') '.$field['field_label'];
			}
		}
		// ---------------------------------

		// misc assets/classes required
		$vars['drag_handle'] = '&nbsp;';
		$vars['nav'] = '<a class="remove_row" href="#">-</a> <a class="add_row" href="#">+</a>';
		$vars['roland_template'] = array(
				'table_open'		=> '<table class="mainTable roland_table" border="0" cellspacing="0" cellpadding="0">',
				'row_start'			=> '<tr class="row">',
				'row_alt_start'     => '<tr class="row">'
		);

		$vars['tidy_options'] = array(
			'' 		=> 'None',
			'1' 	=> 'Two spaces per indent',
			'1t1' 	=> 'A tab per indent, and a tab of leading indent',
			'-1'	=> 'Compact'
		);

		$vars['settings'] = $current;

		return $this->EE->load->view('settings', $vars, TRUE);
	}


	// ----------------------------------------------------------------------

	/**
	 * Save Settings
	 *
	 * This function provides a little extra processing and validation
	 * than the generic settings form.
	 *
	 * @return void
	 */
	function save_settings()
	{

		if (empty($_POST))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		unset($_POST['submit']);

		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize($_POST)));

		$this->EE->session->set_flashdata(
			'message_success',
			$this->EE->lang->line('preferences_updated')
		);

	}

	// ----------------------------------------------------------------------
	
	/**
	 * entry_submission_ready
	 *
	 * @param 
	 * @return 
	 */
	public function entry_submission_ready($meta, $data, $autosave)
	{

		// get our settings
		$qry = $this->EE->db->get_where('extensions', array('class' => __CLASS__), 1)->row();

		$settings = ($qry->settings != '') ? unserialize($qry->settings) : array();

		if(isset($settings['fields']) && count($settings['fields']))
		{
			// access our entry data by reference
			$this->data =& $this->EE->api_channel_entries->data;

			// load our lib
			require_once PATH_THIRD.'scrub/libraries/htmLawed'.EXT;

			// load up our fields table to figure out what type of fields we're filtering
			$channel_fields = $this->EE->db->get('channel_fields')->result_array();
			$types = array();
			
			foreach($channel_fields as $field)
			{
				$types[ $field['field_id'] ] = $field['field_type'];
			}

			

			// go through each field's settting and filter the data
			foreach($settings['fields'] as $setting)
			{

				// make sure our field exists
				if( isset($this->data[ 'field_id_'.$setting['field_id'] ]) )
				{

					// prep our htmLawed config array
					$config = array(
						'safe' => (isset($setting['filter']['safe'])) ? $setting['filter']['safe'] : 0,
						'tidy' => (isset($setting['filter']['tidy'])) ? $setting['filter']['tidy'] : '',
						'comment' => (isset($setting['filter']['remove_comments'])) ? $setting['filter']['remove_comments'] : 0,
						'deny_attribute' => (isset($setting['filter']['deny_attributes'])) ? $setting['filter']['deny_attributes'] : '',
						'elements' => (isset($setting['filter']['elements'])) ? $setting['filter']['elements'] : '',
						'keep_bad' => 0
					);

					// stoopid workaround for EL RTE field
					if($config['tidy'] && $types[ $setting['field_id'] ] == 'rte')
					{
						$config['tidy'] = '';
					}

					// filter our data through htmLawed
					$data = htmLawed($this->data[ 'field_id_'.$setting['field_id']], $config);

					// strip out empty paras
					if(isset($setting['filter']['remove_empty_paras']))
					{	
						// regex empty paras out
						$data = preg_replace('~<p>\s*<\/p>~i','',$data);
						// and those pesky nbsp paras
						$data = str_replace('<p>&nbsp;</p>', '', $data);
					}

					$this->data['field_id_'.$setting['field_id']] = 
						$this->data['revision_post']['field_id_'.$setting['field_id']] = $data;

				}
				

			}
		}
		
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.scrub.php */
/* Location: /system/expressionengine/third_party/scrub/ext.scrub.php */