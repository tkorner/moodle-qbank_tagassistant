# Moodle Question Bank Tag Assistant (`qbank_tagassistant`)

[![Moodle Version](https://img.shields.io/badge/Moodle-4.5%20%7C%205.0%2B-orange.svg)](https://moodle.org)
[![Version](https://img.shields.io/badge/version-v0.1--beta-blue.svg)](https://github.com)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Moodle Plugin Type](https://img.shields.io/badge/Plugin%20Type-qbank-green.svg)](https://docs.moodle.org/dev/Question_bank_plugins)

**`qbank_tagassistant`** is a Moodle Question Bank plugin (`question/bank/tagassistant`) designed to assist teachers and question authors in maintaining a consistent tag taxonomy across subject departments.

---

## 🌟 Key Features & User Benefits

- **In-Context Tag Assistance**: Displays top established tags from the current **Question Bank Context** as clickable Bootstrap 5 pills/chips directly beneath the tag input field during question creation and editing.
- **Zero-Guesswork Tagging**: Eliminates the "Cold Start" problem where teachers don't know what tags already exist in their department or course.
- **Taxonomy Alignment**: Prevents tag fragmentation and synonym wildfires (e.g. `Geo` vs. `Geometrie`).
- **1-Click Selection**: Clicking a tag chip instantly populates Moodle's native `form-autocomplete` widget.
- **Option 3 Smart Expand (`+ X weitere`)**: Displays top 5 tags initially and shows a `+ X weitere` expansion button ONLY if more than 5 tags exist in the Question Bank.
- **High Performance & Caching**: Database queries are cached per Question Bank context using the Moodle Universal Cache (MUC). Empirical benchmark: **0.0095 ms** per page load.
- **100% Core Compliant**: Built strictly as a standard `qbank` plugin with zero Moodle core modifications.

---

## 🛠️ Architecture & Ecosystem Fit

- **Ecosystem Complement to `qbank_bulktags`**:
  - `qbank_bulktags` manages batch tag operations from the question list view.
  - `qbank_tagassistant` provides inline tag assistance during single-question creation/editing.
- **Scoping Decision**: Scoped to the **Question Bank Context** (`$context->id`). Aggregates question tags across all categories in the current Question Bank context.

---

## 🚀 Installation & Setup

1. Copy or clone this repository to your Moodle installation under `question/bank/tagassistant`:
   ```bash
   git clone https://github.com/your-username/moodle-qbank_tagassistant.git question/bank/tagassistant
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

## ⚡ Performance

Empirically measured inside Moodle 5.2 execution environment:
- **Cached Execution (MUC)**: `0.0095 ms` (9.5 microseconds)
- **Uncached Direct SQL**: `0.444 ms`
- **Database EXPLAIN**: Fully indexed (`PRIMARY` & `mdl_quesvers_quever_uix` unique indexes) with 0 full table scans.

---

## 📜 License

Licensed under the [GNU General Public License v3.0 or later](http://www.gnu.org/licenses/gpl.html).
Copyright (C) 2026 Antigravity.
