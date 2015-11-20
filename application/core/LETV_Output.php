<?php
/**
 * PHP Codeigniter Simplicity
 *
 *
 * Copyright (C) 2013  John Skoumbourdis.
 *
 * GROCERY CRUD LICENSE
 *
 * Codeigniter Simplicity is released with dual licensing, using the GPL v3 and the MIT license.
 * You don't have to do anything special to choose one license or the other and you don't have to notify anyone which license you are using.
 * Please see the corresponding license file for details of these licenses.
 * You are free to use, modify and distribute this software, but all copyright information must remain.
 *
 * @package    	Codeigniter Simplicity
 * @copyright  	Copyright (c) 2013, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 * @version    	0.6
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 */
class LETV_Output extends CI_Output {
	const OUTPUT_MODE_NORMAL = 10;
	const OUTPUT_MODE_TEMPLATE = 11;
	const TEMPLATE_ROOT = "layouts/";
	
	private $_template = null;
	private $_mode = self::OUTPUT_MODE_NORMAL;

	/**
	 * Set the  template that should be contain the output <br /><em><b>Note:</b> This method set the output mode to MY_Output::OUTPUT_MODE_TEMPLATE</em>
	 *
	 * @uses MY_Output::set_mode()
	 * @param string $template_view
	 * @return void
	 */
	function set_template($template_view){
		$this->set_mode(self::OUTPUT_MODE_TEMPLATE);
		$template_view = str_replace(".php", "", $template_view);
		$this->_template = self::TEMPLATE_ROOT . $template_view;
	}

	/**set_mode alias
	 *
	 * Enter description here ...
	 */
	function unset_template()
	{
		$this->_template = null;
		$this->set_mode(self::OUTPUT_MODE_NORMAL);
	}

	/**
	 * Sets the way that the final output should be handled.<p>Accepts two possible values 	MY_Output::OUTPUT_MODE_NORMAL for direct output
	 * or MY_Output::OUTPUT_MODE_TEMPLATE for displaying the output contained in the specified template.</p>
	 *
	 * @throws Exception when the given mode hasn't defined.
	 * @param integer $mode one of the constants MY_Output::OUTPUT_MODE_NORMAL or MY_Output::OUTPUT_MODE_TEMPLATE
	 * @return void
	 */
	function set_mode($mode){

		switch($mode){
			case self::OUTPUT_MODE_NORMAL:
			case self::OUTPUT_MODE_TEMPLATE:
				$this->_mode = $mode;
				break;
			default:
				throw new Exception(get_instance()->lang->line("Unknown output mode."));
		}

		return;
	}

	/**
	 * (non-PHPdoc)
	 * @see system/libraries/CI_Output#_display($output)
	 */
	function _display($output=''){

		if($output=='')
			$output = $this->get_output();

		switch($this->_mode){
			case self::OUTPUT_MODE_TEMPLATE:
				$output = $this->get_template_output($output);
				break;
			case self::OUTPUT_MODE_NORMAL:
			default:
				$output = $output;
				break;
		}

		parent::_display($output);
	}

	private function get_template_output($output){

		if(function_exists("get_instance") && class_exists("CI_Controller")){
			$ci = get_instance();
			
			$data["output"] = $output;
			$data["ci"]		= &get_instance();

			$output = $ci->load->view($this->_template, $data, true);
		}

		return $output;
	}
}