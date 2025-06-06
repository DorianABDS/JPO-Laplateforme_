# 📋 Guide d'affichage des tables de base de données

Ce guide vous explique comment afficher les données de votre base MySQL dans vos composants React.

## 📦 Installation

1. Assurez-vous d'avoir le fichier `fetchData.js` dans `src/services/`
2. Importez les fonctions dans vos composants

## 🎯 Syntaxe de base

### Import
```javascript
import { useFetchData, useUsers, useCampus } from '../services/fetchData.js';
```

### Affichage automatique
```javascript
const { data, loading, error } = useFetchData('/api/nom-de-votre-table');
```

---

## 📊 Tables disponibles

| Table | Endpoint | Hook spécialisé | Description |
|-------|----------|----------------|-------------|
| `user` | `/api/users` | `useUsers()` | Tous les utilisateurs |
| `open_day` | `/api/jpo` | `useFetchData('/api/jpo')` | Journées Portes Ouvertes |
| `campus` | `/api/campus` | `useCampus()` | Tous les campus |
| `registration` | `/api/registrations` | `useFetchData('/api/registrations')` | Inscriptions aux JPO |
| `comment` | `/api/comments` | `useFetchData('/api/comments')` | Commentaires |
| `role` | `/api/roles` | `useFetchData('/api/roles')` | Rôles utilisateur |

---

## 💡 Exemples d'utilisation

### 1. Afficher tous les utilisateurs
```javascript
const UserList = () => {
  const { data: users, loading, error } = useUsers();

  if (loading) return <div>Chargement...</div>;
  if (error) return <div>Erreur: {error}</div>;

  return (
    <div>
      <h2>Utilisateurs ({users?.users?.length || 0})</h2>
      {users?.users?.map(user => (
        <div key={user.user_id}>
          <h3>{user.first_name} {user.last_name}</h3>
          <p>Email: {user.email}</p>
          <p>Type: {user.user_type}</p>
        </div>
      ))}
    </div>
  );
};
```

### 2. Afficher toutes les JPO
```javascript
const JpoList = () => {
  const { data: jpos, loading } = useFetchData('/api/jpo');

  if (loading) return <div>Chargement des JPO...</div>;

  return (
    <div>
      <h2>Journées Portes Ouvertes</h2>
      {jpos?.map(jpo => (
        <div key={jpo.jpo_id}>
          <h3>{jpo.name}</h3>
          <p>Date: {jpo.date}</p>
          <p>Capacité: {jpo.max_capacity} personnes</p>
        </div>
      ))}
    </div>
  );
};
```

### 3. Afficher tous les campus
```javascript
const CampusList = () => {
  const { data: campus, loading } = useCampus();

  return (
    <div>
      <h2>Nos Campus</h2>
      {loading ? (
        <p>Chargement...</p>
      ) : (
        campus?.campus?.map(camp => (
          <div key={camp.campus_id}>
            <h3>{camp.name}</h3>
            <p>Ville: {camp.city}</p>
          </div>
        ))
      )}
    </div>
  );
};
```

### 4. Afficher les commentaires
```javascript
const CommentsList = () => {
  const { data: comments, loading } = useFetchData('/api/comments');

  return (
    <div>
      <h2>Commentaires</h2>
      {comments?.map(comment => (
        <div key={comment.comment_id} className="border p-4 mb-2">
          <p>{comment.content}</p>
          <small>Par utilisateur ID: {comment.user_id}</small>
          <small>Date: {comment.comment_date}</small>
        </div>
      ))}
    </div>
  );
};
```

---

## 🔍 Affichage avec filtres

### Utilisateurs par type
```javascript
const StudentList = () => {
  const { data: students } = useFetchData('/api/users', { 
    user_type: 'student' 
  });

  return (
    <div>
      <h2>Étudiants</h2>
      {students?.users?.map(student => (
        <div key={student.user_id}>
          {student.first_name} {student.last_name}
        </div>
      ))}
    </div>
  );
};
```

### JPO par campus
```javascript
const JpoByEtablissement = () => {
  const { data: jpoMarseille } = useFetchData('/api/jpo', { 
    campus_id: 1 
  });

  return (
    <div>
      <h2>JPO Marseille</h2>
      {jpoMarseille?.map(jpo => (
        <div key={jpo.jpo_id}>{jpo.name}</div>
      ))}
    </div>
  );
};
```

### Inscriptions confirmées
```javascript
const ConfirmedRegistrations = () => {
  const { data: confirmed } = useFetchData('/api/registrations', { 
    status: 'registered' 
  });

  return (
    <div>
      <h2>Inscriptions confirmées</h2>
      {confirmed?.registrations?.map(reg => (
        <div key={reg.registration_id}>
          Utilisateur {reg.user_id} → JPO {reg.jpo_id}
        </div>
      ))}
    </div>
  );
};
```

---

## 🎯 Affichage d'un élément spécifique

### Un utilisateur précis
```javascript
const UserProfile = ({ userId }) => {
  const { data: user, loading } = useFetchData(`/api/user/${userId}`);

  if (loading) return <div>Chargement du profil...</div>;

  return (
    <div>
      <h2>Profil de {user?.first_name} {user?.last_name}</h2>
      <p>Email: {user?.email}</p>
      <p>Type: {user?.user_type}</p>
      <p>Inscrit le: {user?.created_at}</p>
    </div>
  );
};
```

### Une JPO précise
```javascript
const JpoDetails = ({ jpoId }) => {
  const { data: jpo, loading } = useFetchData(`/api/jpo/${jpoId}`);

  if (loading) return <div>Chargement...</div>;

  return (
    <div>
      <h2>{jpo?.name}</h2>
      <p>Date: {jpo?.date}</p>
      <p>Campus: {jpo?.campus_name}</p>
      <p>Capacité: {jpo?.max_capacity} personnes</p>
    </div>
  );
};
```

---

## 🔄 Chargement manuel (avec bouton)

```javascript
const DataOnDemand = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);

  const loadUsers = async () => {
    try {
      setLoading(true);
      const data = await fetchData('/api/users');
      setUsers(data.users || []);
    } catch (error) {
      console.error('Erreur:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <button 
        onClick={loadUsers}
        disabled={loading}
        className="bg-blue-500 text-white px-4 py-2 rounded"
      >
        {loading ? 'Chargement...' : 'Charger les utilisateurs'}
      </button>
      
      <div>
        {users.map(user => (
          <div key={user.user_id}>
            {user.first_name} {user.last_name}
          </div>
        ))}
      </div>
    </div>
  );
};
```

---

## 📊 Dashboard avec toutes les données

```javascript
const Dashboard = () => {
  const { data: users } = useUsers();
  const { data: jpos } = useFetchData('/api/jpo');
  const { data: campus } = useCampus();
  const { data: registrations } = useFetchData('/api/registrations');

  return (
    <div className="grid grid-cols-4 gap-4">
      <div className="bg-blue-100 p-4 rounded">
        <h3>Utilisateurs</h3>
        <p className="text-2xl font-bold">{users?.users?.length || 0}</p>
      </div>
      
      <div className="bg-green-100 p-4 rounded">
        <h3>JPO</h3>
        <p className="text-2xl font-bold">{jpos?.length || 0}</p>
      </div>
      
      <div className="bg-purple-100 p-4 rounded">
        <h3>Campus</h3>
        <p className="text-2xl font-bold">{campus?.campus?.length || 0}</p>
      </div>
      
      <div className="bg-orange-100 p-4 rounded">
        <h3>Inscriptions</h3>
        <p className="text-2xl font-bold">{registrations?.registrations?.length || 0}</p>
      </div>
    </div>
  );
};
```

---

## ⚙️ Configuration côté PHP

Pour que ces exemples fonctionnent, vous devez créer les endpoints PHP correspondants :

```php
// /api/users - Retourne tous les utilisateurs
// /api/jpo - Retourne toutes les JPO
// /api/campus - Retourne tous les campus
// /api/registrations - Retourne toutes les inscriptions
// /api/comments - Retourne tous les commentaires
// /api/roles - Retourne tous les rôles
```

**Format de réponse attendu :**
```json
{
  "success": true,
  "users": [...],  // ou "jpos", "campus", etc.
}
```

---

## 🚨 Gestion d'erreurs

```javascript
const SafeComponent = () => {
  const { data, loading, error, refetch } = useFetchData('/api/users');

  if (loading) return <div>⏳ Chargement...</div>;
  
  if (error) {
    return (
      <div className="bg-red-100 p-4 rounded">
        <p>❌ Erreur: {error}</p>
        <button 
          onClick={refetch}
          className="bg-red-500 text-white p-2 rounded mt-2"
        >
          Réessayer
        </button>
      </div>
    );
  }

  return (
    <div>
      {/* Votre contenu */}
    </div>
  );
};
```

---

## 📝 Résumé

**Pour afficher n'importe quelle table :**

1. **Import :** `import { useFetchData } from '../services/fetchData.js';`
2. **Hook :** `const { data, loading, error } = useFetchData('/api/ma-table');`
3. **Affichage :** `{data?.map(item => <div key={item.id}>{item.name}</div>)}`

**C'est tout ! Trois lignes pour afficher n'importe quelles données de votre base MySQL.** 🚀