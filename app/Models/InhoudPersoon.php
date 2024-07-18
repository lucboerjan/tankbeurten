<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\InhoudZoek;
use App\Models\Exception;
use App\Http\Middleware\Instelling;

class InhoudPersoon extends Model
{
    /**
     * public function persoon($persoonID)
     * haalt informatie van betreffende persoon op
     * @param $persoonID
     * @return array
     */
    public function persoon($persoonID)
    {
        $overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];


        $dbSql = sprintf('
    SELECT p.id, p.naam, p.voornamen, p.roepnaam,
           DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum,
           CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats,
           p.geborenop, p.geborenplaatsID,
           DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum,
           CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats,
           p.gestorvenop, p.gestorvenplaatsID,
           p.sex,p.geadopteerd, p.info,


           CASE 
           WHEN p.gestorvenop IS NOT NULL THEN
               CASE 
                   WHEN p.geborenop IS NOT NULL THEN
                       TIMESTAMPDIFF(YEAR, p.geborenop, p.gestorvenop)
                   ELSE "Leeftijd onbekend"
               END 
           ELSE
               CASE
                   WHEN p.geborenop IS NULL THEN 
                       "Leeftijd onbekend"
                   ELSE
                       CASE     
                           WHEN TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE) > %2$d THEN
                               "Leeftijd onbekend"
                           ELSE
                               TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE)
                       END
               END
       END AS leeftijd


    FROM persoon p
    LEFT OUTER JOIN plaats pl1
    ON p.geborenplaatsID = pl1.ID
    LEFT OUTER JOIN plaats pl2
    ON p.gestorvenplaatsID = pl2.ID
    WHERE p.id=%1$d
', $persoonID, $overlijdensGrens);

        return DB::select($dbSql)[0];
    }

    /**
     * public function persoonFamilies()
     * families gerelateerd aan persoon
     * @param $persoonID
     * @return array
     */
    public function persoonFamilies($persoonID)
    {
        $dbSql = sprintf('
            SELECT id, naam
            FROM families
            WHERE id IN (SELECT familie1ID FROM persoon WHERE id=%1$d)
               OR id IN (SELECT familie2ID FROM persoon WHERE id=%1$d)
        ', $persoonID);

        return DB::select($dbSql);
    }

    /* --- FAMILIES --- */
    /**
     * public function familieInfo($familieID)
     * haalt info familie op
     * @param $familieID
     * @return array
     */
    public function familieInfo($familieID)
    {
        $dbSql = sprintf('
            SELECT id, naam
            FROM families
            WHERE id=%d
        ', $familieID);

        $dbRslt = DB::select($dbSql);

        return $dbRslt ? $dbRslt[0] : false;
    }

    /**
     * public function familieVerwijder($familieID, $persoonID)
     * verwijder inhoud familie-veld
     * @param $familieID
     * @param $persoonID
     * @return array
     */
    public function familieVerwijder($familieID, $persoonID)
    {
        $dbSql = sprintf('
            UPDATE persoon
            SET familie1ID = CASE WHEN familie1ID=%1$d THEN NULL ELSE familie1ID END,
                familie2ID = CASE WHEN familie2ID=%1$d THEN NULL ELSE familie2ID END
            wHERE id=%2$d
        ', $familieID, $persoonID);
        return DB::select($dbSql);

    }

    /**
     * public function families()
     * haalt lijst met families op
     * @return array
     */
    public function families()
    {
        $oInhoudzoek = new InhoudZoek();
        return $oInhoudzoek->families();

    }

    /**
     * public function familieBewaar($familieNaam, $familieID)
     * @param $familieNaam
     * @param $familieID
     */
    public function familieBewaar($familieNaam, $familieID, $persoonID)
    {
        if ($familieID) {
            $dbSql = sprintf('
                UPDATE `families`
                SET `naam` = "%s"
                WHERE `id` = %d
            ', $familieNaam, $familieID);

            DB::select($dbSql);

            return [$familieID, $familieNaam];
        } else {
            // controleer of familie reeds bestaat
            $dbSql = sprintf('
                SELECT id, naam
                FROM families
                WHERE UPPER(naam) = "%s"
            ', strtoupper($familieNaam));

            $dbRslt = DB::select($dbSql);
            if (count($dbRslt) > 0)
                return [$dbRslt[0]->id, $dbRslt[0]->naam];

            // familie bestaat niet -> toevoegen
            $dbSql = sprintf('
                INSERT INTO `families` (`naam`)
                VALUES ("%s")
            ', $familieNaam);

            DB::select($dbSql);

            return [DB::getPdo()->lastInsertId(), $familieNaam];
        }
    }

    public function familiePersoon($persoonID,  $familieID) {
        // haal familie1ID en familie2ID op
        $dbSql = sprintf('
            SELECT familie1ID, familie2ID
            FROM persoon
            WHERE id = %d
        ', $persoonID);

        $dbRslt = DB::select($dbSql)[0];

        if (!$dbRslt->familie1ID) {
            $dbSql = sprintf('
                UPDATE persoon
                SET familie1ID = %d
                WHERE id = %d
            ', $familieID, $persoonID);
            
            DB::select($dbSql);
        }
        elseif (!$dbRslt->familie2ID) {
            $dbSql = sprintf('
                UPDATE persoon
                SET familie2ID = %d
                WHERE id = %d
            ', $familieID, $persoonID);
            DB::select($dbSql);
        }
    }

    /**
     * public function familieLinkBewaar
     * link een familie aan een persoon
     * @param $persoonID
     * @param $familieID
     * @return array
     */

     public function familieLinkBewaar($familieID, $persoonID)
    {
    //       // haal familie1ID en familie2ID op
    //       $dbSql = sprintf('
    //       SELECT familie1ID, familie2ID
    //       FROM persoon
    //       WHERE id = %d
    //   ', $persoonID);

    //       $dbRslt = DB::select($dbSql)[0];
  
    //       if (!$dbRslt->familie1ID) {
    //           $dbSql = sprintf('
    //           UPDATE persoon
    //           SET familie1ID = %d
    //           WHERE id = %d
    //       ', $familieID, $persoonID);
    //           DB::select($dbSql);
    //       } elseif (!$dbRslt->familie2ID) {
    //           $dbSql = sprintf('
    //           UPDATE persoon
    //           SET familie2ID = %d
    //           WHERE id = %d
    //       ', $familieID, $persoonID);
    //           DB::select($dbSql);
    //       }
           return $this->persoonFamilies($persoonID);
    }


    

    /**
     * public function ouders($persoonID)
     * haalt de ouders van de betrokken persoon op
     * @param $persoonID
     * @return array
     */

    public function ouders($persoonID)
    {
        $overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];
        $dbSql = sprintf('
            SELECT p.id, p.naam, p.voornamen, p.roepnaam,
                DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, geborenplaatsID,
                DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, gestorvenplaatsID,
                p.geadopteerd, p.info, p.sex,
                   
                CASE 
                WHEN p.gestorvenop IS NOT NULL THEN
                    CASE 
                        WHEN p.geborenop IS NOT NULL THEN
                            TIMESTAMPDIFF(YEAR, p.geborenop, p.gestorvenop)
                        ELSE "Leeftijd onbekend"
                    END 
                    
                ELSE
                     CASE
                        WHEN p.gestorvenop IS NULL THEN
                            CASE 
                        WHEN TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE) > %2$d THEN
                            "Leeftijd onbekend"
                        ELSE
                            TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE)
                    END
                end    
            END AS leeftijd

            FROM persoon p
            LEFT OUTER JOIN plaats pl1
                ON p.geborenplaatsID = pl1.id
            LEFT OUTER JOIN plaats pl2
                ON p.gestorvenplaatsID = pl2.id
            WHERE p.id = (SELECT ouder1ID FROM persoon WHERE id = %1$d)
              OR p.id = (SELECT ouder2ID FROM persoon WHERE id = %1$d)
            ORDER BY geborenop, voornamen
        ', $persoonID, $overlijdensGrens);

        return DB::select($dbSql);
    }


    /**
     * public function ouderInfo($ouderID);
     * haalt informatie ouder op
     * @param $ouderID
     * @return array

     */

    public function ouderInfo($ouderID)
    {
        return $this->persoon($ouderID);
    }

    /**
     * public function ouderVerwijder($ouderID, $persoonID);
     * verwijderd ouder van persoon
     * @param $ouderID
     * @param $persoonID
     * @return array
     */
    public function ouderVerwijder($ouderID, $persoonID)
    {
        $dbSql = sprintf('
            UPDATE persoon
            SET ouder1ID = CASE WHEN ouder1id=%1$d  THEN NULL ELSE ouder1ID END,
                ouder2ID = CASE WHEN ouder2id=%1$d  THEN NULL ELSE ouder2ID END
            WHERE id=%2$d
        
        ', $ouderID, $persoonID);
        return DB::select($dbSql);
    }

    /**
     * public function ouderLijst()
     * lijst van personen die ouder zijn dan de huidige persoon !instelling leeftijdsgrens
     * @param $persoonID
     * @return array
     * 
     */
    public function ouderLijst($persoonID)
    {


        $leeftijdGrens = Instelling::get('leeftijdsgrens')['ouder'];
        $overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];


        $dbSql = sprintf('
            SELECT p.id, p.naam, p.voornamen, p.roepnaam,
            DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, CONCAT (pl1.gemeente, ", ", pl1.land) AS geborenplaats, geborenop, geborenplaatsID,
            DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, CONCAT (pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, gestorvenop, gestorvenplaatsID,
                   
                   
            CASE 
            WHEN p.gestorvenop IS NOT NULL THEN
                CASE 
                    WHEN p.geborenop IS NOT NULL THEN
                        TIMESTAMPDIFF(YEAR, p.geborenop, p.gestorvenop)
                    ELSE "Leeftijd onbekend"
                END 
                
            ELSE
                 CASE
                    WHEN p.gestorvenop IS NULL THEN
                        CASE 
                    WHEN TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE) > %24d THEN
                        "Leeftijd onbekend"
                    ELSE
                        TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE)
                END
            end    
        END AS leeftijd


            FROM persoon p
            LEFT OUTER JOIN plaats pl1
            ON p.geborenplaatsID=pl1.id
            LEFT OUTER JOIN plaats pl2
            ON p.gestorvenplaatsID=pl2.id
            WHERE (DATE(geborenop) BETWEEN ((SELECT geborenop FROM persoon WHERE id=%1$d) - INTERVAL %2$d YEAR) 
            AND ((SELECT geborenop FROM persoon WHERE id=%1$d) - INTERVAL %3$d YEAR) OR geborenop IS NULL)

            
            AND p.id NOT IN ((SELECT ouder1ID FROM persoon WHERE id=%1$d AND ouder1ID IS NOT NULL) UNION
            (SELECT ouder2ID FROM persoon WHERE id=%1$d AND ouder2ID IS NOT NULL))

            and p.naam IS NOT NULL
            and p.voornamen IS NOT NULL

            ORDER BY geborenop DESC, naam, voornamen
        ', $persoonID, $leeftijdGrens[1], $leeftijdGrens[0], $overlijdensGrens);

        return DB::select($dbSql);
    }



    /**
     * public function ouderBewaar()
     * koppel ouder aan persoon
     * @param $persoonID
     * @param $ouderID
     * @return array
     * 
     */
    public function ouderBewaar($persoonID, $ouderID)
    {
      // haal ouder1ID en ouder2ID op
        $dbSql = sprintf('
        SELECT ouder1ID, ouder2ID
        FROM persoon
        WHERE id = %d
    ', $persoonID);
        $dbRslt = DB::select($dbSql)[0];

        if (!$dbRslt->ouder1ID) {
            $dbSql = sprintf('
            UPDATE persoon
            SET ouder1ID = %d
            WHERE id = %d
        ', $ouderID, $persoonID);
            DB::select($dbSql);
        } elseif (!$dbRslt->ouder2ID) {
            $dbSql = sprintf('
            UPDATE persoon
            SET ouder2ID = %d
            WHERE id = %d
        ', $ouderID, $persoonID);
            DB::select($dbSql);
        }
        return $this->ouders($persoonID);
    }

    /** --KINDEREN-- */

    /**
     * public function kinderen($persoonID)
     * haalt de kinderen van de betrokken persoon op
     * @param $persoonID
     * @return array
     */
    public function kinderen($persoonID)
    {
        $overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];
        $dbSql = sprintf('
            SELECT p.id, p.naam, p.voornamen, p.roepnaam,
                   DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, geborenplaatsID,
                   DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, gestorvenplaatsID,          
                   p.geadopteerd, p.info, p.sex,
                   
                   CASE 
                   WHEN p.gestorvenop IS NOT NULL THEN
                       CASE 
                           WHEN p.geborenop IS NOT NULL THEN
                               TIMESTAMPDIFF(YEAR, p.geborenop, p.gestorvenop)
                           ELSE "Leeftijd onbekend"
                       END 
                       
               ELSE	CASE
                   WHEN p.gestorvenop IS NULL THEN
                       CASE 
                           WHEN TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE) > %2$d THEN
                               "Leeftijd onbekend"
                           ELSE
                               TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE)
                       END
                   end    
               END AS leeftijd

            FROM persoon p
            LEFT OUTER JOIN plaats pl1
              ON p.geborenplaatsID = pl1.id
            LEFT OUTER JOIN plaats pl2
              ON p.gestorvenplaatsID = pl2.id
            WHERE ouder1ID=%1$d
               OR ouder2ID=%1$d
            ORDER BY DATE_FORMAT(p.geborenop, "%%Y"), voornamen
        ', $persoonID, $overlijdensGrens);

        return DB::select($dbSql);
    }



    /**
     * public function kindInfo($kindID)
     * haalt informatie kind op
     * @param $kindID
     * @return array
     */
    public function kindInfo($kindID)
    {
        return $this->persoon($kindID);
    }

    /**
     * public function kindVerwijder($kindID, $persoonID)
     * verwijdert persoon van kind
     * @param $kindID
     * @param $persoonID
     * @return array
     */
    public function kindVerwijder($kindID, $persoonID)
    {
        $dbSql = sprintf('
            UPDATE  persoon
            SET ouder1ID = CASE WHEN ouder1ID=%1$d  THEN NULL ELSE ouder1ID END,
                ouder2ID = CASE WHEN ouder2ID=%1$d  THEN NULL ELSE ouder2ID END
            WHERE id=%2$d
        ', $persoonID, $kindID);

        return DB::select($dbSql);

    }
    /**
     * public function jxInhoudPersoonKindLijst()
     * levert lijst op van personen die jonger zijn dan huidige persoon
     * @param $persoonID
     * @return array
     */
    public function kindLijst($persoonID)
    {
        $leeftijdGrens = Instelling::get('leeftijdsgrens')['kind'];
        $overlijdensGrens = Instelling::get('leeftijdsgrens')['overlijden'];

        $dbSql = sprintf('
            SELECT p.id, p.naam, p.voornamen, p.roepnaam,
            DATE_FORMAT(p.geborenop, "%%d-%%m-%%Y") AS geborendatum, CONCAT(pl1.gemeente, ", ", pl1.land) AS geborenplaats, geborenop, geborenplaatsID,
            DATE_FORMAT(p.gestorvenop, "%%d-%%m-%%Y") AS gestorvendatum, CONCAT(pl2.gemeente, ", ", pl2.land) AS gestorvenplaats, gestorvenop, gestorvenplaatsID,
                   
            CASE 
            WHEN p.gestorvenop IS NOT NULL THEN
                CASE 
                    WHEN p.geborenop IS NOT NULL THEN
                        TIMESTAMPDIFF(YEAR, p.geborenop, p.gestorvenop)
                    ELSE "Leeftijd onbekend"
                END 
                
        ELSE	CASE
            WHEN p.gestorvenop IS NULL THEN
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE) > %4$d THEN
                        "Leeftijd onbekend"
                    ELSE
                        TIMESTAMPDIFF(YEAR, p.geborenop, CURRENT_DATE)
                END
            end    
        END AS leeftijd

            FROM persoon p
            LEFT OUTER JOIN plaats pl1
            ON p.geborenplaatsID=pl1.id
            LEFT OUTER JOIN plaats pl2
            ON p.gestorvenplaatsID=pl2.id
            WHERE (DATE(geborenop) BETWEEN  ((SELECT geborenop FROM persoon WHERE id=%1$d) + INTERVAL %2$d YEAR)
            AND ((SELECT geborenop FROM persoon WHERE id=%1$d) + INTERVAL %3$d YEAR) )
           
            AND ((p.ouder1ID IS NULL AND p.ouder2ID IS NULL) OR (p.ouder2ID IS NULL AND p.ouder1ID != %1$d) OR (p.ouder1ID IS NULL AND p.ouder2ID != %1$d) )
            
            AND p.naam IS NOT NULL
            AND p.voornamen IS NOT NULL
            ORDER BY geborenop  DESC, naam, voornamen
          ', $persoonID, $leeftijdGrens[0], $leeftijdGrens[1], $overlijdensGrens);


        return DB::select($dbSql);
            // AND (p.ouder1ID IS NULL OR p.ouder2ID IS NULL)
            // AND (p.ouder1ID != %1$d OR p.ouder2ID != %1$d)

    }

    /**
     *  public function kindBewaar
     * kent kind aan ouder toe
     * @param $persoonID
     * @param $kindID 
     * @return array
     */
     
     public function kindBewaar($persoonID, $kindID) {
        $dbSql = sprintf('
           SELECT ouder1ID, ouder2ID
           FROM persoon 
           WHERE id = %d
        ', $kindID);

        $dbRslt = DB::select($dbSql)[0];

        $ouder = '';
        if (! $dbRslt->ouder1ID) {
            $ouder = 'ouder1ID';
        }
        elseif (! $dbRslt->ouder2ID) {
            $ouder = 'ouder2ID';
        }
        else {
            return $this->kinderen($persoonID);
        }

        $dbSql = sprintf('
            UPDATE persoon
            SET %s = %d
            WHERE id = %d
        ', $ouder, $persoonID, $kindID);

        DB::select($dbSql);
        return $this->kinderen($persoonID);
        
     }
    /**
     *  public function documentLijst
     * haalt lijst documenten database op
     * @param $persoonID
     * @return array
     */
     public function documentLijst($persoonID) {
        $dbSql = sprintf('
            SELECT id, CONCAT("%s", "/", bestand) AS bestand, titel
            FROM document
            WHERE id IN (
                SELECT documentID
                FROM persoondocument
                WHERE persoonID=%d
            )
            ORDER BY info
        ', instelling::get('upload')['map'], $persoonID);
        return DB::select($dbSql);
     }
    

     /**
      * public function persoonBewaar($frmDta
      * update gegevens persoon
      * @param $frmDta
      * @return integer
      */
    public function persoonBewaar($frmDta) {
        $status = 0;

        try {
            $persoonID = $frmDta['persoonID'];
            $naam = $frmDta['naam'];
            $voornamen = $frmDta['voornamen'];
            $roepnaam = $frmDta['roepnaam'];
            $sex = $frmDta['sex'];
            $geadopteerd = $frmDta['geadopteerd'];
            $geborenop = $frmDta['geborenop'] ? "\"{$frmDta['geborenop']}\"" : "NULL";
            $geborenplaatsID = $frmDta['geborenplaatsID'] ?  $frmDta['geborenplaatsID'] : "NULL";
            $gestorvenop = $frmDta['gestorvenop'] ? "\"{$frmDta['gestorvenop']}\"" : "NULL";
            $gestorvenplaatsID = $frmDta['gestorvenplaatsID'] ?  $frmDta['gestorvenplaatsID'] : "NULL";
            $info = $frmDta['info'];

            $dbSql = sprintf('
            
                UPDATE `persoon`
                SET naam = "%s",
                    voornamen = "%s",
                    roepnaam = "%s",
                    sex = "%s",
                    geadopteerd = %d,
                    geborenop = %s,
                    geborenplaatsID = %d,
                    gestorvenop = %s,
                    gestorvenplaatsID = %d,
                    info = "%s"
                WHERE id = %d    
            ',$naam, $voornamen, $roepnaam, $sex, $geadopteerd, $geborenop, $geborenplaatsID, $gestorvenop, $gestorvenplaatsID, $info, $persoonID);

            DB::select($dbSql);
            $status = 1;
        }
        catch (Exception $ex) {

        }

        return $status;
    
    }

    /**
     * public function persoonDummyVerwijder()
     * verwijderd alle lege personen: personen zonder naam en voornamen
     * @return void
     */

    public function persoonDummyVerwijder() {
        $tijdstip = Carbon::now();

        $dbSql = sprintf('
            DELETE FROM persoon
            WHERE naam IS NULL
                AND voornamen IS NULL
                AND TIMESTAMPDIFF(MINUTE, created_at, "%s") >= %d
        ', $tijdstip->format('Y-m-d H:i:s'), Instelling::get('tijd-dummy-persoon'));

        DB::select($dbSql);
    }

    /**
     *  public function persoonNieuw()
     *  maakt een nieuwe persoon aan in de database
     *  @return int
     */

     public function persoonNieuw() {
        $tijdstip = Carbon::now();

        $dbSql = sprintf('
            INSERT INTO persoon (created_at)
            VALUES ("%s")
        ', $tijdstip->format('Y-m-d H:i:s'));

        DB::select($dbSql); 

        return DB::getPdo()->lastInsertId();

     }

     /**
      * public function verwijderPersoon($persoonID))
      * verwijdert persoon uit database :
      *      - persoon
      *      - gekoppelde documenten
      *      - verwijder kinderen
      * @param $persoonID
      * @return boolean
      */

      public function verwijderPersoon($persoonID) {
        DB::beginTransaction();

        try {
            // van kinderen verwijder ouderID
            $dbSql = sprintf('
                UPDATE persoon
                SET ouder1ID = CASE WHEN ouder1ID=%1$d THEN NULL ELSE ouder1ID END,
                    ouder2ID = CASE WHEN ouder2ID=%1$d THEN NULL ELSE ouder2ID END
                
            ', $persoonID);
            DB::select($dbSql);

            // verwijder persoon
            $dbSql = sprintf('
                DELETE FROM persoon
                WHERE id = %d
            ', $persoonID);
            DB::select($dbSql);

            // verwijder uit persoondocument
            $dbSql = sprintf('
                DELETE FROM persoondocument
                WHERE persoonID=%d
            ', $persoonID); 
            DB::select($dbSql);

            DB::commit();
            return true;
        }
        catch (Exception $ex) {
            DB::rollBack();
            return false;
        }
      }


      /**
      * public function plaatsBewaar() 
      * bewaar de plaats en voeg eventueel nieuwe plaats toe aan plaatsen
      * @param plaatsveld
      * @param id
      * @param gemeente
      * @param land
      * @return array      
      */

    //   public function plaatsBewaar($persoonID, $plaatsveld, $id, $gemeente, $land) {
    //     //indien gemeente nog niet in de tabel plaatsz
    //     if ($id == 0) {
    //         $dbSql = sprintf('
    //             INSERT INTO plaats (gemeente, land)
    //             VALUES ("%s","%s")

    //         ', $gemeente, $land);

    //        // echo($dbSql);die();

    //         DB::select($dbSql);
    //         $id = DB::getPdo()->lastInsertId();
    //     }

    //     //schrijf de id van de gemeente weg naar de geboorteplaats of sterfteplaats
    //     switch ($plaatsveld) {
    //         case ('geboren'):
    //             $dbSql = sprintf('
    //                 UPDATE persoon
    //                 SET geborenplaatsID=%d
    //                 WHERE id=%d
    //             ', $id, $persoonID);
    //             break;
    //         case ('gestorven'):
    //             $dbSql = sprintf('
    //                 UPDATE persoon
    //                 SET gestorvenplaatsID=%d
    //                 WHERE id=%d
    //             ', $id, $persoonID);
    //             break;
    //     }
    //     return  DB::select($dbSql);


    //   }

      /* --- PLAATSEN ---*/

      /**
       * public funtion plaatsen()
       * haamt lijst op mezt plaatsnamen
       * @return array
       * 
       */
      
       public function plaatsen() {
        $dbSql = sprintf('
            SELECT id, CONCAT(gemeente, ", " , land) AS plaats
            FROM plaats
            ORDER BY land, gemeente
        ');

        return  DB::select($dbSql);


      }

            /**
      * public function plaatsnieuw() 
      * bewaar plaats en levert ID plaats op
      * @param string land
      * @param string gemeente
      * @return       
      */
      public function plaatsNieuw($gemeente, $land) {
        // land en gemeente moeten inhoud

        if (strlen($gemeente) == 0 || strlen($land) == 0 ) return false;
         

        // bestaat plaats al?
        $dbSql = sprintf('
            SELECT id, CONCAT(gemeente, ", ", land) AS plaats
            FROM plaats
            WHERE gemeente = "%s" AND land = "%s"
        ', $gemeente, $land);

        $dbRslt = DB::select($dbSql);
        if (count($dbRslt)== 1) {
            return $dbRslt[0];
        }

        // plaats bestaat niet -> invoegen
        $dbSql = sprintf('
            INSERT INTO plaats (gemeente, land)
            VALUES ("%s","%s")
        ', $gemeente, $land);

        DB::select($dbSql);

        $plaatsID = DB::getPdo()->lastInsertId();

        $dbSql = sprintf('
        SELECT id, CONCAT(gemeente, ", ", land) AS plaats
        FROM plaats
        WHERE id = %d
        LIMIT 1
    ', $plaatsID);

    //echo($dbSql); die();
       return DB::select($dbSql);

      }
    }