#!/usr/bin/env bash
clear
#        command PowerOff          "systemctl poweroff"
#        command Reboot            "systemctl reboot"

platform='unknown'
unamestr=$(uname)
if [ "$unamestr" = 'Linux' ]; then
   platform='Linux'
elif [ "$unamestr" = 'OpenBSD' ]; then
   platform='Obsd'
elif [ "$unamestr" = 'FreeBSD' ]; then
   platform='Fbsd'
fi

# Check if a command-line argument is provided
if [ "$#" -eq 1 ]; then
case "$1" in   	 
        chromSocks ) 
			google-chrome --proxy-server="socks://127.0.0.1:9999"
			;;
        bright )
            value=$(/usr/bin/zenity --scale --min-value=0 --max-value=100 --step=5 --value=85 --text="Set Brightness " --title "Set Brightness")
            scale=5
            result=$(echo "scale=$scale; $value / 100" | bc)
            /usr/bin/xrandr --output eDP --brightness "${result}"
            exit 0
            ;;

		x20 )
			xterm -fs 16 -fa andale &
			exit 0
			;;
            
        vol )
            xterm -fa 'Noto Mono:size=11' -geometry 70x20 -e alsamixer
            exit 0
            ;;

        virshhalt )
            virsh shutdown openbsd75 --mode acpi
            exit 0
            ;;
            
        cups )
            firefox -url "http://localhost:631"
            exit 0
            ;;
            		
		display2560x1600)
			/usr/bin/xrandr --output eDP --mode 2560x1600; xsetroot -solid steelblue &
			exit 0
			;;
		display1920x1200)
            /usr/bin/xrandr --output eDP --mode 1920x1200; feh --bg-scale "$HOME"/.cwmFish.webp &
            exit 0
            ;;
		display1680x1050)
			/usr/bin/xrandr --output eDP --mode 1680x1050; feh --bg-scale "$HOME"/.cwmLand.webp &
			exit 0
			;;
		display1440x900)
			/usr/bin/xrandr --output eDP --mode 1440x900; feh --bg-scale "$HOME"/.cwmGlass.webp &
			exit 0
			;;
            
        display )
        	#xterm -fa 'Noto Mono:size=13' -geometry 80x30 -e 
            PS3='Resolution: '
            options=("2560x1600" "1920x1200" "1680x1050" "1440x900" "Quit")
            select opt in "${options[@]}"
	            do
                case $opt in
                    2560x1600)
                        xrandr --output eDP --mode 2560x1600
                        ;;
                    1920x1200)
                        xrandr --output eDP --mode 1920x1200
                        ;;
                    1680x1050)
                        xrandr --output eDP --mode 1680x1050
                        ;;
                    1440x900)
                        xrandr --output eDP --mode 1440x900
                        ;;
		   			 Quit)
				 	 exit 0
					 ;;
                esac
            done
            exit 0
            ;;

        xsane )
            xsane
            exit 0
            ;;
        simple-scan )
            simple-scan
            exit 0
            ;;

        iftop )
	       xterm -fa 'Noto Mono:size=13' -geometry 80x30 -e sudo iftop
       	   exit 0
        	;;
        nmtui )
           xterm -fa 'Noto Mono:size=13' -geometry 60x30 -e nmtui
           exit 0  
        	;;
        htop )
	    if [ "$platform" = 'Linux' ]; then
		  xterm -fa 'Noto Mono:size=13' -geometry 80x30 -e htop
	    elif [ "$platform" = 'Obsd' ]; then
   		  xterm -fa 'Noto Mono:size=13' -geometry 80x30 -e echo "Not on Obsd"&
	    elif [ "$platform" = 'Fbsd' ]; then
		  xterm -fa 'Noto Mono:size=13' -geometry 80x30 -e htop
	    fi
	    exit 0
            ;;
            
        bgb )
            xsetroot -solid steelblue
            exit 0
            ;;
        bg1 )
            feh --bg-scale "$HOME"/.cwmFish.webp
            exit 0
            ;;
        bg2 )
            feh --bg-scale "$HOME"/.cwmLand.webp
            exit 0
            ;;
        bg3 )
            feh --bg-scale "$HOME"/.cwmGlass.webp
            exit 0
            ;;
        shellcheck ) 
	    	xterm -fa 'Noto Mono:size=13' -geometry 80x30 -hold -e shellcheck "$HOME"/.local/bin/mm
	    	;;
		iwlist_scan )
		 	xterm -fa 'Noto Mono:size=13' -e sudo iwlist wlp1s0 scan | grep -i ssid
			;;
        Quit )
            exit 0
            ;;
            
        *)
			choice=$(grep ' )' "$HOME"/.local/bin/m | sed 's/)/,/g')
			echo "Invalid option: $1, enter 'm' and one of:"
            echo "$choice" | /usr/bin/sed 's/)/,/g'
            exit 1 
            ;;
esac 

else [ "$#" -eq 0 ]; 
	choice=$(grep ' )' "$HOME"/.local/bin/mm | sed 's/)/,/g')
    echo "You must enter one of:"
    echo "$choice" | /usr/bin/sed 's/)/,/g'
fi


#        volume )
#            VALUE=$(/usr/bin/zenity --scale --min-value=0 --max-value=100 --step=5 --value=100 --text="Volume" --title "Set Speakers")
#            amixer -D pulse sset Master "${VALUE}%"
#            exit 0
#            ;;
