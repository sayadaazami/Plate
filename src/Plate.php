<?php
namespace Plate;

use Plate\Exception\CharIsNotValid;
use Plate\Exception\StateIsNotValid;
use Plate\Exception\CityNotFound;
use Plate\Exception\PlateIsNotValid;
use Plate\FarsiGD;

class Plate{
	private $_plate = null;
	private $_parsed = null;
	private $_data = null;
	private $_suportedChars = null;
	private $_farsiGD = null;
	private $_underLineChars = null;


	public function __construct(){
		$config = config('plate');
		$this->_data = $config['state_data'];
		$this->_suportedChars = $config['supported_chars'];
		$this->_underLineChars = $config['under_line_chars'];
		$this->_farsiGD = new FarsiGD();
	}

	private function parse(){
		preg_match($this->getRegEx(), $this->_plate, $matchs);

		$stateNumber = $matchs[4];
		$stateName = $this->_getStateNameByNumber($stateNumber);
		$character = $matchs[2];

		$this->_parsed = [
			'cityName'			=>	$this->_getCityNameByCharAndNumber($stateName, $character, $stateNumber),
			'type'				=>	$this->_getTypeByChar($character),
			'char'				=>	$character,
			'2DigitNumber'		=>	$matchs[1],
			'3DigitNumber'		=>	$matchs[3],
			'countryName'		=>	$matchs[5],
			'stateNumber'		=>	$stateNumber,
			'stateName'			=>	$stateName,
		];
	}

	private function _getTypeByChar($char){
		if (!array_key_exists($char, $this->_suportedChars)) {
			throw new CharIsNotValid("This Char Is Not Valid");
		}

		return $this->_suportedChars[$char];
	}

	private function _getStateNameByNumber($number){
		foreach ($this->_data as $stateName => $numbers) {
			if (array_key_exists($number, $numbers)) {
				return $stateName;
			}
		}

		throw new StateIsNotValid("There Is Not Any State With This Number");
	}

	private function _getCityNameByCharAndNumber($state, $char, $number){
		if(empty($this->_data[$state][$number][$char][0])){
			throw new CityNotFound("There Is Not Any City With This Information");
		}

		return implode(', ', $this->_data[$state][$number][$char]);
	}

	public function setPlate($plate){
		$this->_plate = $plate;
		$this->validate();
		$this->parse();
		return $this;
	}

	public function getRegEx(){
		$farsiChars = implode('|', array_keys($this->_suportedChars));
		return "/([1-9]\d{1})\s+({$farsiChars})\s+([1-9]\d{2})\s+\-\s+([1-9]\d{1})\s+(ایران)/";
	}

	public function validate($plate = null, $softCheck = false){
		if (empty($plate)){
			$plate = $this->_plate;
		}

		preg_match($this->getRegEx(), $plate, $matchs);

		if (count($matchs) !== 6) {
			if($softCheck) {
				return false;
			} else {
				throw new PlateIsNotValid("Plate Number Is Not Valid");
			}
		}
	}

	public function getparsedData(){
		return $this->_parsed;
	}

	public function getCountry(){
		return $this->_parsed['countryName'];
	}

	public function getCity(){
		return $this->_parsed['cityName'];
	}

	public function getState(){
		return $this->_parsed['stateName'];
	}

	public function getType(){
		return $this->_parsed['type'];
	}

	public function getStateNumber(){
		return $this->_parsed['stateNumber'];
	}

	public function get2DigitNumber(){
		return $this->_parsed['2DigitNumber'];
	}

	public function get3DigitNumber(){
		return $this->_parsed['3DigitNumber'];
	}

	public function isCab(){
		return $this->_parsed['type'] === 'تاکسی' ? true : false;
	}

	public function getImage($exportPath){
		$data = $this->getparsedData();
		$resourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR;
		$im = imagecreatefrompng($resourcePath . 'plate.png');
		$font = $resourcePath . 'BTraffic.ttf';
		$color = imagecolorallocate($im, 20, 20, 20);
		
		$this->_gdDrawText($data['stateNumber'], $im, $font, 48, $color, 268, 20); // draw state number
		$underlineChars = ['','','','','','','','','','','','','','','',''];

		$charLen = mb_strlen($data['char']);
		$fontSize = $charLen == 3 ? 53 : 54;
		$fontSizeChar = $charLen == 3 ? 12 : 7;
		$y = $charLen == 3 ? 5 : 4;
		$yChar = in_array($data['char'], $this->_underLineChars) ? $y - 4 : $y + ($charLen == 3 ? 12 : 9);
		$x1 = $charLen == 3 ? 35 : 43;
		$x2 = $x1 + ($charLen == 3 ? 54 : 58);
		$x3 = $x2 + ($charLen == 3 ? 80 : 65);
		$this->_gdDrawText($data['2DigitNumber'], 	$im, $font, $fontSize, $color, $x1, $y, false); // draw number
		$this->_gdDrawText($data['char'], 			$im, $font, $fontSize - $fontSizeChar, $color, $x2, $yChar); // draw number
		$this->_gdDrawText($data['3DigitNumber'], 	$im, $font, $fontSize, $color, $x3, $y, false); // draw number

		imagepng($im, $exportPath);
		imagedestroy($im);
	}

	private function _gdDrawText($text, $im, $font, $fontSize, $color, $x=0, $y=0, $rtl=true){
		if($rtl){
			$text = $this->_farsiGD->persianText($text, 'fa', 'normal');
		}

		$y += $fontSize;
		imagettftext($im, $fontSize, 0, $x, $y , $color, $font, $text);
	}
}