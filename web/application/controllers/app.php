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
	}

	/**
	 * Main page; user can enter phone number.
	 */
	public function index() {
		$this->template->page('login');
	}

	/**
	 * Trim phone to something we can use (no symbols, exclude country code).
	 */
	private function trimphone($phone) {
		$phone = preg_replace('/\D/', '', $phone);
		if (strlen($phone) == 10) {
			$phone = '1' . $phone;
		}
		return $phone;
	}
	
	private function getFile($phone) {
		return "application/db/$phone.txt";
	}
	
	private function persist($phone, $currentclueid) {
		file_put_contents($this->getFile($phone), $currentclueid);
	}
	
	private function load($phone) {
		return intval(file_get_contents($this->getFile($phone)));
	}

	/**
	 * Make a POST request here to start.
	 */
	const START_CLUE_ID = 1;
	public function start() {
		$phone = $this->trimphone($_POST['phone']);
		
		// create user
		$this->persist($phone, self::START_CLUE_ID);
		
		// send SMS with clue
		$row = $this->db->query(
			'SELECT clue FROM clue WHERE id = ?'
			, array(self::START_CLUE_ID)
		)->row();
		$this->sendSms($row->clue, $phone);
		
		// go to mobile app
		redirect("../mobile?userName=$phone");
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
	const MAX_DIST = 200; //feet
	public function event() {
		//file_put_contents('log.txt', implode('|',$_GET)."\n", FILE_APPEND);

		// geopuzzle:locate
		if ($_GET['_domain'] == 'geopuzzle' && $_GET['_name'] == 'locate') {
			$phone = $_GET['userName'];
			$lat = $_GET['lat'];
			$lng = $_GET['lng'];

			$row = $this->db->query(
				'SELECT name, lat, lng, question, nextclueid FROM clue WHERE id = ? AND id <> -1'
				, array($this->load($phone))
			)->row();
			
			$dist = $this->distance($lat, $lng, $row->lat, $row->lng);
			if (self::MAX_DIST >= $dist) {
				$message = "You made it!\n$row->question";
				$this->sendSMS($message, $phone);
				$this->persist($phone, $row->nextclueid);
			}
			
		}
		// undefined
		else {
			send('Event not understood');
		}
		send('OK');
	}
	
	/**
	 * SMS 
	 */
	public function smsEvent() {
		//file_put_contents('log.txt',implode('|',$_POST)."\n", FILE_APPEND);
	
		$phone = $this->trimphone($_POST['From']);
		$body = $_POST['Body'];

		$message = '';
		// help
		if (strcasecmp($body, 'hint') == 0) {
			$row = $this->db->query(''
				. 'SELECT hint FROM clue WHERE id = ?'
				, array($this->load($phone))
			)->row();
			if ($row->hint != NULL) {
				$message = $row->hint;
			} else {
				$message = 'No hint available. Sorry.';
			}
		}
		// answer
		else {
			$nextclueid = $this->load($phone);
			$row = $this->db->query(
				'SELECT answer FROM clue WHERE nextclueid = ?'
				, array($nextclueid)
			)->row();
			
			file_put_contents('log.txt',$row->answer."\n", FILE_APPEND);
			// correct
			if (strcasecmp($body, $row->answer) == 0) {
				if($nextclueid != -1) {
					$row = $this->db->query(''
						. 'SELECT clue FROM clue WHERE id = ?'
						, array($nextclueid)
					)->row();

					$message = "Correct! Next clue: $row->clue";
				} else {
					$message = 'Correct! You\'re all done. Thanks for playing!';
				}
			}
			// incorrect
			else {
				$message = 'That\'s not quite right. Try again!';
			}
		}
		$this->sendSMS($message, $phone);
	}

	/**
	 * Send SMS message, with twilio.
	 */
	private function sendSMS($message, $to) {
		$this->load->library('twilio');

		$from = '13212521456';

		$response = $this->twilio->sms($from, $to, $message);

		if($response->IsError) {
			echo "Error: $response->ErrorMessage";
		}
		//else file_put_contents('log.txt', "$message\n", FILE_APPEND);	
	}
}

/* End of file app.php */
/* Location: ./application/controllers/app.php */
