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

	/**
	 * Main page; user can enter phone number.
	 */
	public function index() {
		$this->template->page("home_view");
	}

	/**
	 * Trim phone to something we can use (no symbols, exclude country code).
	 */
	private function trimphone($phone) {
		$phone = preg_replace("/\D/", "", $phone);
		while(strlen($phone) > 10) {
			$phone = substr(1, $phone);
		}
	}

	/**
	 * Make a POST request here to start.
	 */
	public function start() {
		$phone = $this->trimphone($this->input->post("phone"));
		
		// create user
		$this->db->query("INSERT OR IGNORE INTO user VALUES (?,1)", array($phone));
		
		// send SMS with clue
		$row = $this->db->query(""
			+ "SELECT clue FROM clue\n"
			+ "  WHERE id = (SELECT currentclueid FROM user WHERE phone = ?)\n"
			, array($phone)
		)->row();
		$this->sendSms($phone, $row->clue);
		
		// go to mobile app
		redirect(sprintf("/mobile#%s", $phone));
	}

	/**
	 * Computes great-circle distance between coordinates
	 */
	const EARTH_RADIUS = 20900000; //feet
	private function distance($lat1, $lng1, $lat2, $lng2){
		return M_PI * self::EARTH_RADIUS * acos(
			  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng1 - $lng2))
			+ sin(deg2rad($lat1)) * sin(deg2rad($lat2)) 
		);
	}

	/**
	 * Receives events.
	 */
	const MAX_DIST = 90; //feet
	public function event() {;
		// geopuzzle:locate
		if ($_GET["_domain"] == "geopuzzle" && $_GET["_name"] == "locate") {
			$username = $_GET["username"];
			$lat = $_GET["lat"];
			$lng = $_GET["lng"];
			
			$row = $this->db->query(""
				+ "SELECT name, lat, lng, clue FROM clue\n"
				+ "  WHERE id = (SELECT currentclueid FROM user WHERE user = ?)\n"
				, array($username)
			)->row();
			
			if (self::MAX_DIST >= $this->distance($lat, $lng, $row->lat, $row->lng)) {
				$message = sprintf("You made it!\n%s", $row->question);
				sendSMS($message, $row->phone);
			}
		}
		// undefined
		else {
			echo "Event not understood";
		}
	}
	
	/**
	 * SMS 
	 */
	public function smsEvent() {
		$phone = $_GET["From"];
		$body = $_GET["Body"];
	
		$message = "";
		// help
		if (strcasecmp($body, "help me") == 0) {
			$row = $this->db->query(""
				+ "SELECT hint FROM clue\n"
				+ "  WHERE id = (SELECT currentclueid FROM user WHERE phone = ?)\n"
				, array($phone)
			)->row();
			if ($row->hint != NULL) {
				$message = $row->hint;
			} else {
				$message = "No hint available. Sorry.";
			}
		}
		// answer
		else {
			$row = $this->db->query(""
				+ "SELECT answer, nextclueid FROM clue\n"
				+ "  WHERE id = (SELECT currentclueid FROM user where phone = ?)\n"
				, array($phone)
			)->row();
			
			// correct
			if (strcasecmp($body, $row->answer) == 0) {
				$this->db->query("UPDATE user SET currentclueid = ?", array($row->nextclueid));
				if($row->nextclueid != NULL) {
					$row = $this->db->query(""
						+ "SELECT clue FROM clue WHERE id = ?"
						, array($row->nextclueid)
					)->row();
					$message = sprintf("Correct! Next clue %s", $row->clue);
				} else {
					$message = "Correct! You're all done. Thanks for playing!";
				}
			}
			// incorrect
			else {
				$message = "That's not quite right. Try again!";
			}
		}
		$this->sendSMS($message, $phone);
	}

	/**
	 * Send SMS message, with twilio.
	 */
	private function sendSMS($message, $to) {
		$this->load->library('twilio');

		$from = '0000000000';

		$response = $this->twilio->sms($from, $to, $message);

		if($response->IsError)
			echo 'Error: ' . $response->ErrorMessage;
		else
			echo 'Sent message to ' . $to;
	}
}

/* End of file app.php */
/* Location: ./application/controllers/app.php */
