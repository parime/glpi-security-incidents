# Feuille de route — Security Incidents

> Ce document présente la direction du plugin. Pour le détail technique de chaque version, voir
> [CHANGELOG.md](CHANGELOG.md).

---

## 🎯 Vision

Donner aux incidents de sécurité un vrai registre GLPI natif — un objet ITIL autonome, au même
titre que Ticket/Problem/Change — plutôt qu'un type de ticket, une catégorie, ou un filtre de
recherche. Voir [README.md](README.md#pourquoi-un-nouvel-objet-plutôt-quun-type-de-ticket-) pour
le raisonnement complet, y compris la comparaison en conditions réelles avec l'alternative
"ticket filtré par catégorie".

## 📅 Versions

### v0.1.0 — squelette initial (non publiée sur le marketplace)

Objet ITIL `SecurityIncident` complet (acteurs, statuts, tâches, notifications), onglet Analyse,
suivi de références CVE, écran de configuration avec vérification de version GitHub. Voir
[CHANGELOG.md](CHANGELOG.md) pour le détail — y compris plusieurs limitations du cœur GLPI 11
découvertes et documentées au passage (convention de nommage legacy, colonnes de template
requises sur `glpi_itilcategories`/`glpi_profiles`/`glpi_entities`, classe de coût requise par
convention).

### Prochaines étapes envisagées

- **Tableau de bord** : widgets natifs (`Glpi\Dashboard`) pour un aperçu du volume d'incidents par
  statut/catégorie/entité, sur le modèle des cartes déjà fournies par GLPI core pour Ticket.
- **Modèles d'incident enrichis** : champs obligatoires/masqués par catégorie, actuellement
  supportés au niveau base de données (`PluginSecurityincidentsSecurityIncidentTemplate` et ses
  satellites) mais sans interface de configuration dédiée.
- **Import CVE en masse** : associer plusieurs références CVE d'un coup (CSV ou collage
  multi-ligne) plutôt qu'une par une.

### Explicitement écarté (pour l'instant)

- **SLA complet à la Ticket** (`slaAffect()`/`manageSlaLevel()`/`manageOlaLevel()`, escalade cron) :
  `Change` n'a pas non plus de SLA en core — un vrai besoin confirmé sera nécessaire avant
  d'investir dans ce chantier conséquent.
- **Scan ou rapprochement automatique de vulnérabilités** : hors périmètre volontaire, voir
  [README.md](README.md#pourquoi-un-nouvel-objet-plutôt-quun-type-de-ticket-) — ce plugin trace des
  incidents, il ne remplace pas un scanner de vulnérabilités.

## 🤝 Comment contribuer

Voir [CONTRIBUTING.md](CONTRIBUTING.md) pour le cycle de développement, l'environnement de test, et
la suite de vérifications à faire passer avant de proposer un changement.

## 🔗 Liens utiles

- [CHANGELOG.md](CHANGELOG.md) — historique détaillé des versions publiées.
- [Releases GitHub](https://github.com/parime/glpi-security-incidents/releases)
- [Issues](https://github.com/parime/glpi-security-incidents/issues)
