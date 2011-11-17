#############################################################
#
# SDKUCLIBC
#
#############################################################

SDKUCLIBC_VERSION:=0.9.32-rc2
SDKUCLIBC_SITE:=http://www.uclibc.org/downloads
SDKUCLIBC_SOURCE = uClibc-$(SDKUCLIBC_VERSION).tar.bz2

define SDKUCLIBC_BUILD_CMDS
	$(MAKE1) -C $(UCLIBC_DIR) CC=$(TARGET_CROSS)gcc \
		CPP=$(TARGET_CROSS)cpp LD=$(TARGET_CROSS)ld \
		ARCH="$(UCLIBC_TARGET_ARCH)" \
		PREFIX=$(TARGET_DIR) utils install_utils

	$(MAKE1) -C $(UCLIBC_DIR) \
		ARCH="$(UCLIBC_TARGET_ARCH)" \
		PREFIX=$(TARGET_DIR) \
		DEVEL_PREFIX=/usr/local/ \
		RUNTIME_PREFIX=/ \
		install_dev
endef

$(eval $(call GENTARGETS,package,sdkuclibc))
