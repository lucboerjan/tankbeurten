<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Middleware\Instelling;
use App\Http\Controllers\TaalController;

class BeheerController extends Controller
{ 
    /**
     * public function index()
     * staat in voor het weergeven van de homepage (na aanmelden)
     * @return \view index.blade.php
     */
    public function index() {
        // zet taal interface
        TaalController::taal();

        // redirect login indien niet aangemeld
        if (! Auth::check()) {
            return redirect('login');
        }
        $dta=[];
       
        return view('pagina.beheer.index')->with($dta);
    }

    public function jxGetInstellingen() {
        $instellingen = [];
        $dta['instellingen']= json_decode(file_get_contents(sprintf('%s/%s', storage_path(), 'instelling.json')), true);
       // $dta['instellingen']=   file_get_contents(sprintf('%s/%s', storage_path(), 'instelling.json'));
        $dta['succes'] = true;
        return $dta;

    }

    
    public function jxSetInstellingen(Request $request) {
        $instellingen = $request->instellingen;
        file_put_contents(sprintf('%s/%s', storage_path(), 'instelling.json'), $instellingen);
        $json['succes'] = true;
        return response()->json($json);

    }
}
