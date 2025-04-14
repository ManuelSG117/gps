/**
 * Initialize the stops chart
 * @param {Array} categories - The x-axis categories (dates)
 * @param {Array} data - The y-axis data (number of stops per day)
 */
function initStopsChart(categories, data) {
    Highcharts.chart('stops-chart', {
        chart: {
            type: 'line',
            events: {
                load: function () {
                    // Hide loading animation when chart is rendered
                    document.getElementById('loading-animation').style.display = 'none';
                }
            }
        },
        title: {
            text: 'Número de Paradas por Día'
        },
        xAxis: {
            categories: categories
        },
        yAxis: {
            title: {
                text: '# Paradas'
            }
        },
        series: [{
            name: 'Paradas',
            data: data,
            dataLabels: {
                enabled: true,
            },
            enableMouseTracking: true
        }]
    });
}