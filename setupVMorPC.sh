#!/usr/bin/sh
# For Linux(debian) OpenBSD and FreeBSD

cd ~
cat "exec cwm" >> .xinitrc
#cat "Xft.dpi: 196" >> .xsession
cat 'export PATH="$HOME/.local/bin:$PATH"' >> .bashrc


curl https://github.com/sustance/configs/blob/main/mm .local/bin/

curl https://github.com/sustance/configs/blob/main/config.rasi ~/local/bin/

curl https://raw.githubusercontent.com/sustance/configs/refs/heads/main/.cwmrc ~

echo "Now... Obsd: 'login root', Debian: sudo Fbsd: doas 
echo "Run suggested script as root"
echo "install rofi micro cwm
echo " As root chmod +x ~/.local/bin/mm
