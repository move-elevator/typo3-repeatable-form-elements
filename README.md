<!-- Generated with 🧡 at typo3-badges.dev -->
![TYPO3 extension](https://typo3-badges.dev/badge/repeatable_form_elements/extension/shields.svg)
![Total downloads](https://typo3-badges.dev/badge/repeatable_form_elements/downloads/shields.svg)
![Stability](https://typo3-badges.dev/badge/repeatable_form_elements/stability/shields.svg)
![TYPO3 versions](https://typo3-badges.dev/badge/repeatable_form_elements/typo3/shields.svg)
![Latest version](https://typo3-badges.dev/badge/repeatable_form_elements/version/shields.svg)

> [!NOTE]
> This is a fork of [tritum/repeatable_form_elements](https://github.com/tritum/repeatable_form_elements), the original extension by Ralf Zimmermann / dreistrom.land. This fork adds TYPO3 v14 compatibility, PSR-14 event migration, CI/CD infrastructure and a DDEV-based multi-version test environment.

# 📦 Repeatable Form Elements

A TYPO3 extension that adds a **Repeatable container** element to the TYPO3 form framework. It allows editors to create container elements with any type of fields. In the frontend, users can dynamically add and remove copies of the container. Validation is copied automatically and all form finishers are aware of the duplicated fields.

## 📋 Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.2 – 8.4 |
| TYPO3 | 13.4 LTS, 14.x |

## 🚀 Installation

```bash
composer require tritum/repeatable-form-elements
```

Add the site set `tritum/repeatable-form-elements` to the dependencies of your site package's site set:

```yaml
# Configuration/Sets/YourSitePackage/config.yaml
dependencies:
  - tritum/repeatable-form-elements
```

## 💡 Usage

1. Open the TYPO3 **form editor** and create or open a form.
2. Add a new element — the modal lists the **Repeatable container**.
3. Add fields with validators to the container.
4. In the frontend, the container renders as a `<fieldset>` with **copy** and **remove** buttons.

### Extended SaveToDatabaseFinisher

An extended version of the `SaveToDatabaseFinisher` is included for persisting repeatable container data. See the [example form definition](Resources/Private/ExampleFormDefinitions/extended-save-to-database-finisher.form.yaml).

## ⚙️ Configuration

To deactivate the copying of variants, disable the feature flag:

```php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['repeatableFormElements.copyVariants'] = false;
```

## 🔌 Extendability

| Event | Description |
|-------|-------------|
| `CopyVariantEvent` | Modify or disable specific copied variants during container duplication. |
| `AfterBuildingFinishedEvent` | React after a form renderable has been built/copied by the repeatable container logic. Replaces the removed `afterBuildingFinished` SC_OPTIONS hook. |

## 🤝 Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for setup instructions, linting, testing and the PR workflow.

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## 🏆 Credits

This extension was originally created by [Ralf Zimmermann](https://dreistrom.land).

**Thank you to all contributors:**

- **Nora Winter** (Faktenkopf / faktenhaus.de) — sponsored the original extension
- **b13.de** — connected all the people involved
- **Elias Häußler** (haeussler.dev) — TYPO3 v11 compatibility and [TYPO3 badges](https://typo3-badges.dev)
- **Uwe** (Hawkeye1909) — removed jQuery as a dependency
- **Alexander Opitz** (extrameile-gehen.de) — SaveToDatabaseFinisher for repeatable elements
- **Falko Linke, Christian Seyfferth** (dreistrom.land) — ongoing development

And everyone else who has contributed to improving this extension.

## 📄 License

GPL-2.0-or-later — see [LICENSE](LICENSE.txt) for details.
