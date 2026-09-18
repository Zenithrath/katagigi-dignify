import './bootstrap';
import Alpine from 'alpinejs';
import select2 from 'select2';
import jQuery from 'jquery';

window.Alpine = Alpine;

// Global Rupiah formatter — single source of truth for all views.
// Accepts numbers, numeric strings, or Indonesian-formatted strings.
window.convertRupiah = function (value) {
    if (typeof value === 'string') {
        const parsed = Number(value.replace(/\./g, '').replace(',', '.'));
        if (Number.isNaN(parsed)) return value;
        value = parsed;
    }
    if (typeof value !== 'number' || Number.isNaN(value)) return value;
    const [integer, decimal] = Math.abs(value).toFixed(2).split('.');
    const grouped = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const sign = value < 0 ? '-' : '';

    return decimal === '00' ? `Rp. ${sign}${grouped}` : `Rp. ${sign}${grouped},${decimal}`;
};

// Picture upload state (profile preview) used across master & profile forms
Alpine.data('pictureState', (hasProfile = false) => ({
    isProfilePreviewMode: hasProfile,
    showProfilePreview(event, targetID) {
        if (event.target.files.length <= 0) return;
        const preview = document.getElementById(targetID);
        this.isProfilePreviewMode = true;
        preview.src = URL.createObjectURL(event.target.files[0]);
        preview.style.display = 'block';
    },
    clearProfile(selfElem, inputID, previewID) {
        selfElem.preventDefault();
        document.getElementById(inputID).value = '';
        const preview = document.getElementById(previewID);
        this.isProfilePreviewMode = false;
        preview.src = '';
        preview.style.display = '';
    },
}));

// Initialise a Select2 element bound to Alpine (guarded: no-op if plugin missing)
window.initSelectable = function (el) {
    if (window.$ && window.$.fn && typeof window.$.fn.select2 === 'function') {
        window.$(el).select2({ width: '100%' });
    }
};

// Revenue Chart (Chart.js, gaya TUK) — headline + % badge + toggle dataset + dropdown periode.
// Chart.js di-load malas via dynamic import supaya halaman lain tetap ringan.
Alpine.data('revenueChartComponent', (analyticsData = {}) => ({
    chart: null,
    ChartLib: null,
    period: 'monthly',
    mode: 'revenue', // 'revenue' | 'visits'
    dataMap: analyticsData || {},
    headline: 'Rp 0',
    pctText: '+0%',
    pctUp: true,

    init() {
        this.$nextTick(() => {
            this.renderChart();
        });
        this.$watch('period', () => this.renderChart());
        this.$watch('mode', () => this.renderChart());
    },

    setPeriod(val) {
        this.period = val;
    },

    setMode(val) {
        this.mode = val;
    },

    currentRows() {
        const ds = this.dataMap[this.period] || this.dataMap['monthly'] || [];
        return Array.isArray(ds) ? ds : [];
    },

    async renderChart() {
        const canvas = this.$refs.chartCanvas;
        if (!canvas) return;
        if (!this.ChartLib) {
            const mod = await import('chart.js/auto');
            this.ChartLib = mod.default;
        }

        const rows = this.currentRows();
        const labels = rows.map((r) => r.label);
        const isRevenue = this.mode === 'revenue';
        const values = rows.map((r) => Number(isRevenue ? r.revenue : r.visits) || 0);
        const color = isRevenue ? '#059669' : '#3b82f6';

        const total = values.reduce((a, b) => a + b, 0);
        const prev = (this.dataMap.prev && this.dataMap.prev[this.period]) || { revenue: 0, visits: 0 };
        const prevTotal = Number(isRevenue ? prev.revenue : prev.visits) || 0;
        const pct = prevTotal > 0 ? ((total - prevTotal) / prevTotal) * 100 : (total > 0 ? 100 : 0);

        this.headline = isRevenue
            ? (window.convertRupiah ? window.convertRupiah(total) : 'Rp. ' + total.toLocaleString('id-ID'))
            : total.toLocaleString('id-ID') + ' kunjungan';
        this.pctUp = pct >= 0;
        this.pctText = (pct >= 0 ? '+' : '') + (Math.round(pct * 10) / 10) + '%';

        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, color + '40');
        gradient.addColorStop(1, color + '00');

        this.chart = new this.ChartLib(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data: values,
                    borderColor: color,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.45,
                    borderWidth: 3,
                    pointBackgroundColor: color,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointHoverBorderWidth: 3,
                    pointHoverBackgroundColor: color,
                    pointHoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        titleColor: '#64748b',
                        bodyColor: '#022c22',
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false,
                        titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '700' },
                        bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 14, weight: '800' },
                        callbacks: {
                            label: (c) => isRevenue
                                ? (window.convertRupiah ? window.convertRupiah(c.parsed.y) : 'Rp. ' + c.parsed.y.toLocaleString('id-ID'))
                                : c.parsed.y.toLocaleString('id-ID') + ' kunjungan'
                        }
                    }
                },
                scales: {
                    y: { display: false },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' }
                        }
                    }
                }
            }
        });
    }
}));

Alpine.start();

// jQuery + Select2 init — guarded so third-party init can NEVER break Alpine boot.
// (select2 UMD exports a factory function; call it explicitly with our jQuery copy.)
try {
    window.$ = window.jQuery = jQuery;
    if (typeof select2 === 'function') {
        select2(window, jQuery);
    }
} catch (error) {
    console.warn('[app] select2 init skipped:', error);
}
