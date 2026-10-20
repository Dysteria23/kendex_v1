import Chart from 'chart.js/auto';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('kxChart', (config) => ({
        chart: null,
        init() {
            this.chart = new Chart(this.$refs.canvas, config);
        },
        destroy() {
            this.chart?.destroy();
        },
    }));
});
