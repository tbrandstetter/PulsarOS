#############################################################
#
# gnutls
#
#############################################################

GNUTLS_VERSION = 2.12.5
GNUTLS_SITE = $(BR2_GNU_MIRROR)/gnutls
GNUTLS_SOURCE = gnutls-$(GNUTLS_VERSION).tar.bz2
GNUTLS_CONF_ENV = LIBS=-ldl
GNUTLS_CONF_OPT = 	--with-libgcrypt \
					--disable-static \
					--disable-gtk-doc-html \
					--disable-camellia \
					--disable-srp-authentication \
					--disable-openpgp-authentication \
					--with-included-libtasn1 \
					--disable-guile
					
GNUTLS_INSTALL_STAGING = YES
GNUTLS_DEPENDENCIES = libgcrypt

$(eval $(call AUTOTARGETS,package,gnutls))
