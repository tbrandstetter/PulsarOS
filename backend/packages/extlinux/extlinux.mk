#############################################################
#
# EXTLINUX
#
#############################################################

EXTLINUX_VERSION:=4.04
#EXTLINUX_SOURCE:=syslinux-$(EXTLINUX_VERSION).tar.bz2
#EXTLINUX_SITE:=http://www.kernel.org/pub/linux/utils/boot/syslinux
EXTLINUX_SOURCE:=syslinux_$(EXTLINUX_VERSION)+dfsg.orig.tar.gz
EXTLINUX_SITE:=http://ftp.de.debian.org/debian/pool/main/s/syslinux
EXTLINUX_TARGET_BINS:=extlinux
EXTLINUX_INSTALL_STAGING:=NO

define EXTLINUX_BUILD_CMDS
	cd $(@D)/libinstaller && $(MAKE)
	cd $(@D)/extlinux && $(MAKE) -e CC="$(TARGET_CC)"
endef

define EXTLINUX_INSTALL_TARGET_CMDS
	cp -dpf $(@D)/extlinux/extlinux $(TARGET_DIR)/usr/sbin
	-$(STRIPCMD) $(STRIP_STRIP_UNNEEDED) $(TARGET_DIR)/usr/sbin/extlinux
	cp -dpf $(@D)/mbr/mbr.bin $(TARGET_DIR)/usr/share/
endef

$(eval $(call GENTARGETS,package,extlinux))