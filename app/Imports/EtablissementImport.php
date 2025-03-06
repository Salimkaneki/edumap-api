<?php 

namespace App\Imports;

use App\Models\Etablissement;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EtablissementImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Etablissement([
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
            'code_etablissement' => $row['code_etablissement'],
            'libelle_type_milieu' => $row['libelle_type_milieu'],
            'region' => $row['region'],
            'prefecture' => $row['prefecture'],
            'canton_village_autonome' => $row['canton_village_autonome'],
            'ville_village_quartier' => $row['ville_village_quartier'],
            'nom_etablissement' => $row['nom_etablissement'],
            'libelle_type_statut_etab' => $row['libelle_type_statut_etab'],
            'libelle_type_systeme' => $row['libelle_type_systeme'],
            'existe_elect' => $row['existe_elect'],
            'existe_latrine' => $row['existe_latrine'],
            'existe_latrine_fonct' => $row['existe_latrine_fonct'],
            'acces_toute_saison' => $row['acces_toute_saison'],
            'eau' => $row['eau'],
            'sommedenb_eff_g' => $row['sommedenb_eff_g'],
            'sommedenb_eff_f' => $row['sommedenb_eff_f'],
            'tot' => $row['tot'],
            'sommedenb_ens_h' => $row['sommedenb_ens_h'],
            'sommedenb_ens_f' => $row['sommedenb_ens_f'],
            'total_ense' => $row['total_ense'],
            'sommedenb_salles_classes_dur' => $row['sommedenb_salles_classes_dur'],
            'sommedenb_salles_classes_banco' => $row['sommedenb_salles_classes_banco'],
            'sommedenb_salles_classes_autre' => $row['sommedenb_salles_classes_autre'],
            'libelle_type_annee' => $row['libelle_type_annee'],
            'commune_etab' => $row['commune_etab'],
        ]);
    }
}
