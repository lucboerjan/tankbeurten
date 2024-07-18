$(() => {
    if ($('#gebruikersIndex').length) INDEX.init();
});

const INDEX = {
    zoekTerm: '',
    labels: null,
    geduld: null,
    fout: null,
    gebruikersID: null,
    reNaam: /^[\w\-\s\d]+$/,
    reVNaam: /^[\w\-\s]+$/,
    reEmail: /^[\w\.]+@[\w\-\.]+[\w]{2,6}$/,    

    init: () => {
    
        INDEX.lijst(0);

        // paginanavigatie
        $('#gebruikersIndex').on('click', '#paginering a', function(evt) {
            evt.preventDefault();
            INDEX.lijst($(this).data('pagina'));
        });              


        // GEBRUIKERSBEHEER
        // gebruiker nieuw
        $('#nieuwKnop').on('click', () => { INDEX.bewerk(0, 'nieuw') });

        // gebruikers bewerk
        $('#gebruikersIndex').on('click', '.bewerk', function(evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('gebruikersID'), 'bewerk');
        });       


        // gebruikers verwijder
        $('#gebruikersIndex').on('click', '.verwijder', function(evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('gebruikersID'), 'verwijder');
        });        


        // gebruikers reset mail
        $('#gebruikersIndex').on('click', '.reset', function(evt) {
            evt.stopPropagation();
            INDEX.reset($(this).data('gebruikersID'));
        });

        // knoppen dialoogvenster
        $('body').on('click', '#gebruikerBewerkBewaar', INDEX.bewerkBewaar);
        $('body').on('click', '#gebruikerBewerkAnnuleer, #gebruikerInfoBtnAnnuleer', () => {MODAAL.verberg(); INDEX.gebruikersID=null;});

        $('body').on('click', '#gebruikerInfoBtnReset', INDEX.resetMail);        
    },

    lijst: (pagina) => {
        let frmDta = new FormData();
            frmDta.append('pagina', pagina);
            frmDta.append('zoek', INDEX.zoekTerm);

        AJX.verstuur(
            '/jxGebruikersLijst',
            'post',
            frmDta,
            INDEX.lijstSucces,
            INDEX.lijstFout
        )
    },

    lijstSucces: (jqDta) => {
        // wis inhoud container #lijst
        $('#lijst').empty();

        // succes false -> stop
        if (!jqDta.succes) return;

        // succes true -> toon gebruikers
        let lijst = $('#lijst');
        if (jqDta.aantal === 0) {
            $(lijst).append(
                $('<div>').addClass('alert alert-info')
                    .append(
                        $('<h5>').addClass('alert-heading').text(jqDta.infoTitel)
                    )
                    .append(
                        $('<hr>')
                    )
                    .append(
                        $('<p>').text(jqDta.infoBoodschap)
                    )
            );
        }
        else {
            let lijn = $('<ul>').addClass('mb-3');
            jqDta.gebruikers.forEach(gebruiker => {
                //let lijnLinks = $('<div>').addClass('lijnLinks float-start').text(`${gebruiker.fullname} | ${gebruiker.email}`);
                let lijnLinks = $('<div>').addClass('lijnLinks float-start col-8')
                                .append($('<div>').addClass('float-start col-1').text(`${gebruiker.name}`))
                                .append($('<div>').addClass('float-start col-3').text(`${gebruiker.fullname}`))
                                .append($('<div>').addClass('float-start col-8').text(`${gebruiker.email}`));
                let lijnRechts = $('<div>').addClass('lijnRechts float-end')
                let gebruikersLevel = parseInt(gebruiker.level);

                // toont toegekende rechten, enkel voor administrator
                if (jqDta.isAdmin) {
                    let rechten = $('<div>').addClass('btn-group me-1');
                    // tankbeurtbeheer (level 2)
                    $(rechten).append($('<button>').prop('type', 'button').prop('disabled', !(gebruikersLevel & 2)).addClass('btn btn-secondary tankbeurt').append($('<i>').addClass('bi bi-fuel-pump')));
                    // volledig beheer (level 4)
                        $(rechten).append($('<button>').prop('type', 'button').prop('disabled', !(gebruikersLevel & 4)).addClass('btn btn-secondary user').append($('<i>').addClass('bi bi-gear')));
                    // administrator (level 8)
                    //$(rechten).append($('<button>').prop('type', 'button').prop('disabled', !(gebruikersLevel & 8)).addClass('btn btn-secondary admin').append($('<i>').addClass('bi bi-person-gear')));

                    $(lijnRechts).append(rechten);
                }

                // knoppen resetmail | bewerk | verwijder (enkel voor administrator)
                let knoppen = $('<div>').addClass('btn-group');
                $(knoppen).append($('<button>').data('gebruikersID', gebruiker.id).prop('type', 'button').addClass('btn btn-primary reset').append($('<i>').addClass('bi bi-envelope')));
                $(knoppen).append($('<button>').data('gebruikersID', gebruiker.id).prop('type', 'button').addClass('btn btn-primary bewerk').append($('<i>').addClass('bi bi-pencil')));
                if (jqDta.isAdmin) {
                    let visibility = Boolean(gebruikersLevel & 8) ? 'hidden' : 'none';
                    $(knoppen).append($('<button>').data('gebruikersID', gebruiker.id).prop('type', 'button').css('visibility', visibility).prop('disabled', Boolean(gebruikersLevel & 8)).addClass('btn btn-warning verwijder').append($('<i>').addClass('bi bi-trash3')));
                }
                $(lijnRechts).append(knoppen);

                //let lijn = $('<div>').addClass('mt-3 float-none lijn')
                $(lijn).append($('<div>').addClass('card mb-1').append($('<div>').addClass('card-header').append($('<div>').addClass('row')
                    .append($('<div>').append(lijnLinks)
                    .append($('<div>').append(lijnRechts))))));
                $(lijst).append(lijn);
            });

            $(lijst).append(PAGINERING.pagineer(jqDta.pagina, jqDta.knoppen, jqDta.aantalPaginas));
        }
    },

    lijstFout: (jqXHR, jqMsg) => {},    


    // GEBRUIKERSBEHEER
    bewerk: (gebruikersID, mode) => {
        let frmDta = new FormData();
            frmDta.append('gebruikersID', gebruikersID);
            frmDta.append('mode', mode);

        AJX.verstuur(
            '/jxGebruikersGet',
            'post',
            frmDta,
            INDEX.bewerkSucces,
            INDEX.bewerkFout
        )
    },    

    bewerkSucces: (jqDta) => {
        INDEX.labels = jqDta.labels;
        INDEX.geduld = jqDta.geduld;

        if (jqDta.succes) {
            let disabled = '';

            // titel dialoogvenster
            switch(jqDta.mode) {
                case 'bewerk':
                    MODAAL.kop(INDEX.labels.gebruikerBewerkTitelBewerk);
                    break;
                case 'nieuw':
                    MODAAL.kop(INDEX.labels.gebruikerBewerkTitelNieuw);
                    break;
                case 'verwijder':
                    MODAAL.kop(INDEX.labels.gebruikerBewerkTitelVerwijder);
                    disabled = 'disabled';
                    break;
            }

            // inhoud dialoogvenster
            let inhoud = `
                <div id="gebruikerBewerkBoodschap"></div>
                <input type="hidden" id="gebruikerBewerkID" value="${jqDta.gebruiker.id}">
                <input type="hidden" id="gebruikerBewerkLevel" value="${jqDta.gebruiker.level}">
                <input type="hidden" id="gebruikerBewerkMode" value="${jqDta.mode}">
                <div class="form-group mb-3">
                    <label for="gebruikerBewerkNaam" class="form-label">${INDEX.labels.gebruikerBewerkNaam}</label>
                    <input type="text" class="form-control" id="gebruikerBewerkNaam" value="${jqDta.gebruiker.name}" ${disabled}>
                </div>
                <div class="form-group mb-3">
                    <label for="gebruikerBewerkVNaam" class="form-label">${INDEX.labels.gebruikerBewerkVNaam}</label>
                    <input type="text" class="form-control" id="gebruikerBewerkVNaam" value="${jqDta.gebruiker.fullname}" ${disabled}>
                </div>
                <div class="form-group mb-3">
                    <label for="gebruikerBewerkEmail" class="form-label">${INDEX.labels.gebruikerBewerkEmail}</label>
                    <input type="text" class="form-control" id="gebruikerBewerkEmail" value="${jqDta.gebruiker.email}" ${disabled}>
                </div>
            `;

            if (jqDta.isAdmin) {
                inhoud += `
                    <h4 class="mb-2">${INDEX.labels.gebruikerBewerkRechten}</h4>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="gebruikerBewerkTankbeurt" ${jqDta.gebruiker.level & 2 ? "checked": ""} ${disabled}>
                        <label class="form-check-label" for="gebruikerBewerkTankbeurt">
                            <i class="bi bi-fuel-pump"></i>&nbsp;
                            ${INDEX.labels.gebruikerBewerkTankbeurt}
                        </label>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="gebruikerBewerkGebruikers" ${jqDta.gebruiker.level & 4 ? "checked": ""} ${disabled}>
                        <label class="form-check-label" for="gebruikerBewerkGebruikers">
                            <i class="bi bi-person-vcard"></i>&nbsp;
                            ${INDEX.labels.gebruikerBewerkGebruikers}
                        </label>
                    </div>

                `;
            }

            MODAAL.inhoud(inhoud);

            let voet = '';
            if (jqDta.mode === 'verwijder')
                voet += MODAAL.knop('gebruikerBewerkBewaar', 'warning', 'trash3', INDEX.labels.gebruikerBewerkVerwijder);
            else
                voet += MODAAL.knop('gebruikerBewerkBewaar', 'primary', 'check-square', INDEX.labels.gebruikerBewerkBewaar);
            voet += MODAAL.knop('gebruikerBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.gebruikerBewerkAnnuleer);
            MODAAL.voet(voet);

            MODAAL.toon();
        }
    },



    bewerkFout: (jqXHR, jqMsg) => {
        MODAAL.verberg();
    },

    bewerkBewaar: () => {
        let mode = $('#gebruikerBewerkMode').val();
        let gebruikersID = $('#gebruikerBewerkID').val();
        let gebruikerNaam = $('#gebruikerBewerkNaam').val().trim();
        let gebruikerVNaam = $('#gebruikerBewerkVNaam').val().trim();
        let gebruikerEmail = $('#gebruikerBewerkEmail').val().trim().toLowerCase();

        $('#gebruikerBewerkBoodschap').empty();

        let boodschap = '';
        boodschap += INDEX.reNaam.test(String(gebruikerNaam)) ? `` : `<li>${INDEX.labels.gebruikerBewerkNaam}</li>`;
        boodschap += INDEX.reVNaam.test(String(gebruikerVNaam)) ? `` : `<li>${INDEX.labels.gebruikerBewerkVNaam}</li>`;
        boodschap += INDEX.reEmail.test(String(gebruikerEmail)) ? `` : `<li>${INDEX.labels.gebruikerBewerkEmail}</li>`;

        if (boodschap.length != 0){
            $('#gebruikerBewerkBoodschap').html(`
                <div class="alert alert-warning">
                    ${INDEX.labels.gebruikerBewerkBoodschap}
                    <ul>
                        ${boodschap}
                    </ul>
                </div>
            `);
        }
        else {
            let level = 1;

            if ($('#gebruikerBewerkGebruikers').length) {
                level += $('#gebruikerBewerkTankbeurt').is(':checked') ? 2 : 0;
                level += $('#gebruikerBewerkGebruikers').is(':checked') ? 4 : 0;
            }
            else {
                level = $('#gebruikerBewerkLevel').val();
            }

            let frmDta = new FormData();
                frmDta.append('mode', mode);
                frmDta.append('gebruikersID', gebruikersID);
                frmDta.append('naam', gebruikerNaam);
                frmDta.append('vNaam', gebruikerVNaam);
                frmDta.append('email', gebruikerEmail);
                frmDta.append('level', level);

            AJX.verstuur(
                '/jxGebruikersBewaar',
                'post',
                frmDta,
                INDEX.bewerkBewaarSucces,
                INDEX.bewerkBewaarFout
            )
        }
    },

    bewerkBewaarSucces: (jqDta) => {
        if (jqDta.succes) {
            MODAAL.verberg();

            let pagina = 0;
            if ($('.pagination').length > 0) {
                $('.pagination a').each((ndx, el) => {
                    if ($(el).hasClass('active'))
                        pagina = $(el).data('pagina');
                })
            }

            // stuur wachtwoordmail naar nieuwe gebruiker
            if (jqDta.mode === 'nieuw') {
                INDEX.gebruikersID = jqDta.gebruikersID;
                INDEX.resetMail();
            }

            INDEX.lijst(pagina);
        }
        else {
            $('#gebruikerBewerkBoodschap').html(`
                <div class="alert alert-warning">
                    ${jqDta.boodschap}
                </div>
            `);
            MODAAL.toon();
        }        
    },

    
    bewerkBewaarFout: (jqXHR, jqMsg) => {
        MODAAL.kop(jqDta.gebruiker.id === 0 ? INDEX.labels.gebruikerBewerkTitelNieuw : INDEX.labels.gebruikerBewerkTitelBewerk);
        MODAAL.inhoud(`<p>${INDEX.fout}</p>`);
        MODAAL.voet(
            MODAAL.knop('gebruikerBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.gebruikerBewerkAnnuleer)
        );
        MODAAL.toon();
    },


    reset: (gebruikersID) => {
        let frmDta = new FormData();
            frmDta.append('gebruikersID', gebruikersID);

        AJX.verstuur(
            '/jxGebruikersResetInfo',
            'post',
            frmDta,
            INDEX.resetInfo,
            INDEX.resetFout
        )
    },

    resetInfo: (jqDta) => {
        if(jqDta.succes) {
            INDEX.gebruikersID = jqDta.gebruiker.id;
            MODAAL.kop(jqDta.labels.gebruikersInfoTitel);
            MODAAL.inhoud(`<p>${jqDta.labels.gebruikersInfoBericht}
                          <br>
                          <strong>
                            ${jqDta.gebruiker.fullname}<br>
                            ${jqDta.gebruiker.email}
                          </strong></p>`);
            MODAAL.voet(
                MODAAL.knop('gebruikerInfoBtnReset', 'primary', 'envelope', jqDta.labels.gebruikersInfoBtnReset) + 
                MODAAL.knop('gebruikerBewerkAnnuleer', 'secondary', 'x-square', jqDta.labels.gebruikersInfoBtnAnnuleer)
            );
            MODAAL.toon();
        }
    },

    resetFout: (jqXHR, jqMsg) => {
        MODAAL.verberg();
    },

 
    resetMail: () => {
        MODAAL.verberg();

        let frmDta = new FormData();
            frmDta.append('gebruikersID', INDEX.gebruikersID);

        INDEX.lidID = null;

        AJX.verstuur(
            '/jxGebruikersResetMail',
            'post',
            frmDta,
            (jqDta) => {},
            (jqXHR, jqMsg) => {}
        );
        setTimeout(
            function(){
                window.location = "/" 
            },
        3000);
    }

    
}    