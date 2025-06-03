# MVP — Plateforme de Gestion des JPO

## Objectif
Livrer une plateforme fonctionnelle en 12 jours, permettant aux étudiants de s'inscrire à des Journées Portes Ouvertes (JPO), aux recruteurs de les gérer, et aux modérateurs d'interagir avec les avis/commentaires.

---

## Fonctionnalités essentielles à livrer

### 🔐 Authentification
- [ ] Inscription d’un utilisateur (étudiant, recruteur, admin…)
- [ ] Connexion avec session
- [ ] Déconnexion
- [ ] Gestion des rôles utilisateur (admin, modérateur, staff, étudiant)

### 📅 Gestion des JPO
- [ ] Liste des JPO accessibles publiquement
- [ ] Détail d’une JPO
- [ ] Inscription à une JPO
- [ ] Désinscription d’une JPO
- [ ] Capacité max configurable par JPO

### 📬 Notifications
- [ ] Envoi automatique d’un e-mail de rappel 24h avant l’événement (mock ou console pour MVP)

### 🔍 Moteur de recherche
- [ ] Recherche de JPO par lieu (Paris, Martigues, Cannes)

### 🗨️ Commentaires
- [ ] Ajouter un commentaire à une JPO (étudiant connecté)
- [ ] Réponse d’un modérateur à un commentaire
- [ ] Modération (suppression) des commentaires

### 📊 Dashboard (Recruteur)
- [ ] Ajout / édition / suppression d'une JPO
- [ ] Liste des inscrits à une JPO
- [ ] Statistiques simples : nombre d’inscrits / désinscrits
- [ ] Modification des contenus pratiques (sessions, infos pratiques)

### 🛡️ Sécurité & accès
- [ ] Accès restreint selon les rôles :
  - Étudiant : inscription, commentaires
  - Recruteur : gestion des JPO, modération
  - Admin : gestion des rôles
- [ ] Routes privées frontend (React Router)
- [ ] Sécurisation des API (sessions, vérification côté serveur)

### 🧪 QA & Documentation
- [ ] README avec instructions d’installation et usage
- [ ] Documentation API
- [ ] Tests manuels des principaux parcours

---

## Périmètre volontairement exclu du MVP
- Authentification via OAuth ou réseaux sociaux
- Back-office visuellement très raffiné
- Statistiques graphiques poussées
- Envoi d’emails réel (SMTP intégré)
