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
	

	const EARTH_RADIUS = 20900000; //feet
	private function distance($lat1, $lng1, $lat2, $lng2){
		return M_PI * self::EARTH_RADIUS * acos(
			  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng1 - $lng2))
			+ sin(deg2rad($lat1)) * sin(deg2rad($lat2)) 
		);
	}

	const MAX_DIST = 90; //feet
	public function event() {;
		if ($_GET["_domain"] == "geopuzzle" && $_GET["_name"] == "locate") {
			$username = $_GET["username"];
			$lat = $_GET["lat"];
			$lng = $_GET["lng"];
			
			$row = $this->db->query(""
				+ "SELECT u.number, c.lat, c.lng, c.altquestion, nc.question, nc.id \n"
				+ "FROM user u, clue c \n"
				+ "LEFT OUTER JOIN clue nc ON (nc.id = c.nextclueid) \n"
				+ "WHERE c.id = u.currentclueid \n"
				+ "AND u.username = ? \n"
			, array($username))->row();
			if (self::MAX_DIST >= distance($lat, $lng, $row->lat, $row->lng)) {
				$message = sprintf("You made it! %s", $username);
				if($row->altquestion) {
					$message = sprintf("%s\n%s", $message, $row->altquestion);
				} else {
					if($row->question) {
						$message = sprintf("%s\nYour next clue: %s", $message, $row->question);
						$this->db->query("UPDATE user SET currentclueid = ?", array($row->nextclueid));
					} else {
						$message = sprintf("%s You're all done. Thanks for playing!", $message);
					}
				}
				sendSMS($message, $row-number);
			}
		} else if($_GET["_domain"] == "geopuzzle" && $_GET["_name"] == "answer") {
			$number = $_GET["From"];
			$body = $_GET["Body"];
			
			//trim number to something we can use (no symbols, exclude country code)
			$number = preg_replace("/\D/", "", $number);
			while(strlen($number) > 10) {
				$number = substr($number, 1);
			}
			
			$row = $this->db->query(""
				+ "SELECT c.answer, nc.question, nc.id \n"
				+ "FROM user u, clue c \n"
				+ "LEFT OUTER JOIN clue nc ON (nc.id = c.nextclueid) \n"
				+ "WHERE c.id = u.currentclueid \n"
				+ "AND u.number = ? \n"
			, array($username))->row();
			if (strcasecmp($body, $row->answer) == 0) {		//Correct answer. Process next question
				$message = "Correct!";
				if($row->question) {	//If there is another question, send it
					$message = sprintf("%s\nYour next clue: %s", $message, $row->question);
					$this->db->query("UPDATE user SET currentclueid = ?", array($row->nextclueid));
				} else {
					$message = sprintf("%s You're all done. Thanks for playing!", $message);
				}
				sendSMS($message, $number);
			} else {	//Wrong answer
				$message = "That's not quite right. Try again!";
				sendSMS($message, $number);
			}
		} else {
			echo "Event not understood";
		}
	}

	private function sendSMS($message, $number) {
		//TODO: implement
	}
}

/* End of file app.php */
/* Location: ./application/controllers/app.php */
