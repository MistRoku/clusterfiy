import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('trend');
  if (el && window.__trend) {
    import('chart.js/auto').then(({ default: Chart }) => {
      const labels = Object.keys(window.__trend);
      const values = Object.values(window.__trend);
      new Chart(el, {
        type: 'bar',
        data: { labels, datasets: [{ data: values }] },
        options: { responsive: true, plugins: { legend: { display: false } } },
      });
    });
  }
});
