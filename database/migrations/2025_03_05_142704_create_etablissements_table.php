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
            $table->enum('region', [
                'PLATEAUX EST', 'PLATEAUX OUEST', 'CENTRE', 'SAVANES', 'KARA', 'GRAND LOME'
            ]);
            
            // Préfecture avec toutes les préfectures de Lomé en grand caractère
            $table->string('prefecture'); 
            
            // Canton / Village / Quartier (ou 'canton_village_autonome')
            $table->string('canton_village_autonome');
            $table->string('ville_village_quartier');
            
            // Type de milieu
            $table->enum('libelle_type_milieu', ['Urbain', 'Rural', 'Semi urbain']);
            
            // Statut de l'établissement
            $table->enum('libelle_type_statut_etab', [
                'Privé Laïc', 'Public', 'Privé Protestant', 'Privé Catholique', 'Privé Islamique'
            ]);
            
            // Type du système
            $table->enum('libelle_type_systeme', [
                'SECONDAIRE I', 'SECONDAIRE II', 'PRIMAIRE', 'PRESCOLAIRE'
            ]);
            
            // Infrastructure
            $table->boolean('existe_elect');
            $table->boolean('existe_latrine');
            $table->boolean('existe_latrine_fonct');
            $table->boolean('acces_toute_saison');
            $table->boolean('eau');
            
            // Données géographiques
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Autres informations
            $table->integer('sommedenb_eff_g');
            $table->integer('sommedenb_eff_f');
            $table->integer('tot');
            $table->integer('sommedenb_ens_h');
            $table->integer('sommedenb_ens_f');
            $table->integer('total_ense');
            $table->integer('sommedenb_salles_classes_dur');
            $table->integer('sommedenb_salles_classes_banco');
            $table->integer('sommedenb_salles_classes_autre');
            
            // Autres champs
            $table->string('libelle_type_annee');
            $table->string('commune_etab');
            
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
