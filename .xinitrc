xrandr --dpi 168
#xrandr --dpi 160

export GTK_IM_MODULE=fcitx
export QT_IM_MODULE=fcitx
export XMODIFIERS=@im=fcitx
export DefaultIMModule=fcitx
#fcitx &
#fcitx-diagnose

exec cwm
