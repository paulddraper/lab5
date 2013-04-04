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

	private function distance($lat1, $lng1, $lat2, $lng2, $miles = true)
	{
		$pi80 = M_PI / 180;
		$lat1 *= $pi80;
		$lng1 *= $pi80;
		$lat2 *= $pi80;
		$lng2 *= $pi80;

		$r = 6372.797; // mean radius of Earth in km
		$dlat = $lat2 - $lat1;
		$dlng = $lng2 - $lng1;
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$km = $r * $c;

		return ($miles ? ($km * 0.621371192) : $km);
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
}

/* End of file app.php */
/* Location: ./application/controllers/app.php */