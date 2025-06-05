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
    PING: '/api/ping',
    JPO: '/api/jpo',
    JPO_BY_ID: (id) => `/api/jpo/${id}`,
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

// Props pour les fonctions utilitaires
buildUrl.propTypes = {
  endpoint: PropTypes.string.isRequired
};

log.propTypes = {
  level: PropTypes.oneOf(['error', 'warn', 'info', 'debug']).isRequired,
  message: PropTypes.string.isRequired,
  data: PropTypes.any
};