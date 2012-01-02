#!/bin/sh
#
# 
#
# 
# Description
# 
# @license		GNU General Public License
# @author		Thomas Brandstetter
# @link			http://www.pulsaros.com
# @email		admin@pulsaros.com
# 
# Copyright (c) 2009-2011
#
# ##############################################
 
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT
# HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED,
# INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR
# FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE
# OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,
# COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.COPYRIGHT HOLDERS WILL NOT
# BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL OR CONSEQUENTIAL
# DAMAGES ARISING OUT OF ANY USE OF THE SOFTWARE OR DOCUMENTATION.
# 
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://gnu.org/licenses/>.
# 

# Variables
#
# ============================================================

trap "" 2 3
HOME=/pulsarroot/frontend/bin/setup
LOG=/pulsarroot/frontend/www/install_log.txt
PACMAN="pacman -Uf --noconfirm"

# Private functions
#
# ============================================================

label_cleanup()
{
	for i in `blkid|grep ${1}|awk -F: '{ print $1 }'`; do
		e2label ${i} ""
	done
}

post_cleanup()
{
	[ `df -k|awk '/\/mnt$/ { print $6 }'|wc -l` = 1 ] && /bin/umount /mnt
	[ `losetup |wc -l` -gt 1 ] && losetup -d /dev/loop/0
	[ -f $LOG ] && rm $LOG
	# cleanup old labels
	label_cleanup "PULSARROOT"
	label_cleanup "USR"
	label_cleanup "BOOT"
}

get_debug()
{
	# function to get information about your system - in case of problems!
	printf "Starting debug information logging\n" >> $LOG
	printf "==================================\n" >> $LOG
	lspci >> $LOG
	lsusb >> $LOG
	# end of debug
}
	

get_installer()
{
	if [ $mount = "none" ]; then
		if [ -f /proc/sys/dev/cdrom/info ]; then
			cdrom=`cat /proc/sys/dev/cdrom/info |grep "drive name"|awk '{ print $3 }'`
			mount /dev/${cdrom} /mnt
			if [ -f /mnt/.pulsarinstall ]; then
				printf "Installer Device: $cdrom\n" >> $LOG
				mount=$cdrom
			else
				umount /mnt
			fi
		else
			if [ `file /dev/${i}1 | grep -c x86` = 1 ]; then
				mount /dev/${i}1 /mnt
				if [ -f /mnt/.pulsarinstall ]; then
					printf "Installer Device: $i\n" >> $LOG
					mount=$i
				else
					umount /mnt
				fi
			fi
		fi
	fi
}

check_dir()
{
	[ ! -d ${1} ] && mkdir -p ${1}
}

# Functions for the webbased installer
# 
# ============================================================

get_disks()
{
	# Variables for this function
	number=0
	mount="none"
	# ===========================
	# cleanup everything before
	post_cleanup
	clear
	printf "begin\n"
	for i in `fdisk -l| grep Disk| grep -v partition | awk '{print $2}'|cut -d'/' -f3|cut -d':' -f1`; do
		get_installer $i
		size=` fdisk -l|grep Disk| grep -v partition | grep $i | awk '{print $3}'`
		model=`hdparm -i /dev/$i|grep Model|awk -F= '{ print $2$3 }'|cut -d',' -f1`
		# do not print out the pulsar installer
		if [ $mount != $i ]; then
			printf "Disk : $i Size: $size Model: $model \n" >> $LOG
			printf "$i $size $model \n"
		fi
	done
}

get_net()
{
	# Variables for this function
	number=0
	# ===========================
	for i in `ip link|grep -v link|grep -v lo|awk '{print $2}'|cut -d':' -f1`; do
		printf "Network Card: $i\n" >> $LOG
		printf "$i\n"
	done
}

install_os()
{
	# Variables for this function
	disk=$1
	nwcard=$2
	dhcp=$3
	hostname=$4
	ipaddr=$5
	netmask=$6
	gateway=$7
	nameserver=$8
	printf "Variables for installation: Disk: $1, Network Card: $2, DHCP: $3, Hostname: $4, IP Address: $5, Netmask: $6, Gateway: $7, Nameserver: $8\n" >> $LOG
	# ===========================
	# prepare choosen disk
	fdisk ${disk} < $HOME/format.cmd >/dev/null 2>&1
	sleep 2
	if [ `fdisk -l | head -n 7| tail -n 1| grep -c '\*'` != "1" ]; then
		fdisk ${disk} < $HOME/activate.cmd >/dev/null 2>&1
		sleep 2
	fi
	mkfs.ext2 ${disk}1 >/dev/null 2>&1
	mkfs.ext4 ${disk}2 >/dev/null 2>&1
	mkfs.ext4 ${disk}3 >/dev/null 2>&1
	check_dir "/boot"
	check_dir "/pulsarcore"
	check_dir "/usr/local"
	#=============================
	# install mbr to disk
	dd if=/dev/zero of=${disk} bs=446 count=1
	dd if=/usr/share/mbr.bin of=${disk}
	#=============================
	# install os to disk
	mount -t ext2 ${disk}1 /boot
	mount -t ext4 ${disk}2 /pulsarcore
	mount -t ext4 ${disk}3 /usr/local
	# write mbr on bootdisk
	mkdir -p /boot/boot/extlinux
	cp  /mnt/boot/isolinux/isolinux.cfg /boot/boot/extlinux/extlinux.conf
	extlinux -i /boot/boot/extlinux
	touch /boot/.installed
	#======================================================================================
	# create filesystem labels
	e2label ${disk}1 BOOT
	e2label ${disk}2 PULSARROOT
	e2label ${disk}3 USR
	#======================================================================================
	# install system
	check_dir "/usr/local/var/pacman"
	rm -rf /var/lib/nfs
	$PACMAN /mnt/core/*.pkg.tar.gz
	cd $HOME
	
	# move netatalk config to system's pulsarroot
	mv /pulsarroot/configs/netatalk /pulsarcore/configs/
	
	# change permissions
	chown -R root:root /pulsarcore/*
	chmod 600 /pulsarcore/configs/monit/monitrc
	
	#======================================================================================
	# configure network
	echo $hostname > /pulsarcore/configs/system/hostname
	echo "127.0.0.1 localhost $hostname" > /pulsarcore/configs/network/hosts
	if [ "$dhcp" = "n" ]; then
		echo "auto $nwcard" >> /pulsarcore/configs/network/interfaces_${nwcard}
		echo "iface $nwcard inet static" >> /pulsarcore/configs/network/interfaces_${nwcard}
		echo "address $ipaddr" >> /pulsarcore/configs/network/interfaces_${nwcard}
		echo "netmask $netmask" >> /pulsarcore/configs/network/interfaces_${nwcard}
		echo "gateway $gateway" >> /pulsarcore/configs/network/interfaces_${nwcard}
		echo "nameserver ${nameserver}" > /pulsarcore/config/network/resolv.conf
		echo "static network configured" >> $LOG
	elif [ "$dhcp" = "y" ]; then
		echo "auto $nwcard" >> /pulsarcore/configs/network/interfaces_${nwcard}
		echo "iface $nwcard inet dhcp" >> /pulsarcore/configs/network/interfaces_${nwcard}
		echo "dhcp configured" >> $LOG
	fi
	# cp dropbear keys to system
	cp -rp /pulsarroot/configs/dropbear /pulsarcore/configs/
	#======================================================================================
	# remove old frontend from bootimage
	mkdir /image
	cp /boot/boot/extlinux/initrd.bz2 /usr/local/
	bzip2 -d /usr/local/initrd.bz2
	mount -o loop /usr/local/initrd /image
	cp /pulsarcore/configs/system/fstab /image/etc/fstab
	rm -r /image/pulsarroot
	mkdir /image/pulsarcore
	cd /image && ln -s pulsarcore pulsarroot
	cd / && umount /image
	bzip2 -9 /usr/local/initrd
	cp /usr/local/initrd.bz2 /boot/boot/extlinux/
	rm /usr/local/initrd.bz2 && rm -r /image
	# finish
	sync
	umount /boot
	umount /pulsarcore
	umount -l /usr/local
	printf "PulsarOS successfully installed" >> $LOG
	#======================================================================================
}

# Main program starts here
# Options for the webbased installer

case $1 in
	get_disks)
		get_disks
		exit 0
		;;
	get_net)
		get_net
		exit 0
		;;
	install_os)
		get_debug
		install_os $2 $3 $4 $5 $6 $7 $8 $9 $10
		exit 0
		;;	
	*)
		printf "Not an api function!"
		;;
esac

exit 0