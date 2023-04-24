/*
 *  Document   : app.js
 *  Author     : pixelcave
 *  Description: Main entry point
 *
 */

// Import global dependencies
import './bootstrap';

import 'flatpickr';
import 'bootstrap-datepicker';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.ru'
import 'datatables.net-bs4'
// import 'datatables.net'
// import 'datatables.net-buttons'
// import 'datatables.net-buttons-bs4'
import Dropzone from 'dropzone'
import 'magnific-popup/dist/jquery.magnific-popup'
import 'chart.js/dist/chart.min'

// Import required modules
import Template from './modules/template';
import Helpers from "./modules/helpers";

const lat_chars = ["y", "c", "u", "k", "e", "n", "g", "sh", "sch", "z", "kh", "y", "f", "y", "v", "a", "p", "r", "o", "l", "d", "zh", "e", "ya", "ch", "s", "m", "i", "t", "", "b", "yu", "y", "c", "u", "k", "e", "n", "g", "sh", "sch", "z", "kh", "y", "f", "y", "v", "a", "p", "r", "o", "l", "d", "zh", "e", "ya", "ch", "s", "m", "i", "t", "", "b", "yu"];
const eng_chars = ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', '[', ']', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', ';', '\'', 'z', 'x', 'c', 'v', 'b', 'n', 'm', ',', '.', 'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', '[', ']', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', ';', '\'', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', ',', '.'];
const rus_chars = ['й', 'ц', 'у', 'к', 'е', 'н', 'г', 'ш', 'щ', 'з', 'х', 'ъ', 'ф', 'ы', 'в', 'а', 'п', 'р', 'о', 'л', 'д', 'ж', 'э', 'я', 'ч', 'с', 'м', 'и', 'т', 'ь', 'б', 'ю', 'Й', 'Ц', 'У', 'К', 'Е', 'Н', 'Г', 'Ш', 'Щ', 'З', 'Х', 'Ъ', 'Ф', 'Ы', 'В', 'А', 'П', 'Р', 'О', 'Л', 'Д', 'Ж', 'Э', 'Я', 'Ч', 'С', 'М', 'И', 'Т', 'Ь', 'Б', 'Ю'];

// App extends Template
export default class App extends Template {
    /*
     * Auto called when creating a new instance
     *
     */
    constructor() {
        super();
    }


    rusToUrl(str) {
        const arrAllow = [['_', '/', '-', '_', 'e', 'e', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], [' ', ' / ', ' - ', '_', 'ё', 'Ё', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9']];
        const lat_chars_adv = lat_chars.concat(arrAllow[0]);
        const rus_chars_adv = rus_chars.concat(arrAllow[1]);

        let val = "";
        for (let i = 0; i < str.length; i++) {
            if (lat_chars_adv.indexOf(str[i]) !== -1) {
                val += str[i];
            } else if (rus_chars_adv.indexOf(str[i]) !== -1) {
                val += lat_chars_adv[rus_chars_adv.indexOf(str[i])];
            } else {
                val += str[i];
            }
        }
        return val;
    }

    engToRus(str, arrAllow, isOnlyArray) {
        let eng_chars_adv, rus_chars_adv;
        if (arrAllow !== undefined) {
            eng_chars_adv = eng_chars.concat(arrAllow[0]);
            rus_chars_adv = rus_chars.concat(arrAllow[1]);
        } else {
            eng_chars_adv = eng_chars;
            rus_chars_adv = rus_chars;
        }
        let val = "";
        for (let i = 0; i < str.length; i++) {
            if (rus_chars_adv.indexOf(str[i]) !== -1) {
                val += str[i];
            } else if (eng_chars_adv.indexOf(str[i]) !== -1) {
                val += rus_chars_adv[eng_chars_adv.indexOf(str[i])];
            } else if (isOnlyArray !== true) {
                val += str[i];
            }
        }
        return val;
    }

    rusToEng(str, arrAllow, isOnlyArray) {
        let eng_chars_adv, rus_chars_adv;
        if (arrAllow !== undefined) {
            eng_chars_adv = eng_chars.concat(arrAllow[0]);
            rus_chars_adv = rus_chars.concat(arrAllow[1]);
        } else {
            eng_chars_adv = eng_chars;
            rus_chars_adv = rus_chars;
        }
        let val = "";
        for (let i = 0; i < str.length; i++) {
            if (eng_chars_adv.indexOf(str[i]) !== -1) {
                val += str[i];
            } else if (rus_chars_adv.indexOf(str[i]) !== -1) {
                val += eng_chars_adv[rus_chars_adv.indexOf(str[i])];
            } else if (isOnlyArray !== true) {
                val += str[i];
            }
        }
        return val;
    }

    engToRusFirstUpper(str, arrAllow, isOnlyArray) {
        let val = this.engToRus(str, arrAllow, isOnlyArray).toLowerCase();
        val = val.substring(0, 1).toUpperCase() + val.substring(1);
        return val;
    }

    engToRusUpper(str, arrAllow, isOnlyArray) {
        return this.engToRus(str, arrAllow, isOnlyArray).toUpperCase();
    }

    engToRusLower(str, arrAllow, isOnlyArray) {
        return this.engToRus(str, arrAllow, isOnlyArray).toLowerCase();
    }

    rusToEngUpper(str, arrAllow, isOnlyArray) {
        return this.rusToEng(str, arrAllow, isOnlyArray).toUpperCase();
    }

    rusToEngLower(str, arrAllow, isOnlyArray) {
        return this.rusToEng(str, arrAllow, isOnlyArray).toLowerCase();
    }

    maskPhoneInit() {
        const elements = document.querySelectorAll('input.js-masked-phonemob');

        for (let i = 0; i < elements.length; i++) {
            let value = elements[i].value;
            const country = $(elements[i]).parents('.form-group').find('select');
            country.children().each(function () {
                let s = this.value.substring(0, this.value.indexOf('9'));
                s = s.replace('(', '');
                if (value.substring(0, s.length) === s) country.val(this.value);
            });
            country.change(function () {
                window.One.maskPhone(this);
            });
            this.maskPhone(country);
        }
    }

    mtRand(min, max) {
        const range = max - min + 1;
        return Math.floor(Math.random() * range) + min;
    }

    mkPass(len) {
        len = len ? len : 14;
        let pass = '';
        let rnd = 0;
        let c = '';
        for (let i = 0; i < len; i++) {
            rnd = window.One.mtRand(0, 2);
            if (rnd === 0) {
                c = String.fromCharCode(window.One.mtRand(48, 57));
            }
            if (rnd === 1) {
                c = String.fromCharCode(window.One.mtRand(65, 90));
            }
            if (rnd === 2) {
                c = String.fromCharCode(window.One.mtRand(97, 122));
            }
            pass += c;
        }
        return pass;
    }

    maskPhone(country) {
        $(country).each(function () {
            const phones = $(this).parents('.form-group').find('.js-masked-phonemob');
            const mask = $(this).val();
            phones.each(function () {
                $(this).mask(mask);
            });
        });
    }

    deleteFile(obj, text) {
        if (obj.dataset.delete_url) {
            $('#modalConfirmText').text(text || 'Вы уверены, что хотите удалить файл/картинку?');
            $('#modalConfirmButton').unbind('click');
            $('#modalConfirmButton').on('click', function () {
                $.ajax({
                    url: obj.dataset.delete_url,
                    method: 'get',
                    success: function (html) {
                        $('#' + obj.dataset.image_block_id).hide(500);
                        $(obj).parent().hide(500);
                    }
                });
            });
        }
    }

    deleteItemWithCheckbox(obj, html, checkboxID) {
        let url = $(obj).attr('href');
        $('#modalConfirmText').html(html || 'Вы уверены, что хотите удалить эту строчку?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', () => {
            if (document.getElementById(checkboxID).checked) url += '?' + checkboxID + '=1';
            One.resultDeleteItem(obj, url);
        });
    }

    deleteItem(obj, text) {
        let url = $(obj).attr('href');
        $('#modalConfirmText').text(text || 'Вы уверены, что хотите удалить эту строчку?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', () => {
            One.resultDeleteItem(obj, url);
        });
    }

    resultDeleteItem(obj, url) {
        $.ajax({
            url: url,
            method: 'get',
            success: function (html) {
                if (html) {
                    if (html.code === 200) {
                        if (html.reload) {
                            location.reload(true);
                        } else if (html.redirectToUrl) {
                            window.location.href = html.redirectToUrl;
                        } else {
                            Helpers.run('notify', {
                                align: 'center',
                                type: 'success',
                                icon: 'fa fa-check mr-1',
                                message: html.message
                            });
                            $(obj).parents('tr').hide(500);

                            const sortDeleted = $(obj).parents('tr').find('.sortable').text();
                            $('td.sortable').each(function () {
                                const direction = $(this).attr('data-direction');
                                const sort = parseInt($(this).text());
                                if (sort > +sortDeleted)
                                    if (direction !== 'asc')
                                        $(this).text(parseInt($(this).text()) + 1);
                                    else
                                        $(this).text(parseInt($(this).text()) - 1);
                            });
                        }
                    } else {
                        Helpers.run('notify', {
                            align: 'center',
                            type: 'danger',
                            icon: 'fa fa-times mr-1',
                            message: html.message
                        });
                    }
                }
            }
        });
    }

    confirmModal(obj, text) {
        let url = $(obj).attr('href');
        $('#modalConfirmText').text(text || 'Вы уверены, что хотите совершить это действие?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', function () {
            $.ajax({
                url: url,
                method: 'get',
                success: function (html) {
                    if (html) {
                        if (html.code === 200) {
                            if (html.reload) {
                                location.reload(true);
                            } else {
                                Helpers.run('notify', {
                                    align: 'center',
                                    type: 'success',
                                    icon: 'fa fa-check mr-1',
                                    message: html.message
                                });
                            }
                        } else {
                            Helpers.run('notify', {
                                align: 'center',
                                type: 'danger',
                                icon: 'fa fa-times mr-1',
                                message: html.message
                            });
                        }
                    }
                }
            });
        });
        return false;
    }

    ajaxWithParams(url, data) {
        $.ajax({
            url: url,
            method: 'post',
            data: data,
            success: function (html) {
                if (html) {
                    if (html.code === 200) {
                        if (html.func) {
                            window[html.func](html.params ? html.params : '');
                        } else if (html.reload) {
                            location.reload(true);
                        } else {
                            Helpers.run('notify', {
                                align: 'center',
                                type: 'success',
                                icon: 'fa fa-check mr-1',
                                message: html.message
                            });
                        }
                    } else {
                        Helpers.run('notify', {
                            align: 'center',
                            type: 'danger',
                            icon: 'fa fa-times mr-1',
                            message: html.message
                        });
                    }
                }
            }
        });
        return false;
    }

    confirmModalAndRedirectToUrl(obj, text) {
        const url = $(obj).attr('href');
        $('#modalConfirmText').text(text || 'Вы уверены, что хотите совершить это действие?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', function () {
            window.location = url;
        });
        return false;
    }

    confirmModalAndFunc(text, func) {
        $('#modalConfirmText').text(text || 'Вы уверены, что хотите совершить это действие?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', function () {
            func();
        });
        return false;
    }

    confirmFromCheckbox(obj, text) {
        const url = $(obj).attr('href');
        $(obj).parents('.dataTables_wrapper').find('input[name="check-all"]').prop('checked', '');

        $('#modalConfirmText').text(text || 'Вы уверены, что хотите скрыть/восстановить выделенные объекты?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', function () {

            $(obj).parents('.dataTables_wrapper').find('td>div>input[name="check-item[]"]').each(function () {
                if ($(this).prop('checked')) {
                    $(this).prop('checked', '');
                    const tr = $(this).parents('tr');
                    tr.removeClass('table-active');

                    $.ajax({
                        url: url,
                        method: 'get',
                        data: {id: $(this).val()},
                        success: function (html) {
                            if (html.code === 200) {
                                if (html.action === 'hide') {
                                    tr.addClass($(obj).data('class'));
                                }
                                if (html.action === 'unHide') {
                                    tr.removeClass($(obj).data('class'));
                                }
                                if (html.action === 'delete') {
                                    tr.hide(500);
                                }
                                if (html.action === 'reload') {
                                    location.reload(true);
                                }
                            } else {
                                Helpers.run('notify', {
                                    align: 'center',
                                    type: 'danger',
                                    icon: 'fa fa-times mr-1',
                                    message: html.message
                                });
                            }
                        }
                    });

                }
            });
        });
        return false;
    }

    changeTableCols(event, e) {
        event.preventDefault();
        const url = $(e).attr('href');

        let arr = [];
        $('input[name="tableCol[]"]').each(function () {
            if ($(this).prop('checked')) {
                arr.push($(this).val());
            }
        });

        //console.log(arr);
        $.ajax({
            url: url,
            method: 'post',
            data: {'cols[]': arr},
            success: function (html) {
                // console.log(html);
                location.reload(true);
            }
        });
    }

    pricesLog() {
        if (document.getElementById('prices_log')) {
            const one = this;
            $.ajax({
                type: "GET",
                url: '/providers/prices/upload/logs',
                success: function (html) {
                    document.getElementById('prices_log').innerHTML = html;
                    setTimeout(() => One.pricesLog(), 20000);
                }
            });

        }
    }

    pricesLogAll() {
        if (document.getElementById('prices_log_all')) {
            const one = this;
            $.ajax({
                type: "GET",
                url: '/providers/prices/upload/logs/all',
                success: function (html) {
                    document.getElementById('prices_log_all').innerHTML = html;
                    setTimeout(() => One.pricesLogAll(), 20000);
                }
            });
        }
    }

    setDatePicker(id, value) {
        $('#' + id).datepicker('setDate', value);
    }

    fromSelectedItemsSubmit(event, el, objectNumber = 0) {
        let target = null;
        if ($(el).parents('.modal').length > 0) {
            target = '#' + $(el).parents('.modal').attr('id');
        }
        let url = $(el).attr('action');
        let checkSelectedItem = true;
        if ($(el).data('no-check-selected-item') && +($(el).data('no-check-selected-item')) === 1) {
            checkSelectedItem = false;
        }

        if ($(el).data('submit') && +($(el).data('submit')) === 1) {
            let arr = One.checkSelectedItems(checkSelectedItem, objectNumber);
            if (arr.length !== 0 || !checkSelectedItem) {
                $(el).find('input[name="form[cols]"]').each(function () {
                    $(this).val(arr);
                });
            } else {
                event.preventDefault();
            }
        } else {
            event.preventDefault();
            const data = {};
            $(el).find('input, textarea, select').each(function () {
                if ($(this).attr('type') === 'checkbox') {
                    if ($(this).prop('checked')) data[$(this).attr('name')] = $(this).prop('checked');
                } else if ($(this).attr('type') === 'radio') {
                    if ($(this).prop('checked')) data[$(this).attr('name')] = $(this).val();
                } else {
                    data[$(this).attr('name')] = $(this).val();
                }
            });
            One.getResultsCols(target, url, data, checkSelectedItem, objectNumber);
        }
    }

    fromSelectedItemsModal(obj, text, objectNumber = 0) {
        let target = null;
        if ($(obj).data('target').length > 0) {
            target = $(obj).data('target');
        }
        let url = $(obj).attr('href');
        $('#modalConfirmText').html(text || 'Вы уверены, что хотите совершить это действие?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', () => {
            One.getResultsCols(target, url, {}, true, objectNumber);
        });
    }

    checkSelectedItems(checkSelectedItem, objectNumber) {
        let arr = [];
        if (checkSelectedItem) {
            if ($('.dataTables_wrapper')[objectNumber]) {
                $($('.dataTables_wrapper')[objectNumber]).find('td>div>input[name="check-item[]"]').each(function () {
                    if ($(this).prop('checked')) {
                        arr.push($(this).val());
                    }
                });
            }
        }
        if (arr.length === 0 && checkSelectedItem) {
            Helpers.run('notify', {
                align: 'center',
                type: 'danger',
                icon: 'fa fa-times mr-1',
                message: "Не выделено ни одной позиции"
            });
        }
        return arr;
    }

    getResultsCols(target, url, data, checkSelectedItem, objectNumber) {
        let arr = One.checkSelectedItems(checkSelectedItem, objectNumber);

        if (arr.length !== 0 || !checkSelectedItem) {
            if (checkSelectedItem) data['cols[]'] = arr;
            if (target) One.modalSpinnerOn(target);
            $.ajax({
                url: url,
                method: 'post',
                data: data,
                success: function (html) {
                    if (html) {
                        if (target && !html.reload && !html.redirectToUrl) {
                            One.modalSpinnerOff(target);
                        }
                        if (html.code === 200) {
                            if (html.reload) {
                                location.reload(true);
                            } else if (html.redirectToUrl) {
                                window.location.href = html.redirectToUrl;
                            } else {
                                if (html.message !== '') {
                                    Helpers.run('notify', {
                                        align: 'center',
                                        type: 'success',
                                        icon: 'fa fa-check mr-1',
                                        message: html.message
                                    });
                                } else if (html.messages) {
                                    (html.messages).forEach(message => {
                                        Helpers.run('notify', {
                                            align: 'center',
                                            type: message.type,
                                            icon: 'fa fa-times mr-1',
                                            message: message.message
                                        });
                                    })
                                }
                            }
                        } else {
                            if (html.message) {
                                Helpers.run('notify', {
                                    align: 'center',
                                    type: 'danger',
                                    icon: 'fa fa-times mr-1',
                                    message: html.message
                                });
                            } else if (html.messages) {
                                (html.messages).forEach(message => {
                                    Helpers.run('notify', {
                                        align: 'center',
                                        type: 'danger',
                                        icon: 'fa fa-times mr-1',
                                        message: message
                                    });
                                })
                            }
                        }

                        if (html.modalClose) {
                            $('#' + html.modalClose).modal('hide');
                        }

                        if (html.unChecked) {
                            if ($('.dataTables_wrapper')[objectNumber]) {
                                $($('.dataTables_wrapper')[objectNumber]).find('td>div>input[name="check-item[]"]').each(function () {
                                    $(this).prop('checked', '');
                                });
                            }
                        }
                    }
                }
            });
        }
    }

    onChangeParentForm(el, url, childID) {
        $.ajax({
            url: url,
            method: 'get',
            data: {id: $(el).val()},
            success: function (html) {
                if (html) {
                    $('#' + childID).html(html);
                } else {
                    $('#' + childID).html('');
                }
            }
        });
    }

    changeGoodInSchet(el, url) {
        event.preventDefault();
        $.ajax({
            url: url,
            method: 'get',
            success: function (html) {
                if ($(el).hasClass('btn-success')) {
                    $(el).removeClass('btn-success');
                    $(el).addClass('btn-danger');
                    $(el).html('<i class="fas fa-minus"></i>');
                } else {
                    $(el).addClass('btn-success');
                    $(el).removeClass('btn-danger');
                    $(el).html('<i class="fas fa-plus"></i>');
                }
                if (html.newSchetData && document.getElementById('newSchet')) {
                    if (html.newSchetData.qnt > 0) {
                        document.getElementById('newSchet').innerText = 'В счете ' + html.newSchetData.qnt + ' товаров на ' + html.newSchetData.sum + ' р.';
                    } else {
                        document.getElementById('newSchet').innerText = 'Счет не создан';
                    }
                }
            }
        });
    }

    clearGoodSchet(url) {
        event.preventDefault();

        $('#modalConfirmText').text('Вы уверены, что хотите удалить все товары из счета?');
        $('#modalConfirmButton').unbind('click');
        $('#modalConfirmButton').on('click', function () {
            $.ajax({
                url: url,
                method: 'get',
                success: function (html) {
                    $('button.schet-button').each(function () {
                        if (!$(this).hasClass('btn-success')) {
                            $(this).addClass('btn-success');
                            $(this).removeClass('btn-danger');
                            $(this).html('<i class="fas fa-plus"></i>');
                        }
                    })
                    if (document.getElementById('newSchet')) {
                        document.getElementById('newSchet').innerText = 'Счет не создан';
                    }
                }
            });
        });
    }

    getHtmlFromAjax(url, target) {
        $.ajax({
            url: url,
            method: 'get',
            success: function (html) {
                One.modalSpinnerOff('#' + $(target).parents('.modal').attr('id'));
                target.innerHTML = html;
            }
        });
    }

    getAjaxForClickedCheckbox(target) {
        $.ajax({
            url: $(target).data('url'),
            method: 'get',
            data: {
                name: $(target).attr('name'),
                checked: $(target).prop('checked')
            },
            success: function (html) {
                // console.log(html)
            }
        });
    }

    deleteGoodAlert(el, url) {
        event.preventDefault();
        $.ajax({
            url: url,
            method: 'get',
            success: function (html) {
                $(el).parent().html('');
            }
        });
    }

    chart(id, type, data, options) {
        // console.log(options)
        const ctx = document.getElementById(id);
        if (ctx) {
            const myChart = new Chart(ctx, {
                type,
                data,
                options
            });
        }
    }

    reloadGraph(datasets, chartType = 'bar') {
        chartData.datasets = datasets;

        const myChart = document.getElementById('myChart');
        const myChartCont = document.getElementById('myChartCont');
        myChart.remove();
        const newChart = document.createElement('canvas');
        newChart.classList.add('js-chartjs-bars');
        newChart.id = 'myChart';
        myChartCont.append(newChart);
        One.chart('myChart', chartType, chartData, chartOptions);
    }

    modalSpinnerOn(target) {
        $(target).find('.modal-body').append('<div class="modalSpinner"><div><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div></div>');
    }

    modalSpinnerOff(target) {
        $(target).find('.modalSpinner').remove();
    }

    /*
     *  Here you can override or extend any function you want from Template class
     *  if you would like to change/extend/remove the default functionality.
     *
     *  This way it will be easier for you to update the module files if a new update
     *  is released since all your changes will be in here overriding the original ones.
     *
     *  Let's have a look at the _uiInit() function, the one that runs the first time
     *  we create an instance of Template class or App class which extends it. This function
     *  inits all vital functionality but you can change it to fit your own needs.
     *
     */

    /*
     * EXAMPLE #1 - Removing default functionality by making it empty
     *
     */

    //  _uiInit() {}


    /*
     * EXAMPLE #2 - Extending default functionality with additional code
     *
     */

    //  _uiInit() {
    //      // Call original function
    //      super._uiInit();
    //
    //      // Your extra JS code afterwards
    //  }

    /*
     * EXAMPLE #3 - Replacing default functionality by writing your own code
     *
     */

    //  _uiInit() {
    //      // Your own JS code without ever calling the original function's code
    //  }
}

// Once everything is loaded
jQuery(() => {
    // Create a new instance of App
    window.One = new App();
    One.helpers([
        'flatpickr',
        'datepicker',
        'maxlength',
        'select2',
        'masked-inputs',
        'core-bootstrap-custom-file-input',
        'core-bootstrap-tooltip',
        'notify',
        'table-tools-checkable',
        'table-tools-sections',
        'convert'
    ]);
    One.maskPhoneInit();
    window.confirmModal = One.confirmModal;
    window.confirmModalAndRedirectToUrl = One.confirmModalAndRedirectToUrl;
    window.deleteFile = One.deleteFile;
    window.deleteItem = One.deleteItem;
    window.deleteItemWithCheckbox = One.deleteItemWithCheckbox;
    window.changeTableCols = One.changeTableCols;
    window.confirmFromCheckbox = One.confirmFromCheckbox;
    window.confirmModalAndFunc = One.confirmModalAndFunc;
    window.fromSelectedItemsSubmit = One.fromSelectedItemsSubmit;
    window.fromSelectedItemsModal = One.fromSelectedItemsModal;
    window.onChangeParentForm = One.onChangeParentForm;
    window.changeGoodInSchet = One.changeGoodInSchet;
    window.clearGoodSchet = One.clearGoodSchet;
    window.deleteGoodAlert = One.deleteGoodAlert;
    window.setDatePicker = One.setDatePicker;
    window.getHtmlFromAjax = One.getHtmlFromAjax;
    window.getAjaxForClickedCheckbox = One.getAjaxForClickedCheckbox;
    window.mkPass = One.mkPass;
    window.getResultsCols = One.getResultsCols;
    window.checkSelectedItems = One.checkSelectedItems;
    window.ajaxWithParams = One.ajaxWithParams;
    Helpers.run('magnific-popup');

    // One.loader('hide');

    if (document.getElementById('myChart') && chartData && chartOptions) {
        // console.log(chartOptions)
        One.chart('myChart', chartType || 'bar', chartData, chartOptions);
    }

    //$('js-table-sortable').sortable();

    const fixHelper = function (e, ui) {
        ui.children().each(function () {
            $(this).width($(this).width());
        });
        return ui;
    };

    let dataSortable = '';

    $(".js-table-sortable").sortable({
        helper: fixHelper,
        items: "tbody tr",
        handle: ".sortable",
        start: function (event, ui) {
            dataSortable = $(this).sortable('serialize', {key: 'idStart[]'});
        },
        stop: function (event, ui) {
            const data = $(this).sortable('serialize', {key: 'idFinish[]'});
            dataSortable += '&' + data;

            const url = ui.item.find('.sortable').attr('data-url');
            const direction = ui.item.find('.sortable').attr('data-direction');

            $.ajax({
                type: "POST",
                url: url,
                data: 'direction=' + direction + '&' + dataSortable,
                success: function (html) {
                    const arSort = [];
                    $('td.sortable').each(function () {
                        arSort.push(parseInt($(this).text()));
                    });
                    arSort.sort((a, b) => direction === 'asc' ? a - b : b - a);
                    $('td.sortable').each(function (index) {
                        $(this).text(arSort[index]);
                    });
                }
            });

        }
    });

    $("a.js-ajax-upload-price").click(function (e) {
        e.preventDefault();
        const el = e.target;
        const tr = $(el).parents('tr');
        el.parentElement.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="sr-only">Loading...</span></div>';
        $.ajax({
            type: "GET",
            url: $(this).attr('href'),
            success: function (html) {
                tr.hide(500);
                // location.reload();
            }
        });

        // setTimeout("location.reload()", 3000);
    });

    if (document.getElementById('prices_log')) {
        One.pricesLog();
    }

    if (document.getElementById('prices_log_all')) {
        One.pricesLogAll();
    }


    $(".brand_group").click(function () {
        const createrID = $(this).data('creater');
        const zamena = $(this).data('zamena');

        $(this).parent().children('tr').each(function () {
            if ($(this).data('creater') === createrID && $(this).data('zamena') === zamena && !$(this).hasClass('brand_group')) {
                if ($(this).hasClass('d-none'))
                    $(this).removeClass('d-none');
                else
                    $(this).addClass('d-none');
            }
        });
    });

    $(document).on("click.bs.dropdown.data-api", ".noCloseDropdown", function (e) {
        e.stopPropagation()
    });

    $('#modalForm').on('show.bs.modal', function (e) {
        const relatedTarget = $(e.relatedTarget);
        if (relatedTarget.data('title')) {
            document.getElementById('modalFormLabel').innerText = relatedTarget.data('title');
        }
    })

    if (document.getElementById('modalCreate')) {
        const url = new URL(document.location);
        let params = url.searchParams;
        if (+params.get("add") === 1) {
            history.replaceState(null, null, url.origin + url.pathname);
            $('#modalCreate').modal('show');
            ajaxModalHtml($('#modalCreaterLink'));
        }
    }

    $('#modalNumber').on('shown.bs.modal', function () {
        $('#searchNumber').trigger('focus')
    })
    if (document.getElementById('modalNumber')) {
        let params = (new URL(document.location)).searchParams;
        if (+params.get("scan") === 1) {
            $('#modalNumber').modal('show');
        }
    }

    $('#modalQuantityPack').on('shown.bs.modal', function () {
        $('#quantityPack').trigger('focus')
        $('#quantityIncome').trigger('focus')
    })
    if (document.getElementById('modalQuantityPack')) {
        let params = (new URL(document.location)).searchParams;
        if (+params.get("scan") === 2) {
            $('#modalQuantityPack').modal('show');
        }
    }


    // Dropzone.options.zapCardPhotoDropzone = {
    //     dictDefaultMessage: 'sss',
    //     paramName: "file", // The name that will be used to transfer the file
    //     maxFilesize: 0.4, // MB
    //     // accept: function(file, done) {
    //     //     if (file.name == "justinbieber.jpg") {
    //     //         done("Naha, you don't.");
    //     //     }
    //     //     else { done(); }
    //     // }
    // };
    // console.log(Dropzone.options)


});


// import './modules/js-convert';
import './modules/copy-number';
import './modules/autocomplete';
import './modules/abc';
import './modules/table-col';
import './modules/ajax-form';
import './modules/ajax-modal-html';
import './modules/orders-list';
import './modules/ajax-period';