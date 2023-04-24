import Helpers from "./helpers";

jQuery(() => {
    window.tableColInputSubmit = tableColInputSubmit;
});


$(".table-col-input").blur(function (e) {
    tableColInputSubmit($(this));
});

$(".table-col-input").keydown(function (e) {
    if (e.keyCode === 27) {
        $(this).val($(this).data('old'));
        $(this).removeClass('is-invalid');
    }
    if (e.keyCode === 13) {
        e.preventDefault();
        tableColInputSubmit($(this));
    }
});

function tableColInputSubmit(el, params = {}) {
    el = $(el);
    if (el.data('old') === undefined || el.data('old') != el.val()) {
        params.value = el.val();

        $.ajax({
            url: el.data('url-submit'),
            method: 'post',
            data: params,
            success: function (html) {
                if (html) {
                    if (html.code === 200) {
                        if (html.reload) {
                            location.reload(true);
                        } else if (html.delete) {
                            el.parents('tr').hide(500);
                        } else {
                            if (el.data('old') !== undefined) {
                                el.data('old', el.val());
                            }
                            el.removeClass('is-invalid');
                            el.addClass('is-valid');

                            const inputList = document.querySelectorAll('input');
                            for (let col of inputList) {
                                if (col.dataset.ident && col.dataset.ident === html.ident) {
                                    if (html.inputIdentification) {
                                        let isBool = true;
                                        for (let identification of html.inputIdentification) {
                                            if (col.dataset[identification.name.toLowerCase()] != identification.value) isBool = false;
                                        }
                                        if (isBool) {
                                            col.value = el.val();
                                            col.dataset['old'] = el.val();
                                        }
                                    }
                                }
                                if (html.valuesIdentification) {
                                    for (let identification of html.valuesIdentification) {
                                        if (col.id === identification.id) {
                                            col.value = identification.value;
                                            col.dataset['old'] = identification.value;
                                        }
                                    }
                                }
                            }
                            if (html.idIdentification) {
                                for (let identification of html.idIdentification) {
                                    if (document.getElementById(identification.name)) {
                                        document.getElementById(identification.name).innerText = identification.value;
                                    }
                                }
                            }
                            if (html.removeParentClasses) {
                                for (let className of html.removeParentClasses) {
                                    el.parents('tr').removeClass(className);
                                }
                            }
                            if (html.addParentClasses) {
                                for (let className of html.addParentClasses) {
                                    el.parents('tr').addClass(className);
                                }
                            }

                            setTimeout(() => {
                                el.removeClass('is-valid');
                            }, 3000);

                            if (html.message) {
                                Helpers.run('notify', {
                                    align: 'center',
                                    type: 'success',
                                    icon: 'fa fa-check mr-1',
                                    message: html.message
                                });
                            }
                        }
                    } else {
                        el.removeClass('is-valid').addClass('is-invalid');
                        if (html.message) {
                            Helpers.run('notify', {
                                align: 'center',
                                type: 'danger',
                                icon: 'fa fa-times mr-1',
                                message: html.message
                            });
                        }
                    }
                }
            }
        });
    }
}