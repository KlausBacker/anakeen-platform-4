$(function () {
    findtarget();
});

function inhib(ckbut) {
    ckbut.checked = (!ckbut.checked);

    return true;
}
function computeglobacl(id) {
    // compute final ACL
    var idgroup = nbinput + id * 4;
    var idplus = idgroup + 1;
    var idmoins = idgroup + 2;
    var idglob = idgroup + 3;

    if (document.edit.elements[idmoins].checked) {
        document.edit.elements[idglob].checked = false;
    } else {
        document.edit.elements[idglob].checked = (document.edit.elements[idgroup].checked) ||
            (document.edit.elements[idplus].checked);
    }


}
function globacl(id) {
    var idgroup = nbinput + id * 4;
    var idplus = idgroup + 1;
    var idmoins = idgroup + 2;
    var idglob = idgroup + 3;

    if (document.edit.elements[idglob].checked) {
        document.edit.elements[idmoins].checked = false;
        if (!document.edit.elements[idgroup].checked) {
            document.edit.elements[idplus].checked = true;
        }
    } else {
        document.edit.elements[idplus].checked = false;
        if (document.edit.elements[idgroup].checked) {
            document.edit.elements[idmoins].checked = true;
        }
    }

    return true;
}
function plusacl(id) {
    var idgroup = nbinput + id * 4;
    var idplus = idgroup + 1;
    var idmoins = idgroup + 2;
    var idglob = idgroup + 3;


    if (document.edit.elements[idplus].checked) {
        document.edit.elements[idmoins].checked = false;
    }
    computeglobacl(id);
    return true;
}
function moinsacl(id) {
    var idgroup = nbinput + id * 4;
    var idplus = idgroup + 1;
    var idmoins = idgroup + 2;
    var idglob = idgroup + 3;

    if (document.edit.elements[idmoins].checked)
        document.edit.elements[idplus].checked = false;
    computeglobacl(id);
    return true;
}
function findtarget() {
    if (document.edit) {
        if (window.opener) {
            var n = window.opener.name;
            if (n) document.edit.target = n;
        }
    }
}
