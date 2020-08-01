/* A few animation on users form */
$(document).on('click', '#addUserFormDisplayed', function(){ 
    $('#listUserOrga').css('display', 'none');
    $('#formAddUser').css('display', 'block');
});

$(document).on('click', '#cancelAddUser', function(){ 
    $('#listUserOrga').css('display', 'block');
    $('#formAddUser').css('display', 'none');
});

/*-- Call Ajax Users --*/

/* Add User */

$(document).on('click', '#add_user_add', function(e) {
    e.preventDefault();
    var form = $('#add_user_form');
    var url = $(this).attr('data-target');
    $.ajax({
        url: url,
        type : 'POST',
        data: form.serialize(),
        success : function(data) {
            $('#oneUser').html(data);
            // On ferme la modale de Edit User eton ouvre la list des users
            $('#listUserOrga').css('display', 'block');
            $('#formAddUser').css('display', 'none');
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
    var form = $('#edit_user_form');
    var url = $(this).attr('data-target');
    console.log(url);
    var urllist = $(this).attr('data-targetlist');
    console.log(form.serialize());
    $.ajax({
        url : url,
        type : 'POST',
        data: form.serialize(),
        success : function(data) {
            $('#oneUser').html(data);
            // On ferme la modale de Edit User eton ouvre la list des users
            $('#editUsersOrganization').modal('hide');
            $('#usersOrga').modal('show');
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

$(document).on('click', '#deleteUser', function(e) {
    e.preventDefault();
    var url = $(this).attr('data-target');
    $.ajax({
        url: url,
        type : 'POST',
        success : function(data) {
            $('#oneUser').html(data);
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