<?php
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2005-2009 Christian Technology Ministries International Inc.
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
 * International (http://CTMIinc.org). The WEC is developing TYPO3-based
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
 * *************************************************************
 */

/**
 * Test case for WEC Map
 *
 * WARNING: Never ever run a unit test like this on a live site!
 */
class tx_cal_DayByWeek_testcase extends tx_phpunit_testcase {
	public function testFirstDayOfWeek1OfYear2013_is_20121231() {
		$this->assertEquals ('20121231', tx_cal_functions::getDayByWeek (2013, 1, 1));
	}
	public function testSecondDayOfWeek1OfYear2013_is_20130101() {
		$this->assertEquals ('20130101', tx_cal_functions::getDayByWeek (2013, 1, 2));
	}
	public function testThirdDayOfWeek1OfYear2013_is_20130102() {
		$this->assertEquals ('20130102', tx_cal_functions::getDayByWeek (2013, 1, 3));
	}
	public function testFourthDayOfWeek1OfYear2013_is_20130103() {
		$this->assertEquals ('20130103', tx_cal_functions::getDayByWeek (2013, 1, 4));
	}
	public function testFifthDayOfWeek1OfYear2013_is_20130104() {
		$this->assertEquals ('20130104', tx_cal_functions::getDayByWeek (2013, 1, 5));
	}
	public function testSixthDayOfWeek1OfYear2013_is_20130105() {
		$this->assertEquals ('20130105', tx_cal_functions::getDayByWeek (2013, 1, 6));
	}
	public function testSeventhDayOfWeek1OfYear2013_is_20130106() {
		$this->assertEquals ('20130106', tx_cal_functions::getDayByWeek (2013, 1, 0));
	}
	public function testFirstDayOfWeek1OfYear2013WeekstartSunday_is_20130304() {
		$this->assertEquals ('20130304', tx_cal_functions::getDayByWeek (2013, 10, 1));
	}
}
?>