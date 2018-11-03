-- Adminer 4.6.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `suricata2ips`;
CREATE DATABASE `suricata2ips` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `suricata2ips`;

DROP TABLE IF EXISTS `block_queue`;
CREATE TABLE `block_queue` (
  `que_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `que_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the block was added',
  `que_ip_adr` bigint(20) NOT NULL COMMENT 'The IP address to block',
  `que_timeout` varchar(12) COLLATE utf8_unicode_ci NOT NULL COMMENT 'How long to block for',
  `que_sig_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of the signature that caused the block',
  `que_sig_gid` int(10) NOT NULL COMMENT 'The signature group ID',
  `que_sig_sid` int(10) NOT NULL COMMENT 'The signature ID',
  `que_event_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the event was triggered',
  `que_processed` int(11) NOT NULL DEFAULT '0' COMMENT 'If this item has been processed (0=no, <>0=yes)',
  `json_raw` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`que_id`),
  KEY `que_added` (`que_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Queue of ip addresses to block on firewall';


DROP TABLE IF EXISTS `sigs_to_block`;
CREATE TABLE `sigs_to_block` (
  `sig_name` text COLLATE utf8_unicode_ci NOT NULL,
  `src_or_dst` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'src',
  `timeout` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT '01:00:00',
  `active` int(1) NOT NULL DEFAULT '1',
  `id` int(1) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sig_name_unique_index` (`sig_name`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `sigs_to_block` (`sig_name`, `src_or_dst`, `timeout`, `active`, `id`) VALUES
('ET COMPROMISED Known Compromised or Hostile Host Traffic',    'src',  '01:00:00', 1,  1),
('ET POLICY Suspicious inbound to', 'src',  '01:00:00', 1,  2),
('ET DROP Dshield Block Listed Source', 'src',  '01:00:00', 1,  3),
('ET SCAN Sipvicious Scan', 'src',  '01:00:00', 1,  4),
('ET SCAN Sipvicious User-Agent Detected (friendly-scanner)',   'src',  '01:00:00', 1,  5),
('ET DROP Spamhaus DROP Listed Traffic Inbound',    'src',  '01:00:00', 1,  6),
('ET POLICY Outgoing Basic Auth Base64 HTTP Password detected unencrypted', 'dst',  '23:59:59', 1,  7),
('ET CINS Active Threat Intelligence Poor Reputation IP',   'src',  '01:00:00', 1,  8),
('GPL SNMP public access udp',  'src',  '01:00:00', 1,  9),
('ET TOR Known Tor Relay/Router (Not Exit) Node Traffic',   'src',  '01:00:00', 1,  10),
('GPL DNS named version attempt',   'src',  '01:00:00', 1,  11),
('ET VOIP Modified Sipvicious Asterisk PBX User-Agent', 'src',  '01:00:00', 1,  12),
('GPL RPC xdmcp info query',    'src',  '01:00:00', 1,  13),
('GPL RPC portmap listing UDP 111', 'src',  '01:00:00', 1,  14),
('GPL ATTACK_RESPONSE id check returned root',  'src',  '00:01:10', 1,  15),
('ET VOIP Multiple Unauthorized SIP Responses UDP', 'dst',  '00:59:59', 1,  16),
('ET SCAN Behavioral Unusually fast Terminal Server Traffic, Potential Scan or Infection (Inbound)',    'src',  '00:10:00', 1,  18),
('ET DOS Possible NTP DDoS Inbound Frequent',   'src',  '00:10:00', 1,  19),
('ET SCAN SipCLI VOIP Scan',    'src',  '01:00:00', 1,  20),
('ET POLICY GNU/Linux APT', 'src',  '01:00:00', 1,  21),
('ATTACK [PTsecurity]', 'src',  '01:00:00', 1,  22),
('ETN AGGRESSIVE IPs',  'src',  '01:00:00', 1,  25);

-- 2018-10-31 21:57:40