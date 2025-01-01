#!/usr/bin/sh
git="https://github.com/sustance/configs/blob/main/"
git="https://raw.githubusercontent.com/sustance/configs/refs/heads/main/"
cd /home/aaa/
curl ${git}mm /home/aaa/.local/bin/mm
curl ${git}config.rasi /home/aaa/.config/rofi/config.rasi
curl ${git}.cwmrc /home/aaa/.cwmrc

echo "exec cwm" >> .xinitrc
#cat "Xft.dpi: 196" >> .xsession
touch .bashrc
echo 'export PATH="$HOME/.local/bin:$PATH"' >> /home/aaa/.bashrc

curl https://github.com/sustance/configs/blob/main/mm /home/aaa/.local/bin/mm
curl https://github.com/sustance/configs/blob/main/config.rasi ~/local/bin/config.rasi
curl https://raw.githubusercontent.com/sustance/configs/refs/heads/main/.cwmrc ~/.cwmrc

