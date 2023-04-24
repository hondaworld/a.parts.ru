$(".ajax-modal-html").click(function (e) {
    ajaxModalHtml($(this));
});

jQuery(() => {
    window.ajaxModalHtml = ajaxModalHtml;
});

function ajaxModalHtml(el) {
    const url = el.data('url');
    const target = el.data('target');
    One.modalSpinnerOn(target);
    // $(target).find('.modal-body').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
    $(target).find('.modal-title').text(el.data('title'));
    const objectNumber = el.data('object-number') || 0;

    let isSelectedItems = true;

    if (!!el.data('check-selected-item')) {
        isSelectedItems = false;
        if ($('.dataTables_wrapper')[objectNumber]) {
            $($('.dataTables_wrapper')[objectNumber]).find('td>div>input[name="check-item[]"]').each(function () {
                if ($(this).prop('checked')) {
                    isSelectedItems = true;
                }
            });
        }
    }

    if (!!isSelectedItems) {
        $.ajax({
            url: url,
            method: 'get',
            success: function (html) {
                if (html) {
                    $(target).find('.modal-body').html('<div class="alert d-none" id="modalFormAlert" role="alert"><p class="mb-0" id="modalFormMessage"></p></div>' + html);
                    One.helpers(['datepicker', 'convert']);
                    One.maskPhoneInit();
                }
            }
        });
    } else {
        $(target).find('.modal-body').html('<div class="text-center text-danger">Не выделено ни одной позиции</div>');
    }
}