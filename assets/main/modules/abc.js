import Helpers from "./helpers";

$(".abc_block > ul > li").click(function () {
    const zapSkladID = $(this).parent().parent().data('sklad');
    const url = $(this).parent().parent().data('url');
    const abc = $(this).html();

    const el = $(this);
    $.ajax({
        type: "GET",
        url: url,
        data: "abc=" + abc + "&zapSkladID=" + zapSkladID,
        success: function (html) {
            if (html.code === 200) {
                el.parent().find('li').each(function () {
                    $(this).removeClass("active")
                });
                el.addClass("active");

                el.parent().parent().find('div > a').data('data', html.data);
                el.parent().parent().find('div > a').text(html.data[0].dateofadded);

                Helpers.run('notify', {
                    type: 'success',
                    icon: 'fa fa-check mr-1',
                    message: html.message
                });
            } else {
                Helpers.run('notify', {type: 'danger', icon: 'fa fa-times mr-1', message: html.message});
            }
        }
    });
});


$(".abc_block > div > a").click(function () {
    $('#modalAlertLabel').text('История изменения ABC');

    const data = $(this).data('data');
    if (data !== '') {
        let html = '<table class="table table-striped">';

        data.forEach(item => {
            html += '<tr><td>' + item.abc + '</td><td>' + item.dateofadded + '</td><td>' + item.manager + '</td></tr>';
        });

        html += '</table>';
        $('#modalAlertText').html(html);
    }
});