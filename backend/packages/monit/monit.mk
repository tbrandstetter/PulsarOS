#############################################################
#
# MONIT
#
#############################################################

MONIT_VERSION:=5.2.5
MONIT_SOURCE:=monit-$(MONIT_VERSION).tar.gz
MONIT_SITE:=http://www.mmonit.com/monit/dist
MONIT_INSTALL_STAGING:=NO

define MONIT_CONFIGURE_CMDS
#	cp package/monit/file.c $(@D)
        (cd $(@D); rm -rf config.cache; \
        	$(TARGET_CONFIGURE_ARGS) \
        	$(TARGET_CONFIGURE_OPTS) \
        	./configure \
        	--host=$(GNU_TARGET_NAME) \
        	--prefix=/usr \
			--without-pam \
			--without-ssl \
        )
endef

define MONIT_BUILD_CMDS
	$(MAKE) -C $(@D) all
endef

define MONIT_INSTALL_TARGET_CMDS
	cp -dpf $(@D)/monit $(TARGET_DIR)/usr/sbin
	-$(STRIPCMD) $(STRIP_STRIP_UNNEEDED) $(TARGET_DIR)/usr/sbin/monit
	
endef

$(eval $(call GENTARGETS,package,monit))
