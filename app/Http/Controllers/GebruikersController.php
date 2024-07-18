<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// -- toevoegen begin --
use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;
use App\Models\Gebruikers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
// -- toevoegen einde --

class GebruikersController extends Controller
{
    /**
     * constructor
     */
    public function __construct() {
        $this->_oGebruikers = new Gebruikers();
    }

    /**
     * staat in voor zetten taal interface
     * en controle op aangemeld
     * toont weergave homepage (indien aangemeld en taal)
     */
    public function index() {
        // zet taal interface
        TaalController::taal();

        // redirect naar login indien niet aangemeld
        if (!Auth::check()) return redirect('login');

        // anders toon pagina
        return view('pagina.gebruikers.index');
    }


    /**
     * public function jxGebruikersLijst($request)
     * haalt lijst met Gebruikers op (zoek & paginering)
     * @param $request formulierdata: zoek | pagina
     * @return json
     */
    public function jxGebruikersLijst(Request $request) {
        $json = ['succes' => false];

        // is gebruiker een administrator ?
        $json['isAdmin'] = Auth::user()->level & 0x04;

        $zoekTerm = trim($request->zoek);
        $pagina = intval($request->pagina);
        $json['zoekTerm'] = $zoekTerm;
        $json['pagina'] = $pagina;

        try {
            $dta = $this->_oGebruikers->lijst($zoekTerm, $pagina, Instelling::get('paginering')['aantalperpagina']);
            $json['gebruikers'] = $dta['gebruikers'];
            $json['sql-1'] = $dta['sql-1'];
            $json['aantal'] = $dta['aantal'];
            $json['infoTitel'] = $dta['aantal'] == 0 ? trans('boodschappen.jxGebruikerslijst_titel') : '';
            $json['infoBoodschap'] = $dta['aantal'] == 0 ? trans('boodschappen.jxGebruikerslijst_boodschap') : '';
            $json['sql-2'] = $dta['sql-2'];
            $json['knoppen'] = Instelling::get('paginering')['knoppen'];
            $json['aantalPaginas'] = $dta['aantalPaginas'];
            $json['succes'] = true;
        }
        catch(Exception $ex) {

        }

        return response()->json($json);
    }

    /* public function jxLedenGet($request)
    * haalt info gebruiker op
    * @param $request: form data
    * @return json
    */
   public function jxGebruikersGet(Request $request) {
       $json = [
           'succes' => false,
           'isAdmin' => Auth::user()->level & 4,
           'mode' => $request->mode
       ];

       // vertalingen
       $labels = [];
       foreach(explode(',', __('boodschappen.gebruikersBewerk')) as $item) {
           $tmp = explode(':', $item);
           $labels[$tmp[0]] = $tmp[1];
       }
       $json['labels'] = $labels;
       $json['geduld'] = __('boodschappen.geduld');
       $json['fout'] = __('boodschappen.fout');

       $gebruikersID = intval($request->gebruikersID);
       if ($gebruikersID == 0) {
           $json['gebruiker'] = [
               'id' => 0,
               'name' => '',
               'email' => '',
               'fullname' => '',
               'level' => 1
           ];
       }
       else {
           $json['gebruiker'] = $this->_oGebruikers->getGebruiker($gebruikersID);
       }

       $json['succes'] = true;

       return response()->json($json);
   }

   public function jxGebruikersBewaar(Request $request) {
    $json = ['succes' => false];

    $mode = $request->mode;
    $json['mode'] = $mode;

    $gebruikersID = $request->gebruikersID;
    $naam = $request->naam;
    $vNaam = $request->vNaam;
    $email = $request->email;
    $level = $request->level;

    try {
        if ($mode == 'verwijder') {
            $dta = $this->_oGebruikers->verwijderGebruiker($gebruikersID);
        }
        else {
            $dta = $this->_oGebruikers->setGebruiker($gebruikersID, $naam, $vNaam, $email, $level);
            if ($mode == 'nieuw' && $dta['succes']) $json['gebruikersID'] = $dta['gebruikersID'];
        }

        $json['succes'] = $dta['succes'];
        $json['boodschap'] = $dta['boodschap'];
    }
    catch(Exception $ex) {

    }

    return response()->json($json);
}



    /**
     * 
     */
    public function jxGebruikersResetInfo(Request $request) {
        $json = ['succes' => false];

        $gebruikersID = $request->gebruikersID;
        $json['gebruiker'] = $this->_oGebruikers->getGebruiker($gebruikersID);

        $labels = [];
        foreach(explode(',', __('boodschappen.gebruikersInfo')) as $item) {
            $tmp = explode(':', $item);
            $labels[$tmp[0]] = $tmp[1];
        }
        $json['labels'] = $labels;

        $json['succes'] = True;

        return response()->json($json);
    }

    /**
     * 
     */
    public function jxGebruikersResetMail(Request $request) {
        $user = User::where('id', '=', $request->gebruikersID)->first();
        $token = Password::getRepository()->create($user);
        $user->sendPasswordResetNotification($token);
    }
}

