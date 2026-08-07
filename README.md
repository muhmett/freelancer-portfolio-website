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
| `bin/make-felt.php` | Régénère `assets/img/felt.jpg`, le feutre de l'habillage casino |
| `tests/` | Vérifications, sans WordPress |

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
php tests/test-i18n.php     # les trois langues
php tests/test-builder.php  # créateur de jeu et fichier exporté
tests/run-embed.sh          # moteur autonome, dans un navigateur
tests/run-banner.sh         # bannières : mise en page, formats, régies
tests/run-e2e.sh            # les six jeux dans un navigateur
```

Les tests chargent `inc/defaults.php` et `inc/leads.php` avec des doublures
minimales des fonctions WordPress (`tests/wp-stubs.php`). Ils couvrent ce qui
coûte cher à casser :

- les plafonds sont respectés au tirage,
- les poids donnent bien les fréquences annoncées,
- un contact effacé dans les réglages reste effacé,
- le lot enregistré vient du serveur, pas de ce que le navigateur prétend,
- rien de ce que le créateur reçoit du navigateur ne ressort tel quel dans le
  fichier exporté.

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

**La pellicule de la carte à gratter est une image.** `assets/img/foil.jpg`
(24 Ko) est une texture de métal brossé générée, redimensionnée et recompressée.
Elle est peinte sur le canevas dans `sizeScratch()`, et le dégradé CSS d'origine
reste le repli tant qu'elle n'est pas chargée : la carte est jouable tout de
suite. C'est le seul endroit du thème où une image bat le CSS — une matière
avec du grain ne se code pas. Tout le reste (boîte cadeau, icônes) est du SVG
en `currentColor`, qui suit la couleur de la marque sans produire un fichier
par teinte.

**Les trois langues ne dépendent d'aucune extension.** Le thème est vendu : on
ne peut pas exiger du client qu'il installe WPML pour voir sa page en anglais.
`inc/i18n.php` porte donc la mécanique, `inc/lang/*.php` les dictionnaires. Ces
trois fichiers sont générés une première fois par un script, mais **c'est la
version du dépôt qui fait foi** : les corrections se font directement dedans.

`jmk_get()` distingue deux familles de réglages. Le contenu (`jmk_translatable()`)
vit sous `jmk_settings[<langue>][<clé>]` ; la configuration technique reste à
plat. `jmk_sanitize()` remonte le contenu soumis, le nettoie, puis le redescend
dans le seul compartiment de la langue éditée — les deux autres ne sont jamais
réécrites.

**L'arabe demande plus que `direction: rtl`.** Deux réglages hérités du latin le
cassent : l'interlettrage, qui sépare des lettres censées être liées, et
l'interligne serré, qui coupe les hampes. La police monospace, elle, n'a aucun
glyphe arabe — chaque caractère retombe sur une police système différente et
perd ses liaisons. Les trois sont neutralisés dans le bloc `.jmk-rtl` de
`main.css` ; y toucher casse l'arabe sans rien changer aux deux autres langues.

**Ce qui se mesure se redessine, jamais une seule fois.** Un canevas construit
alors que son conteneur mesure zéro reste vide pour toujours — vue de carrousel
encore hors champ, feuille de style pas encore appliquée, onglet ouvert en
arrière-plan. `refreshGames()` reconstruit donc rouleaux, plateau et pellicule
dès que la taille devient réelle, et il est rappelé sur quatre signaux :
`IntersectionObserver` par carte, fin de défilement du carrousel, `resize`, et
`load` / `document.fonts.ready`. C'est ce qui manquait quand les deux jeux
sortaient vides sur une installation réelle alors qu'ils passaient en test.

**Les fichiers portent leur date de modification.** `jmk_asset_version()` ajoute
`filemtime()` à la version des styles et scripts. Sans cela, `JMK_VERSION` ne
bouge qu'aux versions publiées et une extension de cache continue de servir
l'ancien fichier : la correction n'arrive jamais chez le client.

**Le titre du haut se découpe en mots, sauf le dégradé.** `.accent-text` se peint
par `background-clip` sur son propre élément ; le découper en `<span>` rend le
texte invisible, puisque les enfants héritent d'une couleur transparente sans
fond. Il est donc animé d'un bloc.

**Les habillages sont entièrement contenus.** L'arcade vit sous
`.jmk-skin-arcade` dans `main.css`, le casino sous `.jmk-skin-casino`, plus
quelques branches dans `drawWheel()` gardées par `C.skin`. Retirer la classe
rend la page exactement telle qu'elle était : c'est ce qui permet de proposer
trois looks sans maintenir trois thèmes. La liste fait foi dans `jmk_skins()`
— l'administration, le nettoyage et le créateur la lisent tous les trois.

Le piège de l'habillage, rencontré une fois : le moteur autonome ne posait la
classe que pour l'arcade (`'arcade' === skin ? ' skin-arcade' : ''`). Le
casino se montait sans erreur, avec les couleurs du sobre. Un habillage qui ne
casse rien quand il ne s'applique pas ne se remarque pas — d'où la
vérification qui compare la classe attendue pour chacun.

La couronne d'ampoules se dessine **hors de la rotation**. Dessinée dedans, elle
tournerait avec les segments et le mouvement deviendrait illisible.

**Le moteur autonome est le produit, le thème est la vitrine.**
`assets/js/jmk-embed.js` ne dépend de rien : ni WordPress, ni jQuery, ni
feuille de style jointe — son CSS est une chaîne qu'il injecte lui-même. C'est
ce fichier que `inc/builder.php` recopie dans la page qu'on télécharge, et
c'est ce fichier que désigne le code d'intégration montré sous le devis. Avant,
ce code d'intégration citait un `jeu.min.js` qui n'existait pas : la
documentation le reconnaissait en note de bas de page. Il pointe maintenant un
fichier réel.

**L'aperçu du créateur est le fichier exporté.** `jmk_builder_html()` en PHP et
`skeleton()` en JavaScript produisent le même squelette : une div, le moteur,
un appel à `mount()`. Rien à y diverger, et ce que le vendeur montre au client
est littéralement ce que le client recevra.

**Le feutre du casino n'existe qu'une fois côté site.** `bin/make-felt.php`
dessine `assets/img/felt.jpg` (13 Ko, en tuile) : deux trames de fibres
croisées et du bruit fin, bouclés sur les bords pour que la répétition ne se
voie pas. Le moteur autonome, lui, ne peut charger aucune image — il doit
tenir dans un fichier — et peint son feutre avec deux
`repeating-linear-gradient` croisés. Les deux se ressemblent assez pour que le
client ne fasse pas la différence.

**Un moteur embarqué ne partage aucun nom avec sa page d'accueil.**
`jmk-embed.js` préfixe tout en `.jmkg-`, `jmk-banner.js` en `.jmkb-`. Ce n'est
pas de la cosmétique : les bannières portaient d'abord des classes `.stage`,
`.cta`, `.logo`. Le thème définit `.stage{flex-direction:column}` pour la
roue — la règle s'appliquait aussi aux bannières, le bandeau passait en
colonne, la colonne de texte tombait à zéro et le titre sortait à un mot par
ligne. Sur le site d'un client, où `.cta` et `.logo` existent presque
toujours, le même accident se produirait sans qu'on le voie jamais. Le test
`banner.html` rejoue l'accident : il injecte les règles hostiles et vérifie
que la bannière ne bouge pas.

**La largeur du texte se mesure, elle ne s'estime pas.** `jmk-banner.js`
choisit la taille du titre en la cherchant : il part de la plus grande
plausible et descend jusqu'à ce que le bloc tienne. Le calcul repose sur
`measureText` dans un canevas hors écran — une estimation « tant de fois la
taille de police par caractère » s'est trompée d'assez pour faire déborder
quatre formats sur vingt, sans que rien ne le signale.

**Ce qui est mis à l'échelle doit être mesuré après la mise en page.** La
section « bannières » affiche les formats à leur taille réelle en pixels et
ne les réduit que si la colonne est trop étroite. La largeur de cette colonne
n'est pas connue au moment où le script s'exécute : mesurée trop tôt, elle
donne une échelle de 1 et la bannière est simplement rognée. Un
`ResizeObserver` sur chaque emplacement remplace le choix d'un bon moment.

**Les polices viennent de Google Fonts.** C'est le point qui reste en tension
avec l'argument RGPD de la page : pour un client européen strict, il faut
héberger les trois familles dans `assets/fonts/` et remplacer l'appel à
`fonts.googleapis.com` dans `jmk_assets()`.
