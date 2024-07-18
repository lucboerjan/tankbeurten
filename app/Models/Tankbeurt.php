<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Start toevoegen
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
// Einde toevoegen

class Tankbeurt extends Model
{
    use HasFactory;
    protected $table = 'tankbeurt';



    public function lijst($voertuigID, $pagina=0,  $aantalPerPagina=1) {
        $dta = [];

             // description voertuig ophalen
             $dbSql=sprintf("
                SELECT description, obsolete
                FROM voertuig WHERE id=%d
            ", $voertuigID);
            $dta['description']=DB::select($dbSql)[0]->description;
            $dta['obsolete']=DB::select($dbSql)[0]->obsolete;

        // aantal rijen in zoekresultaat
               $dbSql = sprintf("
               SELECT COUNT(1) AS aantal
               FROM tankbeurt WHERE voertuigID=%d
           ", $voertuigID);


           
           $dta['sql-1'] = $dbSql;
           $dta['aantal'] = DB::select($dbSql)[0]->aantal;
           $aantalPaginas = ceil($dta['aantal'] / $aantalPerPagina);


           $dta['aantalPaginas'] = $aantalPaginas;
           if ($pagina > $aantalPaginas) $pagina = $aantalPaginas;
           $limit = sprintf("LIMIT %s, %s", $pagina * $aantalPerPagina, $aantalPerPagina);

        $tankbeurten = DB::Select(
            sprintf("
                
                SELECT id, datum, kmstand, kmstand - @km AS afstand, @km:=kmstand, volume, bedrag
                FROM tankbeurt
                WHERE voertuigID = %d
                ORDER BY kmstand DESC
                %s
                ", $voertuigID, $limit)
        ); 
        $dta['tankbeurten'] = $tankbeurten;
        return $dta;
    
    }


    public function getTankbeurt($id) {
        return DB::select(sprintf("
            SELECT id, datum, kmstand, volume, bedrag
            FROM tankbeurt
            WHERE id=%d
            ", $id))[0];
    }    

    
   /**
     * 
     */
    public function verwijderTankbeurt($tankbeurtID) {
        $dta = ['succes' => false, 'boodschap' => ''];

        Tankbeurt::where('id', '=', $tankbeurtID)->delete();
        $dta['succes'] = true;

        return $dta;
    }

        /**
     * 
     */
    public function setTankbeurt($tankbeurtID, $voertuigID, $datum, $kmstand, $volume, $bedrag) {
        $dta = ['succes' => false, 'boodschap' => ''];

           if ($tankbeurtID == 0) {
            
                $tankbeurt = new Tankbeurt();
                $tankbeurt->voertuigID = $voertuigID;
                $tankbeurt->datum = $datum;
                $tankbeurt->kmstand = $kmstand;
                $tankbeurt->volume = $volume;
                $tankbeurt->bedrag = $bedrag;
                $tankbeurt->save();
                $dta['tankbeurtID'] = $tankbeurt->id;
                $dta['succes'] = true;
        }    
        else {
                $tankbeurt = Tankbeurt::find($tankbeurtID);
                $tankbeurt->voertuigID = $voertuigID;
                $tankbeurt->datum = $datum;
                $tankbeurt->kmstand = $kmstand;
                $tankbeurt->volume = $volume;
                $tankbeurt->bedrag = $bedrag;
                $tankbeurt->update();   
                $dta['succes'] = true;   

        }

        return $dta;
    }
}
