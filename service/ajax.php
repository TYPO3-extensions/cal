<?php
// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

	// Initialize FE user object:
$feUserObj = tslib_eidtools::initFeUser();

	// Connect to database:
tslib_eidtools::connectDB();

$startTimestamp = intval(t3lib_div::_GET('starttimestamp'));
$endTimestamp = intval(t3lib_div::_GET('endtimestamp'));
$pid = intval(t3lib_div::_GET('pid'));
$uid = intval(t3lib_div::_GET('uid'));
$calendar = intval(t3lib_div::_GET('calendar'));
$pidList = t3lib_div::_GET('pidlist');

require_once (t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
require_once (PATH_tslib.'/class.tslib_content.php');
$tx_cal_api = t3lib_div :: makeInstanceClassName('tx_cal_api');
$tx_cal_api = new $tx_cal_api();

$cObj = t3lib_div :: makeInstance('tslib_cObj');

require_once(PATH_t3lib.'class.t3lib_tsparser_ext.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
//we need to get the plugin setup to create correct source URLs
$template = t3lib_div::makeInstance('t3lib_tsparser_ext'); // Defined global here!
$template->tt_track = 0; 
// Do not log time-performance information
$template->init();
$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
$rootLine = $sys_page->getRootLine($pid);
$template->runThroughTemplates($rootLine); // This generates the constants/config + hierarchy info for the template.
$template->generateConfig();//
$conf = $template->setup['plugin.']['tx_cal_controller.'];

if($pidList==''){
	// get the calendar plugin record where starting pages value is the same
	// as the pid
	$fields = 'tt_content.pi_flexform AS flex';
	$tables = 'tt_content';
	$where = 'tt_content.list_type="cal_controller" AND tt_content.deleted=0 AND pid='.$pid;
	
	list($tt_content_row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields,$tables,$where);
	
	// if starting point didn't return any records, look for general records
	// storage page.
	if (!$tt_content_row) {
		$tables = 'tt_content LEFT JOIN pages ON tt_content.pid = pages.uid';
		$where = 'tt_content.list_type="cal_controller" AND tt_content.deleted=0 AND tt_content.pid='.$pid;
		list($tt_content_row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields,$tables,$where);
	}
		
	if ($tt_content_row['flex']) 
		$flex_arr = t3lib_div::xml2array($tt_content_row['flex']);
	
	$conf['pidList'] = $flex_arr['data']['sDEF']['lDEF']['pages']['vDEF'];
	
}else{
	$conf['pidList'] = $pidList;
}
$conf['calendar'] = $calendar;
$calAPI = $tx_cal_api->tx_cal_api_with ($cObj, $conf);

$master_array = $calAPI->findEventsWithin($startTimestamp,$endTimestamp,'',$conf['pidList']);

$view_array = array ();
if (count ($master_array>0)) {
	foreach ($master_array as $ovlKey => $ovlValue) {
		if($ovlKey=='legend'){
			continue;
		}
		foreach ($ovlValue as $ovl_time_key => $ovl_time_Value) {
			foreach ($ovl_time_Value as $ovl2Value) {
				$starttime = $ovl2Value->getStarttime();
				$endtime = $ovl2Value->getEndtime();
				if($ovl_time_key=='-1'){
					$endtime += 1;
				}
				for ($j = $starttime; $j < $endtime; $j += 86400) { // 60 * 60 * 24
					if($ovl2Value->getUid()!=$uid && !($j >= $endTimestamp) && !($endtime <= $startTimestamp)){
						$view_array[gmdate('Ymd',$j)]['0000'][count($view_array[gmdate('Ymd',$j)]['0000'])]=$ovl2Value;
					}
				}

			}
		}
	}
}

//$newArray = array_slice($events,1,count($events)-1);
if(empty($view_array)){
	$res = 'true';
}else{
	$res = 'false';
}

$ajax_return_data = t3lib_div::array2xml(array('response'=>$res));
$htmlheader_contenttype = 'Content-Type: text/xml';
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache');
header('Content-Length: '.strlen($ajax_return_data));
header($htmlheader_contenttype);

echo $ajax_return_data;
exit;
?>