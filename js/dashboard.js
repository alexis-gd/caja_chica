document.addEventListener('DOMContentLoaded', function(event) {
    fetchFillInput('getDashCaja', 'total_registros', null, 1)
    fetchFillInput('getDashMonthly', 'total_ingreso', 1, 1)
    fetchFillInput('getDashMonthly', 'total_egresos', 2, 1)
    fetchFillInput('getDashMonthly', 'total_saldo', 3, 1)
    // fetchFillInput('getDashVehicleGiven', 'total_files_given', null, 1)
});