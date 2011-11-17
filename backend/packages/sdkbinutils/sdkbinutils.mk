#############################################################
#
# binutils
#
#############################################################

SDKBINUTILS_VERSION = 2.21
SDKBINUTILS_SOURCE = binutils-$(SDKBINUTILS_VERSION).tar.bz2
SDKBINUTILS_SITE = $(BR2_GNU_MIRROR)/binutils

# We need to specify host & target to avoid breaking ARM EABI
SDKBINUTILS_CONF_OPT = --disable-multilib --disable-werror \
		--host=$(REAL_GNU_TARGET_NAME) \
		--target=$(REAL_GNU_TARGET_NAME) \
		--enable-shared \
		--prefix=/usr/local \
		--exec-prefix=/usr/local \
		--libdir=/usr/local/lib \
		--includedir=/usr/local/include
		
# Install binutils after busybox to prefer full-blown utilities
ifeq ($(BR2_PACKAGE_BUSYBOX),y)
BINUTILS_DEPENDENCIES += busybox
endif

SDKBINUTILS_INSTALL_FROM = $(@D)

define SDKBINUTILS_INSTALL_TARGET_CMDS
	$(TARGET_MAKE_ENV) $(MAKE) -C $(SDKBINUTILS_INSTALL_FROM) \
		DESTDIR=$(TARGET_DIR) install
endef

$(eval $(call AUTOTARGETS,package,sdkbinutils))
