# 🧪 Guide de Test des APIs EduMap

## 📋 Endpoints Développés

### 🌐 Serveur de test
```
http://localhost:8000
```

---

## 🔓 APIs Publiques (Non authentifiées)

### 1. 📍 Liste des établissements avec filtres
```http
GET /api/v1/etablissements
```

**Paramètres de requête optionnels :**
- `page=1` - Numéro de page
- `per_page=20` - Nombre d'items par page (max 100)
- `region=Grand+Lomé` - Filtrer par région
- `statut=Public` - Filtrer par statut (Public/Privé laïc/Privé confessionnel)
- `systeme=Primaire` - Filtrer par système éducatif
- `milieu=Urbain` - Filtrer par milieu (Urbain/Rural)
- `search=lomé` - Recherche textuelle
- `avec_electricite=1` - Établissements avec électricité
- `avec_eau=1` - Établissements avec eau
- `avec_latrines=1` - Établissements avec latrines
- `effectif_min=50` - Effectif minimum
- `effectif_max=500` - Effectif maximum

**Exemple :**
```bash
curl "http://localhost:8000/api/v1/etablissements?region=Grand+Lomé&statut=Public&per_page=5"
```

### 2. 🗺️ Recherche par proximité
```http
GET /api/v1/etablissements/nearby?lat=6.1319&lng=1.2228&radius=10
```

**Paramètres requis :**
- `lat` - Latitude (entre -90 et 90)
- `lng` - Longitude (entre -180 et 180)

**Paramètres optionnels :**
- `radius=5` - Rayon en km (défaut: 5km, max: 50km)
- `statut=Public` - Filtrer par statut
- `systeme=Primaire` - Filtrer par système

**Exemple :**
```bash
curl "http://localhost:8000/api/v1/etablissements/nearby?lat=6.1319&lng=1.2228&radius=5&statut=Public"
```

### 3. 🏫 Détail d'un établissement
```http
GET /api/v1/etablissements/{id}
```

**Exemple :**
```bash
curl "http://localhost:8000/api/v1/etablissements/1"
```

### 4. 📊 Statistiques publiques
```http
GET /api/v1/statistics
```

**Exemple :**
```bash
curl "http://localhost:8000/api/v1/statistics"
```

### 5. 📚 Données de référence
```http
GET /api/v1/references/{type}
```

**Types disponibles :**
- `regions` - Liste des régions
- `statuts` - Liste des statuts d'établissement
- `systemes` - Liste des systèmes éducatifs
- `milieux` - Liste des milieux (Urbain/Rural)
- `prefectures` - Liste des préfectures

**Exemples :**
```bash
curl "http://localhost:8000/api/v1/references/regions"
curl "http://localhost:8000/api/v1/references/statuts"
```

---

## 🔐 APIs d'Administration (Authentifiées)

### 1. 🔑 Authentification

#### Connexion admin
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "admin@edumap.tg",
    "password": "admin123"
}
```

**Exemple avec curl :**
```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@edumap.tg","password":"admin123"}'
```

**Réponse :**
```json
{
    "token": "1|xxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "Administrateur EduMap",
        "email": "admin@edumap.tg",
        "role": "admin"
    }
}
```

#### Déconnexion
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Informations utilisateur connecté
```http
GET /api/auth/user
Authorization: Bearer {token}
```

### 2. 📊 Dashboard Admin
```http
GET /api/admin/dashboard
Authorization: Bearer {token}
```

### 3. 🏫 Gestion des établissements

#### Liste admin des établissements
```http
GET /api/admin/etablissements
Authorization: Bearer {token}
```

#### Créer un établissement
```http
POST /api/admin/etablissements
Authorization: Bearer {token}
Content-Type: application/json

{
    "code_etablissement": "TG999999",
    "nom_etablissement": "École Test",
    "region": "Grand Lomé",
    "prefecture": "Golfe",
    "canton_village_autonome": "Lomé",
    "ville_village_quartier": "Bè",
    "libelle_type_milieu": "Urbain",
    "libelle_type_statut_etab": "Public",
    "libelle_type_systeme": "Primaire",
    "latitude": 6.1319,
    "longitude": 1.2228,
    "existe_elect": true,
    "eau": true,
    "existe_latrine": true,
    "tot": 150,
    "total_ense": 6
}
```

#### Modifier un établissement
```http
PUT /api/admin/etablissements/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "nom_etablissement": "École Test Modifiée",
    "tot": 160
}
```

#### Supprimer un établissement
```http
DELETE /api/admin/etablissements/{id}
Authorization: Bearer {token}
```

### 4. 🔄 Actions en masse

#### Suppression en masse
```http
DELETE /api/admin/etablissements/bulk-delete
Authorization: Bearer {token}
Content-Type: application/json

{
    "ids": [1, 2, 3]
}
```

#### Mise à jour en masse
```http
PUT /api/admin/etablissements/bulk-update
Authorization: Bearer {token}
Content-Type: application/json

{
    "ids": [1, 2, 3],
    "updates": {
        "existe_elect": true,
        "eau": true
    }
}
```

### 5. 👥 Gestion des utilisateurs

#### Liste des utilisateurs
```http
GET /api/admin/users
Authorization: Bearer {token}
```

#### Créer un utilisateur
```http
POST /api/admin/users
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Nouvel Admin",
    "email": "nouvel@edumap.tg",
    "password": "password123",
    "role": "moderateur",
    "is_active": true
}
```

---

## 🧪 Script de Test Complet

Créez un fichier `test-api.sh` ou `test-api.bat` :

```bash
#!/bin/bash

# Couleurs pour l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000"

echo -e "${YELLOW}🧪 Test des APIs EduMap${NC}"

# Test 1: Connexion admin
echo -e "\n${YELLOW}1. Test de connexion admin...${NC}"
RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@edumap.tg","password":"admin123"}')

TOKEN=$(echo $RESPONSE | grep -o '"token":"[^"]*' | grep -o '[^"]*$')

if [ ! -z "$TOKEN" ]; then
    echo -e "${GREEN}✅ Connexion réussie${NC}"
    echo "Token: $TOKEN"
else
    echo -e "${RED}❌ Échec de la connexion${NC}"
    echo $RESPONSE
    exit 1
fi

# Test 2: Statistiques publiques
echo -e "\n${YELLOW}2. Test des statistiques publiques...${NC}"
curl -s "$BASE_URL/api/v1/statistics" | jq '.'

# Test 3: Liste des régions
echo -e "\n${YELLOW}3. Test des références (régions)...${NC}"
curl -s "$BASE_URL/api/v1/references/regions" | jq '.'

# Test 4: Dashboard admin
echo -e "\n${YELLOW}4. Test du dashboard admin...${NC}"
curl -s "$BASE_URL/api/admin/dashboard" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Test 5: Liste des établissements publics
echo -e "\n${YELLOW}5. Test de la liste publique des établissements...${NC}"
curl -s "$BASE_URL/api/v1/etablissements?per_page=3" | jq '.'

echo -e "\n${GREEN}🎉 Tests terminés !${NC}"
```

---

## 📝 Comptes de Test

### Administrateur
- **Email :** `admin@edumap.tg`
- **Mot de passe :** `admin123`
- **Rôle :** admin (toutes permissions)

### Modérateur
- **Email :** `moderateur@edumap.tg`
- **Mot de passe :** `moderateur123`
- **Rôle :** moderateur (gestion établissements uniquement)

---

## 🚀 Prochaines Étapes

1. **Tester les APIs** avec Postman ou curl
2. **Importer vos données** avec le script Python corrigé
3. **Développer le frontend** Vue.js/React
4. **Ajouter les fonctionnalités** de carte interactive
5. **Optimiser les performances** avec Redis

**L'API EduMap est maintenant fonctionnelle ! 🎯**
