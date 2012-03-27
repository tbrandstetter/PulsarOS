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
VERSION=0.7alpha
BUILDROOT_VERSION=2011.05
ARCH=$1
BASE=`pwd`
WORKDIR=$2
BOOT_HOME=$WORKDIR/$ARCH/boot/boot/isolinux
MOUNT_CD=$WORKDIR/mount/cdrom
MOUNT_PULSAR=$WORKDIR/mount/pulsar
PACKAGE_DIR=$WORKDIR/$ARCH/corepackages
GCC_DIR=$WORKDIR/$ARCH/gcc

# Build the pulsar os image
# Functions

prepare_pulsar ()
{
	echo "Prepare $ARCH build"
	# create necessary directories
	[ ! -f $WORKDIR/$ARCH/build/.prepared ] && mkdir -p $BOOT_HOME $MOUNT_CD $MOUNT_PULSAR $WORKDIR/images $GCC_DIR
	[ -d $WORKDIR/configs ] && rm -rf $WORKDIR/configs
	[ -d $WORKDIR/pulsarroot ] && rm -rf $WORKDIR/pulsarroot
	[ -d $WORKDIR/frontend ] && rm -rf $WORKDIR/frontend
	[ -d $WORKDIR/startupscripts ] && rm -rf $WORKDIR/startupscripts
	[ -d $WORKDIR/$ARCH/build/output ] && rm -rf $WORKDIR/$ARCH/build/output
	[ -f $BOOT_HOME/initrd.bz2 ] && rm $BOOT_HOME/initrd*
	[ -f $BOOT_HOME/bzImage ] && rm $BOOT_HOME/bzImage
	[ -d $WORKDIR/$ARCH/core ] && rm -rf $WORKDIR/$ARCH/core
	[ -d $WORKDIR/$ARCH/sdk ] && rm -rf $WORKDIR/$ARCH/sdk
	if [ -d $WORKDIR/$ARCH/boot/core ]; then
		rm  $WORKDIR/$ARCH/boot/core/*
	else
		mkdir -p $WORKDIR/$ARCH/boot/core
	fi
	[ ! -f $WORKDIR/$ARCH/boot/.pulsarinstall ] && touch $WORKDIR/$ARCH/boot/.pulsarinstall
}

stage_pulsar ()
{
	echo "Download & prepare buildroot"
	if [ ! -d $WORKDIR/$ARCH/build ]; then
		cd $WORKDIR
		if [ ! -f $WORKDIR/buildroot-$BUILDROOT_VERSION.tar ]; then
			wget http://www.buildroot.net/downloads/buildroot-$BUILDROOT_VERSION.tar.bz2
			bzip2 -d buildroot-2011.05.tar.bz2
		fi
		tar -xf buildroot-$BUILDROOT_VERSION.tar
		mv buildroot-2011.05 $ARCH/build
		touch $ARCH/build/.prepared
	fi
	
	echo "Staging workdir..."
	cp -r $BASE/backend/configs $WORKDIR/
	cp -r $BASE/backend/pulsarroot $WORKDIR/
	cp -r $BASE/frontend $WORKDIR/
	cp -r $BASE/installer $WORKDIR/
	cp -r $BASE/backend/startupscripts $WORKDIR/
	mkdir $WORKDIR/$ARCH/core && mkdir $WORKDIR/$ARCH/sdk
	# necessary to track release changes in corepackages (only for internal development) 
	[ ! -d $PACKAGE_DIR ] && cp -r $BASE/backend/corepackages $WORKDIR/$ARCH/
	cp $BASE/backend/configs/buildroot_$ARCH.config $WORKDIR/$ARCH/build/.config
}

make_pulsar ()
{
	echo "Compile $arch build" 
	cd $WORKDIR
	
	# copy external changes to buildroot framework
	cp $BASE/backend/configs/e2fsprogs.mk $WORKDIR/$ARCH/build/package/e2fsprogs/
	
	# copy all 3rd party packages to buildroot
	cp -r $BASE/backend/packages/* $WORKDIR/$ARCH/build/package
	if [ `grep -c PulsarOS $WORKDIR/$ARCH/build/package/Config.in` = 0 ]; then
		echo 'menu "Additional PulsarOS packages"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/monit/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/extlinux/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/pacman/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/volmgr/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/swraid/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/sdkbinutils/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/sdkgcc/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/sdkgmp/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/sdkmpc/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/sdkmpfr/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/sdkuclibc/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'source "package/sshpass/Config.in"' >> $WORKDIR/$ARCH/build/package/Config.in
		echo 'endmenu' >> $WORKDIR/$ARCH/build/package/Config.in
	fi
	
	# build the backend
	cd $WORKDIR/$ARCH/build && make
	
	# copy kernel and basesystem to $BOOT_HOME
	if [ $ARCH = "arm" ]; then
		cp $WORKDIR/$ARCH/build/output/images/zImage $BOOT_HOME/
	else
		cp $WORKDIR/$ARCH/build/output/images/bzImage $BOOT_HOME/
	fi
	cp $WORKDIR/$ARCH/build/output/images/rootfs.ext2.bz2 $BOOT_HOME/initrd.bz2
	
		
	# move base packages to BOOT
	cd $WORKDIR/$ARCH/core/ && wget -r -l 1 -nd --accept pkg.tar.gz http://repo.pulsaros.com/$ARCH/core_dev
	# we use the new build versions
	rm $WORKDIR/$ARCH/core/kernel* && rm $WORKDIR/$ARCH/core/basesystem* rm $WORKDIR/$ARCH/core/frontend*
	rm robots.txt
	cp $WORKDIR/$ARCH/core/* $WORKDIR/$ARCH/boot/core
	
	# copy needed gpg headers (for development reasons) to gcc package
	#cp $WORKDIR/$ARCH/build/output/build/libgcrypt-*/src/gcrypt.h $PACKAGE_DIR/gcc/local/include/
	#cp $WORKDIR/$ARCH/build/output/build/libgcrypt-*/src/gcrypt-module.h $PACKAGE_DIR/gcc/local/include/
	#cp $WORKDIR/$ARCH/build/output/build/libgpg-error-*/src/gpg-error.h $PACKAGE_DIR/gcc/local/include/
	
	# copy needed gnutls headers (for development reasons) to gcc package
	#cp -r $WORKDIR/$ARCH/build/output/build/gnutls-*/lib/includes/gnutls $PACKAGE_DIR/gcc/local/include/
	#rm $PACKAGE_DIR/gcc/local/include/gnutls/gnutls.h.in
	
	# build gcc package
	PKGVERSION=`cat $PACKAGE_DIR/gcc/PKGBUILD|grep pkgrel|awk -F= '{print $2}'`
	NEWPKGVERSION=$(($PKGVERSION+1))
	cat $PACKAGE_DIR/gcc/PKGBUILD| sed "s|pkgrel=$PKGVERSION|pkgrel=$NEWPKGVERSION|g" > PKGBUILD_TMP
	mv PKGBUILD_TMP $PACKAGE_DIR/gcc/PKGBUILD
	cd $PACKAGE_DIR/gcc && makepkg -f --skipinteg
	cp $PACKAGE_DIR/gcc/gcc-$VERSION-* $WORKDIR/$ARCH/sdk
	
	# build kernel package
	if [ -f $WORKDIR/$ARCH/build/output/images/zImage ]; then
		cp $WORKDIR/$ARCH/build/output/images/zImage $PACKAGE_DIR/kernel/
	else
		cp $WORKDIR/$ARCH/build/output/images/bzImage $PACKAGE_DIR/kernel/
	fi
	PKGVERSION=`cat $PACKAGE_DIR/kernel/PKGBUILD|grep pkgrel|awk -F= '{print $2}'`
	NEWPKGVERSION=$(($PKGVERSION+1))
	cat $PACKAGE_DIR/kernel/PKGBUILD| sed "s|pkgrel=$PKGVERSION|pkgrel=$NEWPKGVERSION|g" > PKGBUILD_TMP
	mv PKGBUILD_TMP $PACKAGE_DIR/kernel/PKGBUILD
	cd $PACKAGE_DIR/kernel && makepkg -f --skipinteg
	cp $PACKAGE_DIR/kernel/kernel-$VERSION-* $WORKDIR/$ARCH/boot/core/
	cp $PACKAGE_DIR/kernel/kernel-$VERSION-* $WORKDIR/$ARCH/core/
	
	# build basesystem package
	cp $WORKDIR/$ARCH/build/output/images/rootfs.ext2.bz2 $PACKAGE_DIR/basesystem/initrd.bz2
	cp -r $WORKDIR/pulsarroot $PACKAGE_DIR/basesystem/
	PKGVERSION=`cat $PACKAGE_DIR/basesystem/PKGBUILD|grep pkgrel|awk -F= '{print $2}'`
	NEWPKGVERSION=$(($PKGVERSION+1))
	cat $PACKAGE_DIR/basesystem/PKGBUILD| sed "s|pkgrel=$PKGVERSION|pkgrel=$NEWPKGVERSION|g" > PKGBUILD_TMP
	mv PKGBUILD_TMP $PACKAGE_DIR/basesystem/PKGBUILD
	cd $PACKAGE_DIR/basesystem && sudo makepkg -f --skipinteg --asroot
	cp $PACKAGE_DIR/basesystem/basesystem-$VERSION-* $WORKDIR/$ARCH/boot/core/
	cp $PACKAGE_DIR/basesystem/basesystem-$VERSION-* $WORKDIR/$ARCH/core/
	
	# cleanup package directories
	sudo rm -r $PACKAGE_DIR/basesystem/initrd* $PACKAGE_DIR/basesystem/pkg $PACKAGE_DIR/basesystem/src $PACKAGE_DIR/basesystem/basesystem-$VERSION-*
	if [ -f $PACKAGE_DIR/kernel/zImage ]; then
		sudo rm -r $PACKAGE_DIR/kernel/zImage
	else
		sudo rm -r $PACKAGE_DIR/kernel/bzImage
	fi
	sudo rm -r $PACKAGE_DIR/kernel/pkg $PACKAGE_DIR/kernel/src $PACKAGE_DIR/kernel/kernel-$VERSION-*
	sudo rm -r $PACKAGE_DIR/gcc/pkg $PACKAGE_DIR/gcc/gcc-$VERSION-*
}

build_frontend ()
{
	cp -r $WORKDIR/frontend $PACKAGE_DIR/frontend/
	PKGVERSION=`cat $PACKAGE_DIR/frontend/PKGBUILD|grep pkgrel|awk -F= '{print $2}'`
	NEWPKGVERSION=$(($PKGVERSION+1))
	cat $PACKAGE_DIR/frontend/PKGBUILD| sed "s|pkgrel=$PKGVERSION|pkgrel=$NEWPKGVERSION|g" > PKGBUILD_TMP
	mv PKGBUILD_TMP $PACKAGE_DIR/frontend/PKGBUILD
	cd $PACKAGE_DIR/frontend && sudo makepkg -f --skipinteg --asroot
	cp $PACKAGE_DIR/frontend/frontend-$VERSION-* $WORKDIR/$ARCH/boot/core/
	cp $PACKAGE_DIR/frontend/frontend-$VERSION-* $WORKDIR/$ARCH/core
	sudo rm -r $PACKAGE_DIR/frontend/frontend $PACKAGE_DIR/frontend/pkg $PACKAGE_DIR/frontend/src $PACKAGE_DIR/frontend/frontend-$VERSION-*
}

make_image ()
{
	echo "Make image"
	cp $BASE/gpl.txt $BASE/backend/configs/isolinux.bin $BOOT_HOME
	cp $BASE/backend/configs/isolinux_$ARCH.cfg $BOOT_HOME/isolinux.cfg
	cd $WORKDIR
	sudo genisoimage -r -J -pad -b boot/isolinux/isolinux.bin -c boot/isolinux/boot.cat -no-emul-boot -boot-info-table -boot-load-size 4 -o $WORKDIR/images/pulsaros_$ARCH.iso $ARCH/boot
}

# Main script

# Syntax check
[ $# != 3 ] && printf "Argument expected: setup.sh 'arch' 'workdir_location' '[all|frontend]'\n" && exit 1

prepare_pulsar
stage_pulsar

case $3 in
	"all")
		make_pulsar
		build_frontend
		make_image
	;;
	"frontend")
		build_frontend
	;;
	*)
		printf "Only the functions 'all' and 'frontend' are implemented"
	;;
esac
exit 0