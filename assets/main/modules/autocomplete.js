jQuery(() => {
    $('.js-autocomplete-simple').autocomplete({
        source: function (request, response) {
            if (this.element[0].dataset.url) {
                $.ajax({
                    url: this.element[0].dataset.url,
                    dataType: "json",
                    data: {
                        maxRows: 10,
                        number: request.term
                    },
                    success: function (html) {
                        response($.map(html, function (item) {
                            return {
                                label: item,
                                value: item,
                            }
                        }));
                    },
                });
            }
        },
        minChars: 1,
        max: 10,
        scroll: false,
        selectFirst: true,
        minLength: 1,
    });

    $('.js-autocomplete').autocomplete({
        source: function (request, response) {
            if (this.element[0].dataset.url) {
                $.ajax({
                    url: this.element[0].dataset.url,
                    dataType: "json",
                    data: {
                        maxRows: 10,
                        name: request.term
                    },
                    success: function (html) {
                        response($.map(html, function (item) {
                            return {
                                label: item.name,
                                value: item.name,
                                id: item.id
                            }
                        }));
                    },
                });
            }
        },
        minChars: 1,
        max: 10,
        scroll: false,
        selectFirst: true,
        minLength: 1,
        select: function (event, ui) {
            const name_id = event.target.id;
            const id = name_id.replace('_name', '_id');
            $("#" + id).val(ui.item.id);
        },
    });

    $('.js-autocomplete').on("input", function () {
        const name_id = $(this).attr('id');
        const id = name_id.replace('_name', '_id');
        if ($(this).val() === "") $('#' + id).val("");
    });

    $('.js-autocomplete-user').autocomplete({
        source: function (request, response) {
            if (this.element[0].dataset.url) {
                $.ajax({
                    url: this.element[0].dataset.url,
                    dataType: "json",
                    data: {
                        maxRows: 10,
                        name: request.term
                    },
                    success: function (html) {
                        response($.map(html, function (item) {
                            return {
                                label: item.name,
                                value: item.name,
                                id: item.id
                            }
                        }));
                    },
                });
            }
        },
        minChars: 1,
        max: 10,
        scroll: false,
        selectFirst: true,
        minLength: 1,
        select: function (event, ui) {
            const name_id = event.target.id;
            const id = name_id.replace('_name', '_id');
            const contactID = name_id.replace('_name', '_contactID');
            const beznalID = name_id.replace('_name', '_beznalID');
            $("#" + id).val(ui.item.id);

            $.ajax({
                url: '/api/user-contacts-and-beznals',
                dataType: "json",
                data: {
                    userID: ui.item.id
                },
                success: function (html) {
                    // console.log(html)
                    $("#" + contactID).html('<option value=""></option>');
                    html.contacts.forEach(contact => {
                        $("#" + contactID).append('<option value="' + contact.id + '"' + (+contact.id === +html.contactID ? ' selected' : '') + '>' + contact.name + '</option>');
                    });

                    $("#" + beznalID).html('<option value=""></option>');
                    html.beznals.forEach(beznal => {
                        $("#" + beznalID).append('<option value="' + beznal.id + '"' + (+beznal.id === +html.beznalID ? ' selected' : '') + '>' + beznal.name + '</option>');
                    });

                },
            });
        },
    });

    $('.js-autocomplete-user').on("input", function () {
        const name_id = $(this).attr('id');
        const id = name_id.replace('_name', '_id');
        const contactID = name_id.replace('_name', '_contactID');
        const beznalID = name_id.replace('_name', '_beznalID');
        if ($(this).val() === "") {
            $('#' + id).val("");
            $('#' + contactID).val("");
            $('#' + beznalID).val("");
            $("#" + contactID).html('<option value=""></option>');
            $("#" + beznalID).html('<option value=""></option>');
        }
    });

    $('.js-autocomplete-firm').on("change", function () {
        const name_id = $(this).attr('id');
        const contactID = name_id.replace('_id', '_contactID');
        const beznalID = name_id.replace('_id', '_beznalID');

        if ($(this).val() === "") {
            $('#' + contactID).val("");
            $('#' + beznalID).val("");
            $("#" + contactID).html('<option value=""></option>');
            $("#" + beznalID).html('<option value=""></option>');
        } else {
            $.ajax({
                url: '/api/firm-contacts-and-beznals',
                dataType: "json",
                data: {
                    firmID: $(this).val()
                },
                success: function (html) {
                    // console.log(html)
                    $("#" + contactID).html('<option value=""></option>');
                    html.contacts.forEach(contact => {
                        $("#" + contactID).append('<option value="' + contact.id + '"' + (+contact.id === +html.contactID ? ' selected' : '') + '>' + contact.name + '</option>');
                    });

                    $("#" + beznalID).html('<option value=""></option>');
                    html.beznals.forEach(beznal => {
                        $("#" + beznalID).append('<option value="' + beznal.id + '"' + (+beznal.id === +html.beznalID ? ' selected' : '') + '>' + beznal.name + '</option>');
                    });

                },
            });
        }
    });
});