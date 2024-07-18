$(() => {
    if ($('#tankbeurtenIndex').length) INDEX.init();
});

    const INDEX = {
        zoekTerm: '',
        labels: null,
        geduld: null,
        fout: null,
        voertuigID: null,
        regexDatum : /^\d{4}(\-)(((0)[0-9])|((1)[0-2]))(\-)([0-2][0-9]|(3)[0-1])$/,


        init: () => {
            let urlEln = window.location.toLocaleString().split('/');
            let voertuigID = urlEln[4];
            let pagina = urlEln[5];
            INDEX.lijst(voertuigID, pagina);

        // paginanavigatie
        $('#tankbeurtenIndex').on('click', '#paginering a', function(evt) {
            evt.preventDefault();
            INDEX.lijst(voertuigID,$(this).data('pagina'));
        });            

        // tankbeurt nieuw
        $('#nieuwKnop').on('click', () => { INDEX.bewerk(0, 'nieuw') });
            
        // tankbeurt bewerk
        $('#tankbeurtenIndex').on('click', '.bewerk', function(evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('tankbeurtID'), 'bewerk');
        });       

        // tanbeurt verwijder
        $('#tankbeurtenIndex').on('click', '.verwijder', function(evt) {
            evt.stopPropagation();
            INDEX.bewerk($(this).data('tankbeurtID'), 'verwijder');
        });           


        // knoppen dialoogvenster
        $('body').on('click', '#tankbeurtBewerkBewaar', INDEX.bewerkBewaar);
        $('body').on('click', '#tankbeurtBewerkAnnuleer', () => {MODAAL.verberg(); INDEX.tankbeurtID=null;});

        },

        lijst: (voertuigID, pagina) => {
            let frmDta = new FormData();
                frmDta.append('voertuigID',voertuigID);
                frmDta.append('pagina', pagina);
    
            AJX.verstuur(
                '/jxTankbeurtenLijst',
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

        // succes true -> toon tankbeurten
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
            let editdelDisabled = false; 
            if(jqDta.obsolete == 1) {
                editdelDisabled = true;
            } 
            else if (jqDta.isTankbeurtBeheerder < 2) {
                    editdelDisabled = true;
                }
            console.log (jqDta.description,'voertuig is obsolete',jqDta.obsolete,'hide buttons',editdelDisabled);
            let tankbeurten = jqDta.tankbeurten;
            //tankbeurten.reverse();    
            jqDta.tankbeurten.forEach(tankbeurt => {
                let datum = tankbeurt.datum;
                datum = datum.split('-').reverse().join('-');
                let ppl = tankbeurt.bedrag/tankbeurt.volume;
                ppl = (Math.round(ppl * 100) / 100).toFixed(2);
                let afstand = tankbeurt.afstand;
                if ( afstand == null) {
                    afstand = "-";
                }
                else {
                    afstand = afstand * -1;
                }

                let verbruik='-';
                if  (afstand=='-' || tankbeurt.volume=='-') {
                    verbruik = '-';
                }
                else {    
                    verbruik = (tankbeurt.volume/afstand*100).toFixed(2);
                } 
               // toont knoppen volgens de toegekende rechten

                let knoppen = $('<div>').addClass('btn-group');
                    $(knoppen).append($('<button>').data('tankbeurtID', tankbeurt.id).prop('type', 'button').prop('disabled', editdelDisabled).addClass('btn btn-primary bewerk').append($('<i>').addClass('bi bi-pencil')));
                    $(knoppen).append($('<button>').data('tankbeurtID', tankbeurt.id).prop('type', 'button').prop('disabled', editdelDisabled).addClass('btn btn-warning verwijder').append($('<i>').addClass('bi bi-trash3')));

                let lijnLinks = $('<div>').addClass('lijnLinks float-start')
                                .append($('<div>').addClass('float-start col-2 datumCel').text(`${datum}`))
                                .append($('<div>').addClass('float-start col-1 tankbeurtTabelCel').text(`${tankbeurt.kmstand}`+' km'))
                                .append($('<div>').addClass('float-start col-1 tankbeurtTabelCel').text(`${afstand}`+' km'))
                                .append($('<div>').addClass('float-start col-1 tankbeurtTabelCel').text(`${tankbeurt.volume}`+' l'))
                                .append($('<div>').addClass('float-start col-1 tankbeurtTabelCel').text(`${tankbeurt.bedrag}`+' €'))
                                .append($('<div>').addClass('float-start col-1 tankbeurtTabelCel').text(ppl +'€/l'))
                                .append($('<div>').addClass('float-start col-1 tankbeurtTabelCel').text(verbruik +'l/100km'))
                                
                                .append($('<div>').addClass('float-end').append(knoppen))
                                //let lijnRechts = $('<div>').addClass('lijnRechts float-end col-2').append($('<div>').addClass('row'));

                // 
                        //$(lijnLinks).append(knoppen);

                   //}
                //else {
                    //$('#nieuwKnop').closest('div').css('display','none');
                //}    
                $('#nieuwKnop').prop('disabled',editdelDisabled);
                //let lijn = $('<div>').addClass('mt-3 float-none lijn')
                $(lijn).append($('<div>').addClass('card mb-1').append($('<div>').addClass('card-header').append($('<div>').addClass('row')
                    .append(lijnLinks))));
                    //.append(lijnLinks).append(lijnRechts))));
                    //.append($('<div>').append(lijnLinks)
                    //.append($('<div>').append(lijnRechts))))));
                $(lijst).append(lijn);
            });
           


            $(lijst).append(PAGINERING.pagineer(jqDta.pagina, jqDta.knoppen, jqDta.aantalPaginas));
            }
        },
            
    
        lijstFout: (jqXHR, jqMsg) => {        alert ('lijstFout');
    },    
            
        

      
      


        // TANKBEURTBEHEER
        bewerk: (tankbeurtID, mode) => {
            let frmDta = new FormData();
                frmDta.append('id', tankbeurtID);
                frmDta.append('mode', mode);
            

            AJX.verstuur(
                    '/jxTankbeurtenGet',
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
                            MODAAL.kop(INDEX.labels.tankbeurtBewerkTitelBewerk);
                            break;
                        case ('nieuw'):
                            MODAAL.kop(INDEX.labels.tankbeurtBewerkTitelNieuw);
                            break;
                        case ('verwijder'):
                            MODAAL.kop(INDEX.labels.tankbeurtBewerkTitelVerwijder);
                            disabled = 'disabled';
                            break;
        
                    } 
         
                    // inhoud dialoogvenster
                    let inhoud = `
                        <div id="tankbeurtBewerkBoodschap"></div>
                        <input type="hidden" id="tankbeurtBewerkID" value="${jqDta.tankbeurt.id}">
                   
                        <input type="hidden" id="tankbeurtBewerkMode" value="${jqDta.mode}">
                        <div class="mb-3">
                            <label for="datum" class="form-label">${INDEX.labels.tankbeurtBewerkDatum}</label>
                            <input type="date" class="form-control" id="tankbeurtBewerkDatum" name="tankbeurtBewerkDatum" value="${jqDta.tankbeurt.datum}" ${disabled}>
                        </div>

                        
                        <div class="mb-3">
                            <label for="tankbeurtBewerkKmstand" class="form-label">${INDEX.labels.tankbeurtBewerkKmstand}</label>
                            <input type="number" min="0" class="form-control" id="tankbeurtBewerkKmstand" name="tankbeurtBewerkKmstand" value="${jqDta.tankbeurt.kmstand}" ${disabled}>
                        </div>
                    
                        <div class="mb-3">
                            <label for="tankbeurtBewerkVolume" class="form-label">${INDEX.labels.tankbeurtBewerkVolume}</label>
                            <input type="number" min="0" class="form-control" id="tankbeurtBewerkVolume" name="tankbeurtBewerkVolume" value="${jqDta.tankbeurt.volume}" ${disabled}>
                        </div>
                    
                        <div class="mb-3">
                            <label for="tankbeurtBewerkBedrag" class="form-label">${INDEX.labels.tankbeurtBewerkBedrag}</label>
                            <input type="number" min="0" class="form-control" id="tankbeurtBewerkBedrag" name="tankbeurtBewerkBedrag" value="${jqDta.tankbeurt.bedrag}" ${disabled}>
                        </div>`
      
                    MODAAL.inhoud(inhoud);
        
                    let voet = '';
                    if (jqDta.mode ==='verwijder') voet += MODAAL.knop('tankbeurtBewerkBewaar', 'warning', 'trash3', INDEX.labels.tankbeurtBewerkVerwijder);
        
                    else
                       voet += MODAAL.knop('tankbeurtBewerkBewaar', 'primary', 'check-square', INDEX.labels.tankbeurtBewerkBewaar);
                       voet += MODAAL.knop('tankbeurtBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.tankbeurtBewerkAnnuleer);
        
                    MODAAL.voet(voet);
                    MODAAL.toon();
                }
            },
            bewerkFout: (jqXHR, jqMsg) => {
                MODAAL.verberg();
            },

            bewerkBewaar: () => {

                let mode = $('#tankbeurtBewerkMode').val();
                let tankbeurtID = $('#tankbeurtBewerkID').val();
                let voertuigID = $('#voertuigBewerkID').val();
                let datum = $('#tankbeurtBewerkDatum').val().trim();
                let kmstand = $('#tankbeurtBewerkKmstand').val().trim();
                let volume = $('#tankbeurtBewerkVolume').val().trim();
                let bedrag = $('#tankbeurtBewerkBedrag').val().trim();
                
                $('#tankbeurtBewerkBoodschap').empty();

        
                let boodschap = '';
                console.log('regex datum =',datum, " => ", INDEX.regexDatum.test(String(datum)));
                boodschap += INDEX.regexDatum.test(String(datum)) ? `` : `<li>${INDEX.labels.tankbeurtBewerkDatum}</li>`;
               
                if (boodschap.length != 0){
                    $('#tankbeurtBewerkBoodschap').html(`
                        <div class="alert alert-warning">
                            ${INDEX.labels.tankbeurtBewerkBoodschap}
                            <ul>
                                ${boodschap}
                            </ul>
                        </div>
                    `);
                }
                else {
    
                    let frmDta = new FormData();
                        frmDta.append('mode', mode);
                        frmDta.append('tankbeurtID', tankbeurtID);
                        frmDta.append('voertuigID', voertuigID);
                        frmDta.append('datum', datum);
                        frmDta.append('kmstand', kmstand);
                        frmDta.append('volume', volume);
                        frmDta.append('bedrag', bedrag);
        
                    AJX.verstuur(
                        '/jxTankbeurtBewaar',
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
                    //window;location.reload();
                    INDEX.lijst(jqDta.voertuigID,pagina);
     
                }
                else {
                    $('#tankbeurtBewerkBoodschap').html(`
                        <div class="alert alert-warning">
                            ${jqDta.boodschap}
                        </div>
                    `);
                    MODAAL.toon();
                }
            },
        
            bewerkBewaarFout: (jqXHR, jqMsg) => {
                MODAAL.kop(jqDta.tankbeurt.id === 0 ? INDEX.labels.tankbeurtBewerkTitelNieuw : INDEX.labels.tankbeurtBewerkTitelBewerk);
                MODAAL.inhoud(`<p>${INDEX.fout}</p>`);
                MODAAL.voet(
                    MODAAL.knop('tankbeurtBewerkAnnuleer', 'secondary', 'x-square', INDEX.labels.tankbeurtBewerkAnnuleer)
                );
                MODAAL.toon();
            },   
 
}