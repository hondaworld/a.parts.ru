function checkNewOrders() {
    if (document.getElementById('toast-orders')) {
        $.ajax({
            type: "GET",
            url: "/orders/countNewOrdersLast5Minutes",
            success: function (html) {
                if (html !== "") {
                    jQuery('#toast-orders .toast-body').html(html);
                    jQuery('#toast-orders').toast('show');
                }
            }
        });
        setTimeout(checkNewOrders, 300000);
    }
}

function checkNewTickets() {
    if (document.getElementById('right-section-tickets-list')) {
        jQuery('#right-section-tickets-list').html('');
        jQuery('#right-section-tickets-list').parent().addClass('block-mode-loading');
        $.ajax({
            type: "GET",
            url: "/client-tickets/tickets/newTickets",
            success: function (html) {
                if (html !== "") {
                    if (html.count > 0) {
                        $('#right-section-tickets-bell').show();
                    } else {
                        $('#right-section-tickets-bell').hide();
                    }
                    jQuery('#right-section-tickets-list').parent().removeClass('block-mode-loading');
                    jQuery('#right-section-tickets-list').html(html.result.join("\n"));
                }
            }
        });
        setTimeout(checkNewTickets, 300000);
    }
}

$('#button-show-overlay').click(function() {
    $('#overlay-services').addClass('block-mode-loading');
    $.ajax({
        type: "GET",
        url: "/main/sms/balance",
        success: function (html) {
            if (html !== "") {
                $('#overlay-services').removeClass('block-mode-loading');
                $('#overlay-smsru').html(html);
            }
        }
    });
    $('#overlay-orders').addClass('block-mode-loading');
    $.ajax({
        type: "GET",
        url: "/orders/overlayOrders",
        success: function (html) {
            if (html !== "") {
                $('#overlay-orders').removeClass('block-mode-loading');
                $('#overlay-new-orders-user').html(html.newByUser);
                $('#overlay-new-orders-manager').html(html.newByManager);
                $('#overlay-new-orders-cron').html(html.newByCron);
                $('#overlay-new-orders-today').html(html.today);
            }
        }
    });

    $.ajax({
        type: "GET",
        url: "/orders/overlaySales",
        success: function (html) {
            if (html !== "") {
                $('#overlay-today-income').html(html.income_string + ' р.');
                $('#overlay-today-profit').html(html.profit_string + ' р.');

                if (html.msk.income > 0) {
                    $('#overlay-today-msk').show();
                    $('#overlay-today-msk-income').html('Доход - ' + html.msk.income_string + ' р.');
                    $('#overlay-today-msk-profit').html('Прибыль - ' + html.msk.profit_string + ' р.');
                }

                if (html.spb.income > 0) {
                    $('#overlay-today-spb').show();
                    $('#overlay-today-spb-income').html('Доход - ' + html.spb.income_string + ' р.');
                    $('#overlay-today-spb-profit').html('Прибыль - ' + html.spb.profit_string + ' р.');
                }

                if (html.region.income > 0) {
                    $('#overlay-today-region').show();
                    $('#overlay-today-region-income').html('Доход - ' + html.region.income_string + ' р.');
                    $('#overlay-today-region-profit').html('Прибыль - ' + html.region.profit_string + ' р.');
                }
            }
        }
    });
});

checkNewOrders();
checkNewTickets();