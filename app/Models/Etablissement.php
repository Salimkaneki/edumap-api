<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_etablissement', 'nom_etablissement', 'region', 'prefecture', 
        'canton_village_autonome', 'ville_village_quartier', 'libelle_type_milieu', 
        'libelle_type_statut_etab', 'libelle_type_systeme', 'existe_elect', 
        'existe_latrine', 'existe_latrine_fonct', 'acces_toute_saison', 'eau', 
        'latitude', 'longitude', 'sommedenb_eff_g', 'sommedenb_eff_f', 'tot', 
        'sommedenb_ens_h', 'sommedenb_ens_f', 'total_ense', 
        'sommedenb_salles_classes_dur', 'sommedenb_salles_classes_banco', 
        'sommedenb_salles_classes_autre', 'libelle_type_annee', 'commune_etab'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'existe_elect' => 'boolean',
        'existe_latrine' => 'boolean',
        'existe_latrine_fonct' => 'boolean',
        'acces_toute_saison' => 'boolean',
        'eau' => 'boolean',
        'sommedenb_eff_g' => 'integer',
        'sommedenb_eff_f' => 'integer',
        'tot' => 'integer',
        'sommedenb_ens_h' => 'integer',
        'sommedenb_ens_f' => 'integer',
        'total_ense' => 'integer',
        'sommedenb_salles_classes_dur' => 'integer',
        'sommedenb_salles_classes_banco' => 'integer',
        'sommedenb_salles_classes_autre' => 'integer',
    ];

    // Scopes pour les filtres fréquents
    public function scopeByRegion($query, $region) {
        return $query->where('region', $region);
    }

    public function scopeByStatut($query, $statut) {
        return $query->where('libelle_type_statut_etab', $statut);
    }

    public function scopeBySysteme($query, $systeme) {
        return $query->where('libelle_type_systeme', $systeme);
    }

    public function scopeByMilieu($query, $milieu) {
        return $query->where('libelle_type_milieu', $milieu);
    }

    public function scopeWithElectricity($query) {
        return $query->where('existe_elect', 1);
    }

    public function scopeWithWater($query) {
        return $query->where('eau', 1);
    }

    public function scopeWithLatrines($query) {
        return $query->where('existe_latrine', 1);
    }

    public function scopeSearch($query, $search) {
        return $query->where(function($q) use ($search) {
            $q->where('nom_etablissement', 'like', '%' . $search . '%')
              ->orWhere('ville_village_quartier', 'like', '%' . $search . '%')
              ->orWhere('canton_village_autonome', 'like', '%' . $search . '%');
        });
    }

    public function scopeNearby($query, $lat, $lng, $radius = 10) {
        return $query->selectRaw("*, 
            (6371 * acos(cos(radians(?)) 
            * cos(radians(latitude)) 
            * cos(radians(longitude) - radians(?)) 
            + sin(radians(?)) 
            * sin(radians(latitude)))) AS distance", 
            [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->orderBy('distance');
    }

    public function scopeEffectifBetween($query, $min, $max) {
        return $query->whereBetween('tot', [$min, $max]);
    }

    // Accesseurs pour formater les données
    public function getInfrastructuresAttribute() {
        return [
            'electricite' => $this->existe_elect,
            'eau' => $this->eau,
            'latrines' => $this->existe_latrine,
            'latrines_fonctionnelles' => $this->existe_latrine_fonct,
            'acces_toute_saison' => $this->acces_toute_saison,
        ];
    }

    public function getEffectifsAttribute() {
        return [
            'eleves_garcons' => $this->sommedenb_eff_g,
            'eleves_filles' => $this->sommedenb_eff_f,
            'total_eleves' => $this->tot,
            'enseignants_hommes' => $this->sommedenb_ens_h,
            'enseignants_femmes' => $this->sommedenb_ens_f,
            'total_enseignants' => $this->total_ense,
        ];
    }

    public function getSallesAttribute() {
        return [
            'dur' => $this->sommedenb_salles_classes_dur,
            'banco' => $this->sommedenb_salles_classes_banco,
            'autres' => $this->sommedenb_salles_classes_autre,
            'total' => $this->sommedenb_salles_classes_dur + $this->sommedenb_salles_classes_banco + $this->sommedenb_salles_classes_autre,
        ];
    }

    public function getLocalisationAttribute() {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'region' => $this->region,
            'prefecture' => $this->prefecture,
            'commune' => $this->commune_etab,
            'ville_village' => $this->ville_village_quartier,
            'canton_village' => $this->canton_village_autonome,
            'milieu' => $this->libelle_type_milieu,
        ];
    }
}
