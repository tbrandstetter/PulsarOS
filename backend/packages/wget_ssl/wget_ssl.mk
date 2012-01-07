#############################################################
#
# wget_ssl
#
#############################################################

WGET_SSL_VERSION = 1.13.4
WGET_SSL_SOURCE = wget-$(WGET_VERSION).tar.bz2
WGET_SSL_SITE = $(BR2_GNU_MIRROR)/wget
WGET_SSL_CONF_OPT += --with-ssl=gnutls --with-libgnutls-prefix=$(STAGING_DIR)
WGET_SSL_DEPENDENCIES += gnutls

$(eval $(call AUTOTARGETS,package,wget_ssl))