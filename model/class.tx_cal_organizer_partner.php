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
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_organizer.php');

/**
 * Base model for the calendar organizer.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_organizer_partner extends tx_cal_organizer {
 	 
 	 var $partner;
 	 var $type = 'tx_partner_main';
 	 
 	 
 	 /**
 	  * Constructor
 	  * @param	object		$controller	The controller object
	  * @param	integer		$uid		The uid to search for
	  * @param	string		$pidList	The pid-list to search in
 	  */
 	 function tx_cal_organizer_partner(&$controller, $uid, $pidList){
 	 	require_once(t3lib_extMgm::extPath('partner').'api/class.tx_partner_main.php');
		require_once(t3lib_extMgm::extPath('partner').'api/class.tx_partner_div.php');
 	 	
		$this->tx_cal_organizer_model($controller, $this->type);
		$partner = t3lib_div::makeInstanceClassName('tx_partner_main');
		$this->partner = new $partner();
	 	$this->partner->getPartner($uid);
	 	$this->partner->getContactInfo($this->conf['view.']['organizer.']['contactInfoType']);
 	 }
 	 
 	 function getUid(){
	 	return $this->partner->data['uid'];	
	 }
	 
	 function setUid($t){
	 	$this->partner->data['uid'] = $t;
	 }
	 
	 function getName(){
	 	return $this->partner->data['first_name'].' '.$this->partner->data['last_name'];	
	 }
	 
	 function getFirstName(){
	 	return $this->partner->data['first_name'];	
	 }
	 
	 function setFirstName($t){
	 	$this->partner->data['first_name'] = $t;
	 }
	 
	 function getMiddleName(){
	 	return $this->partner->data['middle_name'];	
	 }
	 
	 function setMiddleName($t){
	 	$this->partner->data['middle_name'] = $t;
	 }
	 
	 function getLastName(){
	 	return $this->partner->data['last_name'];	
	 }
	 
	 function setLastName($t){
	 	$this->partner->data['last_name'] = $t;
	 }
	 
	 function getDescription(){
	 	return $this->partner->data['remarks'];	
	 }
	 
	 function setDescription($d){
	 	$this->partner->data['remarks'] = $d;
	 }
	 
	 function getStreet(){
	 	return $this->partner->data['street'];	
	 }
	 
	 function setStreet($t){
	 	$this->partner->data['street'] = $t;
	 }
	 
	 function getStreetNumber(){
	 	return $this->partner->data['street_number'];	
	 }
	 
	 function setStreetNumber($t){
	 	$this->partner->data['street_number'] = $t;
	 }
	 
	 function getPostalCode(){
	 	return $this->partner->data['postal_code'];	
	 }
	 
	 function setPostalCode($t){
	 	$this->partner->data['postal_code'] = $t;
	 }
	 
	 function getCity(){
	 	return $this->partner->data['locality'];	
	 }
	 
	 function setCity($t){
	 	$this->partner->data['locality'] = $t;
	 }
	 	 
	 function getImage(){
	 	return $this->partner->data['image'];	
	 }
	 
	 function setImage($t){
	 	$this->partner->data['image'] = $t;
	 }
	 
	 /**
	 * Returns the type attribute
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the type attribute. This should be the service type
	 * @param	$type	string	The service type
	 */
	function setType($type) {
		$this->type = $type;
	}

	 
	 function renderOrganizer(){
	 	global $LANG;
		$LANG->includeLLFile('EXT:partner/locallang.php');

		$lastview = $this->conf['lastview'];
		$uid  = $this->conf['uid'];
		$type = $this->conf['type'];
		$monitoring  = $this->conf['monitor'];
		$getdate  = $this->conf['getdate'];
		$page = $this->cObj->fileResource($this->conf['view.']['organizer.']['organizerTemplate4Partner']);
		if ($page=='') {
			return '<h3>calendar: no organizer template file found:</h3>'.$this->conf['view.']['organizer.']['organizerTemplate4Partner'];
		}
		$rems = array();
		$sims = array();
		
		$this->getLocationMarker($page, $sims, $rems);
		
		$postorganizer = $this->cObj->getSubpart($page, '###POST_ORGANIZER###');
		$organizerloop = $this->cObj->getSubpart($page, '###ORGANIZER###');
		$phone = '';
		$mobilephone = '';
		$fax = '';
		$email = '';
		$link = '';
		if(is_array($this->partner->contactInfo)){
			foreach($this->partner->contactInfo as $cInfo){
				switch ($cInfo->data['type']){
					case 0:
						$phoneloop = $this->cObj->getSubpart($organizerloop, '###PHONE_DATA###');
						$loopsims['###PREFIX###'] = $cInfo->data['_prefix'];
						$loopsims['###AREACODE###'] = $cInfo->data['area_code'];
						$loopsims['###NUMBER###'] = $cInfo->data['number'];
						$loopsims['###EXTENSION###'] = $cInfo->data['extension'];
						$loopsims['###NATURE###'] = $LANG->getLL('tx_partner_contact_info.nature.I.'.$cInfo->data['nature']);
						$phoneloop = $this->cObj->substituteMarkerArrayCached($phoneloop, $loopsims, array (), array ());
						$postphoneloop = $this->cObj->getSubpart($organizerloop, '###POST_PHONE_LOOP###');
						$prephoneloop = $this->cObj->getSubpart($organizerloop, '###PRE_PHONE_LOOP###');
						$phone .= $postphoneloop.$phoneloop.$prephoneloop;
					break;
					case 1:
						$mobilephoneloop = $this->cObj->getSubpart($organizerloop, '###MOBILEPHONE_DATA###');
						$loopsims['###PREFIX###'] = $cInfo->data['_prefix'];
						$loopsims['###AREACODE###'] = $cInfo->data['area_code'];
						$loopsims['###NUMBER###'] = $cInfo->data['number'];
						$loopsims['###EXTENSION###'] = $cInfo->data['extension'];
						$loopsims['###NATURE###'] = $LANG->getLL('tx_partner_contact_info.nature.I.'.$cInfo->data['nature']);
						$mobilephoneloop = $this->cObj->substituteMarkerArrayCached($mobilephoneloop, $loopsims, array (), array ());
						$postmobilephoneloop = $this->cObj->getSubpart($organizerloop, '###POST_MOBILEPHONE_LOOP###');
						$premobilephoneloop = $this->cObj->getSubpart($organizerloop, '###PRE_MOBILEPHONE_LOOP###');
						$mobilephone .= $postmobilephoneloop.$mobilephoneloop.$premobilephoneloop;
					break;
					case 2:
						$loopsims = array();
						$faxloop = $this->cObj->getSubpart($organizerloop, '###FAX_DATA###');
						$loopsims['###PREFIX###'] = $cInfo->data['_prefix'];
						$loopsims['###AREACODE###'] = $cInfo->data['area_code'];
						$loopsims['###NUMBER###'] = $cInfo->data['number'];
						$loopsims['###EXTENSION###'] = $cInfo->data['extension'];
						$loopsims['###NATURE###'] = $LANG->getLL('tx_partner_contact_info.nature.I.'.$cInfo->data['nature']);
						$faxloop = $this->cObj->substituteMarkerArrayCached($faxloop, $loopsims, array (), array ());
						$postfaxloop = $this->cObj->getSubpart($organizerloop, '###POST_FAX_LOOP###');
						$prefaxloop = $this->cObj->getSubpart($organizerloop, '###PRE_FAX_LOOP###');
						$fax .= $postfaxloop.$faxloop.$prefaxloop;
					break;
					case 3:
						$loopsims = array();
						$emailloop = $this->cObj->getSubpart($organizerloop, '###EMAIL_DATA###');
						$loopsims['###EMAIL###'] = $this->cObj->parseFunc('<link '.$cInfo->data['email'].'>'.$cInfo->data['email'].'</link>',$this->conf['parseFunc.']);
						$loopsims['###NATURE###'] = $LANG->getLL('tx_partner_contact_info.nature.I.'.$cInfo->data['nature']);
						$emailloop = $this->cObj->substituteMarkerArrayCached($emailloop, $loopsims, array (), array ());
						$postemailloop = $this->cObj->getSubpart($organizerloop, '###POST_EMAIL_LOOP###');
						$preemailloop = $this->cObj->getSubpart($organizerloop, '###PRE_EMAIL_LOOP###');
						$email .= $postemailloop.$emailloop.$preemailloop;
					break;
					case 4:
						$loopsims = array();
						$linkloop = $this->cObj->getSubpart($organizerloop, '###LINK_DATA###');
						$loopsims['###LINK###'] = $this->cObj->parseFunc('<link '.$cInfo->data['url'].'>'.$cInfo->data['url'].'</link>',$this->conf['parseFunc.']);
						$loopsims['###NATURE###'] = $LANG->getLL('tx_partner_contact_info.nature.I.'.$cInfo->data['nature']);
						$linkloop = $this->cObj->substituteMarkerArrayCached($linkloop, $loopsims, array (), array ());
						$postlinkloop = $this->cObj->getSubpart($organizerloop, '###POST_LINK_LOOP###');
						$prelinkloop = $this->cObj->getSubpart($organizerloop, '###PRE_LINK_LOOP###');
						$link .= $postlinkloop.$linkloop.$prelinkloop;
					break;
				}
			}
		}

		if($phone!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###PHONE_DATA_LOOP###');
			$phonerems['###PHONE_LOOP###'] = $phone;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###PHONE_DATA_LOOP###'] = $loop;
			$sims['###PHONE_LABEL###'] = $this->controller->pi_getLL('l_organizer_phone');	
		}else{
			$rems['###PHONE_DATA_LOOP###'] = '';
		}
		if($mobilephone!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###MOBILEPHONE_DATA_LOOP###');
			$phonerems['###MOBILEPHONE_LOOP###'] = $mobilephone;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###MOBILEPHONE_DATA_LOOP###'] = $loop;
			$sims['###MOBILEPHONE_LABEL###'] = $LANG->getLL('tx_partner_contact_info.mobilephone');	
		}else{
			$rems['###MOBILEPHONE_DATA_LOOP###'] = '';
		}
		if($fax!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###FAX_DATA_LOOP###');
			$phonerems['###FAX_LOOP###'] = $fax;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###FAX_DATA_LOOP###'] = $loop;
			$sims['###FAX_LABEL###'] = $LANG->getLL('tx_partner_contact_info.fax');	
		}else{
			$rems['###FAX_DATA_LOOP###'] = '';
		}
		if($email!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###EMAIL_DATA_LOOP###');
			$phonerems['###EMAIL_LOOP###'] = $email;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###EMAIL_DATA_LOOP###'] = $loop;
			$sims['###EMAIL_LABEL###'] = $LANG->getLL('tx_partner_contact_info.email');	
		}else{
			$rems['###EMAIL_DATA_LOOP###'] = '';
		}
		if($link!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###LINK_DATA_LOOP###');
			$phonerems['###LINK_LOOP###'] = $link;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###LINK_DATA_LOOP###'] = $loop;
			$sims['###LINK_LABEL###'] = $LANG->getLL('tx_partner_contact_info.url');	
		}else{
			$rems['###LINK_DATA_LOOP###'] = '';
		}
		
		if($this->getFirstName()!='' && $this->getLastName()!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###NAME_DATA_LOOP###');			
			$sims['###FIRST_NAME###'] = $this->partner->data['first_name'];
			$sims['###LAST_NAME###'] = $this->partner->data['last_name'];
			$sims['###LAST_NAME_PREFIX###'] = $this->partner->data['last_name_prefix'];
			$sims['###MAIDEN_NAME###'] = $this->partner->data['maiden_name'];
			$sims['###MIDDLE_NAME###'] = $this->partner->data['middle_name'];
			$sims['###LABEL###'] = $this->partner->data['label'];
			$sims['###NAME_LABEL###'] = $this->controller->pi_getLL('l_location_name');
			$sims['###FIRST_NAME_LABEL###'] = $LANG->getLL('tx_partner_main.first_name');
			$sims['###LAST_NAME_LABEL###'] = $LANG->getLL('tx_partner_main.last_name');
			$sims['###MIDDLE_NAME_LABEL###'] = $LANG->getLL('tx_partner_main.middle_name');
		}else{
			$rems['###NAME_DATA_LOOP###'] = '';
		}

		if($this->getStreet()!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###STREET_DATA_LOOP###');
			$sims['###STREET###'] = $this->partner->data['street'];
			$sims['###STREET_NUMBER###'] = $this->partner->data['street_number'];
			$sims['###STREET_LABEL###'] = $LANG->getLL('tx_partner_main.street');
			$sims['###STREET_NUMBER_LABEL###'] = $LANG->getLL('tx_partner_main.street_number');
		}else{
			$rems['###STREET_DATA_LOOP###'] = '';
		}
		if($this->getPostalCode()!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###ZIP_DATA_LOOP###');
			$sims['###ZIP###'] = $this->getPostalCode();
			$sims['###ZIP_LABEL###'] = $LANG->getLL('tx_partner_main.postal_code');	
		}else{
			$rems['###ZIP_DATA_LOOP###'] = '';
		}
		if($this->getCity()!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###CITY_DATA_LOOP###');
			$sims['###CITY###'] = $this->getCity();
			$sims['###CITYL_LABEL###'] = $LANG->getLL('tx_partner_main.locality');	
		}else{
			$rems['###CITY_DATA_LOOP###'] = '';
		}
		if($this->getImage()!=''){
			$loop = $this->cObj->getSubpart($organizerloop, '###IMAGE_DATA_LOOP###');
			$sims['###IMAGE_SRC###'] = 'uploads/tx_partner/' .$this->getImage();
			$sims['###IMAGE_LABEL###'] = $LANG->getLL('tx_partner_main.image');	
		}else{
			$rems['###IMAGE_DATA_LOOP###'] = '';
		}

		$organizerloop = $this->cObj->substituteMarkerArrayCached($organizerloop,  array (), $rems, array ());
			
		$preorganizer = $this->cObj->getSubpart($page, '###PRE_ORGANIZER###');
		$page = $postorganizer.$organizerloop.$preorganizer;
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	 }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_organizer_partner.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_organizer_partner.php']);
}
?>