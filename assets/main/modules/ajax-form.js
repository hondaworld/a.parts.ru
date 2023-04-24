$(".ajax-form").submit(function (e) {
    const form = $(this);
    ajaxForm(e, form);
});

jQuery(() => {
    window.ajaxFormSubmit = ajaxFormSubmit;
});

function ajaxFormSubmit(event, form) {
    ajaxForm(event, $(form));
}

function ajaxForm(event, form) {
    event.preventDefault();
    let url = form.attr('action');
    const data = {};
    form.find('input, textearea, select').each(function () {
        if ($(this).attr('type') === 'checkbox') {
            if ($(this).prop('checked')) data[$(this).attr('name')] = $(this).prop('checked');
        } else {
            data[$(this).attr('name')] = $(this).val();
        }
    });
    const modalId = form.parents('.modal').attr('id');
    One.modalSpinnerOn('#' + modalId);
    $.ajax({
        url: url,
        method: 'post',
        data: data,
        success: function (html) {
            if (html) {
                if (!html.reload && !html.redirectToUrl) {
                    One.modalSpinnerOff('#' + modalId);
                }
                // console.log(html)
                if (html.code === 200) {
                    if (html.reload) {
                        // window.location = location.href;
                        location.reload(true);
                    } else if (html.redirectToUrl) {
                        window.location.href = html.redirectToUrl;
                    } else {
                        if (html.message) {
                            form.parents('.modal').find('.alert').each(function () {
                                $(this).addClass('d-block');
                                $(this).removeClass('alert-danger');
                                $(this).addClass('alert-success');
                                $(this).find('p').html(html.message);
                                $(this).show();
                            });
                        } else if (html.ident && html.dataIdentification) {
                            form.parents('.modal').modal('hide');
                            const colsList = Array.from(document.querySelectorAll('td')).concat(Array.from(document.querySelectorAll('div')));
                            for (let col of colsList) {
                                if (col.dataset.ident && col.dataset.ident === html.ident) {
                                    let isBool = true;
                                    for (let identification of html.dataIdentification) {
                                        if (col.dataset[identification.name.toLowerCase()] != identification.value) isBool = false;
                                        // console.log(identification.name.toLowerCase(), identification.value);
                                    }
                                    if (isBool) {
                                        col.innerHTML = html.value || '';
                                        if (html.dataValue) {
                                            for (let value of html.dataValue) {
                                                col.dataset[value.name.toLowerCase()] = value.value;
                                            }
                                        }
                                        if (html.removeClasses) {
                                            for (let className of html.removeClasses) {
                                                col.classList.remove(className);
                                            }
                                        }
                                        if (html.addClasses) {
                                            for (let className of html.addClasses) {
                                                col.classList.add(className);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if (html.idIdentification) {
                            form.parents('.modal').modal('hide');
                            for (let identification of html.idIdentification) {
                                if (document.getElementById(identification.name)) {
                                    document.getElementById(identification.name).innerText = identification.value;
                                    if (document.getElementById(identification.name).dataset.value) {
                                        document.getElementById(identification.name).dataset.value = identification.value;
                                    }
                                }
                            }
                        }
                        // Helpers.run('notify', {
                        //     type: 'success',
                        //     icon: 'fa fa-check mr-1',
                        //     message: html.message
                        // });
                    }
                } else {
                    form.parents('.modal').find('.alert').each(function () {
                        $(this).addClass('d-block');
                        $(this).removeClass('alert-success');
                        $(this).addClass('alert-danger');
                        $(this).find('p').html(html.message);
                        $(this).show();
                    });
                    // Helpers.run('notify', {type: 'danger', icon: 'fa fa-times mr-1', message: html.message});
                }
            }
        }
    });
}