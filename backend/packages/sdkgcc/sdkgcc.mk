#############################################################
#
# gcc
#
#############################################################

SDKGCC_VERSION = 4.4.6
SDKGCC_SOURCE = gcc-$(SDKGCC_VERSION).tar.bz2
SDKGCC_SITE = $(BR2_GNU_MIRROR)/gcc
SDKGCC_DEPENDENCIES = sdkgmp sdkmpfr sdkmpc
SDKGCC_CONF_OPT = --build=x86_64-unknown-linux-gnu \
		--host=$(REAL_GNU_TARGET_NAME) \
		--target=$(REAL_GNU_TARGET_NAME) \
		--enable-languages=c,c++ \
		--with-gxx-include-dir=/usr/local/include/c++ \
		--disable-__cxa_atexit \
		--with-gnu-ld \
		--disable-libssp \
		--disable-multilib \
		--enable-tls \
		--enable-shared \
		--disable-nls \
		--enable-threads \
		--disable-decimal-float \
		$(GCC_WITH_ARCH) \
		$(GCC_WITH_TUNE) \
		--with-pkgversion="PulsarOS SDK" \
		--prefix=/usr/local \
		--exec-prefix=/usr/local

$(eval $(call AUTOTARGETS,package,sdkgcc))
