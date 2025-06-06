import PropTypes from 'prop-types';
import { API_CONFIG, buildUrl, log } from './apiConfig.js';

// === CLASSE D'ERREUR PERSONNALISÉE ===
export class ApiError extends Error {
  constructor(message, status = 0, data = null) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
    this.timestamp = new Date().toISOString();
  }

  // Méthode pour obtenir un message d'erreur user-friendly
  getUserMessage() {
    const { ERROR_MESSAGES } = API_CONFIG;
    
    switch (this.status) {
      case 0: return ERROR_MESSAGES.NETWORK;
      case 401: return ERROR_MESSAGES.UNAUTHORIZED;
      case 403: return ERROR_MESSAGES.FORBIDDEN;
      case 404: return ERROR_MESSAGES.NOT_FOUND;
      case 408: return ERROR_MESSAGES.TIMEOUT;
      case 500:
      case 502:
      case 503:
      case 504: return ERROR_MESSAGES.SERVER;
      default: return this.message || ERROR_MESSAGES.UNKNOWN;
    }
  }
}

// === FONCTION PRINCIPALE POUR LES APPELS API ===
const apiRequest = async (endpoint, options = {}) => {
  const url = buildUrl(endpoint);
  const startTime = Date.now();
  
  // Configuration par défaut
  const config = {
    method: 'GET',
    headers: { ...API_CONFIG.DEFAULT_HEADERS },
    ...options,
  };

  // Merge des headers
  if (options.headers) {
    config.headers = { ...config.headers, ...options.headers };
  }

  // Timeout personnalisé
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), API_CONFIG.TIMEOUT);
  config.signal = controller.signal;

  try {
    log('info', `🚀 API Request: ${config.method} ${url}`);
    
    const response = await fetch(url, config);
    const duration = Date.now() - startTime;
    
    clearTimeout(timeoutId);

    // Vérification du statut HTTP
    if (!response.ok) {
      let errorData = null;
      
      // Tentative de récupération des détails d'erreur
      try {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          errorData = await response.json();
        } else {
          errorData = await response.text();
        }
      } catch (parseError) {
        log('warn', 'Impossible de parser l\'erreur de l\'API', parseError);
      }

      const apiError = new ApiError(
        `HTTP ${response.status}: ${response.statusText}`,
        response.status,
        errorData
      );
      
      log('error', `❌ API Error (${duration}ms):`, apiError);
      throw apiError;
    }

    // Parsing de la réponse
    const contentType = response.headers.get('content-type');
    let data;
    
    if (contentType && contentType.includes('application/json')) {
      data = await response.json();
    } else {
      data = await response.text();
    }

    log('info', `✅ API Success (${duration}ms): ${endpoint}`, data);
    return data;

  } catch (error) {
    clearTimeout(timeoutId);
    
    if (error.name === 'AbortError') {
      const timeoutError = new ApiError('Délai d\'attente dépassé', 408);
      log('error', '⏰ API Timeout:', timeoutError);
      throw timeoutError;
    }
    
    if (error instanceof ApiError) {
      throw error;
    }
    
    // Erreur réseau ou autre
    const networkError = new ApiError(
      `Erreur de connexion: ${error.message}`,
      0,
      error
    );
    
    log('error', '🌐 Network Error:', networkError);
    throw networkError;
  }
};

// === FONCTIONS API PUBLIQUES ===

/**
 * Test de connexion avec le backend
 * @returns {Promise<Object>} Réponse du ping
 */
export const ping = async () => {
  return await apiRequest(API_CONFIG.ENDPOINTS.PING);
};

/**
 * Récupère la liste complète des JPO
 * @param {Object} params - Paramètres de requête (optionnel)
 * @returns {Promise<Array>} Liste des JPO
 */
export const getJpoList = async (params = {}) => {
  const searchParams = new URLSearchParams(params);
  const endpoint = searchParams.toString() 
    ? `${API_CONFIG.ENDPOINTS.JPO}?${searchParams}` 
    : API_CONFIG.ENDPOINTS.JPO;
    
  return await apiRequest(endpoint);
};

/**
 * Récupère les détails d'une JPO par son ID
 * @param {string|number} id - ID de la JPO
 * @returns {Promise<Object>} Détails de la JPO
 */
export const getJpoById = async (id) => {
  if (!id) {
    throw new ApiError('ID requis pour récupérer une JPO', 400);
  }
  
  return await apiRequest(API_CONFIG.ENDPOINTS.JPO_BY_ID(id));
};

/**
 * Crée une nouvelle JPO (pour plus tard)
 * @param {Object} jpoData - Données de la JPO
 * @returns {Promise<Object>} JPO créée
 */
export const createJpo = async (jpoData) => {
  return await apiRequest(API_CONFIG.ENDPOINTS.JPO, {
    method: 'POST',
    body: JSON.stringify(jpoData),
  });
};

/**
 * Met à jour une JPO existante (pour plus tard)
 * @param {string|number} id - ID de la JPO
 * @param {Object} jpoData - Nouvelles données
 * @returns {Promise<Object>} JPO mise à jour
 */
export const updateJpo = async (id, jpoData) => {
  if (!id) {
    throw new ApiError('ID requis pour mettre à jour une JPO', 400);
  }
  
  return await apiRequest(API_CONFIG.ENDPOINTS.JPO_BY_ID(id), {
    method: 'PUT',
    body: JSON.stringify(jpoData),
  });
};

/**
 * Supprime une JPO (pour plus tard)
 * @param {string|number} id - ID de la JPO
 * @returns {Promise<boolean>} Succès de la suppression
 */
export const deleteJpo = async (id) => {
  if (!id) {
    throw new ApiError('ID requis pour supprimer une JPO', 400);
  }
  
  await apiRequest(API_CONFIG.ENDPOINTS.JPO_BY_ID(id), {
    method: 'DELETE',
  });
  
  return true;
};

// === FONCTIONS UTILITAIRES ===

/**
 * Vérifie si l'API est accessible
 * @returns {Promise<boolean>} true si l'API répond
 */
export const checkApiHealth = async () => {
  try {
    await ping();
    return true;
  } catch (error) {
    log('warn', 'API Health Check Failed:', error.getUserMessage());
    return false;
  }
};

/**
 * Retourne les informations de configuration de l'API
 * @returns {Object} Configuration API
 */
export const getApiInfo = () => ({
  baseUrl: API_CONFIG.BASE_URL,
  environment: API_CONFIG.ENVIRONMENT,
  timeout: API_CONFIG.TIMEOUT,
  logsEnabled: API_CONFIG.ENABLE_LOGS,
});

// === EXPORT PAR DÉFAUT ===
export default {
  // Fonctions principales
  ping,
  getJpoList,
  getJpoById,
  createJpo,
  updateJpo,
  deleteJpo,
  
  // Utilitaires
  checkApiHealth,
  getApiInfo,
  
  // Classes/Types
  ApiError,
};

// Props pour les fonctions API
apiRequest.propTypes = {
  endpoint: PropTypes.string.isRequired,
  options: PropTypes.shape({
    method: PropTypes.string,
    headers: PropTypes.object,
    body: PropTypes.string,
    signal: PropTypes.object
  })
};

getJpoList.propTypes = {
  params: PropTypes.object
};

getJpoById.propTypes = {
  id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired
};

createJpo.propTypes = {
  jpoData: PropTypes.shape({
    title: PropTypes.string,
    description: PropTypes.string,
    date: PropTypes.string,
    location: PropTypes.string,
    capacity: PropTypes.number
  }).isRequired
};

updateJpo.propTypes = {
  id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
  jpoData: PropTypes.object.isRequired
};

deleteJpo.propTypes = {
  id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired
};