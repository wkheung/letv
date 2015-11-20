<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends LETV_CMSController {
	
	public function index()
	{
		$this->load->view('cms/login');
	}
}
