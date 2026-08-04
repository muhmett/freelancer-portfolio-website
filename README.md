# freelancer-portfolio-website

Dépôt du thème WordPress **Jeux Marketing** : une page de vente qui est
elle-même la démonstration du produit. Le visiteur joue à la roue, règle les
probabilités, compose son devis et repart avec un récapitulatif.

Le guide destiné à la personne qui installe le thème est dans
[`LISEZMOI.md`](LISEZMOI.md). Ce fichier-ci s'adresse à qui travaille sur le
code.

## Contenu

| Chemin | Rôle |
|---|---|
| `jeux-marketing/` | Le thème lui-même, tel qu'il est livré |
| `bin/build-zip.sh` | Produit `dist/jeux-marketing.zip`, le fichier à téléverser |
| `bin/make-pot.php` | Régénère `languages/jeux-marketing.pot` depuis les sources |
| `bin/screenshot.html` | Page qui sert à produire `screenshot.png` |
| `tests/` | Vérifications de la logique de tirage, sans WordPress |

Le thème fait deux choses : il vend les jeux, et il sert de portfolio. Les
sections « packs », « réalisations », « déroulé », « à propos » et « avis »
composent la partie portfolio ; les six jeux jouables en sont la démonstration.

## Construire l'archive

```sh
bin/build-zip.sh
```

Le script régénère le fichier de traduction puis écrit
`dist/jeux-marketing.zip`. C'est cette archive qui part chez le client.

## Lancer les tests

```sh
php tests/test-draw.php     # logique de tirage
tests/run-e2e.sh           # les six jeux dans un navigateur
```

Les tests chargent `inc/defaults.php` et `inc/leads.php` avec des doublures
minimales des fonctions WordPress (`tests/wp-stubs.php`). Ils couvrent ce qui
coûte cher à casser :

- les plafonds sont respectés au tirage,
- les poids donnent bien les fréquences annoncées,
- un contact effacé dans les réglages reste effacé,
- le lot enregistré vient du serveur, pas de ce que le navigateur prétend.

## Refaire la capture d'écran

`screenshot.png` est la vignette affichée dans **Apparence → Thèmes**. Elle est
produite à partir du vrai `main.css`, pour rester fidèle au thème :

```sh
chromium --headless --window-size=1200,900 \
  --screenshot=jeux-marketing/screenshot.png \
  bin/screenshot.html
```

## Points à connaître

**Le tirage fait autorité côté serveur.** `jmk_ajax_play()` tire le lot,
applique les plafonds, génère le code, puis mémorise le résultat sous un jeton
à usage unique (`transient`, une heure). Le formulaire de capture renvoie ce
jeton et non le lot : `jmk_ajax_lead()` ignore tout lot annoncé par le
navigateur. Le tirage local dans `app.js` n'existe que comme repli si l'appel
AJAX échoue — page mise en cache avec un nonce périmé, visiteur hors ligne.

**Le mode Fiverr coupe le numéro à la source.** `jmk_js_config()` n'envoie pas
le numéro WhatsApp au navigateur quand le mode est `fiverr`. Le masquer en CSS
ne suffirait pas : il resterait lisible dans le code source de la page.

**Les réglages de contact peuvent être vides.** `jmk_get()` retombe sur la
valeur d'exemple pour un champ de texte laissé vide, mais pas pour les clés
listées dans `jmk_blankable()`. Sans cela, effacer le numéro WhatsApp le
remplacerait par celui du thème.

**Les réalisations et les avis ne sont jamais pré-remplis.** `section-work.php`
et `section-reviews.php` s'effacent tant qu'aucun contenu réel n'a été saisi.
C'est délibéré : publier des références inventées est un motif de suspension sur
Fiverr et Upwork, et ce sont précisément les comptes que ce thème cherche à
protéger avec son mode Fiverr.

**Les polices viennent de Google Fonts.** C'est le point qui reste en tension
avec l'argument RGPD de la page : pour un client européen strict, il faut
héberger les trois familles dans `assets/fonts/` et remplacer l'appel à
`fonts.googleapis.com` dans `jmk_assets()`.
