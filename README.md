
# 🇨​🇴​🇳​🇫​🇮​🇬​'s

### Philosophy 
- Start with OpenBSD standard install which includes CWM and include X and xenodm X launcher 
and CWM window manager. 
- Add only enough aps and complexity to easily run the windows tui's cli's 
you need.
- Avoid anything possible that runs a background process.
- Compromise, you do need to run modern browsers, Socil media, switch to new 
Wifi's anf etc. 
When you compromise but make the least possible compromises and find how to kill thee bloat when you finish.
- When OpenBSD won't run on your hardware, or your software (which 
is most of the time) use Freebsd or Linux, you lose only some security.
- OpenBSD will run well on QEMU VM or your old low spec PC/Laptop
- My ambition is to use the same configs across all the *BSD's and Linux disto's.
- This constrains choices in order to have the same apps and configs across all server and local machines.

My previous "Ubuntu everywhere" setup was less secure, slower starting, 
more distractions, had a smaller usable "desktop" and about 150% of 
the battery consumption of the configuration below.

### Unresolved issues are:
- Handling multiple badly configured "automatic" wifi logins when travelling.
  Until I fix it I reboot into Ubuntu and login then Reboot back to CWM.
- Evolve mm to add to existing function a TUI interface to mm which lists 
  options and a short explanation.
- My own clumsy inefficient, insecure code 
- Identifying more processes I dont want and getting rid of them.
- A bsd with OpenBSD security, FreeBSD compatability and Linux speed. I wish!
- People to comment on and criticise this setup.
- Better AI's that make shell scripts more understandable and even 
  easier to write.
- A return of the cheap smaller servers with just enough 
  disk and ram for a tiny *bsd to run ssh socks proxies thru. 
  Today you need a number to avoid all the access barriers globally. 

Those configs are below

# configs.
 - Wherever possible the same configs are used on all system types. 
 - In some cases things will not work, for me its easier to 
remember what doesn't work than remember the exact command to make it work.
Configs below are maintained in this project

At the lowest level and working on cli of all systems is 

### ~/.local/bin/mm (a shell script)
This menu will display a list of options or launch them with "mm <option>"
this works on headless systems.

### ~/.xinitrc
xrandr --dpi 168
export GTK_IM_MODULE=fcitx
export QT_IM_MODULE=fcitx
export XMODIFIERS=@im=fcitx
export DefaultIMModule=fcitx
exec cwm

### ~/.xsession
 - Xft.dpi: 196

### ~/.cwmrc
menu launched by r-mouse click on background
on gui systems this launches the menu. if it makes sense it actually 
runs the command from "mm <option>" 
 - Note there are heaps of "better" WM's than CWM. I choose to stay as 
 possible to my ideal OpenBSD generic vanilla system as possible

### ~/.config/rofi/config.rasi
menu launched by Meta-a to launch or Meta-w to switch to open window
This will launch programs that install a desktop file or search 
for installed programs by name.
If I add my own desktop I try to get it to call a mm <option>

### xfce4-panel
~/?
I install this and hope never to run it, its mainly for someone 
else who uses the PC and wants a desktop. or if I want a quick 
look at time, network, etc. other I prefer the extra realestate.

### ~/.tint2/tint2rc
Have also installed this panel but will probably drop it, see 
below about installing entire DM below CWM

### .newsboat/urls
For those servers with CLI newsreader installed
 
### xfce4 gnome 
Disk space is usually surplus to requirements I sometimes install 
a full xfce4 dsektop. I think it runs as few  background proceses 
as possible if I dont launch anything and it,s handy for other users. 
I need to learn more about this and other desktops (e.g. gnome) 
installed but unused except for some apps and third parties and 
wierd stuff like getting the login menu to pop up for hotel wifi!

I run on a daily basis: 
Texstudio, Inkscape, Gimp, Telegram, Gramps, 
Virtual-Machine-Manager, QEMU, PDFArranger, LibreOffice, 
Lagrange, xfce-terminal, Xfe, Xfwrite, GoogleChrome,
Firefox,NMTUI, gnome-settings, xfce4-settings

I design, create, print and bind handmade paper books for no 
practical reason at all.



