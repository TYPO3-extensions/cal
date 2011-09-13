<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
 * (http://evangelize.org). The WEC is developing TYPO3-based
 * (http://typo3.org) free software for churches around the world. Our desire
 * is to use the Internet to help offer new life through Jesus Christ. Please
 * see http://WebEmpoweredChurch.org/Jesus.
 *
 * You can redistribute this file and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This file is distributed in the hope that it will be useful for ministry,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the file!
 ***************************************************************/

/**
 * date parser
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */

class tx_cal_dateParser {

	var $tokenString = '';
	var $mode = -1; //0 = string, 1 = number, 2 = range
	var $stack = array();
	
	var $day = 0;
	var $week = 0;
	var $weekday = -1;
	var $month = 0;
	var $year = 0;
	var $special = '';
	
	var $conf;

	function dateParser(){
	}

	function parse($value,$conf=array()){
		$this->conf = $conf;
		for ($i=0;$i<strlen($value);$i++) {
			$chr = $value{$i};

			switch ($chr){
				case ' ':
				case '_':
				case '.':
				case ':':
				case ',':
				case '/':
					if($this->tokenString!=''){
						if($this->mode==0){
							$this->_parseString($this->tokenString);
						}else{
							$this->_parseNumber($this->tokenString);
						}
						$this->tokenString = '';
					}
					$this->mode=-1;
					break;
				case '-':
				case '+':
					if($this->mode==-1){
						$this->mode=2;
						array_push($this->stack,array('?',$chr));
					}else{
						$this->_parseString($this->tokenString);
						$this->tokenString = '';
						$this->mode=0;
					}
					break;
				case '0':
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
				case '6':
				case '7':
				case '8':
				case '9':
					if ($this->mode==1){
						$firstPart = array_pop($this->stack);
						$firstPart = array_pop($firstPart);
						$this->_parseNumber($firstPart.$chr);
					}else if($this->mode==2){
						$firstPart = array_pop($this->stack);
						$firstPart = array_pop($firstPart);
						array_push($this->stack,array('range'=>intval($firstPart.$chr)));
					}else{
						$this->_parseNumber($chr);
					}
					$this->mode = 1;
					$this->tokenString = '';
					break;
				case 'A':
				case 'B':
				case 'C':
				case 'D':
				case 'E':
				case 'F':
				case 'G':
				case 'H':
				case 'I':
				case 'J':
				case 'K':
				case 'L':
				case 'M':
				case 'N':
				case 'O':
				case 'P':
				case 'Q':
				case 'R':
				case 'S':
				case 'T':
				case 'U':
				case 'V':
				case 'W':
				case 'X':
				case 'Y':
				case 'Z':
				case 'a':
				case 'b':
				case 'c':
				case 'd':
				case 'e':
				case 'f':
				case 'g':
				case 'h':
				case 'i':
				case 'j':
				case 'k':
				case 'l':
				case 'm':
				case 'n':
				case 'o':
				case 'p':
				case 'q':
				case 'r':
				case 's':
				case 't':
				case 'u':
				case 'v':
				case 'w':
				case 'x':
				case 'y':
				case 'z':
					if($this->mode==1){
						$this->_parseString($this->tokenString);
						$this->tokenString = '';
					}
					$this->mode = 0;
					$this->tokenString .= $chr;
					break;
				default:
					break;
			}
		}
		
		if($this->tokenString!=''){
			if($this->mode==0){
				$this->_parseString($this->tokenString);
			}else{
				$this->_parseNumber($this->tokenString);
			}
		}

	}
	
	function _parseNumber($num){
		$number = intval($num);
		if ($number > 31){
			array_push($this->stack,array('year' => $number));
			return;
		}
		if ($number > 12){
			array_push($this->stack,array('day' => $number));
			return;
		}
		array_push($this->stack,array('?' => $number));
	}
	
	function _parseString($value){
		$value = strtolower($value);
		switch ($value){
			case 'last':
				array_push($this->stack,array('range' => 'last'));
				break;
			case 'next':
				array_push($this->stack,array('range' => 'next'));
				break;
			case 'now':
			case 'today':
				array_push($this->stack,array('value' => strtotime($value)));
				break;
			case 'day':
			case 'days':
				array_push($this->stack,array('value' => 86400));
				break;
			case 'week':
			case 'weeks':
				array_push($this->stack,array('value' => 604800));
				break;
			case 'month':
			case 'months':
				array_push($this->stack,array('value' => 'month'));
				break;
			case 'year':
			case 'years':
				array_push($this->stack,array('value' => 'year'));
				break;
			case 'mon':
			case 'monday':
				array_push($this->stack,array('weekday' => 1));
				break;
			case 'tue':
			case 'tuesday':
				array_push($this->stack,array('weekday' => 2));
				break;
			case 'wed':
			case 'wednesday':
				array_push($this->stack,array('weekday' => 3));
				break;
			case 'thu':
			case 'thursday';
				array_push($this->stack,array('weekday' => 4));
				break;
			case 'fri':
			case 'friday':
				array_push($this->stack,array('weekday' => 5));
				break;
			case 'sat':
			case 'saturday':
				array_push($this->stack,array('weekday' => 6));
				break;
			case 'sun':
			case 'sunday';
				array_push($this->stack,array('weekday' => 0));
				break;
			case 'jan':
			case 'january':
				array_push($this->stack,array('month' => 1));
				break;
			case 'feb':
			case 'february':
				array_push($this->stack,array('month' => 2));
				break;
			case 'mar':
			case 'march':
				array_push($this->stack,array('month' => 3));
				break;
			case 'apr':
			case 'april':
				array_push($this->stack,array('month' => 4));
				break;
			case 'may':
				array_push($this->stack,array('month' => 5));
				break;
			case 'jun':
			case 'june':
				array_push($this->stack,array('month' => 6));
				break;
			case 'jul':
			case 'july':
				array_push($this->stack,array('month' => 7));
				break;
			case 'aug':
			case 'august':
				array_push($this->stack,array('month' => 8));
				break;
			case 'sep':
			case 'september':
				array_push($this->stack,array('month' => 9));
				break;
			case 'oct':
			case 'october':
				array_push($this->stack,array('month' => 10));
				break;
			case 'nov':
			case 'november':
				array_push($this->stack,array('month' => 11));
				break;
			case 'dec':
			case 'december':
				array_push($this->stack,array('month' => 12));
				break;
			default:
				break;
		}
	}
	
	function getDateObjectFromStack(){
		$date = new tx_cal_date();
		$date->setMinute(0);
		$date->setSecond(0);
		$date->setHour(0);
		$date->setTZbyId('UTC');
		$lastKey = '';
		$post = array();
		$foundMonth = false;
		$foundDay = false;
		$range = '';
		$rangeValue = '';
		$weekday = '';
		while(!empty($this->stack)){
			$valueArray = array_shift($this->stack);
			foreach($valueArray as $key => $value){
				switch ($key){
					case 'year':
						$date->setYear($value);
						break;
					case 'month':
						$date->setMonth($value);
						$foundMonth = true;
						break;
					case 'day':
						$date->setDay($value);
						break;
					case 'week':
						$date->addSeconds($value);
						break;
					case '?':
						if($lastKey=='month'){
							$date->setDay($value);
							$foundDay = true;
							$key = 'day';
						}else if($lastKey=='year'){
							if($this->conf['USmode']){
								$date->setDay($value);
								$foundDay = true;
								$key = 'day';
							}else{
								$date->setMonth($value);
								$foundMonth = true;
								$key = 'month';
							}
						}else if($lastKey=='day'){
							$date->setMonth($value);
							$foundMonth = true;
							$key = 'month';
						}else{
							$post[] = $valueArray;
						}
						break;
					case 'range':
						$range = $value;
						if($rangeValue){
							$this->evaluateRange($date, $range, $rangeValue);
						}
						break;
					case 'value':
					case 'weekday':
						$rangeValue = $value;
						if($range){
							$this->evaluateRange($date, $range, $rangeValue);
						}
						break;
					default:
						$post[] = $valueArray;
						break;
				}
				$lastKey = $key;
			}
		}	
	
		while(!empty($post)){
			$valueArray = array_pop($post);
			foreach($valueArray as $key => $value){
				switch($key){
					case 'today':
						break;
					case 'tomorrow':
						$day->addSeconds(86400);
						break;
					case 'yesterday':
						$day->subtractSeconds(86400);
					case '?':
						if($foundMonth){
							$date->setDay($value);
						}else{
							if($this->conf['USmode']){
								$date->setDay($value);
								$foundDay = true;
							}else{
								$date->setMonth($value);
								$foundMonth = true;
							}
						}
						break;
				}
			}
		}
		return $date;
	}
	
	function evaluateRange(&$date, $range, $rangeValue){

		if(!is_numeric($range)){
			if($range=='last'){
				$range = -1;
			}else if($range == 'next'){
				$range = 1;
			}
		}
		if(is_numeric($rangeValue)){
			$date->addSeconds($rangeValue*$range);
		}else if(is_array($rangeValue)){
			foreach($rangeValue as $key => $value){
				if($key == 'weekday' && $range>0){
					for($i=0;$i<$range;$i++){
						$formatedDate = Date_Calc::nextDayOfWeek($value,$date->getDay(),$date->getMonth(),$date->getYear());
						$date = new tx_cal_date($formatedDate);
						$date->setTZbyId('UTC');
					}
				}else if($key == 'weekday' && $range<0){
					for($i=0;$i>$range;$i--){
						$formatedDate = Date_Calc::prevDayOfWeek($value,$date->getDay(),$date->getMonth(),$date->getYear());
						$date = new tx_cal_date($formatedDate);
						$date->setTZbyId('UTC');
					}
				}else if($value=='week' && $range >0){
					$date->addSeconds($range*604800);
				}else if($value=='week' && $range <0){
					$date->subtractSeconds($range*604800);
				}
			}
		}else if($range >0){
			if($rangeValue=='month'){
				for($i=0;$i<$range;$i++){
					$days = Date_Calc::daysInMonth($date->getMonth(),$date->getYear());
					$endOfNextMonth = new tx_cal_date(Date_Calc::endOfNextMonth($date->getDay(), $date->getMonth(), $date->getYear()));
					$date->addSeconds(60*60*24*$days);
					if($date->after($endOfNextMonth)){
						$date->setDay($endOfNextMonth->getDay());
						$date->setMonth($endOfNextMonth->getMonth());
						$date->setYear($endOfNextMonth->getYear());
					}
				}
			}else if($rangeValue=='year'){
				$date->setYear($date->getYear()+$range);
			} else {
				$date->addSeconds($range*86400);
			}
		}else if($range <0){
			if($rangeValue=='month'){
				for($i=0;$i>$range;$i--){
					$endOfPrevMonth = new tx_cal_date(Date_Calc::endOfPrevMonth($date->getDay(), $date->getMonth(), $date->getYear()));
					$days = Date_Calc::daysInMonth($endOfPrevMonth->getMonth(),$endOfPrevMonth->getYear());
					$date->subtractSeconds(60*60*24*$days);
				}
			} else if($rangeValue=='year'){
				$date->setYear($date->getYear()-$range);
			} else {
				$date->subtractSeconds($range*86400);
			}
		}
		$date->subtractSeconds(1);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_dateParser.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_dateParser.php']);
}
?>