# MySQL database structure for Parchment transcript recorder plugin.
#
# http://code.google.com/p/parchment-transcript/


CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session` varchar(255) NOT NULL,
  `story` varchar(255) NOT NULL,
  `version` varchar(255) DEFAULT '',
  `started` datetime DEFAULT NULL,
  `ended` datetime DEFAULT NULL,
  `inputcount` int(11) DEFAULT '0',
  `browser` varchar(255) DEFAULT NULL,
  `interpreter` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 ;


CREATE TABLE `transcripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `timestamp` timestamp NULL DEFAULT NULL,
  `output` text NOT NULL,
  `input` varchar(255) NOT NULL,
  `turncount` int(11) DEFAULT NULL,
  `inputcount` int(11) NOT NULL,
  `outputcount` int(11) NOT NULL,
  `window` int(11) NOT NULL,
  `styles` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 ;

