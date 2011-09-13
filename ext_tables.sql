#
# Table structure for table 'tx_cal_events'
#
CREATE TABLE tx_cal_event (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	start_date int(11) unsigned DEFAULT '0' NOT NULL,
	end_date int(11) unsigned DEFAULT '0' NOT NULL,
	start_time int(11) unsigned DEFAULT '0' NOT NULL,
	end_time int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	calendar_id int(11) unsigned DEFAULT '0' NOT NULL,
	category_id int(11) unsigned DEFAULT '0' NOT NULL,
	organizer varchar(128) DEFAULT '' NOT NULL,
	organizer_id int(11) unsigned DEFAULT '0' NOT NULL,
	location varchar(128) DEFAULT '' NOT NULL,
	location_id int(11) unsigned DEFAULT '0' NOT NULL,
	description text,
	freq varchar(128) DEFAULT '',
	until tinytext,
	cnt tinyint(4) unsigned DEFAULT '0',
	byday varchar(128) DEFAULT '',
	bymonthday varchar(128) DEFAULT '',
	bymonth varchar(128) DEFAULT '',
	intrval tinyint(4) unsigned DEFAULT '1',
	monitor_cnt int(11) unsigned DEFAULT '0',
	exception_cnt int(11) unsigned DEFAULT '0',
	fe_cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	fe_crgroup_id int(11) unsigned DEFAULT '0' NOT NULL,
	shared_user_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	type tinyint(4) DEFAULT '0' NOT NULL,
	page int(11) DEFAULT '0' NOT NULL,
	ext_url tinytext NOT NULL,
	isTemp tinyint(1) DEFAULT '0' NOT NULL,
	image tinyblob NOT NULL,
  	imagecaption text NOT NULL,
  	imagealttext text NOT NULL,
  	imagetitletext text NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_cal_event_shared_user_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_calendar (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	owner int(11) unsigned DEFAULT '0' NOT NULL,
	activate_fnb tinyint(4) unsigned DEFAULT '0' NOT NULL,
	fnb_user_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	type tinyint(4) DEFAULT '0' NOT NULL,
	ext_url tinytext NOT NULL,
	ics_file tinytext NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

// fnb = free & busy
CREATE TABLE tx_cal_calendar_fnb_user_group_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_category (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	headerstyle varchar(20) DEFAULT '' NOT NULL,
	bodystyle varchar(20) DEFAULT '' NOT NULL,
	calendar_id int(11) unsigned DEFAULT '0' NOT NULL,
	shared_user_allowed tinyint(4) unsigned DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_cal_event_category_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_calendar_user_group_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_fe_user_event_monitor_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_unknown_users (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	email varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_cal_exception_event_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_cal_exception_event_group_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);


CREATE TABLE tx_cal_exception_event_group (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	exception_event_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_cal_exception_event (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	start_date int(11) unsigned DEFAULT '0' NOT NULL,
	end_date int(11) unsigned DEFAULT '0' NOT NULL,
	start_time int(11) unsigned DEFAULT '0' NOT NULL,
	end_time int(11) unsigned DEFAULT '0' NOT NULL,
	relation_cnt int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(128) DEFAULT '' NOT NULL,
	freq varchar(128) DEFAULT '',
	until tinytext,
	cnt tinyint(4) unsigned DEFAULT '0',
	byday varchar(128) DEFAULT '',
	bymonthday varchar(128) DEFAULT '',
	bymonth varchar(128) DEFAULT '',
	intrval tinyint(4) unsigned DEFAULT '1',
	monitor_cnt int(11) unsigned DEFAULT '0',
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_cal_organizer (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	description text NOT NULL,
	street varchar(128) DEFAULT '' NOT NULL,
	zip varchar(16) DEFAULT '' NOT NULL,
	city varchar(128) DEFAULT '' NOT NULL,
	phone varchar(24) DEFAULT '' NOT NULL,
	email varchar(64) DEFAULT '' NOT NULL,
	image varchar(64) DEFAULT '' NOT NULL,
	link varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_cal_location (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	name varchar(128) DEFAULT '' NOT NULL,
	description text NOT NULL,
	street varchar(128) DEFAULT '' NOT NULL,
	zip varchar(16) DEFAULT '' NOT NULL,
	city varchar(128) DEFAULT '' NOT NULL,
	phone varchar(24) DEFAULT '' NOT NULL,
	email varchar(64) DEFAULT '' NOT NULL,
	image varchar(64) DEFAULT '' NOT NULL,
	link varchar(128) DEFAULT '' NOT NULL,
	latitude tinytext NOT NULL,
	longitude tinytext NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tt_address (
	tx_cal_controller_isorganizer tinyint(4) DEFAULT '0' NOT NULL,
	tx_cal_controller_islocation tinyint(4) DEFAULT '0' NOT NULL,
	tx_cal_controller_latitude tinytext NOT NULL,
	tx_cal_controller_longitude tinytext NOT NULL,
);