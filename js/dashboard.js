document.addEventListener('DOMContentLoaded', function(event) {
    fetchFillInput('getDashCaja', 'total_registros', null, 1)
    fetchFillInput('getDashMonthly', 'total_ingreso', 1, 1)
    fetchFillInput('getDashMonthly', 'total_egresos', 2, 1)
    fetchFillInput('getDashMonthly', 'total_saldo', 3, 1)
    fetchFillInput('getDashMonthly', 'total_saldo_grafica', 3, 1)

    fetchNewData();
});

async function fetchNewData() {
    const formData = new FormData();
    formData.append('opcion', 'getChartData');

    const response = await fetch('functions/select_general.php', {
        method: 'POST',
        body: formData,
    });

    const data = await response.json();
    if (data.type === 'SUCCESS') {
        // Manejar los datos obtenidos
        updateSalesChart(data)
    } else {
        console.error(data);
    }
}

function updateSalesChart(data) {
    var $salesChart = $('#sales-chart');
    var salesChart = new Chart($salesChart, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                backgroundColor: '#007bff',
                borderColor: '#007bff',
                data: data.data_ingresos
            },
            {
                backgroundColor: '#ced4da',
                borderColor: '#ced4da',
                data: data.data_egresos
            }]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: 'index',
                intersect: true
            },
            hover: {
                mode: 'index',
                intersect: true
            },
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    gridLines: {
                        display: true,
                        lineWidth: '4px',
                        color: 'rgba(0, 0, 0, .2)',
                        zeroLineColor: 'transparent'
                    },
                    ticks: $.extend({
                        beginAtZero: true,
                        callback: function(value) {
                            if (value >= 1000) {
                                value /= 1000;
                                value += 'k';
                            }
                            return '$' + value;
                        }
                    }, {
                        fontColor: '#495057',
                        fontStyle: 'bold'
                    })
                }],
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        fontColor: '#495057',
                        fontStyle: 'bold'
                    }
                }]
            }
        }
    });
}