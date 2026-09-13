#!/usr/bin/env bash
#
# vapor-fetch.sh — download ~/vapor from host "4" into a tmpfs ramdisk,
# with optional timed self-destruct.

#play with mpv from the terminal, 
#open images with mpv or feh instead of eog, 
#and skip the file manager entirely 
#
# Usage:
#   ./vapor-fetch.sh                 # mount ramdisk, download, keep until you eject
#   ./vapor-fetch.sh --expire 3      # same, but wipe + unmount after 3 hours
#   ./vapor-fetch.sh --size 4g       # bigger ramdisk (default 2g)
#   ./vapor-fetch.sh --skip-swapoff  # leave system swap untouched (not recommended)
#
# Manual eject when done:
#   rm -rf /mnt/ram/vapor && sudo umount /mnt/ram
#
set -euo pipefail

HOST="4"
REMOTE_DIR="vapor"          # relative to your home dir on the server
MOUNT="/mnt/ram"
SIZE="2g"
EXPIRE_HOURS=0
SKIP_SWAPOFF=0

die() { echo "error: $*" >&2; exit 1; }

usage() { grep '^#' "$0" | head -n 12 | sed 's/^# \{0,1\}//'; exit 0; }

while [[ $# -gt 0 ]]; do
  case "$1" in
    --expire)        EXPIRE_HOURS="${2:?--expire needs hours}"; shift 2;;
    --size)          SIZE="${2:?--size needs value}"; shift 2;;
    --host)          HOST="${2:?--host needs name}"; shift 2;;
    --remote)        REMOTE_DIR="${2:?--remote needs path}"; shift 2;;
    --skip-swapoff)  SKIP_SWAPOFF=1; shift;;
    -h|--help)       usage;;
    *)               die "unknown option: $1 (see --help)";;
  esac
done

command -v rsync  >/dev/null || die "rsync not installed"
command -v ssh    >/dev/null || die "openssh not installed"

# Confirm the host alias actually resolves (from ~/.ssh/config or DNS)
ssh -G "$HOST" >/dev/null 2>&1 || die "ssh host '$HOST' not found in ~/.ssh/config"

# --- swap: tmpfs pages CAN be swapped to disk under memory pressure. ---
# If the plaintext must never touch the SSD, swap must be off for the session.
if [[ "$SKIP_SWAPOFF" -eq 0 ]] && swapon --show --noheadings 2>/dev/null | grep -q .; then
  echo "swap is active — disabling for this session (plaintext must not page out)"
  sudo swapoff -a
elif [[ "$SKIP_SWAPOFF" -eq 0 ]]; then
  echo "no swap active — good"
fi

# --- ramdisk ---
if mount | grep -qE "[[:space:]]${MOUNT}[[:space:]]"; then
  echo "ramdisk already mounted at $MOUNT — reusing it"
else
  echo "creating tmpfs ramdisk: $MOUNT (size=$SIZE)"
  sudo mkdir -p "$MOUNT"
  sudo mount -t tmpfs -o "size=$SIZE,mode=755" tmpfs "$MOUNT"
  sudo chown "$USER:$USER" "$MOUNT"
fi

# --- download (rsync over ssh: progress, partial-file resume, idempotent) ---
mkdir -p "$MOUNT/$REMOTE_DIR"
echo "pulling $HOST:~/$REMOTE_DIR/ -> $MOUNT/$REMOTE_DIR/"
rsync -a --partial --info=progress2 -e ssh "$HOST:$REMOTE_DIR/" "$MOUNT/$REMOTE_DIR/"

echo
echo "done. files live at: $MOUNT/$REMOTE_DIR"
echo "play them from there, then eject with:"
echo "  rm -rf $MOUNT/$REMOTE_DIR && sudo umount $MOUNT"

# --- optional timed self-destruct ---
if [[ "$EXPIRE_HOURS" -gt 0 ]]; then
  echo "watchdog set: contents wiped and ramdisk unmounted in $EXPIRE_HOURS hour(s)"
  # Launch as root NOW, so no sudo password is needed hours later.
  sudo bash -c "nohup bash -c '
    sleep ${EXPIRE_HOURS}h
    rm -rf \"$MOUNT/$REMOTE_DIR\"
    umount -l \"$MOUNT\" 2>/dev/null || umount \"$MOUNT\" 2>/dev/null || true
  ' >/dev/null 2>&1 &"
fi
