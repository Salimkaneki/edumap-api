<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->string('code_etablissement')->unique();
            $table->string('nom_etablissement');
            
            // Région avec des valeurs spécifiques
            $table->string('region');
            
            // Préfecture avec toutes les préfectures de Lomé en grand caractère
            $table->string('prefecture'); 
            
            // Canton / Village / Quartier (ou 'canton_village_autonome')
            $table->string('canton_village_autonome');
            $table->string('ville_village_quartier');
            
            // Type de milieu
            $table->string('libelle_type_milieu');
            
            // Statut de l'établissement
            $table->string('libelle_type_statut_etab');
            
            // Type du système
            $table->string('libelle_type_systeme');
            
            // Infrastructure
            $table->boolean('existe_elect')->default(false);
            $table->boolean('existe_latrine')->default(false);
            $table->boolean('existe_latrine_fonct')->default(false);
            $table->boolean('acces_toute_saison')->default(false);
            $table->boolean('eau')->default(false);
            
            // Données géographiques
            $table->string('latitude', 20);
            $table->string('longitude', 20);
            
            // Effectifs et enseignants
            $table->integer('sommedenb_eff_g')->default(0); // Effectif garçons
            $table->integer('sommedenb_eff_f')->default(0); // Effectif filles
            $table->integer('tot')->default(0);             // Total effectif
            $table->integer('sommedenb_ens_h')->default(0); // Enseignants hommes
            $table->integer('sommedenb_ens_f')->default(0); // Enseignantes femmes
            $table->integer('total_ense')->default(0);      // Total enseignants
            
            // Salles de classe
            $table->integer('sommedenb_salles_classes_dur')->default(0);  // Salles en dur
            $table->integer('sommedenb_salles_classes_banco')->default(0); // Salles en banco
            $table->integer('sommedenb_salles_classes_autre')->default(0); // Autres types de salles
            
            // Autres champs
            $table->string('libelle_type_annee')->nullable(); // Année scolaire
            $table->string('commune_etab')->nullable();       // Commune
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};