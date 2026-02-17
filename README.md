# TokenCurrency
Token in Blockchain ( PHP persit DATA  BD ) -Simulation
# 💎 DahnizToken - Ecosystem Simulation

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-purple)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)

**DahnizToken** est une simulation d'écosystème blockchain développée par **AhniZTech**. Elle permet de gérer des identités numériques (Principal IDs) et d'effectuer des transactions de jetons virtuels (DAHNIZ) sur une interface Web moderne.

---

## 🚀 Fonctionnalités principales

* **Identité Blockchain** : Génération automatique d'un `Principal ID` unique à l'inscription.
* **Faucet (Robinet)** : Réclamez 10 000 DAHNIZ pour démarrer (limité à une fois par compte).
* **Transferts Sécurisés** : Envoyez des jetons à d'autres membres via leur ID avec vérification du solde en temps réel.
* **Historique des Transactions** : Visualisation des 5 dernières activités (envois et réceptions).

---

## 📂 Structure du Projet

Le projet est structuré de manière modulaire pour une maintenance facile :

| Fichier | Rôle |
| :--- | :--- |
| `db.php` | Connexion à la base de données `u866442872_MyDatabase`. |
| `auth.php` | Système d'inscription et de connexion sécurisé. |
| `index.php` | Tableau de bord principal (Dashboard). |
| `logout.php` | Déconnexion et destruction de la session. |
| `style.css` | Design unifié et responsive (AhniZTech Style). |

---

## 🛠️ Installation & Configuration

1. **Base de données** : Importez le schéma SQL suivant dans votre interface MySQL :
   - Table `users_dahniztoken` : Stocke les utilisateurs, soldes et IDs.
   - Table `transactions_dahniztoken` : Enregistre l'historique des échanges.

2. **Configuration** : Modifiez les identifiants de connexion dans `db.php` pour qu'ils correspondent à votre environnement.

3. **Déploiement** : Placez les fichiers dans votre répertoire `public_html/dahniztoken/`.

---

## 👤 Auteur

**Mr. Hanifi Khelaf** - *CEO @ AhniZTech*
- **Site Web** : [khelaf-hanifi.com](https://khelaf-hanifi.com/)
- **Projet** : DahnizToken Ecosystem

---
© 2000-2026 ® **AhniZTech** - Tous droits réservés.
