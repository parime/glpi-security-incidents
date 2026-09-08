# Security Incidents — incidents de sécurité comme objet ITIL natif de GLPI

Créé par **Vincent GUILLOTTE**.

🇫🇷 **Français** | [🇬🇧 English](README.en.md)

## Sommaire

- [Pourquoi un nouvel objet plutôt qu'un type de ticket ?](#pourquoi-un-nouvel-objet-plutôt-quun-type-de-ticket-)
- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Mettre à jour le plugin](#mettre-à-jour-le-plugin)
- [Configuration](#configuration)
- [Licence](#licence)

## Pourquoi un nouvel objet plutôt qu'un type de ticket ?

Un incident de sécurité (accès non autorisé, fuite de données, malware, phishing...) a un cycle de
vie différent d'un ticket de support classique : il nécessite une analyse dédiée (impact, mesures
appliquées, plan de retour arrière/confinement), reste consultable comme son propre registre
plutôt que noyé dans le volume de tickets courant, et n'a pas vocation à être créé accidentellement
via le même formulaire que "mon imprimante ne marche pas".

Ce plugin ajoute `SecurityIncident` comme objet ITIL de premier rang, **le même mécanisme
core que Ticket/Problem/Change** — pas une catégorie, pas un type de ticket, pas un filtre. Créer
un incident de sécurité ne crée jamais de Ticket sous le capot ; c'est une distinction volontaire,
vérifiée en conditions réelles (voir [CHANGELOG.md](CHANGELOG.md)).

C'est un concept différent et complémentaire de :
- l'entrée de registre de conformité de `glpi-iso27001-management` pour les incidents de sécurité
  de l'information (clause ISO 27001 A.5.24-27) — celle-là est un enregistrement de conformité,
  celui-ci est le workflow opérationnel (assigner, investiguer, résoudre).
- le rapprochement CVE/vulnérabilité de `glpi-vulnerability-manager` — ce plugin permet seulement
  à un analyste d'**associer** une référence CVE à un incident (voir ci-dessous), il ne scanne ni
  ne rapproche rien lui-même.

## Fonctionnalités

- Un objet ITIL `SecurityIncident` complet : statut/priorité/urgence/impact, acteurs
  demandeur/observateur/assigné (utilisateurs, groupes, prestataires), tâches, notes, historique.
- Un onglet **Analyse** dédié (impact, mesures appliquées, plan de retour arrière/confinement).
- Suivi de références **CVE** : associer un ou plusieurs identifiants `CVE-AAAA-NNNN` à un
  incident (format validé à la saisie).
- Notifications natives (nouveau / mise à jour / résolu / clôturé), préconfigurées à
  l'installation.
- Écran de configuration (icône clé sur **Configuration > Plugins**) affichant la version
  installée et la dernière version publiée sur GitHub.

## Prérequis

- GLPI 11.0.x
- PHP 8.2+
- MariaDB / MySQL
- Composer **uniquement si vous développez sur le plugin** — **pas nécessaire pour l'installer**,
  voir ci-dessous.

## Installation

### Méthode recommandée : archive de release (aucun Git ni Composer requis)

Idéale pour une instance GLPI en production, y compris dans un conteneur Docker minimal.

1. Téléchargez `glpi-security-incidents-X.Y.Z.zip` depuis les
   [Releases GitHub](https://github.com/parime/glpi-security-incidents/releases).
2. Extrayez l'archive dans le dossier `plugins/` de votre instance GLPI :
   ```bash
   cd /chemin/vers/glpi/plugins
   unzip glpi-security-incidents-X.Y.Z.zip
   ```
   L'archive contient déjà un dossier `securityincidents/` à la racine, avec `vendor/` inclus
   (aucune étape `composer install` nécessaire sur le serveur cible) — pas de renommage à faire.
3. Installez et activez, depuis l'interface (**Configuration > Plugins**, chercher
   « Security Incidents ») ou en ligne de commande :
   ```bash
   php bin/console plugin:install securityincidents
   php bin/console plugin:activate securityincidents
   ```

### Méthode développeur : `git clone`

Pratique pour ensuite `git pull` lors des mises à jour, mais nécessite Composer sur la machine où
vous clonez (`vendor/` n'est pas versionné) :

```bash
cd /chemin/vers/glpi/plugins
git clone https://github.com/parime/glpi-security-incidents.git securityincidents
cd securityincidents
composer install --no-dev
```

Important, dans les deux cas : le dossier doit impérativement s'appeler **`securityincidents`** —
GLPI déduit la clé du plugin (`plugin_version_securityincidents()`, etc.) du nom du dossier dans
`plugins/`.

### Accorder les droits aux profils qui en ont besoin

Après une installation fraîche, seul le profil **Super-Admin** a accès au plugin. Pour donner
accès à un autre profil : **Administration > Profils**, ouvrez le profil concerné, onglet
**« Incident de sécurité »**, cochez les droits voulus et enregistrez.

## Mettre à jour le plugin

1. Récupérez le nouveau code sur le serveur GLPI, par la même méthode qu'à l'installation
   (`git pull`, ou re-téléchargez l'archive ZIP en remplaçant le dossier `securityincidents/` par
   son contenu).
2. Relancez la migration et réactivez :
   ```bash
   php bin/console plugin:install securityincidents --force
   php bin/console plugin:activate securityincidents
   php bin/console cache:clear
   ```
   Le vidage de cache est nécessaire dès qu'un fichier `.twig` a changé (onglet Analyse, onglet
   CVE, écran de configuration) — en environnement de production, GLPI ne recharge jamais
   automatiquement un gabarit compilé sans ce vidage explicite.

## Configuration

Un menu **Assistance > Security incidents** apparaît, avec une icône clé sur la ligne du plugin
dans **Configuration > Plugins** menant à un écran affichant la version installée face à la
dernière version publiée sur GitHub, et un rappel de l'emplacement des réglages de notification
(**Configuration > Notifications**, natif à GLPI — ce plugin ne duplique pas cet écran).

## Licence

GPL-3.0-or-later — voir [LICENSE](LICENSE).
