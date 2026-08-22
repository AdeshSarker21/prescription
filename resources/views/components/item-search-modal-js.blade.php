document.addEventListener('alpine:init', () => {
    function createItemSearchModal(type) {
        return () => ({
            showModal: false,
            searchQuery: '',
            searchResults: [],
            recentItems: [],
            popularItems: [],
            highlightedIndex: -1,
            loading: false,

            async openModal() {
                this.showModal = true;
                this.searchQuery = '';
                this.searchResults = [];
                this.highlightedIndex = -1;

                await this.$nextTick();
                this.$refs.searchInput?.focus();

                this.loadDefaults();
            },

            closeModal() {
                this.showModal = false;
                this.searchQuery = '';
                this.searchResults = [];
            },

            async loadDefaults() {
                this.loading = true;
                try {
                    const [recentRes, popularRes] = await Promise.all([
                        fetch(`/doctor/items/${type}/recent`),
                        fetch(`/doctor/items/${type}/popular`),
                    ]);
                    this.recentItems = await recentRes.json();
                    this.popularItems = await popularRes.json();
                } catch (e) {
                    console.error('Failed to load defaults:', e);
                } finally {
                    this.loading = false;
                }
            },

            async search() {
                if (this.searchQuery.length === 0) {
                    this.searchResults = [];
                    this.highlightedIndex = -1;
                    this.loadDefaults();
                    return;
                }

                this.loading = true;
                this.highlightedIndex = -1;
                try {
                    const res = await fetch(`/doctor/items/${type}/search?q=${encodeURIComponent(this.searchQuery)}`);
                    this.searchResults = await res.json();
                } catch (e) {
                    console.error('Search failed:', e);
                } finally {
                    this.loading = false;
                }
            },

            async createAndSelect() {
                if (!this.searchQuery.trim()) return;

                this.loading = true;
                try {
                    const res = await fetch(`/doctor/items/${type}/store`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ name: this.searchQuery.trim() }),
                    });
                    const data = await res.json();
                    this.selectItem(data.item);
                } catch (e) {
                    console.error('Create failed:', e);
                } finally {
                    this.loading = false;
                }
            },

            selectItem(item) {
                window.dispatchEvent(new CustomEvent(`item-selected-${type}`, {
                    detail: { id: item.id, name: item.name, used_count: item.used_count }
                }));
                this.closeModal();
            },

            selectHighlighted() {
                const allItems = this.searchQuery.length > 0
                    ? this.searchResults
                    : [...this.recentItems, ...this.popularItems];

                if (this.highlightedIndex >= 0 && this.highlightedIndex < allItems.length) {
                    this.selectItem(allItems[this.highlightedIndex]);
                } else if (this.searchQuery.length > 0 && this.searchResults.length === 0) {
                    this.createAndSelect();
                }
            },

            moveHighlight(direction) {
                const allItems = this.searchQuery.length > 0
                    ? this.searchResults
                    : [...this.recentItems, ...this.popularItems];

                if (allItems.length === 0) return;

                if (this.highlightedIndex === -1) {
                    this.highlightedIndex = direction === 1 ? 0 : allItems.length - 1;
                } else {
                    this.highlightedIndex = (this.highlightedIndex + direction + allItems.length) % allItems.length;
                }

                const container = this.$refs.resultsContainer;
                const items = container?.querySelectorAll('[class*="cursor-pointer"]');
                if (items && items[this.highlightedIndex]) {
                    items[this.highlightedIndex].scrollIntoView({ block: 'nearest' });
                }
            },
        });
    }

    Alpine.data('itemSearchModal_complaint', createItemSearchModal('complaint'));
    Alpine.data('itemSearchModal_test', createItemSearchModal('test'));
    Alpine.data('itemSearchModal_medical_history', createItemSearchModal('medical_history'));
    Alpine.data('itemSearchModal_advice', createItemSearchModal('advice'));
});
