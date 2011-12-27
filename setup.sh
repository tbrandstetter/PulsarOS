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
ARCH=$1
BASE=`pwd`
WORKDIR=$2
BOOT_HOME=$WORKDIR/boot_$ARCH/boot/isolinux
MOUNT_CD=$WORKDIR/mount/cdrom
MOUNT_PULSAR=$WORKDIR/mount/pulsar
PACKAGE_DIR=$WORKDIR/corepackages
GCC_DIR=$WORKDIR/gcc

# Build the pulsar os image
# Functions

prepare_pulsar ()
{
	echo "Prepare $ARCH build"
	# create necessary directories
	[ ! -f $WORKDIR/.prepared ] && mkdir -p $BOOT_HOME $MOUNT_CD $MOUNT_PULSAR $WORKDIR/images $GCC_DIR
	[ -d $WORKDIR/configs ] && rm -rf $WORKDIR/configs
	[ -d $WORKDIR/pulsarroot ] && rm -rf $WORKDIR/pulsarroot
	[ -d $WORKDIR/startupscripts ] && rm -rf $WORKDIR/startupscripts
	[ -d $WORKDIR/corepackages ] && rm -rf $WORKDIR/corepackages
	[ -d $WORKDIR/build_$ARCH/output ] && rm -rf $WORKDIR/build_$ARCH/output
	[ -f $BOOT_HOME/initrd.bz2 ] && rm $BOOT_HOME/initrd*
	[ -f $BOOT_HOME/bzImage ] && rm $BOOT_HOME/bzImage
	if [ -d $WORKDIR/boot_$ARCH/core ]; then
		rm  $WORKDIR/boot_$ARCH/core/*
	else
		mkdir -p $WORKDIR/boot_$ARCH/core
	fi
	[ ! -f $WORKDIR/boot_$ARCH/.pulsarinstall ] && touch $WORKDIR/boot_$ARCH/.pulsarinstall
}

make_pulsar ()
{
	echo "Download & prepare buildroot"
	if [ ! -d $WORKDIR/build_$ARCH ]; then
		cd $WORKDIR
		wget http://www.buildroot.net/downloads/buildroot-2011.05.tar.bz2
		bzip2 -d buildroot-2011.05.tar.bz2 && tar -xf buildroot-2011.05.tar
		mv buildroot-2011.05 build_$ARCH
		touch .prepared
	fi
	
	echo "Staging workdir..."
	cp -r $BASE/backend/configs $WORKDIR/
	cp -r $BASE/backend/pulsarroot $WORKDIR/
	cp -r $BASE/frontend/pulsarroot/frontend $WORKDIR/
	cp -r $BASE/installer $WORKDIR/
	cp -r $BASE/backend/startupscripts $WORKDIR/
	cp -r $BASE/backend/corepackages $WORKDIR/
	cp $BASE/backend/configs/buildroot_$ARCH.config $WORKDIR/build_$ARCH/.config
	
	echo "Compile $arch build" 
	cd $WORKDIR
	
	# copy external changes to buildroot framework
	cp $BASE/backend/configs/e2fsprogs.mk $WORKDIR/build_$ARCH/package/e2fsprogs/
	
	# copy all 3rd party packages to buildroot
	cp -r $BASE/backend/packages/* $WORKDIR/build_$ARCH/package
	if [ `grep -c PulsarOS $WORKDIR/build_$ARCH/package/Config.in` = 0 ]; then
		echo 'menu "Additional PulsarOS packages"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/monit/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/extlinux/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/pacman/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/volmgr/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/swraid/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/sdkbinutils/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/sdkgcc/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/sdkgmp/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/sdkmpc/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/sdkmpfr/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/sdkuclibc/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/sshpass/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/msmtp/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'source "package/gnutls/Config.in"' >> $WORKDIR/build_$ARCH/package/Config.in
		echo 'endmenu' >> $WORKDIR/build_$ARCH/package/Config.in
	fi
	
	# build the backend
	cd $WORKDIR/build_$ARCH && make
	
	# copy kernel and basesystem to $BOOT_HOME
	cp $WORKDIR/build_$ARCH/output/images/bzImage $BOOT_HOME/
	cp $WORKDIR/build_$ARCH/output/images/rootfs.ext2.bz2 $BOOT_HOME/initrd.bz2
	
		
	# move base packages to BOOT
	cd $WORKDIR/boot_$ARCH/core/ && wget -r -l 1 -nd --accept pkg.tar.gz http://repo.pulsaros.com/core
	rm robots.txt
	
	# copy needed gpg headers (for development reasons) to gcc package
	cp $WORKDIR/build_$ARCH/output/build/libgcrypt-*/src/gcrypt.h $GCC_DIR/local/include/
	cp $WORKDIR/build_$ARCH/output/build/libgcrypt-*/src/gcrypt-module.h $GCC_DIR/local/include/
	cp $WORKDIR/build_$ARCH/output/build/libgpg-error-*/src/gpg-error.h $GCC_DIR/local/include/
	
	# build gcc package
	cp $PACKAGE_DIR/gcc/PKGBUILD $GCC_DIR/
	cd $GCC_DIR && makepkg -f --skipinteg
	
	# build kernel package
	cp $WORKDIR/build_$ARCH/output/images/bzImage $PACKAGE_DIR/kernel/
	cd $PACKAGE_DIR/kernel && makepkg -f --skipinteg
	cp $PACKAGE_DIR/kernel/kernel-$VERSION-* $WORKDIR/boot_$ARCH/core/
	
	# build basesystem package
	cp $WORKDIR/build_$ARCH/output/images/rootfs.ext2.bz2 $PACKAGE_DIR/basesystem/initrd.bz2
	cp -r $WORKDIR/pulsarroot $PACKAGE_DIR/basesystem/
	cd $PACKAGE_DIR/basesystem && sudo makepkg -f --skipinteg --asroot
	cp $PACKAGE_DIR/basesystem/basesystem-$VERSION-* $WORKDIR/boot_$ARCH/core/
	
	# build frontend package
	build_frontend
	
	# cleanup package directories
	sudo rm -r $PACKAGE_DIR/basesystem/initrd* $PACKAGE_DIR/basesystem/pkg $PACKAGE_DIR/basesystem/src $PACKAGE_DIR/basesystem/basesystem-$VERSION-*
	sudo rm -r $PACKAGE_DIR/kernel/bzImage $PACKAGE_DIR/kernel/pkg $PACKAGE_DIR/kernel/src $PACKAGE_DIR/kernel/kernel-$VERSION-*
	sudo rm -r $PACKAGE_DIR/frontend/frontend $PACKAGE_DIR/frontend/pkg $PACKAGE_DIR/frontend/src $PACKAGE_DIR/kernel/frontend-$VERSION-*
}

build_frontend ()
{
	cp -r $WORKDIR/frontend $PACKAGE_DIR/frontend/
	cd $PACKAGE_DIR/frontend && sudo makepkg -f --skipinteg --asroot
	cp $PACKAGE_DIR/frontend/frontend-$VERSION-* $WORKDIR/boot_$ARCH/core/
}

make_image ()
{
	echo "Make image"
	cp $BASE/gpl.txt $BASE/backend/configs/isolinux.bin $BASE/backend/configs/isolinux.cfg $BOOT_HOME
	cd $WORKDIR
	sudo genisoimage -r -J -pad -b boot/isolinux/isolinux.bin -c boot/isolinux/boot.cat -no-emul-boot -boot-info-table -boot-load-size 4 -o $WORKDIR/images/pulsaros_$ARCH.iso boot_$ARCH
}

# Main script

# Syntax check
[ $# != 3 ] && printf "Argument expected: setup.sh 'arch' 'workdir_location' '[all|frontend]'\n" && exit 1

prepare_pulsar

case $2 in
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