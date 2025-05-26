## ✅ Objectifs réalisés

## 🔧 0. Complétion de l'import

- ✔ Importation depuis un fichier CSV.
- ✔ Optimisation du traitement en batch (`flush()` toutes les 100 entrées).
- ✔ Utilisation de `Symfony Profiler` pour mesurer les performances.
- ✔ Import automatisé avec la commande :
  ```bash
  bin/console import:card
  ```

---

## 📝 1. Ajout de logs

- ✔ Utilisation de `LoggerInterface` pour tracer :
  - Chaque appel aux routes API (GET `/api/card`, `/search`, etc).

Logs visibles dans :  
`var/log/dev.log`
---

## 🔎 2. Recherche de cartes

- ✔ Page de recherche ajoutée.
- ✔ Route API : `GET /api/card/search?q=...`
- ✔ Résultats limités à 20 cartes.
- ✔ Paramètre optionnel `setCode` pour filtrer.
- ✔ Documenté avec OpenAPI/Swagger.

---

## 🧪 3. Filtres

- ✔ Filtres disponibles dans l'API :
  - `setCode` (via `/api/card/search`)
  - Tous les `setCode` listés via :
    ```http
    GET /api/card/set-codes
    ```
---

## 📚 4. Pagination

- ✔ Pagination activée sur la liste des cartes.
- ✔ Route `GET /api/card?page=...&setCode=...`
---
