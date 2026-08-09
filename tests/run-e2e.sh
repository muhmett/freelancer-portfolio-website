#!/usr/bin/env bash
#
# Joue le scénario navigateur dans les deux modes de contact.
#
# Usage : tests/run-e2e.sh [chemin-vers-chrome]
#
# Le serveur admin-ajax est simulé dans la page : aucun WordPress requis.
#
set -uo pipefail

here="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
chrome="${1:-}"

if [ -z "$chrome" ]; then
	for c in "${CHROME_BIN:-}" chromium chromium-browser google-chrome \
		/opt/pw-browsers/chromium-*/chrome-linux/chrome; do
		if [ -n "${c:-}" ] && command -v "$c" >/dev/null 2>&1; then chrome="$c"; break; fi
		if [ -n "${c:-}" ] && [ -x "$c" ]; then chrome="$c"; break; fi
	done
fi

if [ -z "$chrome" ]; then
	echo "Chrome ou Chromium introuvable. Passer le chemin en argument." >&2
	exit 1
fi

status=0
for mode in direct fiverr; do
	echo "── mode $mode ──"
	dom="$( "$chrome" --headless --no-sandbox --disable-gpu \
		--virtual-time-budget=20000 --dump-dom \
		"file://$here/e2e.html?mode=$mode" 2>/dev/null )"

	log="$( printf '%s' "$dom" | sed -n 's/.*<pre id="LOG">\(.*\)<\/pre>.*/\1/p' )"
	if [ -z "$log" ]; then
		# Le <pre> peut contenir des retours à la ligne : reprise en Python.
		log="$( printf '%s' "$dom" | python3 -c "
import sys,re,html
s=sys.stdin.read()
m=re.search(r'<pre id=\"LOG\">(.*?)</pre>', s, re.S)
print(html.unescape(m.group(1)) if m else '')
" )"
	fi

	if [ -z "${log// }" ]; then
		echo "  scénario non terminé (aucun résultat)"
		status=1
		continue
	fi

	printf '%s\n' "$log"
	case "$log" in
		*ECHEC*) status=1 ;;
	esac
done

exit $status
