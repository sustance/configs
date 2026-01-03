<?php
/**
 * Markdown to HTML Converter
 * 
 * This script converts markdown text embedded in the PHP file to formatted HTML
 */

// Markdown text block - paste your markdown between the HEREDOC tags
$markdown = <<<MARKDOWN
# Welcome to Markdown Converter

This is a **simple markdown** to HTML converter.

## Features

- Converts markdown syntax to HTML
- Supports basic formatting
- Easy to use

### Code Example

```php
<?php
echo "Hello, World!";
?>



Yes, kiosk mode is possible on Android 4.0.3, but with significant limitations and workarounds. Android 4.0.3 (Ice Cream Sandwich, circa 2011) predates modern kiosk features, so you'll need creative solutions.

Native Android 4.0.3 Limitations:
No built-in kiosk/lockdown features

No "screen pinning" (added in Android 5.0)

No "dedicated device" mode

Limited accessibility/automation APIs

Most modern kiosk apps require Android 5.0+

Workable Solutions for Android 4.0.3:
Option 1: Launcher Replacement (Simplest)
App: "Nova Launcher" or "ADW Launcher" (still support Android 4.0)

Install launcher

Set as default launcher

Remove all icons except your media player

Disable app drawer access

Disable notifications/status bar in launcher settings

Option 2: Auto-start App on Boot
App: "Startup Manager" or "Boot Start" (old apps that work on 4.0)

Install app

Set your media player to launch on boot

May require root for best results

Option 3: Custom Home App (Requires Root)
If rooted, you can:

bash
# Disable stock launcher
pm disable com.android.launcher

# Create simple home app or use existing player
Option 4: Tasker/Automation (If It Runs)
Tasker (old versions) might work:

Create profile: Event → Display → Display On

Task: Launch App → Your media player

May need accessibility permissions

Recommended Setup for Android 4.0.3 Media Stick:
Software Stack:
Root the device (usually possible with KingRoot/Framaroot for old devices)

Install: BusyBox, Terminal Emulator

Disable unwanted system apps:

bash
pm disable com.android.browser
pm disable com.android.email
pm disable com.android.calendar
# Keep only essentials
Simple Kiosk Script:
Create /data/local/kiosk.sh:

bash
#!/system/bin/sh
# Wait for boot complete
while [ "$(getprop sys.boot_completed)" != "1" ]; do
    sleep 5
done

# Kill stock launcher
pkill com.android.launcher

# Launch media player
am start -n com.justplayer/.MainActivity

# Keep restarting if closed
while true; do
    # Check if player is running
    if ! pgrep com.justplayer; then
        am start -n com.justplayer/.MainActivity
    fi
    sleep 10
done
Make executable and run at boot via init.d or install-recovery.sh.

Hardware Reality Check: Android 4.0.3 Devices in 2025
The Problems:
Codec Support: Won't handle modern HEVC/H.265, VP9, AV1

Performance: 1GB RAM max, slow ARMv7 processors

Security: No updates since ~2013, massive vulnerabilities

App Compatibility: Most modern media players need Android 5.0+

USB/BT: Often USB 2.0 only, poor power delivery





# Toner Cartridges on hand
Box in in Kitchen ceiling right hand  of outer wall side

 - Laser cartridges CRG-137, CRG 137, Canon 137 Toner Cartridge for 
   Canon imageCLASS MF249dw / MF247dw / MF236n -- CYMK  PRINTER SCRAPPED

 - NEW Refillable cartridge HP 80X CF280X Compatible Toner Cartridge 
   possibly 80A
   for HP Laserjet Pro 400, M401dn PRINTER SCRAPPED

 - One more PRINTER SCRAPPED need to look in box to identiy... 3rd down


Plastic welding

The melting point for high-density polyethylene (HDPE) typically ranges from 
120°C to 135°C (248°F to 275°F), with some sources reporting the typical 
melting point as approximately 135°C. 
The exact melting point depends on the specific molecular structure and can be 
affected by factors like branching, crystallinity, and the presence of additives.

HDPE Rod is an excellent engineering plastic that has many unique and useful properties 
to offer users who need a varied and combined range of beneficial features. 

HDPE is short for High Density Polyethylene and HDPE Rod is commercially available 
in either white (natural) or black.

















# Refilling your HP LaserJet Pro M15w printer's 48A cartridge 


### disassembling the cartridge, cleaning internal components, replacing the toner powder using a special tool to melt a hole in the cartridge, and then reassembling it and replacing the chip. It is a challenging DIY task that requires specific tools and expertise, with the risk of damaging the printer or cartridge.  

Steps for DIY Refill (Advanced Users Only)

https://www.youtube.com/watch?v=I_oeRxyXsPs

https://www.youtube.com/watch?v=-Qsu8rEqrHE     <<< good

Disassemble the Cartridge:

Remove the end cap on the right side of the cartridge. 

Separate the toner and drum sections by sliding the toner section away from the drum section. 

Remove the charge roller from the drum. 

Carefully unscrew the doctor blade and remove it. 

Clean the Cartridge:

Use compressed air to clean out any remaining toner powder from the cartridge. 

Clean the magnetic roller with a dry, soft cloth. 

Clean the charge roller with water and isopropyl alcohol. 

Refill with Toner:

You will need a special toner hole tool kit to melt a hole in the cartridge, as the 48A cartridge does not have a convenient fill port. 

Carefully pour approximately 60g of black HP toner into the cartridge through the newly created hole. 

Reassemble the Cartridge:

Carefully clean and reinstall the doctor blade and the two plastic spacers. 

Reinstall the end cap and screw. 
Insert the charge roller. 

Place the drum section into the toner section, ensuring the hinge pins fit into their holes. 

Reinstall the drum end cap and screw. 

Turn the developer roller by hand to ensure it is correctly installed. 

Replace the Chip: A new chip must be installed on the cartridge every time it is refilled. 
