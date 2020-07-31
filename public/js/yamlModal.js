$(document).on('click', '#editOrganization', function(e) {
    e.preventDefault();
    //On récupère l'url depuis la propriété "Data-target" de la balise html a
    url = $(this).attr('data-target');
    console.log(url);
    //on fait un appel ajax vers l'action symfony qui nous renvoie la vue
    $.get(url, function (data) {
        //on injecte le html dans la modale
        $('#modalBodyEdit').html(data);
        //on ouvre la modale
        $('#editOrga').modal('show');
    });
});