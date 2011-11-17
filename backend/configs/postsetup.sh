#!/bin/sh
#
# 
#
# 
# Description
# 
# @license		GNU General Public License
# @author       Thomas Brandstetter
# @link         http://www.pulsaros.com
# @email        admin@pulsaros.com
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
BASE=`pwd`
WORKDIR=`cd .. && pwd && cd $BASE`
TARGET_DIR=$1

copy_startupfiles ()
{
	echo "Copy startupfiles"
	cp $BASE/../startupscripts/* $TARGET_DIR/etc/init.d/ && chmod 755 $TARGET_DIR/etc/init.d/*
}

copy_configs ()
{
	echo "Copy configs"
	cp $BASE/../configs/system/* $TARGET_DIR/etc/
	[ -f $TARGET_DIR/etc/init.d/S10udev ] && rm $TARGET_DIR/etc/init.d/S10udev
	[ -f $TARGET_DIR/etc/init.d/S20urandom ] && rm $TARGET_DIR/etc/init.d/S20urandom
	[ -f $TARGET_DIR/etc/init.d/S40network ] && rm $TARGET_DIR/etc/init.d/S40network
	[ -f $TARGET_DIR/etc/init.d/S50dropbear ] && rm $TARGET_DIR/etc/init.d/S50dropbear
}

copy_pulsarroot ()
{
	echo "Copy installer"
	# needed for the installer image
	cp -r $BASE/../installer/* $TARGET_DIR/pulsarroot
}

copy_misc ()
{
	echo "Copy misc"
	[ ! -d $TARGET_DIR/storage ] && mkdir $TARGET_DIR/storage
	[ ! -d $TARGET_DIR/boot ] && mkdir $TARGET_DIR/boot
	
	# create sftp-server symlink
	mkdir $TARGET_DIR/usr/libexec
	ln -s /usr/local/sbin/sftp-server $TARGET_DIR/usr/libexec/sftp-server
	
	# generate ld cache file
	echo "/usr/local/lib" >> $TARGET_DIR/etc/ld.so.conf
	
	# move sdk
	if [ -d $TARGET_DIR/usr/local ]; then
		[ -d $TARGET_DIR/usr/local/include ] && mv -f $TARGET_DIR/usr/include/* $TARGET_DIR/usr/local/include/
		rm -r $TARGET_DIR/usr/include
		[ -d $TARGET_DIR/usr/local/lib ] && mv -f $TARGET_DIR/usr/lib/*.a $TARGET_DIR/usr/local/lib/
		[ -d $BASE/../gcc/local ] && rm -r $BASE/../gcc/local
		# fix libdir locations in all *.la files
		for i in `find $TARGET_DIR/usr/lib -name *.la`; do
			cat $i | sed "s|$WORKDIR/build_x86/output/host/usr/i686-unknown-linux-uclibc/sysroot/usr/lib|/usr/lib|g" > ${i}_new
			mv ${i}_new $i
			cat $i | sed "s|$WORKDIR/build_x86/output/build/gnutls-2.12.5/lib|/usr/lib|g" > ${i}_new
			mv ${i}_new $i
			cat $i | sed "s|$WORKDIR/build_x86/output/build/pcre-7.9|/usr/lib|g" > ${i}_new
			mv ${i}_new $i
			cat $i | sed "s|$WORKDIR/build_x86/output/host/usr/i686-unknown-linux-uclibc/sysroot/usr/lib|/usr/local/lib|g" > ${i}_new
			mv ${i}_new $i
		done
		# fix libstdc++.la libdir location
		cat $TARGET_DIR/usr/local/lib/libstdc++.la | sed "s|libdir='$WORKDIR/build_x86/output/host/usr/i686-unknown-linux-uclibc/sysroot/usr/lib'|libdir='/usr/local/lib'|g" > $TARGET_DIR/usr/local/lib/libstdc++.la_new
		mv $TARGET_DIR/usr/local/lib/libstdc++.la_new $TARGET_DIR/usr/local/lib/libstdc++.la
		mv $TARGET_DIR/usr/local $BASE/../gcc/local
		mkdir $TARGET_DIR/usr/local
	fi
}

# main script

copy_startupfiles
copy_configs
copy_pulsarroot
copy_misc

exit 0