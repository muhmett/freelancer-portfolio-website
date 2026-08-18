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

# Les pièces de la roue et les fonds. Sans eux la page ne casse pas : elle
# s'affiche avec un anneau vide là où devrait être le laiton, et personne ne
# voit rien dans les journaux. C'est exactement le genre de panne qu'on ne
# découvre que chez le client — donc on refuse de construire l'archive.
for f in wheel/rim.webp wheel/rim.png wheel/hub.webp wheel/hub.png \
	bg/casino.webp bg/arcade.webp bg/elegant.webp bg/metal.webp \
	hero/aurore.webp hero/confetti.webp hero/rayons.webp hero/grille.webp; do
	if [ ! -s "$theme/assets/img/$f" ]; then
		echo "Image manquante ou vide : assets/img/$f" >&2
		echo "La produire avec bin/cut-disc.php ou bin/make-texture.php." >&2
		exit 1
	fi
done

# Le moteur autonome part seul : s'il n'emporte pas ses pièces en base64,
# la roue livrée au client n'a pas de monture.
if ! grep -q "data:image/webp;base64," "$theme/assets/js/jmk-embed.js"; then
	echo "jmk-embed.js ne contient pas ses pièces." >&2
	echo "Lancer : php bin/build-embed-assets.php" >&2
	exit 1
fi

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
