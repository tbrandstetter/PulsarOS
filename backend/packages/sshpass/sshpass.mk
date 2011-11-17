#############################################################
#
# SSHPASS
#
#############################################################

SSHPASS_VERSION = 1.04
SSHPASS_SITE = http://sourceforge.net/projects/sshpass/files/sshpass/$(SSHPASS_VERSION)
SSHPASS_SOURCE = sshpass-$(SSHPASS_VERSION).tar.gz
SSHPASS_CONF_OPT = --prefix=/usr --exec-prefix=/usr

$(eval $(call AUTOTARGETS,package,sshpass))