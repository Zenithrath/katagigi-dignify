import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import ApexCharts from 'apexcharts';
import select2 from 'select2';
import jQuery from 'jquery';

window.$ = window.jQuery = jQuery;
window.Chart = Chart;
window.ApexCharts = ApexCharts;
window.Alpine = Alpine;
select2();

Alpine.start();
