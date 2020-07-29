$(document).ready(function() {
    var getParams = function (url) {
        var params = {};
        var parser = document.createElement('a');
        parser.href = url;
        var query = parser.search.substring(1);
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            params[pair[0]] = decodeURIComponent(pair[1]);
        }
        return params;
    };

    var params = getParams(window.location.href);

    if(params['usersOrga'] !== null) {
        $('#usersOrga' + params['usersOrga']).modal('show');
    }
});

/* A few animation on users form */
$(document).on('click', '#addUserFormDisplayed', function(){ 
    $('.list-usersorga').css('display', 'none');
    $('.form-adduser').css('display', 'block');
});

$(document).on('click', '#cancelAddUser', function(){ 
    $('.list-usersorga').css('display', 'block');
    $('.form-adduser').css('display', 'none');
});

/*-- Call Ajax Users --*/

/* Add User */

$(document).on('submit', '.add-user-form-fields', function(e) {
    e.preventDefault();
    var form = $('#addUserFormFields' + $(this).data('organame'));
    var orgaName = form.data('organame');
    $.ajax({
        url: '/add-user-orga/' + orgaName,
        type : 'POST',
        data: form.serialize(),
        success : function(data) {
            $('.list-usersorga').css('display', 'block');
            $('.form-adduser').css('display', 'none');
            window.location = '/?modal-user=open&usersOrga='+orgaName;
        },
        error : function(resultat, statut, erreur){
            $('.errors-ajax-invalid-feedback').html(
                '<i class="fas fa-exclamation-circle"></i>' + 
                '<strong>An error occured</strong>'
            );
        },
    });
});

/* edit User */

$(document).on('click','.edit-user-form-button',function(e){
    e.preventDefault();
    var orgaName = $(this).attr("data-organame");
    var userName = $(this).attr("data-username");
    console.log(orgaName, userName);
    var form = $('#editUserFormFields'+orgaName+userName);
    var button = $('#editUserFormButton'+orgaName+userName);
    console.log(form.serialize());
    $.ajax({
        url : '/edit-user-orga/' + orgaName + '/' + userName,
        type : 'POST',
        data: form.serialize(),
        success : function(data) {
            button.attr("data-organame", orgaName);
            button.attr("data-username", data.Organizations['name']);
            form.attr("id", 'editUserFormFields'+orgaName+data.Organizations['name']);
            button.attr("id", 'editUserFormButton'+orgaName+data.Organizations['name']);

            console.log(orgaName, data.Organizations['name']);

            $('#nameUserTitle'+orgaName+userName).attr("id", 'nameUserTitle'+orgaName+data.Organizations['name']);
            $('#nameUserTitle'+orgaName+data.Organizations['name']).html(data.Organizations['name']);
        },
        error : function(resultat, statut, erreur){
            $('.errors-ajax-invalid-feedback').html(
                '<i class="fas fa-exclamation-circle"></i>' + 
                '<strong>An error occured</strong>'
            );
        }
    });
});

/* Delete User */

$(document).on('click', '.delete-user', function(e) {
    e.preventDefault();
    var orgaName = $(this).data('organame');
    var userName = $(this).data('username');

    console.log($(this));

    $.ajax({
        url: '/delete-user-orga/' + orgaName + '/' + userName,
        type : 'POST',
        success : function(data) {
            console.log(data.success);
            $('#oneUser'+ orgaName + userName).slideUp();
        },
        error : function(resultat, statut, erreur){
            $('.errors-ajax').css('display', 'block');

            $('.errors-ajax-invalid-feedback').html(
                '<i class="fas fa-exclamation-circle"></i>' + 
                '<strong>An error occured</strong>'
            );
        },
    });
});