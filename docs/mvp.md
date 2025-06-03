# ✅ Définition du MVP

## 🎯 Objectif

Livrer une première version fonctionnelle du projet (Minimum Viable Product) en 12 jours répartis sur 4 sprints de 3 jours calendaires glissants.

Le MVP doit démontrer :
- Un **frontend React** moderne (Vite.js + Tailwind CSS + React Router)
- Un **backend PHP** structuré, accessible uniquement via des appels API
- Une **authentification utilisateur simple** (inscription, connexion, déconnexion)
- Une interface fonctionnelle avec **navigation client-side uniquement**

---

## 🧩 Fonctionnalités essentielles à livrer

### 🔗 Routage et Navigation
- Navigation gérée par **React Router** (pages : Accueil, Connexion, Inscription, Profil)
- Affichage conditionnel des liens selon l’état de connexion

### 👤 Authentification
- Formulaire d’inscription (React)
- API PHP `/api/register` pour créer un utilisateur
- Formulaire de connexion
- API PHP `/api/login` pour authentifier l'utilisateur
- Gestion de la session PHP (cookie ou token de session)
- API `/api/logout` pour la déconnexion

### 🔒 Accès protégé
- API `/api/profile` qui retourne les infos de l’utilisateur connecté
- Route `/profile` côté React accessible uniquement si connecté

### 🌐 Communication Front - Back
- Appels API via `fetch` ou `axios`
- Base URL configurable via `.env` (`VITE_API_URL`)
- Gestion des erreurs simples (auth échouée, redirection)

---

## 🧪 Validation du MVP

Le projet est **considéré comme validé à J+12** si les conditions suivantes sont remplies :

- [ ] L’utilisateur peut s’inscrire
- [ ] L’utilisateur peut se connecter
- [ ] L’utilisateur peut consulter son profil s’il est connecté
- [ ] L’utilisateur peut se déconnecter
- [ ] Les routes frontend sont entièrement gérées côté React
- [ ] Aucune page ne provoque un rechargement complet
- [ ] Les appels à l’API se font bien via le backend PHP

---

## 📁 Arborescence prévue

Voir `../README.md` pour le détail de la structure projet.

---

## 🔄 Évolutions possibles post-MVP

- Mise à jour du profil utilisateur
- Validation de formulaire côté client
- JWT ou token d'auth à la place des sessions PHP
- Persistance dans une vraie base de données (MySQL, SQLite)
  
---

## 🗂 Liens utiles

- [User Stories](./user-stories.md)
- [Structure du projet](../README.md)