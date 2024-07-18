const PAGINERING = {
    pagineer: (pagina, knoppen, aantalPaginas) => {
       
        // geen of één pagina -> geen paginanavigatie
        if (aantalPaginas < 2) return '';

        // 2 of meer pagina's
        let ul = $('<ul>').addClass('pagination mt-5');

        let midden = Math.floor(knoppen / 2);

        // knop vorige
        if (pagina > 0)
            $(ul).append($('<li>').addClass('page-item').append($('<a>').addClass('page-link').prop('href', '#').data('pagina', pagina -1).append($('<i>').addClass('bi bi-chevron-double-left'))));

        // knop ...
        if (pagina > midden)    
            $(ul).append($('<li>').addClass('page-item').append($('<a>').addClass('page-link').prop('href', '#').text('...').prop('disabled', true)));

        // knoppen pagina's
        let start = 0;
        if (pagina <= midden) 
            start = 0;
        else if (pagina >= aantalPaginas - midden)    
            start = aantalPaginas - knoppen + 1;
        else 
            start = pagina - midden;    

        let stop = start + knoppen - 1;
        if (stop >= aantalPaginas)
            stop  = aantalPaginas - 1;

        for (let paginaNo = start; paginaNo <= stop; paginaNo++) {
            let actief = pagina == paginaNo ? 'active' : '';
            $(ul).append($('<li>').addClass('page-item').append($('<a>').addClass('page-link').prop('href', '#').data('pagina', paginaNo).text(paginaNo+1).addClass(actief)));
        }

        // knop ...
        if (pagina < aantalPaginas - midden -1)    
        $(ul).append($('<li>').addClass('page-item').append($('<a>').addClass('page-link').prop('href', '#').text('...').prop('disabled', true)));


        //knop volgende
        if (pagina < aantalPaginas -1)
            $(ul).append($('<li>').addClass('page-item').append($('<a>').addClass('page-link').prop('href', '#').data('pagina', pagina +1).append($('<i>').addClass('bi bi-chevron-double-right'))));


        return $('<div>').addClass('justify-content-center').prop('id','paginering').addClass('btn-group').append(ul);
    }
}    