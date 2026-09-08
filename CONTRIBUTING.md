[🇫🇷 Français](#-français) · [🇬🇧 English](#-english)

## 🇫🇷 Français

**Contribuer**

### Workflow des branches

- `main` est la branche stable : toute modification doit passer par une Pull Request avec une CI
  verte (voir `.github/workflows/ci.yml`).
- Ne travaillez pas directement sur `main`.

### Environnement de test

Ce plugin a besoin d'une vraie instance GLPI pour être testé sérieusement : il étend
`CommonITILObject`/`ITILTemplate`/`CommonITILTask`, des conventions du cœur GLPI qui n'ont de sens
qu'exécutées contre un vrai `$DB` et un vrai `Glpi\Kernel\Kernel`. La stack Docker fournie
(`docker-compose.test.yml`) monte GLPI + MariaDB avec ce dépôt monté en lecture seule comme
plugin :

```bash
composer install --no-dev                # vendor/autoload.php est requis au runtime, voir setup.php
docker compose -f docker-compose.test.yml up -d
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console database:configure --allow-superuser -n --db-host=db --db-name=glpi --db-user=glpi --db-password=glpi
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console database:install --allow-superuser -n --default-language=fr_FR
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console plugin:install securityincidents --allow-superuser -n
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console plugin:activate securityincidents --allow-superuser -n
```

### Avant de pousser

Toutes ces vérifications tournent automatiquement en CI à chaque push/Pull Request, mais autant
les faire passer localement d'abord :

```bash
composer install                                            # dépendances dev incluses
php -l <fichier modifié>                                    # syntaxe
vendor/bin/phpstan analyse --no-progress                    # analyse statique (src/ uniquement, voir phpstan.neon.dist)
vendor/bin/phpcs --standard=vendor/glpi-project/coding-standard/GlpiStandard/ruleset.xml src inc front hook.php setup.php
```

Les tests d'intégration (`tests/`, contre une vraie instance) doivent tourner **depuis le
conteneur GLPI**, car ils démarrent un vrai `Glpi\Kernel\Kernel` et s'exécutent contre le plugin
réellement installé/activé :

```bash
docker compose -f docker-compose.test.yml exec -w /var/www/html/glpi/plugins/securityincidents glpi \
  vendor/bin/phpunit -c phpunit.xml
```

- **Analyse statique** : PHPStan est volontairement limité à `src/` (`phpstan.neon.dist`), car
  `inc/` étend des classes du cœur GLPI (`CommonITILObject`, `ITILTemplate`...) qui n'existent que
  dans une instance réelle, non stubée pour l'analyse statique ici.
- **Convention de nommage legacy dans `inc/`** : toutes les classes qui s'accrochent aux
  conventions `CommonITILObject`/`ITILTemplate`/`CommonITILTask` du cœur GLPI vivent dans `inc/`
  sous la convention historique `PluginSecurityincidentsXxx` (pas le PSR-4
  `GlpiPlugin\Securityincidents\*` utilisé partout ailleurs) — voir le docblock de
  `inc/securityincident.class.php` pour les deux limitations réelles du cœur GLPI 11 qui imposent
  ça. N'essayez pas de PSR-4-iser ces classes sans relire ce docblock d'abord.

### Vérification fonctionnelle

Au-delà de la suite qualité, tout changement visible dans l'interface (nouvel onglet, nouveau
réglage) doit être vérifié en le soumettant réellement via l'interface (requêtes HTTP réelles ou
navigateur) et en contrôlant l'état créé en base — la suite qualité seule ne détecte pas une
fonction Twig inexistante ou une colonne manquante découverte uniquement à l'exécution. Voir
[CHANGELOG.md](CHANGELOG.md) pour plusieurs exemples réels de bugs qui n'ont été trouvés qu'en
cliquant dans l'interface, pas par relecture de code.

### Messages de commit

Ce dépôt écrit ses messages de commit en français, à l'impératif, et explique le **pourquoi**
plutôt que de reformuler le diff. `git log` sur ce dépôt donne le ton à suivre.

### Numéro de version

Toute Pull Request qui change le comportement du plugin doit incrémenter
`PLUGIN_SECURITYINCIDENTS_VERSION` dans `setup.php` : c'est ce que GLPI affiche sur
**Configuration > Plugins**, et c'est lui que compare l'écran de configuration du plugin
(`front/config.php`) à la dernière version GitHub pour signaler qu'une mise à jour est disponible.

### Publier une release

Une fois `main` à jour avec les changements voulus, un push sur `main` avec une nouvelle
`PLUGIN_SECURITYINCIDENTS_VERSION` déclenche automatiquement `auto-tag.yml`, qui tague et
déclenche `release.yml` — celui-ci construit l'archive de distribution, l'installe réellement sur
une instance GLPI fraîche pour valider sa structure, publie la GitHub Release, puis ouvre une PR
mettant à jour `securityincidents.xml` (catalogue marketplace).

Pensez à ajouter une entrée dans [CHANGELOG.md](CHANGELOG.md) (format
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/)) pour toute version publiée.

### Signaler une vulnérabilité

Ne passez pas par une issue publique : voir [SECURITY.md](SECURITY.md) pour la procédure.

### Pour aller plus loin

- **[ROADMAP.md](ROADMAP.md)** : ce qui est prévu, en cours, ou explicitement écarté (avec la
  raison).

### Références officielles GLPI

Ce plugin suit les conventions officielles de développement de plugins GLPI 11 :

- **[Tutoriel officiel de création de plugin](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html)** : `plugin_init_<key>()`, hooks, autoloading PSR-4 natif.
- **[Documentation plugins GLPI, page "Create a new plugin"](https://glpi-plugins.readthedocs.io/fr/latest/empty/index.html#create-a-new-plugin)** : conventions de structure côté écosystème `pluginsGLPI` (marketplace).
- **[pluginsGLPI/empty](https://github.com/pluginsGLPI/empty)** : squelette officiel minimal, référence pour `setup.php`/`hook.php`/le manifeste XML.

## 🇬🇧 English

**Contributing**

### Branch workflow

- `main` is the stable branch: any change must go through a Pull Request with a green CI (see
  `.github/workflows/ci.yml`).
- Do not work directly on `main`.

### Test environment

This plugin needs a real GLPI instance to be tested seriously: it extends
`CommonITILObject`/`ITILTemplate`/`CommonITILTask`, GLPI core conventions that only make sense run
against a real `$DB` and a real `Glpi\Kernel\Kernel`. The provided Docker stack
(`docker-compose.test.yml`) spins up GLPI + MariaDB with this repository mounted read-only as a
plugin:

```bash
composer install --no-dev                # vendor/autoload.php is required at runtime, see setup.php
docker compose -f docker-compose.test.yml up -d
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console database:configure --allow-superuser -n --db-host=db --db-name=glpi --db-user=glpi --db-password=glpi
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console database:install --allow-superuser -n --default-language=fr_FR
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console plugin:install securityincidents --allow-superuser -n
docker compose -f docker-compose.test.yml exec glpi \
  php bin/console plugin:activate securityincidents --allow-superuser -n
```

### Before pushing

All these checks run automatically in CI on every push/Pull Request, but it's worth running them
locally first:

```bash
composer install                                            # dev dependencies included
php -l <changed file>                                       # syntax
vendor/bin/phpstan analyse --no-progress                    # static analysis (src/ only, see phpstan.neon.dist)
vendor/bin/phpcs --standard=vendor/glpi-project/coding-standard/GlpiStandard/ruleset.xml src inc front hook.php setup.php
```

Integration tests (`tests/`, against a real instance) must run **from the GLPI container**, since
they boot a real `Glpi\Kernel\Kernel` and run against the plugin actually installed/activated:

```bash
docker compose -f docker-compose.test.yml exec -w /var/www/html/glpi/plugins/securityincidents glpi \
  vendor/bin/phpunit -c phpunit.xml
```

- **Static analysis**: PHPStan is deliberately limited to `src/` (`phpstan.neon.dist`), as `inc/`
  extends GLPI core classes (`CommonITILObject`, `ITILTemplate`...) that only exist in a real
  instance, not stubbed for static analysis here.
- **Legacy naming convention in `inc/`**: every class hooking into GLPI core's
  `CommonITILObject`/`ITILTemplate`/`CommonITILTask` conventions lives in `inc/` under the legacy
  `PluginSecurityincidentsXxx` convention (not the PSR-4 `GlpiPlugin\Securityincidents\*` used
  everywhere else) — see `inc/securityincident.class.php`'s own docblock for the two real GLPI 11
  core limitations that force this. Don't try to PSR-4-ize these classes without reading that
  docblock first.

### Functional verification

Beyond the quality suite, any change visible in the interface (a new tab, a new setting) must be
verified by actually submitting it through the interface (real HTTP requests or a browser) and
checking the resulting state in the database — the quality suite alone won't catch a
nonexistent Twig function or a missing column only discovered at runtime. See
[CHANGELOG.md](CHANGELOG.md) for several real examples of bugs found only by clicking through the
interface, not by re-reading code.

### Commit messages

This repository writes its commit messages in French, in the imperative mood, and explains the
**why** rather than restating the diff. `git log` on this repository sets the tone to follow.

### Version number

Any Pull Request that changes the plugin's behavior must bump `PLUGIN_SECURITYINCIDENTS_VERSION`
in `setup.php`: this is what GLPI displays under **Configuration > Plugins**, and what the
plugin's own configuration screen (`front/config.php`) compares against the latest GitHub release
to signal that an update is available.

### Publishing a release

Once `main` is up to date with the intended changes, a push to `main` with a new
`PLUGIN_SECURITYINCIDENTS_VERSION` automatically triggers `auto-tag.yml`, which tags and triggers
`release.yml` — that workflow builds the distribution archive, actually installs it on a fresh
GLPI instance to validate its structure, publishes the GitHub Release, then opens a PR updating
`securityincidents.xml` (marketplace catalogue).

Remember to add an entry to [CHANGELOG.md](CHANGELOG.md) (in
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format) for every published version.

### Reporting a vulnerability

Do not go through a public issue: see [SECURITY.md](SECURITY.md) for the procedure.

### Going further

- **[ROADMAP.md](ROADMAP.md)**: what's planned, in progress, or explicitly ruled out (with the
  reason).

### Official GLPI references

This plugin follows the official GLPI 11 plugin development conventions:

- **[Official plugin creation tutorial](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html)**: `plugin_init_<key>()`, hooks, native PSR-4 autoloading.
- **[GLPI plugins documentation, "Create a new plugin" page](https://glpi-plugins.readthedocs.io/fr/latest/empty/index.html#create-a-new-plugin)**: structure conventions on the `pluginsGLPI` ecosystem (marketplace) side.
- **[pluginsGLPI/empty](https://github.com/pluginsGLPI/empty)**: official minimal skeleton, reference for `setup.php`/`hook.php`/the XML manifest.
