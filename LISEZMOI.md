# Thème « Jeux Marketing » — guide

## 1. Installation

1. Dans WordPress : **Apparence → Thèmes → Ajouter → Téléverser un thème**
2. Choisir `jeux-marketing.zip`, puis **Installer** et **Activer**
3. Aller dans **Réglages → Lecture** et cocher *Une page statique*, puis choisir une page vide comme page d'accueil

> Si l'hébergeur limite la taille d'envoi, décompresser le zip et téléverser le dossier `jeux-marketing` dans `/wp-content/themes/` par FTP.

---

## 2. Les trois langues

Le site est livré en **anglais, français et arabe**, sans extension à installer.
L'anglais est la langue d'arrivée par défaut ; le visiteur change avec le
sélecteur **EN / FR / ع** en haut à droite. Son choix est retenu, et l'adresse
`?lang=fr` ouvre directement dans une langue — pratique pour un lien envoyé à
un client précis.

L'arabe bascule toute la page en lecture de droite à gauche.

Ce qui est traduit :

| | |
|---|---|
| **Par langue** | Titres, textes, lots, options du devis, packs, déroulé, compétences, FAQ, points techniques, quiz, avis |
| **Commun aux trois** | Mode de contact, numéro, couleur, sections affichées, plafonds, prix du devis, webhook |

Dans l'administration, une barre **« Contenu affiché en : »** en haut de la page
choisit la langue que vous modifiez. Modifier le titre en français ne touche ni
l'anglais ni l'arabe.

> Un contenu que vous n'avez jamais modifié affiche la version livrée dans cette
> langue. Vous n'êtes donc jamais obligé de tout traduire pour publier.

---

## 3. Le panneau de réglages

Tout se passe dans le menu **Jeux Marketing** de la colonne de gauche. Sept onglets,
plus l'écran **Créateur de jeu** décrit à la section 6.

### Général
- **Mode direct / Mode Fiverr** — le réglage le plus important, voir la section 4
- Numéro WhatsApp, lien Fiverr, email de notification
- Quelles sections afficher sur la page d'accueil
- Webhook (Zapier, Make, CRM) et lien vers la politique de confidentialité

### Marque
Nom affiché, couleur principale, langue, symbole monétaire, et le **style
visuel** :

| Style | À quoi il ressemble | Pour qui |
|---|---|---|
| **Sobre** | Fond sombre, or discret, formes nettes | Marque haut de gamme, cabinet, boutique de créateur |
| **Fête foraine** | Violet saturé, or épais, ampoules autour de la roue, boutons bombés | Promotion grand public, jeu-concours de marque, stand de salon |
| **Casino** | Tapis de feutre vert, filets or, roue cerclée d'or, boutons jeton rouges | Soirée de marque, tirage d'anniversaire, boisson, hôtellerie, restaurant |

Le style ne touche ni aux lots, ni aux probabilités, ni à la capture d'email :
uniquement l'apparence. On peut basculer à tout moment.
La couleur se propage à toute la page : roue, boutons, accents, graphiques.
Le logo se règle dans **Apparence → Personnaliser**.

### Jeux
Activer ou désactiver les six jeux : la roue, la carte à gratter, le tap-to-win,
le quiz, la machine à sous et la pluie de lots.

La roue reste en haut de page. Les cinq autres sont présentés en **carrousel** :
un jeu à la fois sur téléphone, trois côte à côte sur grand écran. On y navigue
au doigt, aux flèches, aux puces sous le carrousel, ou aux touches ← et →.
Les questions du quiz se modifient dans le même onglet.

Tous partagent les mêmes lots, les mêmes probabilités et les mêmes plafonds.
Changer de jeu ne demande aucun autre réglage.

### Lots et probabilités
Le tableau central du thème.

| Colonne | Signification |
|---|---|
| **Libellé** | Le texte affiché sur le segment |
| **Poids** | Part relative, pas un pourcentage. 40 face à 10 sort quatre fois plus souvent |
| **Plafond** | Nombre maximum de gagnants sur la campagne. `0` = illimité |
| **Code promo** | Le code envoyé au gagnant. Un suffixe aléatoire est ajouté automatiquement |
| **Teinte** | Décalage de couleur par rapport à la couleur principale, de -180 à 180 |
| **Perdant** | Case « Réessayez ». N'attribue aucun code |

Le pourcentage réel est calculé et affiché en direct sous le tableau, pendant la saisie.

Le bouton **Remettre les compteurs de plafond à zéro** est à utiliser au lancement de chaque nouvelle campagne.

### Devis
Les options, leurs prix et leur délai.
- **Cochée** : sélectionnée d'avance pour le visiteur
- **Verrouillée** : le visiteur ne peut pas la décocher — à réserver à la prestation de base

C'est aussi ici que se règlent les valeurs de départ du calculateur de rentabilité.

### Textes
Titre, paragraphe d'introduction, étiquettes, points techniques et questions fréquentes.

### Portfolio
Les packs et leurs prix, le déroulé d'un projet, les compétences, la section
« à propos » avec ses chiffres clés, l'intitulé des réalisations et les avis
clients.

Les réalisations elles-mêmes ne sont pas ici : elles ont leur propre menu
**Réalisations**, pour pouvoir porter une image, un texte et des champs
(client, secteur, jeu utilisé, résultat mesuré).

> **Les avis sont vides au départ, et c'est voulu.** Un avis inventé est un faux
> témoignage : sur Fiverr comme sur Upwork c'est un motif de suspension, et un
> prospect qui demande à parler à la référence vous met en difficulté. La
> section reste masquée tant que vous n'avez saisi aucun avis réel. Même chose
> pour les réalisations.

> Un champ de texte laissé vide reprend la valeur d'exemple du thème. Les champs de contact font exception : effacer le numéro WhatsApp, le lien Fiverr, l'email de notification, le webhook, le lien RGPD ou le nom de marque les efface vraiment. C'est volontaire — un moyen de contact ne doit jamais réapparaître tout seul.

---

## 4. Les deux modes — à lire avant de publier

Fiverr interdit le partage de coordonnées pour sortir de la plateforme. Un lien menant vers une page où votre numéro WhatsApp est visible peut suffire à faire suspendre un compte.

D'où les deux modes :

| Mode | Ce qui s'affiche | Où l'utiliser |
|---|---|---|
| **Direct** | Bouton WhatsApp flottant, « Envoyer ce devis sur WhatsApp » | TikTok, Facebook, LinkedIn, prospection, carte de visite |
| **Fiverr** | WhatsApp entièrement masqué, bouton « Commander sur Fiverr » | Le lien placé dans votre annonce Fiverr |

En mode Fiverr, le numéro n'est pas seulement caché à l'écran : il n'est pas envoyé au navigateur du tout. Un clic droit « afficher le code source » ne le trouvera pas.

**Deux façons de gérer ça :**
- Deux installations WordPress, deux domaines, chacune sur son mode
- Ou une seule installation, et vous basculez le mode selon la campagne en cours

Le passage d'un mode à l'autre prend deux secondes et ne demande aucune autre modification.

---

## 5. Les participants

Chaque personne qui remplit le formulaire est enregistrée dans **Jeux Marketing → Participants**, avec prénom, email, lot et code.

- **Export CSV** : le bouton apparaît en haut de la liste. Le fichier s'ouvre directement dans Excel
- **Webhook** : renseignez une URL dans l'onglet Général et chaque participant y est envoyé en JSON, pour Zapier, Make, Mailchimp, Brevo ou votre CRM
- **Notification** : renseignez un email et vous recevez un message à chaque participation

Le tirage se fait **côté serveur**, pour les six jeux. Les plafonds sont donc réellement respectés : personne ne peut forcer un gain en modifiant la page dans son navigateur.

Le lot enregistré avec un participant est celui que le serveur a tiré, et pas celui que le navigateur annonce avoir gagné : chaque partie produit un jeton à usage unique, et c'est ce jeton que le formulaire renvoie. Un formulaire bricolé à la main ne peut donc pas s'attribuer le gros lot.

---

## 6. Le créateur de jeu

**Jeux Marketing → Créateur de jeu.** C'est de là que sort ce que vous livrez
à un client.

Vous composez le jeu à gauche — mécanique, marque, langue, lots, formulaire —
et il se met à jour à droite. **L'aperçu est le fichier lui-même** : même
moteur, mêmes réglages. Ce que vous voyez est ce que le client recevra.

Trois façons d'en repartir :

| Bouton | Ce que vous obtenez | Quand s'en servir |
|---|---|---|
| **Télécharger le fichier du jeu** | Un fichier `.html` unique, moteur compris | La livraison Fiverr. Le client double-clique, ça marche, sans serveur |
| **Copier le code** | Une balise `<script>` à coller | Le client a déjà un site et veut le jeu dedans |
| **Reprendre ces lots sur le site** | Les lots, la couleur et le style passent dans les réglages | Vous avez réglé le jeu dans le créateur et voulez la même chose sur votre page |

Le fichier téléchargé ne dépend de rien : ni WordPress, ni internet, ni
bibliothèque. Il s'ouvre depuis une clé USB. C'est ce qui permet aussi de
l'utiliser sur une borne de salon hors ligne.

### Où vont les participants

| Réglage | Ce qui se passe |
|---|---|
| Adresse vide | Rien n'est envoyé, rien n'est enregistré. Le jeu est une démonstration |
| **Utiliser ce site WordPress** | Chaque participant arrive dans **Participants**, et le tirage passe côté serveur |
| Une autre adresse | Les participants sont envoyés en `POST` à votre webhook |

> **Le tirage serveur n'est pas un détail.** Sans lui, les plafonds sont
> comptés dans le navigateur de chaque visiteur : chacun repart avec son
> propre compteur, et « 5 cadeaux maximum » n'en limite aucun. C'est suffisant
> pour une démonstration, jamais pour un stock réel. Le créateur l'affiche en
> clair quand vous laissez l'adresse vide.

---

## 7. Insérer un jeu dans une autre page

Le code court `[jeu]` fonctionne dans n'importe quelle page ou article.

```
[jeu]                              → la roue + le formulaire
[jeu type="grattage"]              → la carte à gratter
[jeu type="tap"]                   → le tap-to-win
[jeu type="quiz"]                  → le quiz
[jeu type="machine"]               → la machine à sous
[jeu type="plinko"]                → la pluie de lots
[jeu type="roue" formulaire="non"] → la roue seule, sans formulaire
```

---

### Sur un site qui n'est pas le vôtre

Le code court ne vaut que dans WordPress. Pour poser un jeu sur le site d'un
client, c'est le code d'intégration du créateur :

```html
<script src="https://votre-site.com/wp-content/themes/jeux-marketing/assets/js/jmk-embed.js"
  data-jeu="roue"
  data-couleur="#D9A441"
  data-style="casino"
  data-marque="ma-marque"></script>
```

Le jeu se pose là où la balise est écrite. `data-cible="#mon-bloc"` le place
ailleurs dans la page.

---

## 8. Structure des fichiers

```
jeux-marketing/
├── style.css                  en-tête du thème
├── screenshot.png             vignette affichée dans Apparence → Thèmes
├── functions.php              chargement, couleurs, config JS
├── front-page.php             page d'accueil
├── index.php                  articles et pages classiques
├── single-jmk_work.php        une réalisation en détail
├── header.php / footer.php
├── inc/
│   ├── defaults.php           valeurs par défaut
│   ├── builder.php            créateur de jeu et export
│   ├── admin.php              panneau de réglages
│   ├── leads.php              participants, tirage serveur, export CSV
│   ├── portfolio.php          réalisations
│   ├── i18n.php               langues, sélecteur, sens d'écriture
│   ├── lang/en.php            interface et contenu anglais
│   ├── lang/fr.php            interface et contenu français
│   ├── lang/ar.php            interface et contenu arabe
│   └── shortcode.php          code court [jeu]
├── languages/
│   └── jeux-marketing.pot     modèle de traduction
├── template-parts/            sections et jeux
└── assets/
    ├── img/felt.jpg           feutre de l'habillage casino
    ├── css/main.css           styles publics
    ├── css/admin.css          styles de l'administration
    ├── js/app.js              jeux, calculateur, devis
    ├── js/jmk-embed.js        moteur autonome, livrable seul
    ├── js/builder.js          le créateur de jeu
    └── js/admin.js            onglets, répéteurs, aperçu des probabilités
```

---

## 9. Avant de mettre en ligne

- [ ] Renseigner le numéro WhatsApp et le lien Fiverr
- [ ] Choisir le bon mode selon la destination du lien
- [ ] Créer une page « Politique de confidentialité » et coller son adresse dans l'onglet Général — obligatoire pour le RGPD
- [ ] Remplacer les lots d'exemple par les vôtres
- [ ] Régler les prix du devis
- [ ] Tester une partie complète : jouer, remplir le formulaire, vérifier que le participant apparaît dans l'administration
- [ ] Remettre les compteurs de plafond à zéro avant le lancement réel
- [ ] Régler les prix des packs dans l'onglet Portfolio
- [ ] Remplacer le texte « à propos » par le vôtre
- [ ] Ajouter vos vraies réalisations — et seulement les vraies
- [ ] N'ajouter que des avis réellement reçus, ou laisser la section vide
- [ ] Relire la page dans les trois langues avec le sélecteur
- [ ] Vérifier l'arabe sur téléphone : la page doit se lire de droite à gauche

---

## 10. Ce qui n'est pas inclus

Pour rester honnête sur le périmètre :

- Aucune connexion directe à Mailchimp ou Brevo n'est codée : le webhook couvre ces cas via Zapier ou Make. Une intégration native demanderait les clés d'API de chaque service.
- Le mode borne tactile hors-ligne est décrit dans les arguments de vente, mais n'est pas fourni dans ce thème.
- Les polices d'écriture sont chargées depuis Google Fonts. Sur un site soumis au RGPD européen, mieux vaut les héberger soi-même : voir la note dans le `README.md` du dépôt.
