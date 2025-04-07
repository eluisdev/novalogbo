import Swal from 'sweetalert2';
window.Swal = Swal;

import './bootstrap';
import './loading';
import './quotations';

import $ from 'jquery';
window.$ = window.jQuery = $;

import select2 from 'select2';
select2($);

import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';
