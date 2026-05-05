$(document).ready(function() {
    setTimeout(function() {
        var plot = $('#c1').data('jqplot');
        if (plot) {
            plot.series[0].color      = '#ffc107';
            plot.series[0].fillColor  = 'rgba(255,193,7,0.4)';
            plot.series[1].color      = '#0548ad';
            plot.series[1].fillColor  = 'rgba(5,72,173,0.4)';
            plot.replot();
        }
    }, 100);
});