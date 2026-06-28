#!/bin/bash
set -euo pipefail

UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36"
OUTDIR="${HOME}/public_html/img"
mkdir -p "$OUTDIR"

# ------------------------------------------------------------
# 1. Try WordPress REST API (latest post + embedded media)
# ------------------------------------------------------------
API="https://www.visualcapitalist.com/wp-json/wp/v2/posts?per_page=1&_embed"
JSON=$(curl -s -L -H "User-Agent: $UA" -H "Accept: application/json" "$API")

IMG_URL=$(python3 -c "
import sys, json
try:
    posts = json.load(sys.stdin)
    if not posts:
        sys.exit(0)
    post = posts[0]

    # Method A: WordPress 'featured media' (the official infographic)
    media_list = post.get('_embedded', {}).get('wp:featuredmedia', [])
    if media_list:
        media = media_list[0]
        sizes = media.get('media_details', {}).get('sizes', {})
        for sz in ['full', 'large', 'medium_large', 'medium']:
            if sz in sizes:
                print(sizes[sz]['source_url'])
                sys.exit(0)
        if 'source_url' in media:
            print(media['source_url'])
            sys.exit(0)

    # Method B: first <img> inside the post body
    content = post.get('content', {}).get('rendered', '')
    import re
    m = re.search(r'src=[\\\"\\'](https?://[^\\\"\\']+\\.(?:webp|jpg|jpeg|png|gif))', content)
    if m:
        print(m.group(1))
except Exception:
    sys.exit(0)
" <<< "$JSON")

# ------------------------------------------------------------
# 2. Fallback: RSS feed if API is blocked/empty
# ------------------------------------------------------------
if [ -z "$IMG_URL" ]; then
    RSS="https://www.visualcapitalist.com/feed/"
    RSS_XML=$(curl -s -L -H "User-Agent: $UA" "$RSS")
    IMG_URL=$(python3 -c "
import sys, re
xml = sys.stdin.read()
item = re.search(r'<item>.*?</item>', xml, re.DOTALL)
if not item:
    sys.exit(0)
content = re.search(r'<content:encoded>.*?<!\\[CDATA\\[(.*?)\\]\\]>.*?</content:encoded>', item.group(0), re.DOTALL)
if content:
    m = re.search(r'src=[\\\"\\'](https?://[^\\\"\\']+\\.(?:webp|jpg|jpeg|png|gif))', content.group(1))
    if m:
        print(m.group(1))
" <<< "$RSS_XML")
fi

# ------------------------------------------------------------
# 3. Download the image
# ------------------------------------------------------------
if [ -z "$IMG_URL" ]; then
    echo "Could not find image URL. Exiting." >&2
    exit 1
fi

FILENAME=$(basename "$IMG_URL" | sed 's/[?#].*$//')
[ -z "$FILENAME" ] && FILENAME="vc-daily-$(date +%Y%m%d).webp"

echo "Found: $IMG_URL"
curl -L \
  -H "User-Agent: $UA" \
  -H "Accept: image/webp,image/apng,image/*,*/*;q=0.8" \
  -H "Referer: https://www.visualcapitalist.com/" \
  -o "$OUTDIR/$FILENAME" \
  "$IMG_URL"

echo "Saved: $OUTDIR/$FILENAME"
