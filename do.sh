#!/usr/bin/env bash
source $HOME/.bashrc 

# Calculate ping averages Use awk 
overall_avg=$(awk '
    BEGIN { sum = 0; count = 0 }
    $1 ~ /^[0-9]+(\.[0-9]+)?$/ { sum += $1; count++ }
    END {
        if (count > 0)
            printf "%.2f", sum / count
        else
            print "NA"
    }
' "${HOME}/.local/ping.txt")


# Generate output, C_ID is set with command as a label 
{
printf "\n<p>"

printf "<u> %-10.10s %-3s %-3s %-3s %-3s %-3s %-3s</u><br>"\
    "Hostname" "Os" "Lua" "Php" "Rb" "Gem" "Sh"

printf "]%s %-10.10s %-3s %-3s %-3s %-3s %-3s %-3s<br>\n"\
    "${C_ID:-N}" \
    "${HOSTNAME} $(cat /proc/sys/kernel/hostname)" \
    "$(uname | cut -c -3 )" \
    "$(lua -v 2>/dev/null | awk 'NR==1 {print $2}'|cut -d. -f1,2 || echo "<s>lua</s>")" \
    "$(php -v 2>/dev/null | awk 'NR==1 {print $2}'|cut -d. -f1,2 || echo "<s>php</s>")" \
    "$(ruby -v 2>/dev/null| awk 'NR==1 {print $2}'|cut -d. -f1,2 || echo "<s>rb</s>")" \
    "$(gem -v 2>/dev/null | cut -d. -f1,2 || echo "<s>gem</s>")" \
    "$(basename "$SHELL")" 
    # D, P ok $HOSTNAME.... C, E, J, O, T, S  OK cat /proc....

printf "!%s %s %s %s %s %s %s %s<br>\n" \
	"${C_ID:-N}" \
	"$(command -v w3m      >/dev/null 2>&1 && echo "w3m" || echo "<s>w3m</s>"  )" \
	"$(command -v lynx     >/dev/null 2>&1 && echo "lynx"|| echo "<s>lynx</s>" )" \
    "$(command -v jekyll   >/dev/null 2>&1 && echo "jek" || echo "<s>jek</s>"  )" \
	"$(command -v newsboat >/dev/null 2>&1 && echo "nwsb"|| echo "<s>nwsb</s>" )" \ 
 	"$(command -v weechat  >/dev/null 2>&1 && echo "wee" || echo "<s>wee</s>"  )" \
  	"$(command -v tldr     >/dev/null 2>&1 && echo "tldr"|| echo "<s>tldr</s>" )" \
  	"$(command -v fzf      >/dev/null 2>&1 && echo "fzf" || echo "<s>fzf</s>"  )" \
    "$(command -v rtorrent >/dev/null 2>&1 && echo "rto" || echo "<s>rto</s>"  )" \
    "$(command -v nim      >/dev/null 2>&1 && echo "nim" || echo "<s>nim</s>"  )" \
   	"$(command -v ${HOME}/.local/bin/tgpt >/dev/null 2>&1 && echo "tgpt" || echo "<s>tgpt</s>")"
    
#printf "|%s %s %s %s<br>\n" \
#	"${C_ID:-N}" \
#    "$(command -v mutt     >/dev/null 2>&1 && echo "mutt"|| echo "<s>mutt</s>" )" \
	


printf "[%s %s<br>\n" \
	"${C_ID:-N}" \
	"$(grep "^$(whoami):" /etc/passwd | \
	sed 's/identit//g; s/\.l/<u>.l<\/u>/g; s/aaa/aa/g; s/in//; s/sr//; s/current-system/c-s/; s/ome//; s/nfo//; s/kg//; s/ocal//; s/,,,//; s/User \&// ; s/:100:/: 100:/')" 


#tiny_path=$( echo "$PATH" | sed -e 's/current-system/c-s/g' -e 's/bin/b/g' -e 's/usr/u/g' -e 's/local/l/g' -e 's/games/g/g' -e 's/home/h/g' -e 's/identity/I/g' )
#printf ")%s %s<br>\n" "${C_ID:-N}" "$tiny_path" 

 
# printf "   <u>%-8s %-8s|%-3s %-3s %-3s %-3s</u>\n" "@H.K." "@Site" "dns" "h.k" "ave" "ping"	

# remove "-W 1" in ping -c 1 -W 1  8.8.8.8 (-W does not exist on I)
printf "(%s %-8s %-8s |dn.%-3s hk.%-3s av.%-3s %-3s<br>\n" \
    "${C_ID:-N}" \
    "$(TZ=UTC-8 date +'%H:%M/%d' 2>/dev/null || date +'%H:%M/%d')" \
    "$(date +'%H:%M/%d')" \
    "$(ping -c 1 8.8.8.8 >/dev/null 2>&1 && ping -c 3  8.8.8.8 2>/dev/null| awk -F'/' 'END {printf "%.0f\n", $5}')"
    "$(ping -c 1 hktv.com >/dev/null 2>&1 && ping -c 3 hktv.com 2>/dev/null| awk -F'/' 'END {printf "%.0f\n", $5}')"
    "$overall_avg" \
    "$total_count"

    	
if [ -d /home/i/identity ]; then
	printf "\n<span style='color: var(--col10-7);'>no last access info</span>\n"
elif [ -d /home/aaa/store ]; then
    printf "\n<span style='color: var(--col10-7);'>access list off for aaa</span>\n"
else
	# Get the date 4 days ago in YYYY-MM-DD format Try Linux, then fall back to current date
	four_days_ago=$(date -d '-4 days' +%Y-%m-%d 2>/dev/null || date +%Y-%m-%d)
	current_user=${USER:-$(whoami)}
	# Fetch login times for the current user in the last 4 days
	last_access=$(last -t "$four_days_ago" "$current_user" 2>/dev/null |
    	awk -F'[()]' '{print $2}' |  # Extract timestamps
    	tr '\n' ' ' |                # Join lines
    	sed 's/00:0[0-9]//g; s/00//g; s/ 0/ /g')  # Remove leading zeros
	printf "\n<span style='color: var(--col10-7);'>%s\n</span>\n" "$last_access"
fi


printf "<a href='#${C_ID:-N}'> ...show cron</a>
<div id='${C_ID:-N}'>"


printf "%s\n</div></p>\n</div>\n\n" "$(crontab -l | grep '* * '|sed 's/>\/dev\/null 2>&1/<br>/')"
} > ~/public_html/a.txt


cat ~/public_html/a.txt

#ps -p $$ – Display your current shell name reliably.
#echo "$SHELL" – Print the shell for the current user but not necessarily the shell that is running at the movement.
#echo $0 – Another reliable and simple method to get the current shell interpreter name on Linux or Unix-like systems.
#readlink /proc/$$/exe – Another option to get the current shell name reliably on Linux operating systems.
