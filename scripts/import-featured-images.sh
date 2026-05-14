#!/usr/bin/env bash
# Bulk-import topic-matched featured images for the seeded demo posts.
#
# Source: loremflickr.com — Creative-Commons Flickr photos selected by tag.
# No API key required.
#
# How it works:
#   1. curl the loremflickr URL to a .jpg in wp-content/uploads/_import/
#      (uploads/ is bind-mounted so the container sees the file)
#   2. wp media import the local path inside the container
#   3. Clean up the staging directory at the end
#
# Usage (from repo root):
#   ./scripts/import-featured-images.sh
#
# Prerequisites:
#   - `docker compose up -d` is running
#   - curl + the ./wp wrapper at repo root work
#
# Idempotent: posts that already have a featured image are skipped.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$SCRIPT_DIR/.."
WP="$REPO_ROOT/wp"

# Staging dir (host path that the wordpress container can see via the uploads bind-mount).
HOST_STAGE="$REPO_ROOT/wp-content/uploads/_import"
CONTAINER_STAGE="/var/www/html/wp-content/uploads/_import"

if [[ ! -x "$WP" ]]; then
    echo "Cannot find executable wp wrapper at $WP" >&2
    exit 1
fi

# Helper: invoke the wp wrapper with MSYS path conversion disabled so absolute /var/...
# paths survive intact when running under Git Bash on Windows. No-op on Linux/macOS.
wp_cli() {
    MSYS_NO_PATHCONV=1 MSYS2_ARG_CONV_EXCL='*' "$WP" "$@"
}

mkdir -p "$HOST_STAGE"
trap 'rm -rf "$HOST_STAGE"' EXIT

# Slug → comma-separated tags for loremflickr (no spaces around commas)
declare -A TAGS=(
    [new-report-format-support]="document,paperwork,form"
    [system-maintenance-mar15]="server,datacenter,maintenance"
    [excel-migration-interview]="spreadsheet,meeting,office"
    [residence-card-expiry-alert]="passport,id-card,calendar"
    [compliance-enhancement-points]="compliance,handshake,business"
    [vietnamese-ai-chatbot]="chatbot,smartphone,communication"
    [invoice-automation-accounting]="invoice,calculator,desk"
    [ai-case-study-inquiries-70-percent-down]="headset,callcenter,helpdesk"
    [cloud-data-protection-security]="cybersecurity,lock,cloud"
    [immigration-law-reform-schedule]="law,gavel,government"
)

imported=0
skipped=0
failed=0

for slug in "${!TAGS[@]}"; do
    tags="${TAGS[$slug]}"

    post_id=$(wp_cli post list --post_type=post --name="$slug" --field=ID --format=ids 2>/dev/null | tr -d '[:space:]\r')

    if [[ -z "$post_id" ]]; then
        echo "  - SKIP (no post): $slug"
        skipped=$((skipped + 1))
        continue
    fi

    existing=$(wp_cli post meta get "$post_id" _thumbnail_id 2>/dev/null | tr -d '[:space:]\r' || true)
    if [[ -n "$existing" && "$existing" != "0" ]]; then
        echo "  - SKIP (already has thumbnail #$existing): #$post_id $slug"
        skipped=$((skipped + 1))
        continue
    fi

    url="https://loremflickr.com/1200/630/${tags}"
    host_file="$HOST_STAGE/${slug}.jpg"
    container_file="$CONTAINER_STAGE/${slug}.jpg"

    echo "  + DOWNLOAD: #$post_id $slug  ←  ${tags}"
    curl_err=$(curl -sSL --max-time 30 -o "$host_file" "$url" 2>&1) || true
    if [[ ! -s "$host_file" ]]; then
        echo "    FAILED to download from $url" >&2
        [[ -n "$curl_err" ]] && echo "    curl: $curl_err" >&2
        failed=$((failed + 1))
        continue
    fi

    wp_cli media import "$container_file" --post_id="$post_id" --featured_image --title="${slug} featured" >/dev/null
    imported=$((imported + 1))
done

echo
echo "Done. Imported: $imported, Skipped: $skipped, Failed: $failed"
