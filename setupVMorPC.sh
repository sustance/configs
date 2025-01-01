#!/usr/bin/sh
git="https://github.com/sustance/configs/blob/main/"
aaa="/home/aaa/"
# For Linux(debian) OpenBSD and FreeBSD
# Assumes user is "aaa"
#sudo -u user
#doas -u user
pkg_add curl rofi micro cwm

EOF

cd /home/aaa/

#no curl on Obsd 
pkg_add curl rofi micro cwm
curl ${git}mm ${aaa}.local/bin/mm
curl ${git}config.rasi ${aaa}.config/rofi/config.rasi
curl ${git}.cwmrc ${aaa}.cwmrc




cat "exec cwm" >> .xinitrc
#cat "Xft.dpi: 196" >> .xsession
touch .bashrc
echo 'export PATH="$HOME/.local/bin:$PATH"' >> /home/aaa/.bashrc

#no curl on Obsd 
curl https://github.com/sustance/configs/blob/main/mm /home/aaa/.local/bin/mm

curl https://github.com/sustance/configs/blob/main/config.rasi ~/local/bin/config.rasi

curl https://raw.githubusercontent.com/sustance/configs/refs/heads/main/.cwmrc ~/.cwmrc

echo "Now... Obsd: 'login root', Debian: sudo Fbsd: doas 
echo "Run suggested script as root"
echo "install rofi micro cwm
echo " As root chmod +x ~/.local/bin/mm



