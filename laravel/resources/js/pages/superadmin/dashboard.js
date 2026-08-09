import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
} from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip);

function colorInstitucional(canvas) {
    const styles = getComputedStyle(document.body);
    const colorVar = canvas.dataset.colorVar || '--sb-primary';
    const fillVar = canvas.dataset.fillVar || '--sb-primary-light';

    return {
        linea: styles.getPropertyValue(colorVar).trim() || '#2d7a4f',
        relleno: styles.getPropertyValue(fillVar).trim() || '#e8f5ee',
    };
}

function renderLineChart(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const valores = JSON.parse(canvas.dataset.valores || '[]');
    const { linea, relleno } = colorInstitucional(canvas);

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: valores,
                borderColor: linea,
                backgroundColor: relleno,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: linea,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
        },
    });
}

renderLineChart('chartCrecimiento');
renderLineChart('chartComunicados');
