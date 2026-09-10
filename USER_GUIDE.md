[🇫🇷 Français](#-français) · [🇬🇧 English](#-english)

## 🇫🇷 Français

**Guide d'utilisation**

Ce guide suppose le plugin déjà installé et activé — voir [README.md](README.md#installation)
sinon.

### Comment ça marche, vu par chaque personne

**Le technicien / RSSI** crée un incident de sécurité comme il créerait un ticket, depuis
**Assistance > Security incidents**. Le suivi (statut, acteurs, tâches, coûts, actifs concernés,
notes) fonctionne exactement comme sur un Ticket/Change natif, avec en plus deux onglets propres à
ce plugin : **Analyse** et **CVE**.

**L'administrateur** configure, une fois pour toutes : qui a le droit de voir/créer/modifier des
incidents (Administration > Profils, onglet **« Incident de sécurité »** — aucun profil n'y a
accès par défaut hormis Super-Admin), et éventuellement des **modèles d'incident** pour
pré-remplir ou rendre obligatoires certains champs selon la catégorie.

### Créer un incident

Depuis **Assistance > Security incidents > Nouveau**, le formulaire reprend les champs standards
d'un objet ITIL GLPI : titre, description, urgence/impact/priorité, catégorie, acteurs
(demandeur/observateur/assigné — utilisateurs, groupes ou prestataires), entité, lieu. Si un
modèle s'applique à l'entité ou à la catégorie choisie (voir plus bas), ses champs
prédéfinis/masqués sont déjà appliqués à l'ouverture du formulaire.

### L'onglet Analyse

Trois champs texte libre, pensés pour documenter l'investigation au fil de l'eau plutôt qu'en une
seule fois à la clôture :

- **Impact** — ce qui a été touché, à quelle échelle.
- **Mesures appliquées / à appliquer** — actions de confinement ou de remédiation.
- **Plan de retour arrière / confinement** — comment revenir à un état sain.

### L'onglet CVE

Associez une ou plusieurs références CVE à l'incident. Le champ accepte **plusieurs identifiants
d'un coup** — un par ligne, et/ou séparés par des virgules :

```
CVE-2026-12345
CVE-2026-67890, CVE-2026-11111
```

Chaque identifiant est validé individuellement (format `CVE-AAAA-NNNN`, insensible à la casse) et
dédupliqué automatiquement ; un identifiant mal formé est signalé sans annuler l'ajout des autres.
Chaque référence pointe vers sa fiche sur la base nationale des vulnérabilités (NVD).

### Notifications

Quatre événements déclenchent une notification (nouveau / mise à jour / résolu / clôturé),
préconfigurée dès l'installation — rien à faire pour les activer. Pour personnaliser les
destinataires ou le contenu des e-mails : **Configuration > Notifications** (natif à GLPI, ce
plugin n'a pas d'écran séparé pour ça — un rappel de cet emplacement apparaît aussi sur l'écran de
configuration du plugin, voir plus bas).

### Modèles d'incident

**Configuration > Intitulés > Modèles d'incident de sécurité** (ou l'icône dédiée depuis la fiche
d'une catégorie) ouvre la même interface que pour un modèle de ticket natif :

- Un modèle par défaut peut être associé à une entité ou une catégorie (onglet de l'entité/de la
  catégorie concernée).
- Sur la fiche d'un modèle, quatre onglets permettent de configurer, champ par champ : **Champs
  obligatoires**, **Champs masqués**, **Champs en lecture seule**, **Valeurs prédéfinies**.

Point important : comme pour un Ticket ou un Change natif, un champ marqué obligatoire est un
guide côté formulaire (affiché en rouge, bloque la soumission dans le navigateur) — ce n'est pas
une contrainte vérifiée côté serveur. Un import ou un appel API qui contourne le formulaire n'est
pas bloqué par cette configuration, exactement comme pour les objets ITIL natifs de GLPI.

### Tableau de bord

Quatre cartes sont disponibles depuis **« Ajouter une carte »** sur n'importe quel tableau de bord
GLPI (Central, ou un tableau de bord personnalisé) :

- **Nombre d'incidents de sécurité** — total, tous statuts confondus.
- **Ouverts** — incidents non résolus/clôturés.
- **Incidents de sécurité par entité** et **par catégorie** — répartition en camembert, barres, ou
  liste selon le type de widget choisi.

Chaque carte respecte l'entité active et les filtres de date du tableau de bord, comme n'importe
quelle carte native.

### Écran de configuration

L'icône clé sur la ligne du plugin dans **Configuration > Plugins** ouvre un écran affichant la
version installée face à la dernière version publiée sur GitHub (badge orange si une mise à jour
est disponible), et un rappel de l'emplacement des réglages de notification.

## 🇬🇧 English

**User Guide**

This guide assumes the plugin is already installed and activated — see
[README.en.md](README.en.md#installation) otherwise.

### How it works, from each person's perspective

**The technician / security officer** creates a security incident the same way they'd create a
ticket, from **Assistance > Security incidents**. Tracking (status, actors, tasks, costs, related
assets, notes) works exactly like a native Ticket/Change, plus two tabs specific to this plugin:
**Analysis** and **CVE**.

**The administrator** configures, once: who can view/create/edit incidents (Administration >
Profiles, the **"Security incident"** tab — no profile has access by default except Super-Admin),
and optionally **incident templates** to pre-fill or require certain fields depending on category.

### Creating an incident

From **Assistance > Security incidents > New**, the form has the standard fields of a GLPI ITIL
object: title, description, urgency/impact/priority, category, actors (requester/observer/assign —
users, groups or suppliers), entity, location. If a template applies to the chosen entity or
category (see below), its predefined/hidden fields are already applied when the form opens.

### The Analysis tab

Three free-text fields, meant to document the investigation as it progresses rather than all at
once at closure:

- **Impact** — what was affected, at what scale.
- **Controls applied / to apply** — containment or remediation actions.
- **Rollback / containment plan** — how to return to a healthy state.

### The CVE tab

Associate one or more CVE references with the incident. The field accepts **several identifiers
at once** — one per line, and/or comma-separated:

```
CVE-2026-12345
CVE-2026-67890, CVE-2026-11111
```

Each identifier is validated individually (`CVE-YYYY-NNNN` format, case-insensitive) and
deduplicated automatically; a malformed identifier is reported without cancelling the others.
Each reference links to its record on the National Vulnerability Database (NVD).

### Notifications

Four events trigger a notification (new / update / solved / closed), pre-configured at install —
nothing to enable. To customize recipients or email content: **Setup > Notifications** (native to
GLPI, this plugin has no separate screen for it — a reminder of this location also appears on the
plugin's own configuration screen, see below).

### Incident templates

**Setup > Dropdowns > Security incident templates** (or the dedicated icon from a category's own
record) opens the same interface as a native ticket template:

- A default template can be attached to an entity or a category (from that entity's/category's
  own tab).
- On a template's own record, four tabs let you configure, field by field: **Mandatory fields**,
  **Hidden fields**, **Read-only fields**, **Predefined values**.

Important: like a native Ticket or Change, a field marked mandatory is a form-side guide
(shown in red, blocks submission in the browser) — it is not a server-side constraint. An import
or API call that bypasses the form is not blocked by this configuration, exactly like GLPI's own
native ITIL objects.

### Dashboard

Four cards are available from **"Add a card"** on any GLPI dashboard (Central, or a custom one):

- **Number of security incidents** — total, all statuses.
- **Open** — incidents not yet solved/closed.
- **Security incidents by entity** and **by category** — pie, bar, or list depending on the
  widget type chosen.

Each card respects the dashboard's active entity and date filters, like any native card.

### Configuration screen

The wrench icon on the plugin's row in **Setup > Plugins** opens a screen showing the installed
version next to the latest version published on GitHub (orange badge if an update is available),
and a reminder of where notification settings live.
