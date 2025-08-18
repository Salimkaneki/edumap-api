<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Dashboard administrateur
     */
    public function dashboard()
    {
        $stats = [
            'total_etablissements' => Etablissement::count(),
            'ajouts_ce_mois' => Etablissement::whereRaw('MONTH(created_at) = ? AND YEAR(created_at) = ?', [
                date('n'), 
                date('Y')
            ])->count(),
            'modifications_ce_mois' => Etablissement::whereRaw('MONTH(updated_at) = ? AND YEAR(updated_at) = ?', [
                date('n'), 
                date('Y')
            ])->where('created_at', '!=', DB::raw('updated_at'))->count(),
        ];

        // Activités récentes (simulé pour l'instant)
        $recent_activities = Etablissement::latest()
            ->limit(10)
            ->get(['id', 'nom_etablissement', 'created_at', 'updated_at'])
            ->map(function ($etablissement) {
                return [
                    'id' => $etablissement->id,
                    'etablissement' => $etablissement->nom_etablissement,
                    'action' => $etablissement->created_at->eq($etablissement->updated_at) ? 'created' : 'updated',
                    'date' => $etablissement->updated_at->toISOString(),
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recent_activities' => $recent_activities
        ]);
    }

    /**
     * Liste des établissements pour l'admin (avec plus de détails)
     */
    public function etablissements(Request $request)
    {
        $query = Etablissement::query();

        // Même logique de filtres que PublicController mais avec plus de données
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('region')) {
            $query->byRegion($request->region);
        }

        if ($request->filled('statut')) {
            $query->byStatut($request->statut);
        }

        // Tri
        $orderBy = $request->get('order_by', 'id');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = min($request->get('per_page', 20), 100);
        $etablissements = $query->paginate($perPage);

        return response()->json($etablissements);
    }

    /**
     * Créer un établissement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_etablissement' => 'required|string|unique:etablissements',
            'nom_etablissement' => 'required|string|max:255',
            'region' => 'required|string',
            'prefecture' => 'required|string',
            'commune_etab' => 'nullable|string',
            'canton_village_autonome' => 'required|string',
            'ville_village_quartier' => 'required|string',
            'libelle_type_milieu' => 'required|string',
            'libelle_type_statut_etab' => 'required|string',
            'libelle_type_systeme' => 'required|string',
            'libelle_type_annee' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'existe_elect' => 'boolean',
            'existe_latrine' => 'boolean',
            'existe_latrine_fonct' => 'boolean',
            'acces_toute_saison' => 'boolean',
            'eau' => 'boolean',
            'sommedenb_eff_g' => 'nullable|integer|min:0',
            'sommedenb_eff_f' => 'nullable|integer|min:0',
            'tot' => 'nullable|integer|min:0',
            'sommedenb_ens_h' => 'nullable|integer|min:0',
            'sommedenb_ens_f' => 'nullable|integer|min:0',
            'total_ense' => 'nullable|integer|min:0',
            'sommedenb_salles_classes_dur' => 'nullable|integer|min:0',
            'sommedenb_salles_classes_banco' => 'nullable|integer|min:0',
            'sommedenb_salles_classes_autre' => 'nullable|integer|min:0',
        ]);

        $etablissement = Etablissement::create($validated);

        // Vider le cache des statistiques
        Cache::forget('public_statistics');

        return response()->json($etablissement, 201);
    }

    /**
     * Mettre à jour un établissement
     */
    public function update(Request $request, $id)
    {
        $etablissement = Etablissement::findOrFail($id);

        $validated = $request->validate([
            'code_etablissement' => 'sometimes|string|unique:etablissements,code_etablissement,' . $id,
            'nom_etablissement' => 'sometimes|string|max:255',
            'region' => 'sometimes|string',
            'prefecture' => 'sometimes|string',
            'commune_etab' => 'nullable|string',
            'canton_village_autonome' => 'sometimes|string',
            'ville_village_quartier' => 'sometimes|string',
            'libelle_type_milieu' => 'sometimes|string',
            'libelle_type_statut_etab' => 'sometimes|string',
            'libelle_type_systeme' => 'sometimes|string',
            'libelle_type_annee' => 'nullable|string',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'existe_elect' => 'boolean',
            'existe_latrine' => 'boolean',
            'existe_latrine_fonct' => 'boolean',
            'acces_toute_saison' => 'boolean',
            'eau' => 'boolean',
            'sommedenb_eff_g' => 'nullable|integer|min:0',
            'sommedenb_eff_f' => 'nullable|integer|min:0',
            'tot' => 'nullable|integer|min:0',
            'sommedenb_ens_h' => 'nullable|integer|min:0',
            'sommedenb_ens_f' => 'nullable|integer|min:0',
            'total_ense' => 'nullable|integer|min:0',
            'sommedenb_salles_classes_dur' => 'nullable|integer|min:0',
            'sommedenb_salles_classes_banco' => 'nullable|integer|min:0',
            'sommedenb_salles_classes_autre' => 'nullable|integer|min:0',
        ]);

        $etablissement->update($validated);

        // Vider le cache des statistiques
        Cache::forget('public_statistics');

        return response()->json($etablissement);
    }

    /**
     * Supprimer un établissement
     */
    public function destroy($id)
    {
        $etablissement = Etablissement::findOrFail($id);
        $etablissement->delete();

        // Vider le cache des statistiques
        Cache::forget('public_statistics');

        return response()->json(['message' => 'Établissement supprimé avec succès']);
    }

    /**
     * Suppression en masse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:etablissements,id'
        ]);

        $deleted = Etablissement::whereIn('id', $request->ids)->delete();

        // Vider le cache des statistiques
        Cache::forget('public_statistics');

        return response()->json([
            'message' => "{$deleted} établissement(s) supprimé(s) avec succès"
        ]);
    }

    /**
     * Mise à jour en masse
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:etablissements,id',
            'updates' => 'required|array',
        ]);

        $updated = Etablissement::whereIn('id', $request->ids)
            ->update($request->updates);

        // Vider le cache des statistiques
        Cache::forget('public_statistics');

        return response()->json([
            'message' => "{$updated} établissement(s) mis à jour avec succès"
        ]);
    }

    /**
     * Export des données (simplifié)
     */
    public function export(Request $request)
    {
        // Version simplifiée - à améliorer plus tard avec Excel
        $query = Etablissement::query();
        
        if ($request->filled('region')) {
            $query->byRegion($request->region);
        }
        
        if ($request->filled('statut')) {
            $query->byStatut($request->statut);
        }

        $etablissements = $query->get();
        
        return response()->json([
            'message' => 'Export préparé',
            'count' => $etablissements->count(),
            'data' => $etablissements
        ]);
    }

    /**
     * Import des données (simplifié)
     */
    public function import(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'mode' => 'required|in:create,update,replace'
        ]);

        try {
            $imported = 0;
            $updated = 0;
            $errors = [];

            foreach ($request->data as $item) {
                try {
                    if ($request->mode === 'create') {
                        Etablissement::create($item);
                        $imported++;
                    } elseif ($request->mode === 'update' && isset($item['id'])) {
                        $etablissement = Etablissement::find($item['id']);
                        if ($etablissement) {
                            $etablissement->update($item);
                            $updated++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Erreur ligne {$imported}: " . $e->getMessage();
                }
            }

            // Vider le cache des statistiques
            Cache::forget('public_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Import réalisé avec succès',
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gestion des utilisateurs
     */
    public function users()
    {
        $users = User::select('id', 'name', 'email', 'role', 'is_active', 'created_at')
            ->paginate(20);

        return response()->json($users);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,moderateur',
            'is_active' => 'boolean'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|min:8',
            'role' => 'sometimes|in:admin,moderateur',
            'is_active' => 'boolean'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);
    }

    public function deleteUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la suppression de son propre compte
        if ($user->id === $request->user()->id) {
            return response()->json(['error' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }
}
