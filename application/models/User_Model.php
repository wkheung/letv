<?php
 
class User_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function get_user_by_where($name, $password)
    {
		$sql = 'select * from user';
		$query = $this->db->query($sql);
		return $query->result_array();
        //return array('id' => 1, 'name' => 'mckee');
    }
}