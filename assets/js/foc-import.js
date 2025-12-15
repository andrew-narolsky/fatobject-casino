class FocImportController {
    constructor() {
        this.importBtn = document.getElementById('foc-import-btn');
        this.resetBtn = document.getElementById('foc-reset-btn');
        this.result = document.getElementById('foc-import-result');

        this.syncProgress = document.getElementById('foc-sync-progress');
        this.syncBar = this.syncProgress?.querySelector('.foc-progress__bar');
        this.syncLabel = this.syncProgress?.querySelector('.foc-progress__label');

        this.importProgress = document.getElementById('foc-import-progress');
        this.importBar = this.importProgress?.querySelector('.foc-progress__bar');
        this.importLabel = this.importProgress?.querySelector('.foc-progress__label');

        this.statusInterval = null;

        this.bindEvents();
        this.checkStatusOnLoad();
    }

    bindEvents() {
        this.importBtn?.addEventListener('click', () => this.startImport());
        this.resetBtn?.addEventListener('click', () => this.resetData());
    }

    post(data) {
        return fetch(FOC_IMPORT.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: new URLSearchParams(data),
        }).then(res => res.json());
    }

    startPolling() {
        if (this.statusInterval) return;

        this.statusInterval = setInterval(async () => {
            const res = await this.post({
                action: 'foc_import_status',
                nonce: FOC_IMPORT.nonce,
            });

            if (!res.success || !res.data) return;

            const status = res.data;

            this.updateProgress(
                status.brandSync,
                this.syncProgress,
                this.syncBar,
                this.syncLabel
            );

            this.updateProgress(
                status.brandImport,
                this.importProgress,
                this.importBar,
                this.importLabel
            );

            const anyRunning =
                status.brandSync?.status === 'running' ||
                status.brandImport?.status === 'running';

            if (!anyRunning) {
                this.stopPolling();
                this.setButtonsState(false);

                this.result.innerHTML =
                    '<div class="notice notice-success"><p>All tasks completed successfully!</p></div>';

                await this.post({
                    action: 'foc_clear_import_status',
                    nonce: FOC_IMPORT.nonce,
                });
            } else {
                this.setButtonsState(true);
            }
        }, 1000);
    }

    stopPolling() {
        clearInterval(this.statusInterval);
        this.statusInterval = null;
    }

    updateProgress(status, container, bar, label) {
        if (!status || !container || !bar || !label) return;

        const percent = status.percent ?? 0;
        container.style.display = 'block';
        bar.style.width = percent + '%';
        label.textContent = percent + '%';
    }

    setButtonsState(running) {
        if (this.importBtn) {
            this.importBtn.disabled = running;
            this.importBtn.textContent = running ? 'Importing...' : 'Start Import';
        }

        if (this.resetBtn) {
            this.resetBtn.disabled = running;
        }
    }

    async checkStatusOnLoad() {
        const res = await this.post({
            action: 'foc_import_status',
            nonce: FOC_IMPORT.nonce,
        });

        if (!res.success || !res.data) return;

        const anyRunning =
            res.data.brandSync?.status === 'running' ||
            res.data.brandImport?.status === 'running';

        if (anyRunning) {
            this.startPolling();
        }
    }

    async startImport() {
        this.setButtonsState(true);

        this.resetProgress(this.syncProgress, this.syncBar, this.syncLabel);
        this.resetProgress(this.importProgress, this.importBar, this.importLabel);

        const res = await this.post({
            action: 'foc_run_import',
            nonce: FOC_IMPORT.nonce,
        });

        if (res.success) {
            this.result.innerHTML =
                `<div class="notice notice-info"><p>${res.data.message}</p></div>`;
            this.startPolling();
        } else {
            this.result.innerHTML =
                '<div class="notice notice-error"><p>Import failed.</p></div>';
            this.setButtonsState(false);
        }
    }

    resetProgress(container, bar, label) {
        if (!container || !bar || !label) return;

        container.style.display = 'block';
        bar.style.width = '0%';
        label.textContent = '0%';
    }

    async resetData() {
        if (!confirm('Are you sure you want to delete all brands? This action cannot be undone.')) {
            return;
        }

        const res = await this.post({
            action: 'foc_reset_data',
            nonce_reset: FOC_IMPORT.nonce_reset,
        });

        this.result.innerHTML = res.success
            ? `<div class="notice notice-success"><p>${res.data.message}</p></div>`
            : '<div class="notice notice-error"><p>Error deleting brands.</p></div>';
    }
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    new FocImportController();
});