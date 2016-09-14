<?php
	namespace Plate;
	
	use Plate\Exception\CharIsNotValid;
	use Plate\Exception\StateIsNotValid;
	use Plate\Exception\CityNotFound;
	use Plate\Exception\PlateIsNotValid;

	class Plate{
		private $_plate = null;
		private $_parsed = null;
		private $_data = null;
		private $_suportedChars = null;


		public function __construct($plate){
			$config = config('plate');
			$this->_data = $config['state_data'];
			$this->_suportedChars = $config['supported_chars'];
			$this->setPlate($plate);
		}


		private function parse(){
			preg_match($this->getRegEx(), $this->_plate, $matchs);

			$stateName = $this->_getStateNameByNumber($matchs[5]);
			$this->_parsed = [
				'cityName'			=>	$this->_getCityNameByCharAndNumber($stateName, $matchs[2], $matchs[5]),
				'type'				=>	$this->_getTypeByChar($matchs[2]),
				'char'				=>	$matchs[2],
				'2DigitNumber'		=>	$matchs[1],
				'3DigitNumber'		=>	$matchs[3],
				'countryName'		=>	$matchs[4],
				'stateNumber'		=>	$matchs[5],
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

			return $this->_data[$state][$number][$char][0];
		}

		public function setPlate($plate){
			$this->_plate = $plate;
			$this->validate();
			$this->parse();
		}

		public function getRegEx(){
			$farsiChars = implode('|', array_keys($this->_suportedChars));
			return "/([1-9]\d)({$farsiChars})([1-9]\d{2})(ایران)([1-9]\d)/";
		}

		public function validate($plate = null, $softCheck = false){
			if (empty($plate)){
				$plate = $this->_plate;
			}

			preg_match($this->getRegEx(), $plate, $matchs);

			if (count($matchs) !== 6) {
				if($softCheck)
					return false;
				else
					throw new PlateIsNotValid("Plate Number Is Not Valid");
			}
		}

		public function parsedData(){
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
	}