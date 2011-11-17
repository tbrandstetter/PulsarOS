#############################################################
#
# mpfr
#
#############################################################

SDKMPFR_VERSION = 3.0.1
SDKMPFR_SITE = http://www.mpfr.org/mpfr-$(SDKMPFR_VERSION)
SDKMPFR_SOURCE = mpfr-$(SDKMPFR_VERSION).tar.bz2
SDKMPFR_INSTALL_STAGING = YES
SDKMPFR_DEPENDENCIES = sdkgmp
SDKMPFR_CONF_OPT = --prefix=/usr/local --exec-prefix=/usr/local
SDKMPFR_MAKE_OPT = RANLIB=$(TARGET_RANLIB)

$(eval $(call AUTOTARGETS,package,sdkmpfr))
