<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// TOEVOEGEN BEGIN
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
// TOEVOEGEN EINDE

class Gebruikers extends Model
{
    use HasFactory;

        /**
     * 
     */
    public function lijst ($zoekTerm, $pagina=0, $aantalPerPagina=1) {
        $dta = [];
        

        $zoekTerm = trim($zoekTerm);
        $where = empty($zoekTerm) ? '' : sprintf("WHERE `name`  LIKE '%%%1\$s%%'
                                                     OR `fullname`  LIKE '%%%1\$s%%'
                                                     OR `email`  LIKE '%%%1\$s%%'",
                                                    $zoekTerm);

        // aantal rijen in zoekresultaat
        $dbSql = sprintf("
            SELECT COUNT(1) AS aantal
            FROM users
            %s
        ", $where);
        $dta['sql-1'] = $dbSql;
        $dta['aantal'] = DB::select($dbSql)[0]->aantal;

        $aantalPaginas = ceil($dta['aantal'] / $aantalPerPagina);
        $dta['aantalPaginas'] = $aantalPaginas;
        if ($pagina > $aantalPaginas) $pagina = $aantalPaginas;
        $limit = sprintf("LIMIT %s, %s", $pagina * $aantalPerPagina, $aantalPerPagina);

        $dbSql = sprintf("
            SELECT id, name, fullname, email, level
            FROM users
            %s
            ORDER BY `fullname`
            %s
        ", $where, $limit);
        $dta['gebruikers'] = DB::select($dbSql);
        $dta['sql-2'] = $dbSql;
        
        return $dta;
      }

    /**
     * 
     */
    public function getGebruiker($gebruikersID) {
        return DB::select(sprintf("
            SELECT id, name, email, fullname, level
            FROM users
            WHERE id=%d
        ", $gebruikersID))[0];
    }      


    /**
     * 
     */
    public function verwijdergebruiker($gebruikersID) {
        $dta = ['succes' => false, 'boodschap' => ''];

        User::where('id', '=', $gebruikersID)->delete();
        $dta['succes'] = true;

        return $dta;
    }

    /**
     * 
     */
    public function setgebruiker($gebruikersID, $naam, $vNaam, $email, $level) {
        $dta = ['succes' => false, 'boodschap' => ''];

        if ($gebruikersID == 0) {
            if (count(DB::select(sprintf('SELECT 1 FROM users WHERE email="%s"', $email))) == 0) {
                $gebruiker = new User();
                $gebruiker->name = $naam;
                $gebruiker->fullname = $vNaam;
                $gebruiker->email = $email;
                $gebruiker->level = $level;
                $gebruiker->password = Hash::make(Str::random(10));
                $gebruiker->save();
                $dta['gebruikersID'] = $gebruiker->id;
                $dta['succes'] = true;
            }
            else {
                $dta['boodschap'] = __('boodschappen.setGebruikerEmail');
            }
        }
        else {
            if (count(DB::select(sprintf('SELECT 1 FROM users WHERE email="%s" AND id!=%d', $email, $gebruikersID))) == 0) {
                $gebruiker = User::find($gebruikersID);
                $gebruiker->name = $naam;
                $gebruiker->fullname = $vNaam;
                $gebruiker->email = $email;
                $gebruiker->level = $level;
                $gebruiker->update();   
                $dta['succes'] = true;   
            }
            else {
                $dta['boodschap'] = __('boodschappen.setGebruikerEmail');
            }      
        }

        return $dta;
    }
}
