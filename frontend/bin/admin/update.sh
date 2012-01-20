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
# @email		tb@digitalplayground.at
# 
# @file			update.sh
# @version		0.6alpha
# @date			07/02/2011
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

HOME=/usr/local/tmp
LOG=/pulsarroot/frontend/www/update_log.txt
BOOT=/boot/boot/extlinux
VERSION=$1
MIRROR=http://www.digitalplayground.at
PACMAN_UPDATE="pacman -U --noconfirm"
PACMAN_REMOVE="pacman -R --noconfirm"

# Private functions
#
# ============================================================

post_cleanup()
{
	if [ -d $HOME ]; then
		rm -r $HOME/*
	else
		mkdir $HOME
	fi
	[ `df -k|awk '/\/mnt$/ { print $6 }'|wc -l` = 1 ] && /bin/umount /mnt
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

# Functions for the webbased updater
# 
# ============================================================

get_files()
{
	# Variables for this function
	# ===========================
	# cleanup everything before
	post_cleanup
	cd $HOME
	wget $MIRROR/update/$VERSION/initrd.bz2
	[ $? != 0 ] && exit 1
	wget $MIRROR/update/$VERSION/bzImage
	[ $? != 0 ] && exit 1
	wget $MIRROR/update/$VERSION/postconfig.sh
	wget $MIRROR/update/$VERSION/frontend.tar.bz2
}

mount_all()
{
	# Variables for this function
	# ===========================
	mount /boot
	bzip2 -d $HOME/initrd.bz2
	mount $HOME/initrd /mnt
}

update_packages()
{
	# Variables for this function
	# ===========================
	tbd
}

copy_frontend()
{
	# Variables for this function
	# ===========================
	if [ -f $HOME/frontend.tar.bz2 ]; then
		bzip2 -d frontend.tar.bz2 && tar -xf frontend.tar
		cp /pulsarroot/frontend/www/system/application/config/routes.php frontend/www/system/application/config/
		rm -rf /pulsarroot/frontend
		mv frontend /pulsarroot
		rm frontend.tar
	fi
}

finish_update()
{
	# Variables for this function
	# ===========================
	if [ -f $HOME/postconfig.sh ]; then
		$HOME/postconfig.sh
		rm $HOME/postconfig.sh
	fi
	# remove frontend from image
	rm -rf /mnt/pulsarroot/frontend
	umount /mnt
	bzip2 -9 $HOME/initrd
	cp $HOME/initrd.bz2 $BOOT/
	cp $HOME/bzImage $BOOT/
	sync
	umount /boot
	rm $HOME/bzImage && rm $HOME/initrd.bz2
	echo $VERSION > /pulsarroot/configs/system/version
	
	# correct permissions
	chown -R root:root /pulsarroot
}

if [ ! $1 ]; then
	printf "Not an api function!"
	exit 1
fi

# Main program starts here
get_files
mount_all
update_packages
copy_frontend
finish_update

exit 0