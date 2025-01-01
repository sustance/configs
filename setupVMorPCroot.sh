#!/usr/bin/sh
# For Linux(debian) OpenBSD and FreeBSD
# Assumes user is "aaa"
#sudo -u user
#doas -u user
git="https://github.com/sustance/configs/blob/main/"
git="https://raw.githubusercontent.com/sustance/configs/refs/heads/main/"

cd /home/aaa/
pkg_add curl rofi micro cwm
touch /etc/doas.conf
echo "permit persist keepenv aaa as root" >> /etc/doas.conf
touch /home/aaa/.local/bin/mm
chmod +x /home/aaa/.local/bin/mm
chown aaa:aaa /home/aaa/.local/bin/mm
