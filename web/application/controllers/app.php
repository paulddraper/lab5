<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class App extends CI_Controller {

	private $user = NULL;

	public function __construct()
	{
		date_default_timezone_set('America/Denver');
	    parent::__construct();
	    $this->load->helper('url'); 
	    $this->load->library('session');
	    $this->load->library('template');
	    $this->load->database();
		// $this->load->helper("crud_helper");
		$this->user = $this->user();
	}

	public function index()
	{
		if($this->authenticate()){
			// echo json_encode($this->user);
			$this->template->page("home_view");
		}
	}

	public function logout(){
		$this->session->unset_userdata('username');
		redirect("/app");
		// echo "YOU ARE LOGGED OUT";
	}

	public function login($username = NULL){
		if(!$username) $username = $this->input->post("username");
		$user = $this->user($username);
		if($user){
			$this->session->set_userdata("username", $username);
			redirect("/");
		}
		else {
			$this->template->page("login_view");
		}
	}

	private function authenticate(){
		$loggedIn = $this->user != NULL;
		if(!$loggedIn){
			redirect("/app/login");
		}
		return $loggedIn;
	}

	private function user($username = NULL){
		if(!$username) $username = $this->session->userdata('username');
		$query = $this->db->query("SELECT * FROM user WHERE username = ?", array($username));
		return $query->row();
	}
	

	define("EARTH_RADIUS", 20900000) //feet
	private function distance($lat1, $lng1, $lat2, $lng2){
		return M_PI * EARTH_RADIUS * acos(
			  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng1 - $lng2))
			+ sin(deg2rad($lat1)) * sin(deg2rad($lat2)) 
		);
	}

	public function event() {
		if(this->input->get("_domain") == "geopuzzle" && this->input->get("_name") == "locate"){
			$username = 
			$row = $this->db->query("SELECT lat, lng, text, answer, nextclueid from clue c join user u where username = ?", array($username))->row()

		}
	}
}

//this->input->get("userid")

/* End of file app.php */
/* Location: ./application/controllers/app.php */
