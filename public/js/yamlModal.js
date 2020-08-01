//Operation on modal => get the editorganization content
$(document).on('click', '#editOrganization', function(e) {
    e.preventDefault();
    //On récupère l'url depuis la propriété "Data-target" de la balise html a
    url = $(this).attr('data-target');
    //on fait un appel ajax vers l'action symfony qui nous renvoie la vue
    $.get(url, function (data) {
        //on injecte le html dans la modale
        $('#modalBodyEdit').html(data);
        //on ouvre la modale
        $('#editOrga').modal('show');
    });
});

//Operation on modal => get the adduser content
$(document).on('click', '#usersOrganization', function(e) {
    e.preventDefault();
    //On récupère l'url depuis la propriété "Data-target" de la balise html a
    url = $(this).data('target');
    urlList = $(this).data('listusers');

    //on fait un appel ajax vers l'action symfony qui nous renvoie la vue
    $.get(url, function (data) {
        //on injecte le html dans la modale
        $('#formAddUser').html(data);
        //on ouvre la modale
        $('#usersOrga').modal('show');
    });
    $.get(urlList, function (data) {
        //on injecte le html dans la modale
        $('#oneUser').html(data);
        //on ouvre la modale
    });
});

//Operation on modal => get the edituser content
$(document).on('click', '#editUserModal', function(e) {
    e.preventDefault();
    //On récupère l'url depuis la propriété "Data-target" de la balise html a
    url = $(this).attr('data-target');
    //on fait un appel ajax vers l'action symfony qui nous renvoie la vue
    $.get(url, function (data) {
        //on injecte le html dans la modale
        $('#editUserForm').html(data);
        // On ferme la modale de la liste des users
        $('#usersOrga').modal('hide');
        //on ouvre la modale du formulaire de modification des users
        $('#editUsersOrganization').modal('show');
    });
});