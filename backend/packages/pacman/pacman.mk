#############################################################
#
# PACMAN
#
#############################################################

PACMAN_VERSION:=3.5.3
PACMAN_SOURCE:=pacman-$(PACMAN_VERSION).tar.gz
PACMAN_SITE:=ftp://ftp.archlinux.org/other/pacman
PACMAN_INSTALL_STAGING:=NO

PACMAN_CONF_OPT = 	--sysconfdir=/pulsarcore/configs/pacman \
					--disable-git-version \
					--without-libiconv-prefix \
					--without-libintl-prefix \
					--without-openssl

define PACMAN_CORRECT_STRIP_PATH
	# change path of strip commando in makepkg script
	cat $(TARGET_DIR)/usr/bin/makepkg | sed "s|usr\/bin\/strip|usr\/local\/bin\/strip|g" > $(@D)/makepkg_new
	cp $(@D)/makepkg_new $(TARGET_DIR)/usr/bin/makepkg
endef

PACMAN_POST_INSTALL_TARGET_HOOKS += PACMAN_CORRECT_STRIP_PATH

$(eval $(call AUTOTARGETS,package,pacman))
