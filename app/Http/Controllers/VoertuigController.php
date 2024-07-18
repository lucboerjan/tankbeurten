<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// -- toevoegen begin --
use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;
use App\Models\Voertuig;


// -- toevoegen einde --

class VoertuigController extends Controller
{
     /**
     * constructor
     */
    public function __construct() {
        $this->_oVoertuig = new Voertuig();
    }

    /**
     * staat in voor zetten taal interface
     * en controle op aangemeld
     * toont weergave homepage (indien aangemeld en taal)
     */
    public function index($pagina=0) {
            // zet taal interface
            TaalController::taal();
    
            // redirect naar login indien niet aangemeld
            if (!Auth::check()) return redirect('login');
    
            // anders toon pagina
            $dta['pagina'] = $pagina;
            return view('pagina.voertuig.index')->with($dta);
        }
    
    


        public function jxVoertuigenLijst(Request $request) {

            $json = ['succes' => false];

            // ievel gebruiker bepalen ?
            $json['isAdmin'] = Auth::user()->level & 0x04;
            $pagina = intval($request->pagina);
            $labels = [];
            foreach(explode(',', __('boodschappen.voertuigTabel')) as $item) {
                $tmp = explode(':', $item);
                $labels[$tmp[0]] = $tmp[1];
            }
            $json['labels'] = $labels;

            $json['pagina'] = $pagina;
    

        try {
            $rslt = $this->_oVoertuig->lijst($pagina, Instelling::get('paginering')['aantalperpagina']);
            $json['aantal'] = $rslt['aantal'];
            $json['infoTitel'] = $rslt['aantal'] == 0 ? trans('boodschappen.jxVoertuigenlijst_titel') : '';
            $json['infoBoodschap'] = $rslt['aantal'] == 0 ? trans('boodschappen.jxVoertuigenlijst_boodschap') : '';
            $json['voertuigen'] = $rslt['voertuigen'];
            $json['sql'] = $rslt['sql'];
            $json['knoppen'] = Instelling::get('paginering')['knoppen'];
            $json['aantalPaginas'] = $rslt['aantalPaginas'];
            $json['succes'] = true;
            }

           

        catch(Exception $ex) {

        }
       // var_dump($dta); die();
       return response()->json($json);
       
    }

        /**
     * public function jxVoertuigenGet($request)
     * haalt info voertuig op
     * @param $request: form data
     * @return json
     */
    public function jxVoertuigenGet(Request $request) {
        
        //check of de gebruiker het juiste level geeft indien mode bewerk of verwijder
        if ($request->mode=='bewerk' || $request->mode = 'verwijder') {
            if  (!(Auth::user()->level & 0x04)) {
                $dta['pagina'] = 0;
                return view('pagina.voertuig.index')->with($dta);
            };
        }


        $json = [
            'succes' => false,
            'isAdmin' => Auth::user()->level & 8,
            'mode' => $request->mode
        ];

        // vertalingen
        $labels = [];
        foreach(explode(',', __('boodschappen.voertuigBewerk')) as $item) {
            $tmp = explode(':', $item);
            $labels[$tmp[0]] = $tmp[1];
        }
        $json['labels'] = $labels;
        $json['geduld'] = __('boodschappen.geduld');
        $json['fout'] = __('boodschappen.fout');

        $id = intval($request->id);
        if ($id == 0) {
            $json['voertuig'] = [
                'id' => 0,
                'description' =>''

            ];
        }
        else {
            $json['voertuig'] = $this->_oVoertuig->getVoertuig($id);
        }

        $json['succes'] = true;

        return response()->json($json);
    }

    public function jxVoertuigBewaar(Request $request) {
        $json = ['succes' => false];

        $mode = $request->mode;
        $json['mode'] = $mode;

        $voertuigID = $request->voertuigID;
        $description = $request->description;
        try {
            if ($mode == 'verwijder') {
                $dta = $this->_oVoertuig->verwijderVoertuig($voertuigID);
            }
            else {
                $dta = $this->_oVoertuig->setVoertuig($voertuigID, $description);
                if ($mode == 'nieuw' && $dta['succes']) $json['voertuigID'] = $dta['voertuigID'];
            }

            $json['succes'] = $dta['succes'];
            $json['boodschap'] = $dta['boodschap'];
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

 
}
