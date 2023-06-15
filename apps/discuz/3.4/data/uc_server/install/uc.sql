DROP TABLE IF EXISTS uc_applications;
CREATE TABLE uc_applications (
  appid smallint(6) unsigned NOT NULL auto_increment,
  `type` varchar(16) NOT NULL default '',
  `name` varchar(20) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  authkey varchar(255) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  viewprourl varchar(255) NOT NULL,
  apifilename varchar( 30 ) NOT NULL DEFAULT 'uc.php',
  charset varchar(8) NOT NULL default '',
  dbcharset varchar(8) NOT NULL default '',
  synlogin tinyint(1) NOT NULL default '0',
  recvnote tinyint(1) DEFAULT '0',
  extra text NOT NULL,
  tagtemplates text NOT NULL,
  allowips text NOT NULL,
  PRIMARY KEY  (appid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_members;
CREATE TABLE uc_members (
  uid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  username char(15) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  email char(32) NOT NULL DEFAULT '',
  myid char(30)  NOT NULL DEFAULT '',
  myidkey char(16) NOT NULL DEFAULT '',
  regip char(15) NOT NULL DEFAULT '',
  regdate int(10) unsigned NOT NULL DEFAULT '0',
  lastloginip int(10) NOT NULL DEFAULT '0',
  lastlogintime int(10) unsigned NOT NULL DEFAULT '0',
  salt char(6) NOT NULL,
  secques char(8) NOT NULL default '',
  PRIMARY KEY(uid),
  UNIQUE KEY username(username),
  KEY email(email)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_memberfields;
CREATE TABLE uc_memberfields (
  uid mediumint(8) unsigned NOT NULL,
  blacklist text NOT NULL,
  PRIMARY KEY(uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_newpm;
CREATE TABLE uc_newpm (
  uid mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_friends;
CREATE TABLE uc_friends (
  uid mediumint(8) unsigned NOT NULL default '0',
  friendid mediumint(8) unsigned NOT NULL default '0',
  direction tinyint(1) NOT NULL default '0',
  version int(10) unsigned NOT NULL auto_increment,
  delstatus tinyint(1) NOT NULL default '0',
  comment char(255) NOT NULL default '',
  PRIMARY KEY(version),
  KEY uid(uid),
  KEY friendid(friendid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_tags;
CREATE TABLE uc_tags (
  tagname char(20) NOT NULL,
  appid smallint(6) unsigned NOT NULL default '0',
  data mediumtext,
  expiration int(10) unsigned NOT NULL,
  KEY tagname (tagname,appid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_sqlcache;
CREATE TABLE uc_sqlcache (
  sqlid char(6) NOT NULL default '',
  data char(100) NOT NULL,
  expiry int(10) unsigned NOT NULL,
  PRIMARY KEY  (sqlid),
  KEY(expiry)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_settings;
CREATE TABLE uc_settings (
  `k` varchar(32) NOT NULL default '',
  `v` text NOT NULL,
  PRIMARY KEY  (k)
) Type=MyISAM;

REPLACE INTO uc_settings(k, v) VALUES ('accessemail','');
REPLACE INTO uc_settings(k, v) VALUES ('censoremail','');
REPLACE INTO uc_settings(k, v) VALUES ('censorusername','');
REPLACE INTO uc_settings(k, v) VALUES ('dateformat','y-n-j');
REPLACE INTO uc_settings(k, v) VALUES ('doublee','0');
REPLACE INTO uc_settings(k, v) VALUES ('nextnotetime','0');
REPLACE INTO uc_settings(k, v) VALUES ('timeoffset','28800');
REPLACE INTO uc_settings(k, v) VALUES ('privatepmthreadlimit','25');
REPLACE INTO uc_settings(k, v) VALUES ('chatpmthreadlimit','30');
REPLACE INTO uc_settings(k, v) VALUES ('chatpmmemberlimit','35');
REPLACE INTO uc_settings(k, v) VALUES ('pmfloodctrl','15');
REPLACE INTO uc_settings(k, v) VALUES ('pmcenter','1');
REPLACE INTO uc_settings(k, v) VALUES ('sendpmseccode','1');
REPLACE INTO uc_settings(k, v) VALUES ('pmsendregdays','0');
REPLACE INTO uc_settings(k, v) VALUES ('addappbyurl','0');
REPLACE INTO uc_settings(k, v) VALUES ('maildefault', 'username@21cn.com');
REPLACE INTO uc_settings(k, v) VALUES ('mailsend', '1');
REPLACE INTO uc_settings(k, v) VALUES ('mailserver', 'smtp.21cn.com');
REPLACE INTO uc_settings(k, v) VALUES ('mailport', '25');
REPLACE INTO uc_settings(k, v) VALUES ('mailauth', '1');
REPLACE INTO uc_settings(k, v) VALUES ('mailfrom', 'UCenter <username@21cn.com>');
REPLACE INTO uc_settings(k, v) VALUES ('mailauth_username', 'username@21cn.com');
REPLACE INTO uc_settings(k, v) VALUES ('mailauth_password', 'password');
REPLACE INTO uc_settings(k, v) VALUES ('maildelimiter', '0');
REPLACE INTO uc_settings(k, v) VALUES ('mailusername', '1');
REPLACE INTO uc_settings(k, v) VALUES ('mailsilent', '1');
REPLACE INTO uc_settings(k, v) VALUES ('login_failedtime', '5');
REPLACE INTO uc_settings(k, v) VALUES ('version', '1.6.0');

DROP TABLE IF EXISTS uc_badwords;
CREATE TABLE uc_badwords (
  id smallint(6) unsigned NOT NULL auto_increment,
  `admin` varchar(15) NOT NULL default '',
  find varchar(255) NOT NULL default '',
  replacement varchar(255) NOT NULL default '',
  findpattern varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY `find` (`find`)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_notelist;
CREATE TABLE uc_notelist (
  noteid int(10) unsigned NOT NULL auto_increment,
  operation char(32) NOT NULL,
  closed tinyint(4) NOT NULL default '0',
  totalnum smallint(6) unsigned NOT NULL default '0',
  succeednum smallint(6) unsigned NOT NULL default '0',
  getdata mediumtext NOT NULL,
  postdata mediumtext NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  pri tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (noteid),
  KEY closed (closed,pri,noteid),
  KEY dateline (dateline)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_domains;
CREATE TABLE uc_domains (
  id int(10) unsigned NOT NULL auto_increment,
  domain char(40) NOT NULL default '',
  ip char(15) NOT NULL default '',
  PRIMARY KEY  (id)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_feeds;
CREATE TABLE uc_feeds (
  feedid mediumint(8) unsigned NOT NULL auto_increment,
  appid varchar(30) NOT NULL default '',
  icon varchar(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  hash_template varchar(32) NOT NULL default '',
  hash_data varchar(32) NOT NULL default '',
  title_template text NOT NULL default '',
  title_data text NOT NULL default '',
  body_template text NOT NULL,
  body_data text NOT NULL,
  body_general text NOT NULL,
  image_1 varchar(255) NOT NULL default '',
  image_1_link varchar(255) NOT NULL default '',
  image_2 varchar(255) NOT NULL default '',
  image_2_link varchar(255) NOT NULL default '',
  image_3 varchar(255) NOT NULL default '',
  image_3_link varchar(255) NOT NULL default '',
  image_4 varchar(255) NOT NULL default '',
  image_4_link varchar(255) NOT NULL default '',
  target_ids varchar(255) NOT NULL default '',
  PRIMARY KEY  (feedid),
  KEY uid (uid,dateline)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_admins;
CREATE TABLE uc_admins (
  uid mediumint(8) unsigned NOT NULL auto_increment,
  username char(15) NOT NULL default '',
  allowadminsetting tinyint(1) NOT NULL default '0',
  allowadminapp tinyint(1) NOT NULL default '0',
  allowadminuser tinyint(1) NOT NULL default '0',
  allowadminbadword tinyint(1) NOT NULL default '0',
  allowadmintag tinyint(1) NOT NULL default '0',
  allowadminpm tinyint(1) NOT NULL default '0',
  allowadmincredits tinyint(1) NOT NULL default '0',
  allowadmindomain tinyint(1) NOT NULL default '0',
  allowadmindb tinyint(1) NOT NULL default '0',
  allowadminnote tinyint(1) NOT NULL default '0',
  allowadmincache tinyint(1) NOT NULL default '0',
  allowadminlog tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid),
  UNIQUE KEY username (username)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_failedlogins;
CREATE TABLE uc_failedlogins (
  ip char(15) NOT NULL default '',
  count tinyint(1) unsigned NOT NULL default '0',
  lastupdate int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (ip)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_protectedmembers;
CREATE TABLE uc_protectedmembers (
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  appid tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  `admin` char(15) NOT NULL default '0',
  UNIQUE KEY(username, appid)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_mergemembers;
CREATE TABLE uc_mergemembers (
  appid smallint(6) unsigned NOT NULL,
  username char(15) NOT NULL,
  PRIMARY KEY  (appid,username)
) Type=MyISAM;

DROP TABLE IF EXISTS uc_vars;
CREATE TABLE uc_vars (
  name char(32) NOT NULL default '',
  value char(255) NOT NULL default '',
  PRIMARY KEY(name)
) Type=HEAP;

DROP TABLE IF EXISTS uc_mailqueue;
CREATE TABLE uc_mailqueue (
  mailid int(10) unsigned NOT NULL auto_increment,
  touid mediumint(8) unsigned NOT NULL default '0',
  tomail varchar(32) NOT NULL,
  frommail varchar(100) NOT NULL,
  subject varchar(255) NOT NULL,
  message text NOT NULL,
  charset varchar(15) NOT NULL,
  htmlon tinyint(1) NOT NULL default '0',
  level tinyint(1) NOT NULL default '1',
  dateline int(10) unsigned NOT NULL default '0',
  failures tinyint(3) unsigned NOT NULL default '0',
  appid smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (mailid),
  KEY appid (appid),
  KEY level (level,failures)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_members;
CREATE TABLE uc_pm_members (
  plid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  isnew tinyint(1) unsigned NOT NULL default '0',
  pmnum int(10) unsigned NOT NULL default '0',
  lastupdate int(10) unsigned NOT NULL default '0',
  lastdateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (plid,uid),
  KEY isnew (isnew),
  KEY lastdateline (uid,lastdateline),
  KEY lastupdate (uid,lastupdate)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_lists;
CREATE TABLE uc_pm_lists (
  plid mediumint(8) unsigned NOT NULL auto_increment,
  authorid mediumint(8) unsigned NOT NULL default '0',
  pmtype tinyint(1) unsigned NOT NULL default '0',
  subject varchar(80) NOT NULL,
  members smallint(5) unsigned NOT NULL default '0',
  min_max varchar(17) NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  lastmessage text NOT NULL,
  PRIMARY KEY  (plid),
  KEY pmtype (pmtype),
  KEY min_max (min_max),
  KEY authorid (authorid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_indexes;
CREATE TABLE uc_pm_indexes (
  pmid mediumint(8) unsigned NOT NULL auto_increment,
  plid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_0;
CREATE TABLE uc_pm_messages_0 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_1;
CREATE TABLE uc_pm_messages_1 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_2;
CREATE TABLE uc_pm_messages_2 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_3;
CREATE TABLE uc_pm_messages_3 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_4;
CREATE TABLE uc_pm_messages_4 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_5;
CREATE TABLE uc_pm_messages_5 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_6;
CREATE TABLE uc_pm_messages_6 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_7;
CREATE TABLE uc_pm_messages_7 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_8;
CREATE TABLE uc_pm_messages_8 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS uc_pm_messages_9;
CREATE TABLE uc_pm_messages_9 (
  pmid mediumint(8) unsigned NOT NULL default '0',
  plid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  delstatus tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY plid (plid,delstatus,dateline),
  KEY dateline (plid,dateline)
) TYPE=MyISAM;