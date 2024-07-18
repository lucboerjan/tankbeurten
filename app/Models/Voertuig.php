<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Start toevoegen
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
// Einde toevoegen

class Voertuig extends Model
{
    use HasFactory;
    protected $table = 'voertuig';

        /**
     * 
     */
    public function lijst($pagina=0, $aantalPerPagina=1) {
        $dta = false;
        
        // aantal rijen in zoekresultaat
        $dbSql  ="
            SELECT COUNT(1) AS aantal
            FROM voertuig
        ";

        $dta['aantal'] = DB::select($dbSql)[0]->aantal;
 
        $aantalPaginas = ceil($dta['aantal'] / $aantalPerPagina);
        $dta['aantalPaginas'] = $aantalPaginas;
        if ($pagina > $aantalPaginas) $pagina = $aantalPaginas;
        $limit = sprintf("LIMIT %s, %s", $pagina * $aantalPerPagina, $aantalPerPagina);

        //info van de wagen ophalen
        $dbSql = sprintf('
            SELECT MAX(t.kmstand) AS kmstand, SUM(t.volume) AS volume, sUM(t.bedrag) AS bedrag , v.id, v.description, v.obsolete,
            DATE_FORMAT(MAX(t.datum), "%%d-%%m-%%Y") AS datum
            FROM voertuig v LEFT OUTER JOIN tankbeurt t
            ON v.id = t.voertuigID
            GROUP BY v.id,v.description, v.obsolete
            ORDER BY v.obsolete ASC, v.description
            %s
        ', $limit);

        $dta['voertuigen'] = DB::select($dbSql);
        $dta['sql'] = $dbSql;
        return $dta;
    }

    /**
     * 
     */
    public function getVoertuig($id) {
        return DB::select(sprintf("
        SELECT id, description
        FROM voertuig
        WHERE id=%d
    ", $id))[0];

    }    

   /**
     * 
     */
    public function verwijderVoertuig($voertuigID) {
        $dta = ['succes' => false, 'boodschap' => ''];

        Voertuig::where('id', '=', $voertuigID)->delete();
        $dta['succes'] = true;

        return $dta;
    }

    /**
     * 
     */
    public function setVoertuig($voertuigID, $description) {
        $dta = ['succes' => false, 'boodschap' => ''];

        if ($voertuigID == 0) {
            if (count(DB::select(sprintf('SELECT 1 FROM voertuig WHERE description="%s"', $description))) == 0) {
                $voertuig = new Voertuig();
                $voertuig->description = $description;
                $voertuig->save();
                $dta['voertuigID'] = $voertuig->id;
                $dta['succes'] = true;
            }
            else {
                $dta['boodschap'] = __('boodschappen.setVoertuigDescription');
            }
        }
        else {
            if (count(DB::select(sprintf('SELECT 1 FROM voertuig WHERE description="%s" AND id!=%d', $description, $voertuigID))) == 0) {
                $voertuig = Voertuig::find($voertuigID);
                $voertuig->description = $description;
                $voertuig->update();   
                $dta['succes'] = true;   
            }
            else {
                $dta['boodschap'] = __('boodschappen.setVoertuigDescription');
            }      
        }

        return $dta;
    }
}
