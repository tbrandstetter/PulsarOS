#############################################################
#
# gmp
#
#############################################################

SDKGMP_VERSION = 5.0.1
SDKGMP_SITE = $(BR2_GNU_MIRROR)/gmp
SDKGMP_SOURCE = gmp-$(SDKGMP_VERSION).tar.bz2
SDKGMP_CONF_OPT = --prefix=/usr/local --exec-prefix=/usr/local
SDKGMP_INSTALL_STAGING = YES

$(eval $(call AUTOTARGETS,package,sdkgmp))
