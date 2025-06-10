# 📋 Guide d'utilisation - FetchData

Ce guide vous explique comment récupérer des données depuis la base de données en utilisant les fonctions et hooks personnalisés du module `fetchData.js`.

## 📦 Installation

1. Assurez-vous d'avoir le fichier `fetchData.js` dans `src/services/`
2. Importez les fonctions dans vos composants

## 🎯 Syntaxe de base

### Import
```javascript
import { 
  fetchData, 
  useFetchData, 
  useUsers, 
  useUser,
  useCampus,
  useRegistrations,
  useComments,
  useDashboardData
} from '../services/fetchData.js';
```

### Affichage automatique
```javascript
const { data, loading, error, refetch } = useFetchData('/api/endpoint');
```

---

## 📊 Endpoints disponibles

| Endpoint | Hook spécialisé | Description | Paramètres |
|----------|----------------|-------------|------------|
| `/api/users` | `useUsers(params)` | Tous les utilisateurs | `{ status, page, limit }` |
| `/api/user/{id}` | `useUser(userId)` | Un utilisateur spécifique | - |
| `/api/jpo` | `useFetchData('/api/jpo')` | Journées Portes Ouvertes | `{ startDate, status }` |
| `/api/jpo/{id}` | `useFetchData('/api/jpo/{id}')` | Une JPO spécifique | - |
| `/api/campus` | `useCampus()` | Tous les campus | - |
| `/api/registrations` | `useRegistrations(params)` | Inscriptions aux JPO | `{ dateFrom, status }` |
| `/api/comments` | `useComments(params)` | Commentaires | `{ status, limit }` |
| `/api/roles` | `useFetchData('/api/roles')` | Rôles utilisateur | - |

---

## 💡 Exemples d'utilisation

### 1. Afficher tous les utilisateurs
```javascript
const UserList = () => {
  const { data: users, loading, error } = useUsers();

  if (loading) return <div>⏳ Chargement...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;

  return (
    <div>
      <h2>Utilisateurs ({users?.users?.length || 0})</h2>
      {users?.users?.map(user => (
        <div key={user.user_id} className="border p-4 mb-2">
          <h3>{user.first_name} {user.last_name}</h3>
          <p>📧 Email: {user.email}</p>
          <p>👤 Type: {user.user_type}</p>
        </div>
      ))}
    </div>
  );
};
```

### 2. Afficher un utilisateur spécifique
```javascript
const UserProfile = ({ userId }) => {
  const { data: user, loading, error } = useUser(userId);

  if (loading) return <div>⏳ Chargement du profil...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;
  if (!user) return <div>🚫 Utilisateur non trouvé</div>;

  return (
    <div className="bg-white p-6 rounded shadow">
      <h2>👤 Profil de {user.first_name} {user.last_name}</h2>
      <p>📧 Email: {user.email}</p>
      <p>🏷️ Rôle: {user.user_type}</p>
      <p>📅 Inscrit le: {user.created_at}</p>
    </div>
  );
};
```

### 3. Afficher toutes les JPO
```javascript
const JpoList = () => {
  const { data: jpos, loading, error } = useFetchData('/api/jpo');

  if (loading) return <div>⏳ Chargement des JPO...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;

  return (
    <div>
      <h2>🎯 Journées Portes Ouvertes ({jpos?.length || 0})</h2>
      {jpos?.map(jpo => (
        <div key={jpo.jpo_id} className="bg-blue-50 p-4 mb-3 rounded">
          <h3>🏢 {jpo.name}</h3>
          <p>📅 Date: {jpo.date}</p>
          <p>👥 Capacité: {jpo.max_capacity} personnes</p>
          <p>📍 Campus: {jpo.campus_name}</p>
        </div>
      ))}
    </div>
  );
};
```

### 4. Afficher une JPO spécifique
```javascript
const JpoDetails = ({ jpoId }) => {
  const { data: jpo, loading, error } = useFetchData(`/api/jpo/${jpoId}`);

  if (loading) return <div>⏳ Chargement...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;

  return (
    <div className="bg-white p-6 rounded shadow">
      <h2>🎯 {jpo?.name}</h2>
      <p>📅 Date: {jpo?.date}</p>
      <p>📍 Campus: {jpo?.campus_name}</p>
      <p>👥 Capacité: {jpo?.max_capacity} personnes</p>
      <p>📝 Description: {jpo?.description}</p>
    </div>
  );
};
```

### 5. Afficher tous les campus
```javascript
const CampusList = () => {
  const { data: campus, loading, error } = useCampus();

  if (loading) return <div>⏳ Chargement...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;

  return (
    <div>
      <h2>🏢 Nos Campus ({campus?.campus?.length || 0})</h2>
      {campus?.campus?.map(camp => (
        <div key={camp.campus_id} className="bg-green-50 p-4 mb-2 rounded">
          <h3>📍 {camp.name}</h3>
          <p>🏙️ Ville: {camp.city}</p>
          <p>📬 Adresse: {camp.address}</p>
        </div>
      ))}
    </div>
  );
};
```

### 6. Afficher les inscriptions
```javascript
const RegistrationsList = () => {
  const { data: registrations, loading, error } = useRegistrations();

  if (loading) return <div>⏳ Chargement...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;

  return (
    <div>
      <h2>📝 Inscriptions ({registrations?.registrations?.length || 0})</h2>
      {registrations?.registrations?.map(reg => (
        <div key={reg.registration_id} className="border p-4 mb-2">
          <p>👤 Utilisateur ID: {reg.user_id}</p>
          <p>🎯 JPO ID: {reg.jpo_id}</p>
          <p>📅 Date d'inscription: {reg.registration_date}</p>
          <p>✅ Status: {reg.status}</p>
        </div>
      ))}
    </div>
  );
};
```

### 7. Afficher les commentaires
```javascript
const CommentsList = () => {
  const { data: comments, loading, error } = useComments();

  if (loading) return <div>⏳ Chargement...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;

  return (
    <div>
      <h2>💬 Commentaires ({comments?.comments?.length || 0})</h2>
      {comments?.comments?.map(comment => (
        <div key={comment.comment_id} className="bg-gray-50 p-4 mb-3 rounded">
          <p>💭 {comment.content}</p>
          <small>👤 Par utilisateur ID: {comment.user_id}</small>
          <small>📅 Date: {comment.comment_date}</small>
        </div>
      ))}
    </div>
  );
};
```

### 8. Afficher les rôles
```javascript
const RolesList = () => {
  const { data: roles, loading, error } = useFetchData('/api/roles');

  if (loading) return <div>⏳ Chargement...</div>;
  if (error) return <div>❌ Erreur: {error}</div>;

  return (
    <div>
      <h2>🏷️ Rôles ({roles?.length || 0})</h2>
      {roles?.map(role => (
        <div key={role.role_id} className="bg-purple-50 p-3 mb-2 rounded">
          <h3>🎭 {role.role_name}</h3>
          <p>📝 Description: {role.description}</p>
        </div>
      ))}
    </div>
  );
};
```

---

## 🔍 Affichage avec filtres

### Utilisateurs actifs uniquement
```javascript
const ActiveUsers = () => {
  const { data: activeUsers, loading } = useUsers({ status: 'active' });

  return (
    <div>
      <h2>👥 Utilisateurs actifs</h2>
      {activeUsers?.users?.map(user => (
        <div key={user.user_id} className="p-2 border-b">
          ✅ {user.first_name} {user.last_name}
        </div>
      ))}
    </div>
  );
};
```

### JPO par date
```javascript
const UpcomingJpos = () => {
  const { data: jpos } = useFetchData('/api/jpo', { 
    startDate: '2025-01-01',
    status: 'active' 
  });

  return (
    <div>
      <h2>🗓️ JPO à venir</h2>
      {jpos?.map(jpo => (
        <div key={jpo.jpo_id} className="p-3 bg-blue-100 mb-2 rounded">
          📅 {jpo.name} - {jpo.date}
        </div>
      ))}
    </div>
  );
};
```

### Inscriptions confirmées
```javascript
const ConfirmedRegistrations = () => {
  const { data: confirmed } = useRegistrations({ status: 'confirmed' });

  return (
    <div>
      <h2>✅ Inscriptions confirmées</h2>
      {confirmed?.registrations?.map(reg => (
        <div key={reg.registration_id} className="p-2 bg-green-100 mb-2 rounded">
          👤 Utilisateur {reg.user_id} → 🎯 JPO {reg.jpo_id}
        </div>
      ))}
    </div>
  );
};
```

### Commentaires approuvés
```javascript
const ApprovedComments = () => {
  const { data: comments } = useComments({ 
    status: 'approved',
    limit: 20 
  });

  return (
    <div>
      <h2>✅ Commentaires approuvés</h2>
      {comments?.comments?.map(comment => (
        <div key={comment.comment_id} className="p-3 border-l-4 border-green-500 mb-2">
          💬 {comment.content}
        </div>
      ))}
    </div>
  );
};
```

---

## 📊 Dashboard avec toutes les données

```javascript
const Dashboard = () => {
  const { users, jpos, campus, loading } = useDashboardData();

  if (loading) return <div>⏳ Chargement du tableau de bord...</div>;

  return (
    <div>
      <h1>📊 Tableau de bord</h1>
      
      <div className="grid grid-cols-3 gap-4 mb-6">
        <div className="bg-blue-100 p-4 rounded text-center">
          <h3>👥 Utilisateurs</h3>
          <p className="text-3xl font-bold text-blue-600">{users.length}</p>
        </div>
        
        <div className="bg-green-100 p-4 rounded text-center">
          <h3>🎯 JPO</h3>
          <p className="text-3xl font-bold text-green-600">{jpos.length}</p>
        </div>
        
        <div className="bg-purple-100 p-4 rounded text-center">
          <h3>🏢 Campus</h3>
          <p className="text-3xl font-bold text-purple-600">{campus.length}</p>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-6">
        <div>
          <h3>👥 Derniers utilisateurs</h3>
          {users.slice(0, 5).map(user => (
            <div key={user.user_id} className="p-2 border-b">
              {user.first_name} {user.last_name}
            </div>
          ))}
        </div>
        
        <div>
          <h3>🎯 Prochaines JPO</h3>
          {jpos.slice(0, 5).map(jpo => (
            <div key={jpo.jpo_id} className="p-2 border-b">
              {jpo.name} - {jpo.date}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
```

---

## 🔄 Chargement manuel avec bouton

```javascript
import { useState } from 'react';
import { fetchData } from '../services/fetchData.js';

const DataOnDemand = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);

  const loadUsers = async () => {
    try {
      setLoading(true);
      const data = await fetchData('/api/users');
      setUsers(data.users || []);
    } catch (error) {
      console.error('❌ Erreur:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <button 
        onClick={loadUsers}
        disabled={loading}
        className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
      >
        {loading ? '⏳ Chargement...' : '📥 Charger les utilisateurs'}
      </button>
      
      <div className="mt-4">
        {users.map(user => (
          <div key={user.user_id} className="p-2 border-b">
            👤 {user.first_name} {user.last_name}
          </div>
        ))}
      </div>
    </div>
  );
};
```

---

## 🔄 Actualisation avec refetch

```javascript
const RefreshableUserList = () => {
  const { data: users, loading, error, refetch } = useUsers();

  return (
    <div>
      <div className="flex justify-between items-center mb-4">
        <h2>👥 Liste des utilisateurs</h2>
        <button 
          onClick={refetch}
          disabled={loading}
          className="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600"
        >
          {loading ? '⏳' : '🔄'} Actualiser
        </button>
      </div>
      
      {error && (
        <div className="bg-red-100 p-3 rounded mb-4">
          ❌ Erreur: {error}
        </div>
      )}
      
      {users?.users?.map(user => (
        <div key={user.user_id} className="p-3 border rounded mb-2">
          👤 {user.first_name} {user.last_name}
        </div>
      ))}
    </div>
  );
};
```

---

## 🎯 Composant avec navigation

```javascript
import { useState } from 'react';
import { useUsers, useUser } from '../services/fetchData.js';

const UserManager = () => {
  const [selectedUserId, setSelectedUserId] = useState(null);
  const { data: users, loading: usersLoading } = useUsers();
  const { data: selectedUser, loading: userLoading } = useUser(selectedUserId);

  if (usersLoading) return <div>⏳ Chargement...</div>;

  return (
    <div className="grid grid-cols-2 gap-6">
      {/* Liste des utilisateurs */}
      <div>
        <h2>👥 Tous les utilisateurs</h2>
        {users?.users?.map(user => (
          <div 
            key={user.user_id}
            onClick={() => setSelectedUserId(user.user_id)}
            className={`p-3 border rounded mb-2 cursor-pointer hover:bg-gray-100 ${
              selectedUserId === user.user_id ? 'bg-blue-100 border-blue-500' : ''
            }`}
          >
            👤 {user.first_name} {user.last_name}
          </div>
        ))}
      </div>
      
      {/* Détail de l'utilisateur sélectionné */}
      <div>
        {selectedUserId ? (
          userLoading ? (
            <div>⏳ Chargement du profil...</div>
          ) : (
            <div className="bg-white p-6 border rounded shadow">
              <button 
                onClick={() => setSelectedUserId(null)}
                className="mb-4 text-blue-500 hover:underline"
              >
                ← Retour à la liste
              </button>
              <h2>👤 {selectedUser?.first_name} {selectedUser?.last_name}</h2>
              <p>📧 Email: {selectedUser?.email}</p>
              <p>🏷️ Type: {selectedUser?.user_type}</p>
              <p>📅 Inscrit le: {selectedUser?.created_at}</p>
            </div>
          )
        ) : (
          <div className="text-center text-gray-500 p-8">
            👆 Sélectionnez un utilisateur pour voir ses détails
          </div>
        )}
      </div>
    </div>
  );
};
```

---

## 🚨 Gestion d'erreurs complète

```javascript
const SafeDataComponent = () => {
  const { data, loading, error, refetch } = useFetchData('/api/users');

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        <span className="ml-2">⏳ Chargement des données...</span>
      </div>
    );
  }
  
  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded p-4">
        <div className="flex items-center mb-2">
          <span className="text-red-500 text-xl mr-2">❌</span>
          <h3 className="text-red-800 font-medium">Erreur de chargement</h3>
        </div>
        <p className="text-red-700 mb-3">{error}</p>
        <button 
          onClick={refetch}
          className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
        >
          🔄 Réessayer
        </button>
      </div>
    );
  }

  if (!data || data.length === 0) {
    return (
      <div className="text-center p-8 text-gray-500">
        <span className="text-4xl">📭</span>
        <p>Aucune donnée disponible</p>
      </div>
    );
  }

  return (
    <div>
      {/* Votre contenu ici */}
    </div>
  );
};
```

---

## 📝 Résumé

**Pour afficher n'importe quelle donnée :**

1. **Import :** `import { useFetchData, useUsers, etc. } from '../services/fetchData.js';`
2. **Hook :** `const { data, loading, error, refetch } = useFetchData('/api/endpoint');`
3. **Affichage :** `{data?.map(item => <div key={item.id}>{item.name}</div>)}`

**Endpoints disponibles :**
- `GET /api/users` - Tous les utilisateurs
- `GET /api/user/{id}` - Un utilisateur
- `GET /api/jpo` - Toutes les JPO
- `GET /api/jpo/{id}` - Une JPO
- `GET /api/campus` - Tous les campus
- `GET /api/registrations` - Toutes les inscriptions
- `GET /api/comments` - Tous les commentaires
- `GET /api/roles` - Tous les rôles

**C'est tout ! Trois lignes pour afficher n'importe quelles données de votre base.** 🚀