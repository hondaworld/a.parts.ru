import Helpers from "./helpers";

jQuery(() => {
    window.copyNumber = copyNumber;
    window.copyObject = copyObject;
});

function copyObject(el) {
    selection(el);
    document.execCommand('copy');
    unSelection(el);
}

function copyNumber(el) {
    $(el).parents('td').find('span').each(function () {
        selection(this);
        document.execCommand('copy');
        unSelection(this);
        Helpers.run('notify', {
            type: 'success',
            message: 'Номер ' + this.innerText + ' скопирован'
        });
    })
}

function selection(e) {
    if (window.getSelection) {
        const s = window.getSelection();
        const r = document.createRange();
        r.selectNodeContents(e);
        s.removeAllRanges();
        s.addRange(r);
    } else if (document.getSelection) {
        const s = document.getSelection();
        const r = document.createRange();
        r.selectNodeContents(e);
        s.removeAllRanges();
        s.addRange(r);
    } else if (document.selection) {
        const r = document.body.createTextRange();
        r.moveToElementText(e);
        r.select();
    }
}

function unSelection(e) {
    if (window.getSelection) {
        const s = window.getSelection();
        s.removeAllRanges();
    } else if (document.getSelection) {
        const s = document.getSelection();
        s.removeAllRanges();
    } else if (document.selection) {
        const r = document.body.createTextRange();
        r.moveToElementText(e);
    }
}