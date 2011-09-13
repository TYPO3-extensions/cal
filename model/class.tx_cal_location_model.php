<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_base_model.php');

/**
 * Base model for the calendar location.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location_model extends tx_cal_base_model{
 	 
 	 var $uid;
 	 var $name;
 	 var $description;
 	 var $street;
 	 var $zip;
 	 var $city;
 	 var $phone;
 	 var $fax;
 	 var $mobilephone;
 	 var $email;
 	 var $image;
 	 var $link;
 	 var $longitude;
 	 var $latitude;
 	 
 	 function tx_cal_location_model(&$controller, $serviceKey){
 	 	$this->tx_cal_base_model($controller, $serviceKey);
 	 }
 	 
	 function getUid(){
	 	return $this->uid;	
	 }
	 
	 function setUid($t){
	 	$this->uid = $t;
	 }
	 
	 function getName(){
	 	return $this->name;	
	 }
	 
	 function setName($t){
	 	$this->name = $t;
	 }
	 
	 function getDescription(){
	 	return $this->description;	
	 }
	 
	 function setDescription($d){
	 	$this->description = $d;
	 }
	 
	 function getStreet(){
	 	return $this->street;	
	 }
	 
	 function setStreet($t){
	 	$this->street = $t;
	 }
	 
	 function getPostalCode(){
	 	return $this->zip;	
	 }
	 
	 function setPostalCode($t){
	 	$this->zip = $t;
	 }
	 
	 function getCity(){
	 	return $this->city;	
	 }
	 
	 function setCity($t){
	 	$this->city = $t;
	 }
	 
	 function getPhone(){
	 	return $this->phone;	
	 }
	 
	 function setPhone($t){
	 	$this->phone = $t;
	 }
	 
	 function getMobilephone(){
	 	return $this->mobilephone;	
	 }
	 
	 function setMobilephone($t){
	 	$this->mobilephone = $t;
	 }
	 
	 function getFax(){
	 	return $this->fax;	
	 }
	 
	 function setFax($t){
	 	$this->fax = $t;
	 }
	 
	 function getImage(){
	 	return $this->image;	
	 }
	 
	 function setImage($t){
	 	$this->image = $t;
	 }
	 
	 function getLink(){
	 	return $this->link;	
	 }
	 
	 function setLink($t){
	 	$this->link = $t;
	 }
	 
	 function getEmail(){
	 	return $this->email;	
	 }
	 
	 function setEmail($t){
	 	$this->email = $t;
	 }

	 function getLongitude(){
	 	return $this->longlitude;
	 }
	 
	 function setLongitude($l){
	 	$this->longlitude = $l;
	 }
	 
	 function getLatitude(){
	 	return $this->latitude;
	 }
	 
	 function setLatitude($l){
	 	$this->latitude = $l;
	 }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_model.php']);
}
?>