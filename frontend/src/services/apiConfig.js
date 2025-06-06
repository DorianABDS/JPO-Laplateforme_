// src/services/apiConfig.js
// Configuration centralisée de l'API - Mise à jour pour Database Service

export const API_CONFIG = {
  // URL de base de l'API (depuis les variables d'environnement)
  BASE_URL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  
  // Environnement actuel
  ENVIRONMENT: import.meta.env.VITE_APP_ENV || 'development',
  
  // Timeout pour les requêtes (en millisecondes)
  TIMEOUT: 30000, // 30 secondes
  
  // Headers par défaut
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  
  // Endpoints de l'API
  ENDPOINTS: {
    // Endpoints existants
    PING: '/api/ping',
    JPO: '/api/jpo',
    JPO_BY_ID: (id) => `/api/jpo/${id}`,
    
    // Nouveaux endpoints pour la base de données
    DATABASE_INFO: '/api/database/info',
    DATABASE_TABLE: (tableName) => `/api/database/table?table=${tableName}`,
    DATABASE_SCHEMA: '/api/database/schema',
    DATABASE_RELATIONS: '/api/database/relations',
    
    // Endpoints pour les utilisateurs
    USER_GET: (id) => `/api/user/get?id=${id}`,
    USER_UPDATE: (id) => `/api/user/update?id=${id}`,
    USER_CREATE: '/api/user/create',
    USER_DELETE: (id) => `/api/user/delete?id=${id}`,
    USER_LIST: '/api/user/list',
  },
  
  // Messages d'erreur personnalisés
  ERROR_MESSAGES: {
    NETWORK: 'Problème de connexion réseau',
    SERVER: 'Erreur du serveur, veuillez réessayer',
    NOT_FOUND: 'Ressource non trouvée',
    UNAUTHORIZED: 'Accès non autorisé',
    FORBIDDEN: 'Accès interdit',
    TIMEOUT: 'Délai d\'attente dépassé',
    UNKNOWN: 'Une erreur inattendue s\'est produite',
    DATABASE_ERROR: 'Erreur lors de l\'accès à la base de données',
    TABLE_NOT_FOUND: 'Table non trouvée dans la base de données',
    USER_NOT_FOUND: 'Utilisateur non trouvé',
    VALIDATION_ERROR: 'Données invalides',
  },
  
  // Activer/désactiver les logs selon l'environnement
  ENABLE_LOGS: import.meta.env.VITE_APP_ENV !== 'production',
};

// Fonction utilitaire pour construire les URLs
export const buildUrl = (endpoint) => {
  const baseUrl = API_CONFIG.BASE_URL.endsWith('/') 
    ? API_CONFIG.BASE_URL.slice(0, -1) 
    : API_CONFIG.BASE_URL;
  
  const cleanEndpoint = endpoint.startsWith('/') 
    ? endpoint 
    : `/${endpoint}`;
    
  return `${baseUrl}${cleanEndpoint}`;
};

// Fonction pour logger uniquement en développement
export const log = (level, message, data = null) => {
  if (!API_CONFIG.ENABLE_LOGS) return;
  
  const timestamp = new Date().toISOString();
  const logMessage = `[${timestamp}] [${level.toUpperCase()}] ${message}`;
  
  switch (level) {
    case 'error':
      console.error(logMessage, data || '');
      break;
    case 'warn':
      console.warn(logMessage, data || '');
      break;
    case 'info':
      console.info(logMessage, data || '');
      break;
    default:
      console.log(logMessage, data || '');
  }
};