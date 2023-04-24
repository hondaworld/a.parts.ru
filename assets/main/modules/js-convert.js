// function converts()
// {
//     jQuery('.js-convert-number').on('input', function () {
//         jQuery(this).val(jQuery(this).val().toString().replace(/(\D)/g, ''));
//     });
//
//     jQuery('.js-convert-float').on('input', function () {
//         jQuery(this).val(jQuery(this).val().toString().replace(/[^\d\,\.]/g, '').replace(/^([\d]*[\.|\,])|[\.|\,]/g, '$1').replace(/^([\.|\,])/g, '0$1'));
//     });
//
//     jQuery('.js-convert-float-negative').on('input', function () {
//         let result = '';
//         let val = jQuery(this).val().toString();
//         if (val.indexOf('-') === 0) {
//             result = '-';
//             val = val.substr(1);
//         }
//         result += val.replace(/[^\d\,\.]/g, '').replace(/^([\d]*[\.|\,])|[\.|\,]/g, '$1').replace(/^([\.|\,])/g, '0$1');
//         jQuery(this).val(result);
//     });
//
//     jQuery('.js-convert-name').on('input', function () {
//         jQuery(this).val(One.engToRusFirstUpper(jQuery(this).val(), [['-'], ['-']], true));
//     });
//
//     jQuery('.js-convert-url').on('input', function () {
//         jQuery(this).val(One.rusToEng(jQuery(this).val(), [['-', '-', '_', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['-', ' ', '_', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9']], true));
//     });
//
//     jQuery('.js-convert-login').on('input', function () {
//         jQuery(this).val(One.rusToEng(jQuery(this).val(), [['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']], true));
//     });
// }
//
// jQuery(() => {
//     converts();
// });