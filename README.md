<div align="center">

<img src="Resources/Public/Icons/Extension.png" alt="Extension icon">

# TYPO3 extension `repeatable_form_elements`

[![Latest Stable Version](https://img.shields.io/packagist/v/move-elevator/typo3-repeatable-form-elements)](https://packagist.org/packages/move-elevator/typo3-repeatable-form-elements)
[![Supported TYPO3 versions](https://img.shields.io/badge/TYPO3-13.4%20%7C%2014-orange)](https://packagist.org/packages/move-elevator/typo3-repeatable-form-elements)
[![Supported PHP Versions](https://img.shields.io/packagist/dependency-v/move-elevator/typo3-repeatable-form-elements/php?logo=php)](https://packagist.org/packages/move-elevator/typo3-repeatable-form-elements)
![Stability](https://img.shields.io/badge/stability-stable-brightgreen)
[![CGL](https://img.shields.io/github/actions/workflow/status/move-elevator/typo3-repeatable-form-elements/cgl.yml?label=cgl&logo=github)](https://github.com/move-elevator/typo3-repeatable-form-elements/actions/workflows/cgl.yml)
[![Tests](https://img.shields.io/github/actions/workflow/status/move-elevator/typo3-repeatable-form-elements/tests.yml?label=tests&logo=github)](https://github.com/move-elevator/typo3-repeatable-form-elements/actions/workflows/tests.yml)
[![License](https://poser.pugx.org/move-elevator/typo3-repeatable-form-elements/license)](LICENSE)

</div>

> [!NOTE]
> This is a fork of [tritum/repeatable_form_elements](https://github.com/tritum/repeatable_form_elements), the original extension by Ralf Zimmermann / dreistrom.land. This fork adds TYPO3 v14 compatibility, PSR-14 event migration, CI/CD infrastructure and a DDEV-based multi-version test environment.

A TYPO3 extension that adds a **Repeatable container** element to the TYPO3 form framework. It allows editors to create container elements with any type of fields. In the frontend, users can dynamically add and remove copies of the container. Validation is copied automatically and all form finishers are aware of the duplicated fields.

## ✨ Features

- **Repeatable container**: a form element editors add to the form editor like any other, holding any combination of field types
- **Dynamic frontend duplication**: frontend users add and remove copies of the container via JavaScript, no page reload
- **Validation-aware**: validators on the original fields are copied automatically to every duplicated field
- **Finisher-aware**: all form finishers, including an extended `SaveToDatabaseFinisher`, understand and persist the duplicated fields
- **Extensible**: PSR-14 events let you modify or disable copied variants and react after a container is built
- **TYPO3 v13 and v14 compatible**: SC_OPTIONS hooks on v13, PSR-14 event listeners on v14

## 🔥 Installation

### Requirements

- TYPO3 13.4 LTS or 14.3 LTS
- PHP 8.2 – 8.5

### Composer

```bash
composer require move-elevator/typo3-repeatable-form-elements
```

Add the site set `tritum/repeatable-form-elements` to the dependencies of your site package's site set:

```yaml
# Configuration/Sets/YourSitePackage/config.yaml
dependencies:
  - tritum/repeatable-form-elements
```

## 🚀 Quick start

1. Open the TYPO3 **form editor** and create or open a form.
2. Add a new element — the modal lists the **Repeatable container**.
3. Add fields with validators to the container.
4. In the frontend, the container renders as a `<fieldset>` with **copy** and **remove** buttons.

## ⚡ Usage

### Extended SaveToDatabaseFinisher

An extended version of the `SaveToDatabaseFinisher` is included for persisting repeatable container data. See the [example form definition](Resources/Private/ExampleFormDefinitions/extended-save-to-database-finisher.form.yaml).

## ⚙️ Configuration

To deactivate the copying of variants, disable the feature flag:

```php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['repeatableFormElements.copyVariants'] = false;
```

## 🧩 Extending

| Event | Description |
|-------|-------------|
| `CopyVariantEvent` | Modify or disable specific copied variants during container duplication. |
| `AfterBuildingFinishedEvent` | React after a form renderable has been built/copied by the repeatable container logic. Replaces the removed `afterBuildingFinished` SC_OPTIONS hook. |

## 🧑‍💻 Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## 💎 Credits

Originally created by [Ralf Zimmermann / dreistrom.land](https://dreistrom.land). See the [original repository](https://github.com/tritum/repeatable_form_elements) for the full list of contributors.

This fork is maintained by [move:elevator](https://move-elevator.de).

## ⭐ License

GPL-2.0-or-later — see [LICENSE](LICENSE) for details.
