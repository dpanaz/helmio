export function reviewPreparation(initial, statusUrl, reviewUrl) {
    return {
        state: initial,
        connectionError: false,
        timer: null,
        stopped: false,
        init() { this.poll(); },
        destroy() { this.stopped = true; clearTimeout(this.timer); },
        async poll() {
            try {
                const response = await fetch(statusUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (!response.ok || response.redirected) throw new Error('Status unavailable');
                this.state = await response.json();
                this.connectionError = false;
                if (this.state.ready) { window.location.assign(reviewUrl); return; }
            } catch {
                this.connectionError = true;
            }
            if (!this.stopped) this.timer = setTimeout(() => this.poll(), 5000);
        },
    };
}
