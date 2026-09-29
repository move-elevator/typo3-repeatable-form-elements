# AGENTS.md

Guidance for coding agents working in this repository.

## Project overview

TYPO3 Form Framework extension (`move-elevator/typo3-repeatable-form-elements`) that adds a Repeatable Container form element. Editors place fields inside the container, frontend users add and remove copies via JavaScript. Validation is copied to duplicated fields and all finishers are aware of them. Fork of `tritum/repeatable_form_elements` with TYPO3 v14 support, PSR-14 event migration and CI/CD.

- Extension key: `repeatable_form_elements`
- Namespace: `TRITUM\RepeatableFormElements\`
- PHP: `~8.2 || ~8.3 || ~8.4 || ~8.5`
- TYPO3: `^13.4 || ^14.0`

## Structure

- `Classes/Configuration/Extension.php`: boot, TypoScript setup, SC_OPTIONS hook registration
- `Classes/FormElements/`: `RepeatableContainer` (extends the core `Section`) and its interface
- `Classes/Service/CopyService.php`: duplicates variant field sets
- `Classes/Finisher/SaveToDatabaseFinisher.php`: persists repeatable data
- `Classes/Hooks/FormHooks.php`: SC_OPTIONS hooks for TYPO3 v13
- `Classes/EventListener/`: PSR-14 listeners replacing the removed v14 hooks
- `Classes/Event/`: `CopyVariantEvent` and `AfterBuildingFinishedEvent` extension points
- `Configuration/Yaml/`: `FormSetup.yaml` (frontend) and `FormSetupBackend.yaml` (form editor)
- `Configuration/Sets/RepeatableFormElements/`: site set
- `Configuration/Services.yaml`: autowires `Classes/` except `Domain/Model/*` and `Event/*`, listeners are tagged explicitly
- `Resources/`: templates, language files, JavaScript, example form definitions
- `Tests/Unit/`: PHPUnit tests
- `Tests/Acceptance/Fixtures/`: fixtures for the DDEV TYPO3 instances
- `Tests/CGL/`: isolated Composer project with the code style and analysis tooling

### Hook system

TYPO3 v13 uses SC_OPTIONS hooks registered in `Extension::registerHooks()`. TYPO3 v14 removed them, so PSR-14 listeners in `Services.yaml` take over. Both coexist, the hook registrations are harmless on v14.

### Feature toggle

```php
// Disable variant copying globally (default: true)
$GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['repeatableFormElements.copyVariants'] = false;
```

## Development commands

Requires [DDEV](https://ddev.readthedocs.io/en/stable/).

```bash
ddev start
ddev composer install

ddev cgl lint                  # all linters
ddev cgl fix                   # auto-fix
ddev cgl sca                   # static analysis (PHPStan)
ddev cgl migration:rector      # Rector migration check

ddev install all               # set up all supported TYPO3 versions
ddev install 13                # set up one version
ddev 13 typo3 cache:flush      # TYPO3 CLI for one version
```

`ddev cgl` proxies to the scripts in `Tests/CGL/composer.json`.

## Testing

```bash
ddev composer test             # PHPUnit, no coverage
ddev composer test:coverage    # PHPUnit with Xdebug coverage
```

CI runs PHPUnit through a reusable workflow on PHP 8.2 to 8.5, TYPO3 13.4 and 14.3, with highest and lowest dependencies.

## Code style and static analysis

- PHP CS Fixer with `konradmichalik/php-cs-fixer-preset` (`ddev cgl lint:php`, `ddev cgl fix:php`)
- PHPStan (`ddev cgl sca:php`)
- Rector (`ddev cgl migration:rector`)
- composer-normalize and EditorConfig checks (`ddev cgl lint:composer`, `ddev cgl lint:editorconfig`)
- `declare(strict_types=1)` in every PHP file
- Prefer `final` classes unless abstract

## Git workflow

- Branch from `main`, open a pull request
- Commit format: `<type>: <description>` with type one of feat, fix, refactor, docs, test, chore, perf, ci
- No co-author trailers
