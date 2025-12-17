class FocImportController {
    constructor() {
        this.importBtn = document.getElementById('foc-import-btn');
        this.resetBtn = document.getElementById('foc-reset-btn');
        this.result = document.getElementById('foc-import-result');

        this.progressBars = [
            {key: 'brandSync', containerId: 'foc-brand-sync-progress'},
            {key: 'brandImport', containerId: 'foc-brand-import-progress'},
            {key: 'slotSync', containerId: 'foc-slot-sync-progress'},
            {key: 'slotImport', containerId: 'foc-slot-import-progress'},
        ].map(item => {
            const container = document.getElementById(item.containerId);
            return {
                key: item.key,
                container,
                bar: container?.querySelector('.foc-progress__bar'),
                label: container?.querySelector('.foc-progress__label')
            };
        });

        this.statusInterval = null;

        this.bindEvents();
        this.checkStatusOnLoad();
    }

    bindEvents() {
        this.importBtn?.addEventListener('click', () => this.startImport());
        this.resetBtn?.addEventListener('click', () => this.resetData());
    }

    updateAllProgress(status) {
        this.progressBars.forEach(pb => {
            this.updateProgress(status[pb.key], pb.container, pb.bar, pb.label);
        });
    }

    resetAllProgress() {
        this.progressBars.forEach(pb => this.resetProgress(pb.container, pb.bar, pb.label));
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
            this.updateAllProgress(status);

            const anyRunning = Object.values(status).some(
                s => ['running', 'queued'].includes(s?.status)
            );

            if (!anyRunning) {
                this.stopPolling();
                this.setButtonsState(false);
                this.result.innerHTML =
                    '<div class="notice notice-success"><p>All tasks completed successfully!</p></div>';

                await this.post({action: 'foc_clear_import_status', nonce: FOC_IMPORT.nonce});
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
            ['running', 'queued'].includes(res.data.brandSync?.status) ||
            ['running', 'queued'].includes(res.data.brandImport?.status) ||
            ['running', 'queued'].includes(res.data.slotSync?.status) ||
            ['running', 'queued'].includes(res.data.slotImport?.status);

        if (anyRunning) {
            this.startPolling();
        }
    }

    async startImport() {
        this.setButtonsState(true);
        this.resetAllProgress();

        const res = await this.post({action: 'foc_run_import', nonce: FOC_IMPORT.nonce});

        if (res.success) {
            this.result.innerHTML = `<div class="notice notice-info"><p>${res.data.message}</p></div>`;
            this.startPolling();
        } else {
            this.result.innerHTML = '<div class="notice notice-error"><p>Import failed.</p></div>';
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

document.addEventListener('DOMContentLoaded', () => {
    new FocImportController();
});