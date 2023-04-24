if (document.getElementById('orders-list')) {
    const urls = [];

    $('#orders-list a').click(function(e) {
        e.preventDefault();
        const form = $('#ordersListForm');
        $('#form_param').val($(this).data('id'));
        $('#form_user').val('');
        $('#form_orderID').val('');
        form.submit();
    });

    $('#orders-list a').each(function () {
        const url = $(this).data('url');
        if (urls.indexOf(url) === -1) {
            urls.push(url);
            $.ajax({
                type: "GET",
                url: url,
                success: function (html) {
                    // console.log(html)
                    if (html) {
                        $('#orders-list a').each(function () {
                            if (url === $(this).data('url')) {
                                for (let key in html) {
                                    if (key === $(this).data('id')) {
                                        $(this).find('span').text(html[key]);
                                    }
                                }
                            }
                        });
                    }
                }
            });
        }
    });
}