#############################################################
#
# mpc
#
#############################################################

SDKMPC_VERSION = 0.8.2
SDKMPC_SITE = http://www.multiprecision.org/mpc/download
SDKMPC_SOURCE = mpc-$(SDKMPC_VERSION).tar.gz
SDKMPC_CONF_OPT = --prefix=/usr/local --exec-prefix=/usr/local
SDKMPC_DEPENDENCIES = sdkgmp sdkmpfr

$(eval $(call AUTOTARGETS,package,sdkmpc))
