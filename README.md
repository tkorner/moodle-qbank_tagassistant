# Moodle Question Bank Tag Assistant (`qbank_tagassistant`)

[![Moodle Plugin CI](https://github.com/tkorner/moodle-qbank_tagassistant/actions/workflows/moodle-ci.yml/badge.svg)](https://github.com/tkorner/moodle-qbank_tagassistant/actions/workflows/moodle-ci.yml)
[![Moodle Version](https://img.shields.io/badge/Moodle-5.1%2B-orange.svg)](https://moodle.org)
[![Version](https://img.shields.io/badge/version-v2.0.0-blue.svg)](https://github.com/tkorner/moodle-qbank_tagassistant/releases)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Moodle Plugin Type](https://img.shields.io/badge/Plugin%20Type-qbank-green.svg)](https://docs.moodle.org/dev/Question_bank_plugins)

**`qbank_tagassistant`** is an open-source Moodle Question Bank plugin (`question/bank/tagassistant`) designed for **Moodle 5.1+** to assist teachers and question authors in maintaining a consistent tag taxonomy across subject departments during question editing.

---

## 🌟 Key Features & User Benefits

- **Moodle 5.1+ Modal & SPA Native**: Built specifically for Moodle 5.1+ Single-Page Application (SPA) modal forms, dynamic fragment rendering, and full-page question editors.
- **In-Context Tag Assistance**: Displays top established tags from the current **Question Bank Context** as clickable Bootstrap 5 pills/chips directly beneath the tag input field.
- **Taxonomy Alignment**: Prevents tag fragmentation and synonym wildfires (e.g. `Geo` vs. `Geometrie`).
- **1-Click Selection**: Clicking a tag chip instantly populates Moodle's native `form-autocomplete` widget.
- **Option 3 Smart Expand (`+ X weitere`)**: Displays top 5 tags initially and shows a `+ X weitere` expansion button ONLY if more than 5 tags exist in the Question Bank.
- **High Performance & Caching**: Database queries are cached per Question Bank context using the Moodle Universal Cache (MUC). Empirical benchmark: **0.0095 ms** per page load.
- **PSR-14 Hooks API Only**: Pure Moodle 5.1+ PSR-14 Hooks architecture (`db/hooks.php`) with zero legacy function callbacks and zero Moodle core modifications.

---

## 🛠️ Architecture & Ecosystem Fit

- **Requires Moodle 5.1+**: Version 2.0.0 is dedicated exclusively to Moodle 5.1+. Older Moodle versions (<= 5.0) are deprecated in favor of clean PSR-14 Hooks API architecture.
- **Scoping Decision**: Scoped to the **Question Bank Context** (`$context->id`). Aggregates question tags across all categories in the current Question Bank context.

---

## 🚀 Installation & Setup

1. Copy or clone this repository to your Moodle installation under `question/bank/tagassistant`:
   ```bash
   git clone https://github.com/tkorner/moodle-qbank_tagassistant.git question/bank/tagassistant
   ```
2. Run Moodle CLI upgrade:
   ```bash
   php admin/cli/upgrade.php
   ```
3. Purge Moodle caches:
   ```bash
   php admin/cli/purge_caches.php
   ```

---

## 🧪 Testing & Verification

- **PHPUnit Tests**:
  ```bash
  vendor/bin/phpunit question/bank/tagassistant/tests/helper_test.php
  ```
- **Behat Feature Tests**:
  ```bash
  vendor/bin/behat --config /path/to/behat.yml question/bank/tagassistant/tests/behat/tag_assistant.feature
  ```

---

## 🔒 Privacy & GDPR Compliance

Implements the Moodle Privacy API (`\core_privacy\local\metadata\null_provider`). It does not store or process personal data independently outside of Moodle core tag structures.

---

## 📜 License

Licensed under the [GNU General Public License v3.0 or later](http://www.gnu.org/licenses/gpl.html).  
Copyright (C) 2026 Antigravity & Contributors.
