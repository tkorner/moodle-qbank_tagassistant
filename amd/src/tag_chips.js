define(['core/str'], function(Str) {
    'use strict';

    /**
     * Tag Assistant AMD module for Question Bank in Moodle 5.1+.
     * Handles tag chips, autocomplete integration, and dynamic modal forms.
     *
     * @module     qbank_tagassistant/tag_chips
     * @copyright  2026 Antigravity
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    return {
        /**
         * Initialize the tag chips component.
         *
         * @param {Object} config Config parameters containing tags, targetSelector, and headingText.
         */
        init: function(config) {
            if (!config || !config.tags || config.tags.length === 0) {
                return;
            }

            var tryInit = function() {
                var targetSelect = document.querySelector(config.targetSelector || 'select#id_tags, select[name="tags[]"]');
                if (!targetSelect) {
                    return false;
                }
                this.setupChips(targetSelect, config);
                return true;
            }.bind(this);

            if (!tryInit()) {
                document.addEventListener('core_form/events:formRendered', tryInit);
                document.addEventListener('shown.bs.modal', tryInit);
                if (window.MutationObserver) {
                    var docObserver = new MutationObserver(function() {
                        if (tryInit()) {
                            docObserver.disconnect();
                        }
                    });
                    docObserver.observe(document.body, { childList: true, subtree: true });
                }
            }
        },

        /**
         * Setup chips for a found select element.
         *
         * @param {HTMLSelectElement} targetSelect
         * @param {Object} config
         */
        setupChips: function(targetSelect, config) {
            var formGroup = targetSelect.closest('.form-group, .fitem, div[id^="fitem_id_tags"]') || targetSelect.parentElement;
            if (!formGroup) {
                return;
            }

            var felement = targetSelect.closest('.felement') || formGroup.querySelector('.felement') || formGroup;
            if (felement.querySelector('.qbank-tag-chips-container')) {
                return;
            }

            var container = document.createElement('div');
            container.className = 'qbank-tag-chips-container mt-2 pt-2 border-top d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 w-100';

            var heading = document.createElement('div');
            heading.className = 'qbank-tag-chips-label fw-bold text-nowrap me-md-2';
            heading.textContent = config.headingText || 'Häufige Schlagwörter in dieser Fragensammlung:';
            container.appendChild(heading);

            var ul = document.createElement('ul');
            ul.className = 'inline-list tag_list d-flex flex-wrap gap-1 p-0 m-0 list-unstyled align-items-center flex-grow-1';

            var MAX_INITIAL = 5;
            var initialTags = config.tags.slice(0, MAX_INITIAL);
            var remainingTags = config.tags.slice(MAX_INITIAL);

            var renderTagChip = function(tag) {
                var li = document.createElement('li');
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-secondary rounded-pill me-1 mb-1 qbank-tag-chip';
                btn.setAttribute('data-tag-name', tag.name);
                btn.setAttribute('data-tag-id', tag.id);
                btn.setAttribute('aria-label', 'Add tag ' + tag.name + ' (' + tag.count + ' questions)');

                btn.innerHTML = tag.name + ' <span class="badge bg-secondary text-white rounded-pill ms-1">' + tag.count + '</span>';

                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.addTag(targetSelect, tag.name, btn);
                }.bind(this));

                li.appendChild(btn);
                return li;
            }.bind(this);

            initialTags.forEach(function(tag) {
                ul.appendChild(renderTagChip(tag));
            });

            if (remainingTags.length > 0) {
                var moreLi = document.createElement('li');
                var moreBtn = document.createElement('button');
                moreBtn.type = 'button';
                moreBtn.className = 'btn btn-outline-secondary rounded-pill me-1 mb-1 qbank-tag-more-btn';
                moreBtn.textContent = '+ ' + remainingTags.length + ' weitere';

                moreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    moreLi.remove();
                    remainingTags.forEach(function(tag) {
                        ul.appendChild(renderTagChip(tag));
                    });
                    this.syncStates(targetSelect, container);
                }.bind(this));

                moreLi.appendChild(moreBtn);
                ul.appendChild(moreLi);
            }

            container.appendChild(ul);

            var moveToBottom = function() {
                if (felement.lastElementChild !== container) {
                    felement.appendChild(container);
                }
            };

            moveToBottom();
            setTimeout(moveToBottom, 50);
            setTimeout(moveToBottom, 200);
            setTimeout(moveToBottom, 500);

            if (window.MutationObserver) {
                var observer = new MutationObserver(function() {
                    if (felement.lastElementChild !== container) {
                        felement.appendChild(container);
                    }
                });
                observer.observe(felement, { childList: true });
            }

            this.syncStates(targetSelect, container);

            targetSelect.addEventListener('change', function() {
                this.syncStates(targetSelect, container);
            }.bind(this));
        },

        /**
         * Add tag to select element and update autocomplete UI.
         *
         * @param {HTMLSelectElement} select
         * @param {String} tagName
         * @param {HTMLButtonElement} btn
         */
        addTag: function(select, tagName, btn) {
            var formGroup = select.closest('.form-group, .fitem, div[id^="fitem_id_tags"]') || select.parentElement;

            var option = null;
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value === tagName || select.options[i].text === tagName) {
                    option = select.options[i];
                    option.selected = true;
                    break;
                }
            }

            if (!option) {
                option = new Option(tagName, tagName, true, true);
                select.add(option);
            } else {
                option.selected = true;
            }

            var selectionContainer = formGroup ? formGroup.querySelector('.form-autocomplete-selection, [data-region="form_autocomplete-selection"]') : null;
            if (selectionContainer) {
                var directChildren = Array.from(selectionContainer.children);
                directChildren.forEach(function(child) {
                    if (!child.hasAttribute('data-value') && child.getAttribute('role') !== 'option') {
                        child.style.display = 'none';
                    }
                });

                var escapedName = tagName.replace(/"/g, '\\"');
                var existingBadge = selectionContainer.querySelector('span[data-value="' + escapedName + '"]');

                if (!existingBadge) {
                    var badge = document.createElement('span');
                    badge.setAttribute('role', 'option');
                    badge.setAttribute('data-value', tagName);
                    badge.setAttribute('aria-selected', 'true');
                    badge.className = 'badge bg-secondary text-dark m-1';
                    badge.style.fontSize = '100%';

                    var removeSpan = document.createElement('span');
                    removeSpan.setAttribute('aria-hidden', 'true');
                    removeSpan.textContent = '× ';

                    badge.appendChild(removeSpan);
                    badge.appendChild(document.createTextNode(tagName));

                    badge.addEventListener('click', function(e) {
                        e.preventDefault();
                        option.selected = false;
                        badge.remove();

                        var remainingBadges = selectionContainer.querySelectorAll('span[data-value]');
                        if (remainingBadges.length === 0) {
                            var directChildrenNow = Array.from(selectionContainer.children);
                            directChildrenNow.forEach(function(child) {
                                if (!child.hasAttribute('data-value') && child.getAttribute('role') !== 'option') {
                                    child.style.display = '';
                                }
                            });
                        }

                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        if (window.jQuery) {
                            window.jQuery(select).trigger('change');
                        }
                    });

                    selectionContainer.appendChild(badge);
                }
            }

            select.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.jQuery) {
                window.jQuery(select).trigger('change');
            }

            btn.disabled = true;
            btn.classList.add('disabled', 'opacity-50');
        },

        /**
         * Sync chips disabled state with selected options in select element.
         *
         * @param {HTMLSelectElement} select
         * @param {HTMLElement} container
         */
        syncStates: function(select, container) {
            var selectedValues = Array.from(select.selectedOptions).map(function(opt) {
                return opt.value;
            });

            var chips = container.querySelectorAll('.qbank-tag-chip');
            chips.forEach(function(chip) {
                var tagName = chip.getAttribute('data-tag-name');
                if (selectedValues.indexOf(tagName) !== -1) {
                    chip.disabled = true;
                    chip.classList.add('disabled', 'opacity-50');
                } else {
                    chip.disabled = false;
                    chip.classList.remove('disabled', 'opacity-50');
                }
            });
        }
    };
});
