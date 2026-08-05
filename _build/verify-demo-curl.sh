#!/usr/bin/env bash
# Быстрая проверка HTML-секций без браузера
set -euo pipefail
URL="${BANNERPRO_DEMO_URL:-https://project.test/bannerpro-test.html}"
HTML_FILE=$(mktemp)
trap 'rm -f "$HTML_FILE"' EXIT
curl -sSL "$URL" -o "$HTML_FILE"
fail=0

check() {
  local sec="$1" pat="$2" desc="$3"
  if rg -q "$pat" "$HTML_FILE"; then
    echo "✓ [$sec] $desc"
  else
    echo "✗ [$sec] $desc"
    fail=$((fail + 1))
  fi
}

echo "=== curl smoke: $URL ==="
if rg -qi 'fatal error|parse error' "$HTML_FILE"; then
  echo "✗ [PHP] Fatal в HTML"
  fail=$((fail + 1))
else
  echo "✓ [PHP] нет Fatal"
fi

check 1 'demo-banner-sidebar' 'sidebar баннеры'
check 2 'demo-banner-footer-1' 'footer-1'
check 2 'demo-banner-footer-2' 'footer-2'
check 5 'bannerpro-demo-wrapper' 'tplWrapper'
check 6 'demo-banner-html' 'HTML баннеры'
check 7 'bannerpro-test__section' 'секция 7 (placeholder в DOM)'
check 11 'demo-banner-inactive' 'showInactive'
check 15 'demo-banner-scheduled-active' 'scheduled active'
check 16 'demo-banner-rotator-a' 'rotator A'
check 16 'demo-banner-rotator-c' 'rotator C'
check 17 'tplFirst' 'tplFirst badge'
check 18 'demo-empty-none' 'пустая позиция в тексте секции 18'
check 34 'demo-banner-ab-' 'A/B split (ab-a или ab-b)'
check 36 'demo-banner-lead' 'FormIt lead баннер'
check 0 'bannerpro_webhook_impression_url' 'настройка webhook impression в секции 0'

# click endpoint
AP=$(rg -o 'href="[^"]*bannerclick/[0-9]+"' "$HTML_FILE" | head -1 | sed 's/href="//;s/"$//' || true)
if [[ -n "$AP" ]]; then
  CODE=$(curl -sSL -o /dev/null -w '%{http_code}' "https://project.test/${AP#/}")
  [[ "$CODE" =~ ^30[1278]$ ]] && echo "✓ [20] bannerclick → $CODE" || { echo "✗ [20] bannerclick → $CODE"; fail=$((fail+1)); }
fi

IMP=$(rg -o 'data-bannerpro-impression="[0-9]+"' "$HTML_FILE" | head -1 | rg -o '[0-9]+' | head -1 || true)
if [[ -n "$IMP" ]]; then
  PIX=$(curl -sSL -o /dev/null -w '%{http_code}' "https://project.test/bannerimpression?adposition=$IMP")
  [[ "$PIX" == "204" || "$PIX" == "200" ]] && echo "✓ [21] impression pixel → $PIX" || { echo "✗ [21] pixel → $PIX"; fail=$((fail+1)); }
else
  echo "✗ [20] нет data-bannerpro-impression"
  fail=$((fail + 1))
fi

echo ""
[[ $fail -eq 0 ]] && echo "curl: OK ($fail failures)" || echo "curl: FAIL ($fail failures)"
exit $fail
