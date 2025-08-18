<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    /**
     * Liste des établissements avec pagination et filtres
     */
    public function etablissements(Request $request)
    {
        $query = Etablissement::query();

        // Filtres de base
        if ($request->filled('region')) {
            $query->byRegion($request->region);
        }

        if ($request->filled('statut')) {
            $query->byStatut($request->statut);
        }

        if ($request->filled('systeme')) {
            $query->bySysteme($request->systeme);
        }

        if ($request->filled('milieu')) {
            $query->byMilieu($request->milieu);
        }

        // Recherche textuelle
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtres infrastructures
        if ($request->boolean('avec_electricite')) {
            $query->withElectricity();
        }

        if ($request->boolean('avec_eau')) {
            $query->withWater();
        }

        if ($request->boolean('avec_latrines')) {
            $query->withLatrines();
        }

        // Filtres effectifs
        if ($request->filled('effectif_min') && $request->filled('effectif_max')) {
            $query->effectifBetween($request->effectif_min, $request->effectif_max);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100); // Max 100 par page
        
        $etablissements = $query->select([
            'id', 'nom_etablissement', 'latitude', 'longitude', 'region', 
            'prefecture', 'libelle_type_statut_etab as statut', 
            'libelle_type_systeme as systeme', 'libelle_type_milieu as milieu',
            'tot', 'total_ense', 'existe_elect', 'eau', 'existe_latrine'
        ])->paginate($perPage);

        // Transformer les données pour le front
        $etablissements->getCollection()->transform(function ($etablissement) {
            return [
                'id' => $etablissement->id,
                'nom_etablissement' => $etablissement->nom_etablissement,
                'latitude' => $etablissement->latitude,
                'longitude' => $etablissement->longitude,
                'region' => $etablissement->region,
                'prefecture' => $etablissement->prefecture,
                'statut' => $etablissement->statut,
                'systeme' => $etablissement->systeme,
                'milieu' => $etablissement->milieu,
                'total_eleves' => $etablissement->tot,
                'total_enseignants' => $etablissement->total_ense,
                'infrastructures' => [
                    'electricite' => $etablissement->existe_elect,
                    'eau' => $etablissement->eau,
                    'latrines' => $etablissement->existe_latrine,
                ]
            ];
        });

        return response()->json($etablissements);
    }

    /**
     * Recherche par proximité géographique
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:50'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->get('radius', 5); // 5km par défaut

        $query = Etablissement::nearby($lat, $lng, $radius);

        // Appliquer les filtres optionnels
        if ($request->filled('statut')) {
            $query->byStatut($request->statut);
        }

        if ($request->filled('systeme')) {
            $query->bySysteme($request->systeme);
        }

        $etablissements = $query->limit(50)->get(); // Limiter à 50 résultats

        return response()->json([
            'data' => $etablissements->map(function ($etablissement) {
                return [
                    'id' => $etablissement->id,
                    'nom_etablissement' => $etablissement->nom_etablissement,
                    'latitude' => $etablissement->latitude,
                    'longitude' => $etablissement->longitude,
                    'distance' => round($etablissement->distance, 2),
                    'statut' => $etablissement->libelle_type_statut_etab,
                    'systeme' => $etablissement->libelle_type_systeme,
                    'total_eleves' => $etablissement->tot,
                ];
            })
        ]);
    }

    /**
     * Détail d'un établissement
     */
    public function show($id)
    {
        $etablissement = Etablissement::find($id);

        if (!$etablissement) {
            return response()->json(['error' => 'Établissement non trouvé'], 404);
        }

        return response()->json([
            'id' => $etablissement->id,
            'code_etablissement' => $etablissement->code_etablissement,
            'nom_etablissement' => $etablissement->nom_etablissement,
            'localisation' => $etablissement->localisation,
            'caracteristiques' => [
                'statut' => $etablissement->libelle_type_statut_etab,
                'systeme' => $etablissement->libelle_type_systeme,
                'milieu' => $etablissement->libelle_type_milieu,
                'annee' => $etablissement->libelle_type_annee,
            ],
            'effectifs' => $etablissement->effectifs,
            'infrastructures' => $etablissement->infrastructures,
            'salles' => $etablissement->salles,
        ]);
    }

    /**
     * Statistiques publiques
     */
    public function statistics()
    {
        $stats = Cache::remember('public_statistics', 3600, function () {
            return [
                'total_etablissements' => Etablissement::count(),
                'total_eleves' => Etablissement::sum('tot'),
                'total_enseignants' => Etablissement::sum('total_ense'),
                'repartition_statut' => Etablissement::select('libelle_type_statut_etab as statut')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('libelle_type_statut_etab')
                    ->pluck('count', 'statut'),
                'repartition_regions' => Etablissement::select('region')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('region')
                    ->pluck('count', 'region'),
                'repartition_systemes' => Etablissement::select('libelle_type_systeme as systeme')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('libelle_type_systeme')
                    ->pluck('count', 'systeme'),
                'infrastructures' => [
                    'avec_electricite' => Etablissement::where('existe_elect', 1)->count(),
                    'avec_eau' => Etablissement::where('eau', 1)->count(),
                    'avec_latrines' => Etablissement::where('existe_latrine', 1)->count(),
                    'acces_toute_saison' => Etablissement::where('acces_toute_saison', 1)->count(),
                ]
            ];
        });

        return response()->json($stats);
    }

    /**
     * Données de référence pour les filtres
     */
    public function references($type)
    {
        $data = Cache::remember("references_{$type}", 7200, function () use ($type) {
            switch ($type) {
                case 'regions':
                    return Etablissement::distinct()->pluck('region')->filter()->sort()->values();
                case 'statuts':
                    return Etablissement::distinct()->pluck('libelle_type_statut_etab')->filter()->sort()->values();
                case 'systemes':
                    return Etablissement::distinct()->pluck('libelle_type_systeme')->filter()->sort()->values();
                case 'milieux':
                    return Etablissement::distinct()->pluck('libelle_type_milieu')->filter()->sort()->values();
                case 'prefectures':
                    return Etablissement::distinct()->pluck('prefecture')->filter()->sort()->values();
                default:
                    return [];
            }
        });

        return response()->json($data);
    }
}
