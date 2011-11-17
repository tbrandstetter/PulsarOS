#############################################################
#
# swraid
#
#############################################################
SWRAID_VERSION:=3.1.5
SWRAID_SOURCE:=mdadm-$(SWRAID_VERSION).tar.bz2
#SWRAID_SITE:=http://www.kernel.org/pub/linux/utils/raid/mdadm
#SWRAID_SITE:=ftp://gd.tuwien.ac.at/opsys/linux/kernel.org/linux/utils/raid/mdadm
SWRAID_SITE=http://mirrors.kernel.org/gentoo/distfiles
SWRAID_AUTORECONF = NO

SWRAID_INSTALL_STAGING = NO
SWRAID_INSTALL_TARGET = YES

SWRAID_MAKE_OPT = \
	CFLAGS="$(TARGET_CFLAGS)" CC="$(TARGET_CC)" -C $(SWRAID_DIR) mdadm

SWRAID_INSTALL_TARGET_OPT = \
	DESTDIR=$(TARGET_DIR)/usr CFLAGS="$(TARGET_CFLAGS)" CC="$(TARGET_CC)" -C $(SWRAID_DIR) install

SWRAID_UNINSTALL_TARGET_OPT = \
	DESTDIR=$(TARGET_DIR)/usr -C $(SWRAID_DIR) uninstall

define SWRAID_CONFIGURE_CMDS
	# do nothing
endef

$(eval $(call AUTOTARGETS,package,swraid))
