$(() => {
    if ($('#voertuigIndex').length) INDEX.init();
});

const INDEX = {
    zoekTerm: '',
    labels: null,
    geduld: null,
    fout: null,
    voertuigID: null,
    regexDescription: /^[\w\-\s\d]+$/,
   
    init: () => {
        INDEX.lijst(0);

        // paginanavigatie
        $('#voertuigIndex').on('click', '#paginering a', function(evt) {
            evt.preventDefault();
            INDEX.lijst($(this).data('pagina'));
        });

		// voertuig nieuw
        $('#nieuwKnop').on('click', () => { INDEX.bewerk(0, 'nieuw') });

        // voertuig bewerk
        $('#voertuigIndex').on('click', '.voertuigBewerk', function(evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('voertuigID'), 'bewerk');
        });

        // voertuig verwijder
        $('#voertuigIndex').on('click', '.voertuigVerwijder', function(evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('voertuigID'), 'verwijder');
        });        

        // tankbeurten
        $('#voertuigIndex').on('click', '.tankbeurten', function(evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('voertuigID'), 'tankbeurten');
        });        

        // knoppen dialoogvenster
        $('body').on('click', '#voertuigBewerkBewaar', INDEX.bewerkBewaar);
        $('body').on('click', '#voertuigBewerkAnnuleer', () => {MODAAL.verberg(); INDEX.voertuigID=null;});
     

    },
    
    lijst: (pagina) => {
        let frmDta = new FormData();
            frmDta.append('pagina', pagina);
            frmDta.append('zoek', INDEX.zoekTerm);

        AJX.verstuur(
            '/jxVoertuigenLijst',
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
        INDEX.labels = jqDta.labels;
        // succes true -> toon voertuigen
        let lijst = $('#lijst');

        if (jqDta.aantal === 0) {
            $(lijst).append(
                $('<div>').addClass('alert alert-info').append($('<h5>').addClass('alert-heading').text(jqDta.infoTitel)).append($('<hr>')).append($('<p>').text(jqDta.infoBoodschap))
            );

        }
        else {
            let lijn = $('<ul>').addClass('mb-3');
            let verbruik=0;
            let ppl = 0;
            

            jqDta.voertuigen.forEach(voertuig => {
                if  (voertuig.kmstand=='-' && voertuig.volume=='-') {
                    verbruik = '-';
                }
                else {    
                    verbruik = (voertuig.volume/voertuig.kmstand*100).toFixed(2);
                }     
                
                if  (voertuig.bedrag=='-' && voertuig.volume=='-') {
                    ppl = '-';
                }
                else {    
                    ppl = (voertuig.bedrag/voertuig.volume).toFixed(2);
                }     
                

                let lijnLinks = $('<div>').addClass('lijnLinks float-start col-10')
                                .append($('<div>').addClass('float-start col-2 desc').text(`${voertuig.description}`))
                                .append($('<div>').addClass('float-start col-2 voertuigTabelCel').text(`${voertuig.kmstand}`+' km'))
                                .append($('<div>').addClass('float-start col-2 voertuigTabelCel').text(`${voertuig.bedrag}`+' €'))
                                .append($('<div>').addClass('float-start col-2 voertuigTabelCel').text(verbruik + ' l/100 km'))
                                .append($('<div>').addClass('float-start col-2 voertuigTabelCel').text(`${voertuig.datum}`))
                                .append($('<div>').addClass('float-start col-2 voertuigTabelCel').text(ppl + ' €/l'));
                let lijnRechts = $('<div>').addClass('lijnRechts float-end');

                // toont toegekende rechten


                let knoppen = $('<div>').addClass('btn-group');
                let editdelDisabled = false; 
                if(voertuig.obsolete == 1) {
                    editdelDisabled = true;
                } 
                else if (jqDta.isAdmin < 4) {
                    editdelDisabled = true;
                }
                console.log (voertuig.description,'voertuig is oblsolete',voertuig.obsolete,'hide buttons',editdelDisabled);

                $(knoppen).append($('<button>').data('voertuigID', voertuig.id).attr('id', 'bewerk-'+voertuig.id).prop('type', 'button').prop('disabled',editdelDisabled).addClass('btn btn-primary voertuigBewerk').append($('<i>').addClass('bi bi-pencil')));
                $(knoppen).append($('<button>').data('voertuigID', voertuig.id).attr('id', 'verwijder-'+voertuig.id).prop('type', 'button').prop('disabled',editdelDisabled).addClass('btn btn-warning voertuigVerwijder').append($('<i>').addClass('bi bi-trash3')));
                $('#nieuwKnop').attr('disabled',jqDta.isAdmin != 4);
                $(knoppen).append($('<button>').attr('id','voertuig-'+voertuig.id).data('voertuigID', voertuig.id).prop('type', 'button').attr('title',INDEX.labels.voertuigenTooltipTankbeurten+ ' ' + voertuig.description).addClass('btn btn-primary tankbeurten').append($('<i>').addClass('bi bi-fuel-pump')));
                $(lijnRechts).append(knoppen);
               
                $(lijn).append($('<div>').addClass('card mb-1').append($('<div>').addClass('card-header').append($('<div>').addClass('row').data('voertuigID', voertuig.id)
                    .append($('<div>').append(lijnLinks)
                    .append($('<div>').append(lijnRechts))))));
                $(lijst).append(lijn);
                if (voertuig.obsolete) $('bewerk-'+voertuig.id).prop('disabled', true); 
                if (voertuig.obsolete) $('verwijder-'+voertuig.id).prop('disabled', true); 
                $('#voertuig-'+voertuig.id).tooltip();
            });
            
            $(lijst).append(PAGINERING.pagineer(jqDta.pagina, jqDta.knoppen, jqDta.aantalPaginas));
            }
        },

            
        lijstFout: (jqXHR, jqMsg) => {        alert ('lijstFout');
    },    
         

    // VOERTUIGBEHEER
    bewerk: (voertuigID, mode) => {
        console.log (voertuigID, mode);

        //tankbeurten tonen voor het voertuig
        if (mode=='tankbeurten') {
            window.location='/tankbeurt/' + voertuigID + '/0';
             
        }      
        let frmDta = new FormData();
            frmDta.append('id', voertuigID);
            frmDta.append('mode', mode);
            
        AJX.verstuur(
                '/jxVoertuigenGet',
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
                    case ('bewerk'):
                        MODAAL.kop(INDEX.labels.voertuigBewerkTitelBewerk);
                        break;
                    case ('nieuw'):
                        MODAAL.kop(INDEX.labels.voertuigBewerkTitelNieuw);
                        break;
                    case ('verwijder'):
                        MODAAL.kop(INDEX.labels.voertuigBewerkTitelVerwijder);
                        disabled = 'disabled';
                        break;
    
                } 
     
                // inhoud dialoogvenster
                let inhoud = `
                    <div id="voertuigBewerkBoodschap"></div>
                    <input type="hidden" id="voertuigBewerkID" value="${jqDta.voertuig.id}">
                
                    <input type="hidden" id="voertuigBewerkMode" value="${jqDta.mode}">
                    <div class="form-group mb-3">
                        <label for="voertuigBewerkDescription" class="form-label">${INDEX.labels.voertuigBewerkDescription}</label>
                        <input type="text" class="form-control" id="voertuigBewerkDescription" value="${jqDta.voertuig.description}" ${disabled}>
                    </div>
                    
                `;
    
  
                MODAAL.inhoud(inhoud);
    
                let voet = '';
                if (jqDta.mode ==='verwijder') voet += MODAAL.knop('voertuigBewerkBewaar', 'warning', 'trash3', INDEX.labels.voertuigBewerkVerwijder);
    
                else
                   voet += MODAAL.knop('voertuigBewerkBewaar', 'primary', 'check-square', INDEX.labels.voertuigBewerkBewaar);
                voet += MODAAL.knop('voertuigBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.voertuigBewerkAnnuleer);
    
                MODAAL.voet(voet);
                MODAAL.toon();
            }
        },
    
        bewerkFout: (jqXHR, jqMsg) => {
            MODAAL.verberg();
        },

        bewerkBewaar: () => {
            let mode = $('#voertuigBewerkMode').val();
            let voertuigID = $('#voertuigBewerkID').val();
            let description = $('#voertuigBewerkDescription').val().trim();
            
            $('#voertuigBewerkBoodschap').empty();
    
            let boodschap = '';
            boodschap += INDEX.regexDescription.test(String(description)) ? `` : `<li>${INDEX.labels.voertuigBewerkDescription}</li>`;
           
            if (boodschap.length != 0){
                $('#voertuigBewerkBoodschap').html(`
                    <div class="alert alert-warning">
                        ${INDEX.labels.voertuigBewerkBoodschap}
                        <ul>
                            ${boodschap}
                        </ul>
                    </div>
                `);
            }
            else {

                let frmDta = new FormData();
                    frmDta.append('mode', mode);
                    frmDta.append('voertuigID', voertuigID);
                    frmDta.append('description', description);
    
                AJX.verstuur(
                    '/jxVoertuigBewaar',
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

                INDEX.lijst(pagina);
 
            }
            else {
                $('#voertuigBewerkBoodschap').html(`
                    <div class="alert alert-warning">
                        ${jqDta.boodschap}
                    </div>
                `);
                MODAAL.toon();
            }
        },
    
        bewerkBewaarFout: (jqXHR, jqMsg) => {
            MODAAL.kop(jqDta.voertuig.id === 0 ? INDEX.labels.voertuigBewerkTitelNieuw : INDEX.labels.voertuigBewerkTitelBewerk);
            MODAAL.inhoud(`<p>${INDEX.fout}</p>`);
            MODAAL.voet(
                MODAAL.knop('voertuigBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.voertuigBewerkAnnuleer)
            );
            MODAAL.toon();
        },



    
    

}