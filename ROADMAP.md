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

## 📅 État actuel — v0.1.6 (2026-09-10)

Toutes les fonctionnalités envisagées pour la v0.1 sont livrées et vérifiées en conditions
réelles :

- ✅ Objet ITIL `SecurityIncident` complet (acteurs, statuts, tâches, coûts, liaison d'actifs,
  notifications).
- ✅ Onglet Analyse (impact, mesures appliquées, plan de retour arrière/confinement).
- ✅ Suivi de références CVE, y compris **association en masse** (plusieurs identifiants d'un
  coup, une par ligne et/ou séparées par des virgules) — v0.1.4.
- ✅ Écran de configuration (icône clé, vérification de version GitHub).
- ✅ **Cartes de tableau de bord** natives (total, ouverts, par entité, par catégorie),
  sélectionnables comme n'importe quelle carte GLPI — v0.1.5.
- ✅ **Interface de configuration des modèles d'incident** (champs obligatoires/masqués/en
  lecture seule/valeurs prédéfinies, par catégorie) — v0.1.6.

Voir [CHANGELOG.md](CHANGELOG.md) pour le détail de chaque version, y compris les nombreuses
limitations du cœur GLPI 11 découvertes et documentées au passage (convention de nommage legacy,
colonnes de template requises sur plusieurs tables du cœur, classe de coût et classe de règle
métier requises par convention, switch en dur du cœur sur `getItemsTable()`...).

## 🔭 Prochaines étapes envisagées

Rien d'engagé pour l'instant — le périmètre v0.1 couvre le besoin initial. Pistes possibles si un
besoin réel se confirme :

- **Import CSV pour les CVE** : au-delà du collage multi-ligne actuel (v0.1.4), un import depuis un
  fichier (sortie d'un scanner de vulnérabilités par exemple).
- **Widgets de tableau de bord supplémentaires** : évolution du volume d'incidents dans le temps
  (par mois), sur le modèle des cartes `Provider::getTicketsEvolution()`/`getTicketsStatus()` du
  cœur — non repris pour l'instant, car spécifique à Ticket et plus coûteux à généraliser que les
  cartes déjà livrées.

## 🚫 Explicitement écarté (pour l'instant)

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

- [USER_GUIDE.md](USER_GUIDE.md) — utilisation au quotidien (technicien et administrateur).
- [CHANGELOG.md](CHANGELOG.md) — historique détaillé des versions publiées.
- [Releases GitHub](https://github.com/parime/glpi-security-incidents/releases)
- [Issues](https://github.com/parime/glpi-security-incidents/issues)
