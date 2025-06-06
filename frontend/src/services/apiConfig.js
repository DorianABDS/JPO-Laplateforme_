import PropTypes from 'prop-types';

export const API_CONFIG = {
  BASE_URL: 'http://localhost:8000',
  ENVIRONMENT: import.meta.env.VITE_APP_ENV || 'development',
  TIMEOUT: 30000,
  
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  
  ENDPOINTS: {
    // Endpoints existants
    PING: '/api/ping',
    JPO: '/api/jpo',
    JPO_BY_ID: (id) => `/api/jpo/${id}`,
    USERS: '/api/users',
    USER_BY_ID: (id) => `/api/users/${id}`, 
    CAMPUS: '/api/campus',
    REGISTRATIONS: '/api/registrations',
    COMMENTS: '/api/comments',
    ROLES: '/api/roles',
    DATABASE_INFO: '/api/database/info',
    DATABASE_TABLE: (tableName) => `/api/database/table?table=${tableName}`,
  },
  
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
  
  ENABLE_LOGS: import.meta.env.VITE_APP_ENV !== 'production',
};

// Construire URL complète
export const buildUrl = (endpoint) => {
  const baseUrl = API_CONFIG.BASE_URL.endsWith('/') 
    ? API_CONFIG.BASE_URL.slice(0, -1) 
    : API_CONFIG.BASE_URL;
  
  const cleanEndpoint = endpoint.startsWith('/') 
    ? endpoint 
    : `/${endpoint}`;
    
  return `${baseUrl}${cleanEndpoint}`;
};

buildUrl.propTypes = {
  endpoint: PropTypes.string.isRequired
};

// Logger simple
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

log.propTypes = {
  level: PropTypes.oneOf(['error', 'warn', 'info', 'debug']).isRequired,
  message: PropTypes.string.isRequired,
  data: PropTypes.any
};  