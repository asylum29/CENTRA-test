require('../css/app.css');
const $ = require('jquery');
require('bootstrap');

$('.custom-file-input').on('change',function(){
    let fileName = $(this).val().split("\\").pop();
    $(this).next('.custom-file-label').html(fileName);
});
