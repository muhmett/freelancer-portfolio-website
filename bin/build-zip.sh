#!/usr/bin/env bash
#
# Construit jeux-marketing.zip, le fichier à téléverser dans
# Apparence → Thèmes → Ajouter → Téléverser un thème.
#
# Usage : bin/build-zip.sh
#
set -euo pipefail

root="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
theme="$root/jeux-marketing"
out="$root/dist"
zipfile="$out/jeux-marketing.zip"

if [ ! -f "$theme/style.css" ]; then
	echo "Thème introuvable : $theme" >&2
	exit 1
fi

# Le fichier de traduction est régénéré à chaque construction pour rester
# aligné sur les sources.
php "$root/bin/make-pot.php"

# Les trois langues doivent être présentes, sinon l'archive part incomplète.
for l in en fr ar; do
	if [ ! -f "$theme/inc/lang/$l.php" ]; then
		echo "Langue manquante : inc/lang/$l.php" >&2
		exit 1
	fi
done

rm -rf "$out"
mkdir -p "$out"

staging="$( mktemp -d )"
trap 'rm -rf "$staging"' EXIT

cp -r "$theme" "$staging/jeux-marketing"
cp "$root/LISEZMOI.md" "$staging/jeux-marketing/LISEZ-MOI.md"

# Rien qui n'ait à voyager jusqu'au site du client.
find "$staging" \( -name '.DS_Store' -o -name '*.map' -o -name 'Thumbs.db' \) -delete

( cd "$staging" && zip -rq "$zipfile" jeux-marketing -x '*.git*' )

echo "Écrit : ${zipfile#"$root/"}  ($( du -h "$zipfile" | cut -f1 ))"
