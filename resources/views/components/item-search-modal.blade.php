@props([
    'type' => 'complaint',
    'title' => 'Search Items',
])

@php
$placeholder = match($type) {
    'complaint' => 'Search complaints...',
    'test' => 'Search tests...',
    'medical_history' => 'Search conditions...',
    'advice' => 'Search advice...',
    'procedure' => 'Search procedures...',
    'treatment_plan' => 'Search treatment plans...',
    default => 'Search...',
};
@endphp

<div
    x-data
    x-on:open-item-modal-{{ $type }}.window="window.openItemModal('{{ $type }}')"
    x-ref="wrapper"
    id="item-modal-{{ $type }}"
></div>

<script>
(function() {
    const modalType = '{{ $type }}';
    const placeholder = '{{ $placeholder }}';
    const title = '{{ $title }}';
    const typeLabel = {
        complaint: 'Complaint',
        test: 'Test',
        medical_history: 'Condition',
        advice: 'Advice',
        procedure: 'Procedure',
        treatment_plan: 'Treatment Plan',
    };
    const typeIcon = {
        complaint: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        test: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>',
        medical_history: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        advice: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>'
    };

    function createModal() {
        if (document.getElementById('item-modal-backdrop-' + modalType)) return;

        var backdrop = document.createElement('div');
        backdrop.id = 'item-modal-backdrop-' + modalType;
        backdrop.className = 'item-modal-backdrop';
        backdrop.style.cssText = 'position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;z-index:999999!important;background:rgba(0,0,0,0.5)!important;backdrop-filter:blur(4px)!important;-webkit-backdrop-filter:blur(4px)!important;display:none;transition:opacity 0.2s ease;';
        backdrop.onclick = function() { window._imodal[modalType].close(); };

        var container = document.createElement('div');
        container.id = 'item-modal-box-' + modalType;
        container.className = 'item-modal-box';
        container.style.cssText = 'position:fixed!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;z-index:1000000!important;width:100%!important;max-width:36rem!important;display:none;';

        container.innerHTML = ''
            + '<div style="background:white;border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,0.25);overflow:hidden;display:flex;flex-direction:column;max-height:85vh;">'
            // Header
            + '<div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;">'
            +   '<div style="display:flex;align-items:center;gap:10px;">'
            +     '<div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#e0e7ff;color:#4f46e5;">' + (typeIcon[modalType] || '') + '</div>'
            +     '<div>'
            +       '<h3 style="font-size:15px;font-weight:700;color:#111827;margin:0;">' + title + '</h3>'
            +       '<div id="imodal-count-' + modalType + '" style="font-size:11px;color:#6b7280;margin-top:1px;">0 selected</div>'
            +     '</div>'
            +   '</div>'
            +   '<button id="imodal-close-x-' + modalType + '" style="width:32px;height:32px;border-radius:8px;border:none;background:#f3f4f6;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280;transition:all 0.15s;">'
            +     '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
            +   '</button>'
            + '</div>'
            // Search bar
            + '<div style="padding:12px 20px;border-bottom:1px solid #e5e7eb;">'
            +   '<div style="position:relative;">'
            +     '<svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            +     '<input type="text" id="imodal-search-' + modalType + '" placeholder="' + placeholder + '" autocomplete="off" style="width:100%;padding:10px 12px 10px 38px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;transition:border-color 0.15s;">'
            +     '<div id="imodal-spinner-' + modalType + '" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;">'
            +       '<svg style="animation:spin 1s linear infinite;" width="16" height="16" fill="none" viewBox="0 0 24 24"><circle opacity="0.25" cx="12" cy="12" r="10" stroke="#3b82f6" stroke-width="4"/><path opacity="0.75" fill="#3b82f6" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>'
            +     '</div>'
            +   '</div>'
            + '</div>'
            // Results
            + '<div id="imodal-results-' + modalType + '" style="overflow-y:auto;max-height:45vh;min-height:120px;"></div>'
            // Selected items preview
            + '<div id="imodal-selected-bar-' + modalType + '" style="display:none;border-top:1px solid #e5e7eb;background:#f0fdf4;max-height:80px;overflow-y:auto;"></div>'
            // Footer
            + '<div style="padding:12px 20px;border-top:1px solid #e5e7eb;background:#f9fafb;display:flex;align-items:center;justify-content:space-between;">'
            +   '<div style="font-size:11px;color:#9ca3af;display:flex;gap:6px;align-items:center;">'
            +     '<kbd style="padding:2px 5px;background:white;border:1px solid #e5e7eb;border-radius:4px;font-size:10px;font-family:monospace;">click</kbd> toggle'
            +     '<kbd style="padding:2px 5px;background:white;border:1px solid #e5e7eb;border-radius:4px;font-size:10px;font-family:monospace;">esc</kbd> close'
            +   '</div>'
            +   '<button id="imodal-confirm-' + modalType + '" disabled style="padding:8px 20px;background:#4f46e5;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.15s;opacity:0.5;">'
            +     'Add Selected'
            +   '</button>'
            + '</div>'
            + '</div>';

        document.body.appendChild(backdrop);
        document.body.appendChild(container);

        document.getElementById('imodal-close-x-' + modalType).onclick = function() { window._imodal[modalType].close(); };
        document.getElementById('imodal-close-x-' + modalType).onmouseenter = function() { this.style.background='#fee2e2'; this.style.color='#dc2626'; };
        document.getElementById('imodal-close-x-' + modalType).onmouseleave = function() { this.style.background='#f3f4f6'; this.style.color='#6b7280'; };
        document.getElementById('imodal-confirm-' + modalType).onclick = function() { window._imodal[modalType].confirm(); };
        document.getElementById('imodal-search-' + modalType).oninput = function() { window._imodal[modalType].search(); };
        document.getElementById('imodal-search-' + modalType).onkeydown = function(e) {
            if (e.key === 'Enter') { e.preventDefault(); window._imodal[modalType].searchEnter(); }
        };
    }

    window._imodal = window._imodal || {};
    window._imodal[modalType] = {
        modalType: modalType,
        selected: [],
        searchResults: [],
        recentItems: [],
        popularItems: [],
        searchQuery: '',
        debounceTimer: null,

        open: function() {
            createModal();
            var backdrop = document.getElementById('item-modal-backdrop-' + modalType);
            var box = document.getElementById('item-modal-box-' + modalType);
            var searchInput = document.getElementById('imodal-search-' + modalType);

            this.selected = [];
            this.searchQuery = '';
            this.updateSelectedBar();
            this.updateConfirmBtn();
            this.updateCount();

            backdrop.style.display = 'block';
            box.style.display = 'block';
            document.body.style.overflow = 'hidden';
            searchInput.value = '';
            searchInput.focus();
            this.loadDefaults();
        },

        close: function() {
            var backdrop = document.getElementById('item-modal-backdrop-' + modalType);
            var box = document.getElementById('item-modal-box-' + modalType);
            if (backdrop) backdrop.style.display = 'none';
            if (box) box.style.display = 'none';
            document.body.style.overflow = '';
            this.searchQuery = '';
        },

        toggleItem: function(item) {
            var idx = this.selected.findIndex(function(s) { return s.id === item.id; });
            if (idx >= 0) {
                this.selected.splice(idx, 1);
            } else {
                this.selected.push({ id: item.id, name: item.name });
            }
            this.updateSelectedBar();
            this.updateConfirmBtn();
            this.updateCount();
            this.renderResults();
        },

        isSelected: function(id) {
            return this.selected.some(function(s) { return s.id === id; });
        },

        updateCount: function() {
            var el = document.getElementById('imodal-count-' + modalType);
            if (el) {
                var n = this.selected.length;
                el.textContent = n > 0 ? n + ' selected' : 'Select ' + typeLabel[modalType].toLowerCase() + 's';
            }
        },

        updateConfirmBtn: function() {
            var btn = document.getElementById('imodal-confirm-' + modalType);
            if (btn) {
                btn.disabled = this.selected.length === 0;
                btn.style.opacity = this.selected.length === 0 ? '0.5' : '1';
                btn.style.cursor = this.selected.length === 0 ? 'default' : 'pointer';
            }
        },

        updateSelectedBar: function() {
            var bar = document.getElementById('imodal-selected-bar-' + modalType);
            if (!bar) return;
            if (this.selected.length === 0) {
                bar.style.display = 'none';
                bar.innerHTML = '';
                return;
            }
            bar.style.display = 'block';
            var self = this;
            bar.innerHTML = '<div style="padding:8px 20px;display:flex;flex-wrap:wrap;gap:6px;">'
                + this.selected.map(function(item, i) {
                    return '<span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:#dcfce7;color:#166534;border-radius:6px;font-size:12px;font-weight:500;">'
                        + item.name
                        + '<button type="button" data-remove-item=\'' + JSON.stringify(item).replace(/'/g, "&#39;") + '\' class="imodal-remove-btn" style="border:none;background:none;color:#166534;cursor:pointer;padding:0;line-height:1;font-size:14px;margin-left:2px;">&times;</button>'
                        + '</span>';
                }).join('')
                + '</div>';
            bar.querySelectorAll('.imodal-remove-btn').forEach(function(btn) {
                btn.onclick = function() {
                    var itemData = JSON.parse(btn.getAttribute('data-remove-item'));
                    self.toggleItem(itemData);
                };
            });
        },

        confirm: function() {
            if (this.selected.length === 0) return;
            var items = this.selected.slice();
            window.dispatchEvent(new CustomEvent('items-confirmed-' + modalType, { detail: { items: items } }));
            this.selected = [];
            this.updateSelectedBar();
            this.updateConfirmBtn();
            this.updateCount();
            this.close();
        },

        loadDefaults: function() {
            var self = this;
            var spinner = document.getElementById('imodal-spinner-' + modalType);
            if (spinner) spinner.style.display = 'block';

            Promise.all([
                fetch('/doctor/items/' + modalType + '/recent').then(function(r) { return r.json(); }),
                fetch('/doctor/items/' + modalType + '/popular').then(function(r) { return r.json(); }),
            ]).then(function(data) {
                self.recentItems = data[0];
                self.popularItems = data[1];
                self.renderResults();
            }).catch(function(e) {
                console.error('Failed to load defaults:', e);
            }).finally(function() {
                if (spinner) spinner.style.display = 'none';
            });
        },

        search: function() {
            var self = this;
            var searchInput = document.getElementById('imodal-search-' + modalType);
            self.searchQuery = searchInput.value.trim();

            clearTimeout(self.debounceTimer);

            if (!self.searchQuery) {
                self.searchResults = [];
                self.loadDefaults();
                return;
            }

            if (self.searchQuery.length < 1) return;

            self.debounceTimer = setTimeout(function() {
                var spinner = document.getElementById('imodal-spinner-' + modalType);
                if (spinner) spinner.style.display = 'block';

                fetch('/doctor/items/' + modalType + '/search?q=' + encodeURIComponent(self.searchQuery))
                    .then(function(r) { return r.json(); })
                    .then(function(results) {
                        self.searchResults = results;
                        self.renderResults();
                    }).catch(function(e) {
                        console.error('Search failed:', e);
                    }).finally(function() {
                        if (spinner) spinner.style.display = 'none';
                    });
            }, 200);
        },

        searchEnter: function() {
            if (this.searchResults.length === 1) {
                this.toggleItem(this.searchResults[0]);
            } else if (this.searchResults.length === 0 && this.searchQuery) {
                this.createFromSearch();
            }
        },

        createFromSearch: function() {
            var self = this;
            if (!self.searchQuery) return;

            var spinner = document.getElementById('imodal-spinner-' + modalType);
            if (spinner) spinner.style.display = 'block';

            fetch('/doctor/items/' + modalType + '/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name: self.searchQuery }),
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.item) {
                    self.toggleItem(data.item);
                    self.searchQuery = '';
                    document.getElementById('imodal-search-' + modalType).value = '';
                    self.searchResults = [];
                    self.loadDefaults();
                }
            }).catch(function(e) {
                console.error('Create failed:', e);
            }).finally(function() {
                if (spinner) spinner.style.display = 'none';
            });
        },

        renderResults: function() {
            var self = this;
            var container = document.getElementById('imodal-results-' + modalType);
            if (!container) return;

            var html = '';

            if (self.searchQuery) {
                if (self.searchResults.length === 0) {
                    html = '<div style="padding:32px 20px;text-align:center;">'
                        + '<div style="width:48px;height:48px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#9ca3af;">'
                        + '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
                        + '</div>'
                        + '<p style="font-size:14px;color:#6b7280;margin:0 0 4px 0;">No matching ' + typeLabel[modalType].toLowerCase() + ' found</p>'
                        + '<p style="font-size:12px;color:#9ca3af;margin:0 0 16px 0;">Click below to create a new record</p>'
                        + '<button id="imodal-create-btn-' + modalType + '" style="padding:8px 20px;background:#059669;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:background 0.15s;">'
                        +   '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
                        +   'Create "' + self.searchQuery + '"'
                        + '</button>'
                        + '</div>';
                    container.innerHTML = html;
                    var createBtn = document.getElementById('imodal-create-btn-' + modalType);
                    if (createBtn) {
                        createBtn.onmouseenter = function() { this.style.background='#047857'; };
                        createBtn.onmouseleave = function() { this.style.background='#059669'; };
                        createBtn.onclick = function() { self.createFromSearch(); };
                    }
                } else {
                    html = '<div style="padding:6px 20px;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;background:#f9fafb;border-bottom:1px solid #f3f4f6;">Search Results</div>';
                    self.searchResults.forEach(function(item) {
                        html += self.renderItem(item);
                    });
                    container.innerHTML = html;
                    self.bindItemEvents(container);
                }
            } else {
                if (self.recentItems.length > 0) {
                    html += '<div style="padding:6px 20px;font-size:11px;font-weight:600;color:#d97706;text-transform:uppercase;letter-spacing:0.05em;background:rgba(251,191,36,0.06);border-bottom:1px solid #fef3c7;display:flex;align-items:center;gap:6px;">'
                        + '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                        + 'Recently Used</div>';
                    self.recentItems.forEach(function(item) {
                        html += self.renderItem(item);
                    });
                }
                if (self.popularItems.length > 0) {
                    html += '<div style="padding:6px 20px;font-size:11px;font-weight:600;color:#2563eb;text-transform:uppercase;letter-spacing:0.05em;background:rgba(59,130,246,0.06);border-bottom:1px solid #dbeafe;display:flex;align-items:center;gap:6px;'
                        + (self.recentItems.length > 0 ? '' : '') + '">'
                        + '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
                        + 'Most Frequently Used</div>';
                    self.popularItems.forEach(function(item) {
                        html += self.renderItem(item);
                    });
                }
                if (!self.recentItems.length && !self.popularItems.length) {
                    html = '<div style="padding:32px 20px;text-align:center;color:#9ca3af;">'
                        + '<p style="font-size:13px;margin:0;">Type to search ' + typeLabel[modalType].toLowerCase() + 's</p>'
                        + '</div>';
                }
                container.innerHTML = html;
                self.bindItemEvents(container);
            }
        },

        renderItem: function(item) {
            var selected = this.isSelected(item.id);
            var bg = selected ? '#eff6ff' : 'transparent';
            var borderLeft = selected ? '3px solid #3b82f6' : '3px solid transparent';
            var initials = item.name.substring(0, 1).toUpperCase();
            var initialBg = selected ? '#3b82f6' : '#e5e7eb';
            var initialColor = selected ? 'white' : '#6b7280';

            return '<div class="imodal-item" data-id="' + item.id + '" data-name="' + item.name.replace(/"/g, '&quot;') + '" data-item=\'' + JSON.stringify(item).replace(/'/g, "&#39;") + '\''
                + ' style="display:flex;align-items:center;gap:12px;padding:10px 20px;cursor:pointer;transition:background 0.1s;background:' + bg + ';border-left:' + borderLeft + ';">'
                + '<div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;background:' + initialBg + ';color:' + initialColor + ';">' + initials + '</div>'
                + '<div style="flex:1;min-width:0;">'
                +   '<div style="font-size:13px;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + item.name + '</div>'
                +   (item.used_count > 0 ? '<div style="font-size:11px;color:#9ca3af;">Used ' + item.used_count + 'x</div>' : '')
                + '</div>'
                + (selected ? '<svg width="16" height="16" fill="#3b82f6" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>' : '<svg width="16" height="16" fill="none" stroke="#d1d5db" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>')
                + '</div>';
        },

        bindItemEvents: function(container) {
            var self = this;
            var items = container.querySelectorAll('.imodal-item');
            items.forEach(function(el) {
                el.onclick = function() {
                    var itemData = JSON.parse(el.getAttribute('data-item'));
                    self.toggleItem(itemData);
                };
                el.onmouseenter = function() {
                    if (!self.isSelected(parseInt(el.getAttribute('data-id')))) {
                        el.style.background = '#f9fafb';
                    }
                };
                el.onmouseleave = function() {
                    el.style.background = self.isSelected(parseInt(el.getAttribute('data-id'))) ? '#eff6ff' : 'transparent';
                };
            });
        }
    };

    window.openItemModal = function(type) {
        createModal();
        window._imodal[type].open();
    };

    window.closeItemModal = function(type) {
        window._imodal[type].close();
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            Object.keys(window._imodal).forEach(function(t) {
                var backdrop = document.getElementById('item-modal-backdrop-' + t);
                if (backdrop && backdrop.style.display === 'block') {
                    window._imodal[t].close();
                }
            });
        }
    });
})();
</script>
