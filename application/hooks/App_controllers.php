<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function load_app_controllers()
{
  spl_autoload_register('custom_controllers');
}

function custom_controllers($class)
{
	if (strpos($class, 'CI_') !== 0)
	{
		if (is_readable(APPPATH . 'core/' . $class . '.php'))
		{
		  require_once(APPPATH . 'core/' . $class . '.php');
		}
	}
}