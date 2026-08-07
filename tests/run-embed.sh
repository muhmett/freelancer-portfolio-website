#!/usr/bin/env bash
#
# Vérifie le moteur autonome (assets/js/jmk-embed.js) dans un navigateur.
#
# Usage : tests/run-embed.sh [chemin-vers-chrome]
#
# Le moteur n'a besoin ni de WordPress ni de réseau : c'est tout l'intérêt du
# fichier, et ce test le prouve en le chargeant depuis file://.
#
set -uo pipefail

here="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
chrome="${1:-}"

if [ -z "$chrome" ]; then
	for c in "${CHROME_BIN:-}" chromium chromium-browser google-chrome \
		/opt/pw-browsers/chromium/chrome /opt/pw-browsers/chromium-*/chrome-linux/chrome; do
		if [ -n "${c:-}" ] && command -v "$c" >/dev/null 2>&1; then chrome="$c"; break; fi
		if [ -n "${c:-}" ] && [ -x "$c" ]; then chrome="$c"; break; fi
	done
fi

if [ -z "$chrome" ]; then
	echo "Chrome ou Chromium introuvable. Passer le chemin en argument." >&2
	exit 1
fi

dom="$( "$chrome" --headless --no-sandbox --disable-gpu \
	--virtual-time-budget=20000 --dump-dom \
	"file://$here/embed.html" 2>/dev/null )"

report="$( printf '%s' "$dom" | python3 -c "
import sys, re, html
s = sys.stdin.read()
m = re.search(r'<div id=\"out\">(.*?)</div>', s, re.S)
print(html.unescape(re.sub(r'<[^>]+>', '', m.group(1))) if m else '')
" )"

if [ -z "${report// }" ]; then
	echo "  vérifications non terminées (aucun résultat)"
	exit 1
fi

printf '%s\n' "$report"

case "$report" in
	*FAIL*|*échec*) exit 1 ;;
esac
exit 0
