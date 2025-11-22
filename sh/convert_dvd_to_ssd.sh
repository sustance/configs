#!/bin/bash
# DVD to SSD Conversion Script - for low-quality source preservation
# Save as: convert_dvd_to_ssd.sh

set -e  # Exit on any error

# Configuration
OUTPUT_DIR="/path/to/your/ssd/converted_videos"
DVD_MOUNT="/mnt/dvd"
PRESET="medium"  # Balance between speed and compression
CRF="28"         # Higher CRF = more compression/lower quality (23-28 is reasonable for low-quality sources)

# Create output directory
mkdir -p "$OUTPUT_DIR"

# Check if DVD is mounted
if ! mountpoint -q "$DVD_MOUNT"; then
    echo "Error: DVD not mounted at $DVD_MOUNT"
    echo "Mount with: sudo mount /dev/sr0 $DVD_MOUNT"
    exit 1
fi

# Function to get video duration in seconds
get_duration() {
    local file="$1"
    ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "$file" 2>/dev/null | cut -d. -f1
}

# Function to detect if file is video
is_video_file() {
    local file="$1"
    local mimetype=$(file -b --mime-type "$file" 2>/dev/null)
    [[ "$mimetype" == video/* ]]
}

# Conversion function with error handling
convert_video() {
    local input_file="$1"
    local relative_path="$2"
    local output_file="$OUTPUT_DIR/${relative_path%.*}.mp4"
    
    # Create output directory
    mkdir -p "$(dirname "$output_file")"
    
    # Skip if output already exists and is newer
    if [[ -f "$output_file" && "$output_file" -nt "$input_file" ]]; then
        echo "Skipping (already converted): $relative_path"
        return 0
    fi
    
    echo "Converting: $relative_path"
    
    # Get file info
    local duration=$(get_duration "$input_file")
    
    # Skip very short files (likely menu files, less than 10 seconds)
    if [[ -n "$duration" && "$duration" -lt 10 ]]; then
        echo "Skipping short file (likely menu): $relative_path"
        return 0
    fi
    
    # FFmpeg conversion with settings for old, low-quality sources
    ffmpeg -i "$input_file" \
        -c:v libx264 -preset "$PRESET" -crf "$CRF" \
        -c:a aac -b:a 128k \
        -movflags +faststart \
        -vf "scale='min(1280,iw)':min'(720,ih)':force_original_aspect_ratio=decrease" \
        "$output_file" 2>/dev/null
    
    if [[ $? -eq 0 ]]; then
        echo "✓ Success: $(basename "$output_file")"
        
        # Optional: Compare file sizes
        local orig_size=$(du -h "$input_file" | cut -f1)
        local new_size=$(du -h "$output_file" | cut -f1)
        echo "  Size: $orig_size → $new_size"
    else
        echo "✗ Failed: $relative_path"
        # Keep failed output for debugging
        [[ -f "$output_file" ]] && mv "$output_file" "${output_file}.failed"
    fi
}

# Main conversion loop
echo "Starting DVD conversion..."
echo "Source: $DVD_MOUNT"
echo "Destination: $OUTPUT_DIR"
echo "Settings: CRF=$CRF, preset=$PRESET"
echo "----------------------------------------"

# Find and process video files
find "$DVD_MOUNT" -type f \( -iname "*.vob" -o -iname "*.mpg" -o -iname "*.mpeg" -o -iname "*.avi" \) | while read -r file; do
    if is_video_file "$file"; then
        relative_path="${file#$DVD_MOUNT/}"
        convert_video "$file" "$relative_path"
    fi
done

# Also process any other files that might be video
find "$DVD_MOUNT" -type f | while read -r file; do
    # Skip common non-video files
    case "$(basename "$file")" in
        *.ifo|*.bup|*.idx|*.sub|*.txt|*.nfo)
            continue
            ;;
    esac
    
    if is_video_file "$file"; then
        relative_path="${file#$DVD_MOUNT/}"
        convert_video "$file" "$relative_path"
    fi
done

echo "----------------------------------------"
echo "Conversion complete! Files saved to: $OUTPUT_DIR"
