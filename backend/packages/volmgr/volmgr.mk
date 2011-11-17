#############################################################
#
# VOLMGR
#
#############################################################
VOLMGR_VERSION = 2.02.85
VOLMGR_SOURCE = LVM2.$(VOLMGR_VERSION).tgz
VOLMGR_SITE = ftp://sources.redhat.com/pub/lvm2/releases
VOLMGR_INSTALL_STAGING = YES

VOLMGR_BINS = \
	dmsetup fsadm lvm lvmconf lvmdump vgimportclone \
	lvchange lvconvert lvcreate lvdisplay lvextend 	\
	lvmchange lvmdiskscan lvmsadc lvmsar lvreduce  	\
	lvremove lvrename lvresize lvs lvscan pvchange 	\
	pvck pvcreate pvdisplay pvmove pvremove 		\
	pvresize pvs pvscan vgcfgbackup vgcfgrestore 	\
	vgchange vgck vgconvert vgcreate vgdisplay 		\
	vgexport vgextend vgimport vgmerge vgmknodes 	\
	vgreduce vgremove vgrename vgs vgscan vgsplit

# Make sure that binaries and libraries are installed with write
# permissions for the owner.
VOLMGR_CONF_OPT += --enable-write_install --prefix=/usr/local 

# VOLMGR uses autoconf, but not automake, and the build system does not
# take into account the CC passed at configure time.
VOLMGR_MAKE_ENV = CC="$(TARGET_CC)"

ifeq ($(BR2_PACKAGE_READLINE),y)
VOLMGR_DEPENDENCIES += readline
else
# v2.02.44: disable readline usage, or binaries are linked against provider
# of "tgetent" (=> ncurses) even if it's not used..
VOLMGR_CONF_OPT += --disable-readline
endif

define VOLMGR_UNINSTALL_STAGING_CMDS
	rm -f $(addprefix $(STAGING_DIR)/usr/sbin/,$(VOLMGR_BINS))
	rm -f $(addprefix $(STAGING_DIR)/usr/lib/,libdevmapper.so*)
endef

define VOLMGR_UNINSTALL_TARGET_CMDS
	rm -f $(addprefix $(TARGET_DIR)/usr/sbin/,$(VOLMGR_BINS))
	rm -f $(addprefix $(TARGET_DIR)/usr/lib/,libdevmapper.so*)
endef

$(eval $(call AUTOTARGETS,package,volmgr))
