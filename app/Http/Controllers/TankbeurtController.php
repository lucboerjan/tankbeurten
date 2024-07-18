<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

// -- toevoegen begin --
use Auth;
use Carbon\Carbon;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;
use App\Models\Tankbeurt;
use App\Models\Voertuig;
// -- toevoegen einde --

class TankbeurtController extends Controller
{
    /**
     * constructor
     */
    public function __construct() {
        $this->_oTankbeurt = new Tankbeurt();
        $this->_oVoertuig = new Voertuig();
    }

    public function index($voertuigID=0, $pagina=0) {
        // zet taal interface
        TaalController::taal();

        // redirect naar login indien niet aangemeld
        if (!Auth::check()) return redirect('login');

        // anders toon pagina
        $rslt = $this->_oVoertuig->getVoertuig($voertuigID);
       
        $dta['voertuigID'] = $voertuigID;
        $dta['description'] = $rslt->description;
        $dta['pagina'] = $pagina;
        return view('pagina.tankbeurt.index')->with($dta);
    }



    /**
     * public function jxTankbeurtenLijst($request)
     * haalt lijst met Tankbeurten op (voertuigID & paginering)
     * @param $request formulierdata: voertuigID | pagina
     * @return json
     */
    public function jxTankbeurtenLijst(Request $request) {
        $json = ['succes' => false];

        // ievel gebruiker bepalen ?
        $json['isTankbeurtBeheerder'] = Auth::user()->level & 0x02;
        $json['isAdmin'] = Auth::user()->level & 0x04;

        $voertuigID = trim($request->voertuigID);
        $pagina = intval($request->pagina);
        $json['voertuigID'] = $voertuigID;
        $json['pagina'] = $pagina;

        try {
            $dta = $this->_oTankbeurt->lijst($voertuigID, $pagina, Instelling::get('paginering')['aantalperpagina']);
            $json['tankbeurten'] = $dta['tankbeurten'];
            $json['aantal'] = $dta['aantal'];
            $json['infoTitel'] = $dta['aantal'] == 0 ? trans('boodschappen.jxTankbeurtenlijst_titel') : '';
            $json['infoBoodschap'] = $dta['aantal'] == 0 ? trans('boodschappen.jxTankbeurtenlijst_boodschap') : '';
            $json['knoppen'] = Instelling::get('paginering')['knoppen'];
            $json['aantalPaginas'] = $dta['aantalPaginas'];
            $json['description'] = $dta['description'];
            $json['obsolete'] = $dta['obsolete'];

            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }    
   
    public function tankbeurt($voertuigID=0, $pagina=0) {
        // zet taal interface
        TaalController::taal();

        // redirect naar login indien niet aangemeld
        if (!Auth::check()) return redirect('login');

        // anders toon pagina     
    
        if (!$voertuigID) return redirect('/voertuig/0');

        $dta = [];
        $dta['voertuig'] = $this->_oVoertuig->getVoertuig($voertuigID);
        $rslt = $this->_oTankbeurt->lijst($voertuigID, $pagina, Instelling::get('paginering')['aantalperpagina']);
       // $dta['voertuigID'] = $voertuigID;
        $dta['tankbeurten'] = $rslt['tankbeurten'];
        $dta['aantalPaginas'] = $rslt['aantalPaginas'] ;     
        return view('pagina.tankbeurt.index')->with($dta);
    }    

    public function jxTankbeurtenGet(Request $request) {
        $mode = $request->mode;
        //check of de gebruiker het juiste level geeft indien mode bewerk of verwijder
       if ($mode=='bewerk' || $mode == 'verwijder') {
             if  (!(Auth::user()->level & 0x02)) {
                $dta['pagina'] = 0;
                return view('pagina.tankbeurt.index')->with($dta);
            }; 
        } 

        $json = [
            'succes' => false,
            'mode' => $mode
        ];
        
        // vertalingen
        $labels = [];
        foreach(explode(',', __('boodschappen.tankbeurtBewerk')) as $item) {
            $tmp = explode(':', $item);
            $labels[$tmp[0]] = $tmp[1];
        }
        $json['labels'] = $labels;
        $json['geduld'] = __('boodschappen.geduld');
        $json['fout'] = __('boodschappen.fout');

        $id = intval($request->id);
        if ($id == 0) {
            $oDatum = Carbon::today()->timezone('Europe/Brussels');
            $json['tankbeurt'] = [
                'id' => 0,
                'datum' => $oDatum->format('Y-m-d'),

            ];
        }
        else {
            $json['tankbeurt'] = $this->_oTankbeurt->getTankbeurt($id);
        }
        
        $json['succes'] = true;
        return response()->json($json);
    }

    public function jxTankbeurtBewaar(Request $request) {
        $json = ['succes' => false];

        $mode = $request->mode;
        $json['mode'] = $mode;

        $tankbeurtID = $request->tankbeurtID;
        $voertuigID = $request->voertuigID;
        $json = ['voertuigID' => $voertuigID];
        $datum = $request->datum;
        $kmstand = $request->kmstand;
        $volume = $request->volume;
        $bedrag = $request->bedrag;

        try {
            if ($mode == 'verwijder') {
                $dta = $this->_oTankbeurt->verwijderTankbeurt($tankbeurtID);
            }
            else {
                $dta = $this->_oTankbeurt->setTankbeurt($tankbeurtID, $voertuigID, $datum, $kmstand,$volume, $bedrag);
                if ($mode == 'nieuw' && $dta['succes']) $json['tankbeurtID'] = $dta['tankbeurtID'];
            }

            $json['succes'] = $dta['succes'];
            $json['boodschap'] = $dta['boodschap'];
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }
}
