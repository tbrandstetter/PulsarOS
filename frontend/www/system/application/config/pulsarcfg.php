<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 

 * 
 * Description
 * 
 * @license		GNU General Public License
 * @author		Thomas Brandstetter
 * @link		http://www.pulsaros.com
 * @email		admin@pulsaros.com
 * 
 * Copyright (c) 2009-2011
 */
 
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT
 * HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR
 * FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE
 * OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,
 * COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.COPYRIGHT HOLDERS WILL NOT
 * BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL OR CONSEQUENTIAL
 * DAMAGES ARISING OUT OF ANY USE OF THE SOFTWARE OR DOCUMENTATION.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://gnu.org/licenses/>.
 */
 
/* Main settings */

$config['backup'] = 'pulsaros_backup';
$config['hostname'] = '/pulsarroot/configs/system/hostname';
$config['ntpd'] = '/pulsarroot/configs/system/ntpd.conf';
$config['smtp'] = '/pulsarroot/configs/system/msmtprc';
$config['smtp_log'] = '/var/log/msmtp.log';
$config['update'] = '/pulsarroot/frontend/bin/admin/update.sh';
$config['auth'] = '/pulsarroot/frontend/bin/admin/auth.sh';
$config['setup'] = '/pulsarroot/frontend/bin/setup/setup.sh';
$config['mirror'] = 'www.digitalplayground.at';
$config['confdir'] = '/pulsarroot/configs/system';
$config['timezone'] = '/etc/TZ';
$config['www_root'] = '/pulsarroot/frontend/www';
$config['pulsarroot'] = '/pulsarroot';
$config['version'] = '/pulsarroot/configs/system/version';

/* Power settings */
$config['power'] = '/pulsarroot/configs/system/power.conf';
$config['spindown'] = '/pulsarroot/configs/system/spindown.conf';

/* Storage settings */
$config['pool'] = '/pulsarroot/configs/storage/pool.xml';
$config['volume'] = '/pulsarroot/configs/storage/volume.xml';
$config['mdadm'] = '/pulsarroot/configs/storage/mdadm.conf';
$config['storagedir'] = '/pulsarroot/configs/storage';
$config['filesystems'] = '/pulsarroot/configs/storage/filesystems';
$config['pooldir'] = '/storage';
$config['iscsiconfig'] = '/pulsarroot/configs/iscsi';
$config['raid0_info'] = 'Your whole diskspace in one big storage';
$config['raid0_plus'] = ' + It is fast and you have enough storage for all your stuff';
$config['raid0_minus'] = ' - There is no way back, if one disk fails. Only choose this option for non important data';
$config['raid1_info'] = 'Your important data is mirrored between two disks';
$config['raid1_plus'] = ' + Dont bother. If one disk fails you have another one';
$config['raid1_minus'] = ' - Safety has its price. Half of the storage size is needed for backup';
$config['raid5_info'] = 'A mixture between safety and storage. It is the way in the middle';
$config['raid5_plus'] = ' + One disk failure is nothing. The data is shared across all disks';
$config['raid5_minus'] = ' - It is the slowest method to store your data';

/* Network settings */
$config['interfaces'] = '/pulsarroot/configs/network/interfaces';
$config['nwdir'] = '/pulsarroot/configs/network';

/* User settings */
$config['userdir'] = '/pulsarroot/home';

/* Samba settings */
$config['smbconf'] = '/pulsarroot/configs/samba/smb.conf';
$config['smbdir'] = '/pulsarroot/configs/samba';

/* NFS settings */
$config['nfsdir'] = '/pulsarroot/configs/nfs';

/* AFP settings */
$config['afpconf'] = '/pulsarroot/configs/netatalk/AppleVolumes.default';
