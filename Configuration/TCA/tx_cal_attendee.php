<?php
defined('TYPO3_MODE') or die();

$tx_cal_attendee = array(
		'ctrl' => array(
				'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee',
				'label' => 'uid',
				'tstamp' => 'tstamp',
				'crdate' => 'crdate',
				'cruser_id' => 'cruser_id',
				'default_sortby' => 'uid',
				'delete' => 'deleted',
				'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_attendee.gif',
				'enablecolumns' => array(
						'disabled' => 'hidden'
				),
				'versioningWS' => TRUE,
				'searchFields' => 'email',
				'label_userFunc' => 'TYPO3\\CMS\\Cal\\Backend\\TCA\\Labels->getAttendeeRecordLabel'
		),
		'interface' => array(
				'showRecordFieldList' => 'hidden,fe_user_id,email,attendance,status'
		),
		'columns' => array(
				'hidden' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
						'config' => array(
								'type' => 'check',
								'default' => '0'
						)
				),
				'fe_user_id' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.fe_user_id',
						'config' => array(
								'type' => 'group',
								'internal_type' => 'db',
								'size' => 1,
								'minitems' => 0,
								'maxitems' => 1,
								'allowed' => 'fe_users',
						)
				),
				'fe_group_id' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.fe_group_id',
						'config' => array(
								'type' => 'group',
								'internal_type' => 'db',
								'size' => 1,
								'minitems' => 0,
								'maxitems' => 1,
								'allowed' => 'fe_groups',
						)
				),
				'email' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.email',
						'config' => array(
								'type' => 'input',
								'size' => '30',
								'max' => '64',
								'eval' => 'lower'
						)
				),
				'attendance' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.attendance',
						'config' => array(
                                'renderType' => 'selectSingle',
								'type' => 'select',
								'items' => array(
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.attendance.NON',
												'NON'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.attendance.OPT-PARTICIPANT',
												'OPT-PARTICIPANT'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.attendance.REQ-PARTICIPANT',
												'REQ-PARTICIPANT'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.attendance.CHAIR',
												'CHAIR'
										)
								),
								'size' => '1',
								'minitems' => 1,
								'maxitems' => 1
						)
				),
				'status' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status',
						'config' => array(
                                'renderType' => 'selectSingle',
								'type' => 'select',
								'items' => array(
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.0',
												'0'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.NEEDS-ACTION',
												'NEEDS-ACTION'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.ACCEPTED',
												'ACCEPTED'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.DECLINE',
												'DECLINE'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.TENTATIVE',
												'TENTATIVE'
										),
										array(
												'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.DELEGATED',
												'DELEGATED'
										)
								),
								'size' => '1',
								'minitems' => 1,
								'maxitems' => 1
						)
				)
		),
		'types' => array(
				'0' => array(
						'showitem' => 'hidden,fe_user_id,fe_group_id,email,attendance,status'
				)
		),
		'palettes' => array(
				'1' => array(
						''
				)
		)
);

return $tx_cal_attendee;