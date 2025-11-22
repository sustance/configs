#!/bin/bash
# Batch DVD processor - run this for each DVD
# Save as: process_all_dvds.sh

DVD_DRIVE="/dev/sr0"
MOUNT_POINT="/mnt/dvd"
OUTPUT_BASE="/path/to/your/ssd/dvd_archive"

# Create mount point
sudo mkdir -p "$MOUNT_POINT"

# Get DVD label for folder naming
sudo eject -t "$DVD_DRIVE"  # Close tray first
sleep 2

# Mount the DVD
if sudo mount "$DVD_DRIVE" "$MOUNT_POINT" 2>/dev/null; then
    # Try to get volume name, fall back to timestamp
    VOLUME_NAME=$(lsblk -no LABEL "$DVD_DRIVE" 2>/dev/null)
    if [[ -z "$VOLUME_NAME" ]]; then
        VOLUME_NAME="dvd_$(date +%Y%m%d_%H%M%S)"
    fi
    
    # Clean volume name for filesystem
    VOLUME_NAME=$(echo "$VOLUME_NAME" | tr ' ' '_' | tr -cd '[:alnum:]._-')
    
    OUTPUT_DIR="$OUTPUT_BASE/$VOLUME_NAME"
    
    echo "Processing DVD: $VOLUME_NAME"
    echo "Output: $OUTPUT_DIR"
    
    # Update the conversion script output directory
    export OUTPUT_DIR
    
    # Run conversion
    ./convert_dvd_to_ssd.sh
    
    # Unmount when done
    sudo umount "$MOUNT_POINT"
    sudo eject "$DVD_DRIVE"
    
    echo "Ready for next DVD..."
else
    echo "Failed to mount DVD. Please insert a disc and try again."
fi
