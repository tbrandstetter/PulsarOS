#############################################################
#
# MSMTP
#
#############################################################

MSMTP_VERSION = 1.4.24
MSMTP_SOURCE = msmtp-$(MSMTP_VERSION).tar.bz2
MSMTP_SITE = http://download.sourceforge.net/sourceforge/msmtp
MSMTP_DEPENDENCIES = gnutls
MSMTP_INSTALL_STAGING = NO
MSMTP_CONF_OPT = 	--prefix=/usr \
					--exec-prefix=/usr \
					--sysconfdir=/pulsarroot/configs/system \
					--with-ssl=gnutls
					
$(eval $(call AUTOTARGETS,package,msmtp))