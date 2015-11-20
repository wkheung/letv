<?php
 
class User_Service extends LETV_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }
    
    public function login($name, $password)
    {
        $user = $this->user_model->get_user_by_where($name, $password);
        return $user;
    }
    
}