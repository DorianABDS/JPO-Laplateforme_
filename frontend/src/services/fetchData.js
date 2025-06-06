import { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import { apiRequest } from './api.js';
import { API_CONFIG } from './apiConfig.js';

/**
 * Fonction universelle pour récupérer vos données
 * Utilise votre apiRequest existant pour la cohérence
 * @param {string} endpoint - L'endpoint à appeler
 * @param {Object} options - Paramètres optionnels
 * @returns {Promise<any>} Les données récupérées
 */
export const fetchData = async (endpoint, options = {}) => {
  try {
    // Construire l'URL avec paramètres
    let fullEndpoint = endpoint;
    
    if (Object.keys(options).length > 0) {
      const params = new URLSearchParams(options);
      fullEndpoint += `?${params}`;
    }

    // Utiliser votre apiRequest existant
    const data = await apiRequest(fullEndpoint);
    return data;
    
  } catch (error) {
    console.error(`❌ Erreur fetchData ${endpoint}:`, error);
    throw error;
  }
};

// PropTypes pour fetchData
fetchData.propTypes = {
  endpoint: PropTypes.string.isRequired,
  options: PropTypes.object
};

/**
 * Hook React pour récupérer des données automatiquement
 * Compatible avec votre système d'erreurs ApiError
 * @param {string} endpoint - L'endpoint à appeler
 * @param {Object} options - Paramètres optionnels
 * @returns {Object} { data, loading, error, refetch }
 */
export const useFetchData = (endpoint, options = {}) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchDataInternal = async () => {
    try {
      setLoading(true);
      setError(null);
      const result = await fetchData(endpoint, options);
      setData(result);
    } catch (err) {
      // Compatible avec votre ApiError
      const errorMessage = err.getUserMessage ? err.getUserMessage() : err.message;
      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDataInternal();
  }, [endpoint, JSON.stringify(options)]);

  return {
    data,
    loading,
    error,
    refetch: fetchDataInternal
  };
};

// PropTypes pour useFetchData
useFetchData.propTypes = {
  endpoint: PropTypes.string,
  options: PropTypes.object
};

// === FONCTIONS SPÉCIFIQUES POUR VOS DONNÉES ===
// Utilisant vos endpoints existants + nouveaux

/**
 * Récupère tous les utilisateurs
 */
export const getUsers = async (params = {}) => {
  return await fetchData(API_CONFIG.ENDPOINTS.USER_LIST || '/api/users', params);
};

// PropTypes pour getUsers
getUsers.propTypes = {
  params: PropTypes.object
};

/**
 * Récupère un utilisateur par ID
 */
export const getUser = async (userId) => {
  if (!userId) throw new Error('User ID requis');
  return await fetchData(API_CONFIG.ENDPOINTS.USER_GET(userId));
};

// PropTypes pour getUser
getUser.propTypes = {
  userId: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired
};

/**
 * Récupère toutes les JPO (utilise votre fonction existante)
 */
export const getJpos = async (params = {}) => {
  return await fetchData(API_CONFIG.ENDPOINTS.JPO, params);
};

// PropTypes pour getJpos
getJpos.propTypes = {
  params: PropTypes.object
};

/**
 * Récupère une JPO par ID (utilise votre fonction existante)
 */
export const getJpo = async (jpoId) => {
  if (!jpoId) throw new Error('JPO ID requis');
  return await fetchData(API_CONFIG.ENDPOINTS.JPO_BY_ID(jpoId));
};

// PropTypes pour getJpo
getJpo.propTypes = {
  jpoId: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired
};

/**
 * Récupère tous les campus
 */
export const getCampus = async () => {
  return await fetchData('/api/campus');
};

/**
 * Récupère toutes les inscriptions
 */
export const getRegistrations = async (params = {}) => {
  return await fetchData('/api/registrations', params);
};

// PropTypes pour getRegistrations
getRegistrations.propTypes = {
  params: PropTypes.object
};

/**
 * Récupère les commentaires
 */
export const getComments = async (params = {}) => {
  return await fetchData('/api/comments', params);
};

// PropTypes pour getComments
getComments.propTypes = {
  params: PropTypes.object
};

/**
 * Récupère les rôles
 */
export const getRoles = async () => {
  return await fetchData('/api/roles');
};

// === HOOKS SPÉCIALISÉS POUR VOS DONNÉES ===
// Complètent vos hooks existants dans apiHooks.js

/**
 * Hook pour récupérer tous les utilisateurs
 */
export const useUsers = (params = {}) => {
  return useFetchData(API_CONFIG.ENDPOINTS.USER_LIST || '/api/users', params);
};

// PropTypes pour useUsers
useUsers.propTypes = {
  params: PropTypes.object
};

/**
 * Hook pour récupérer un utilisateur spécifique
 */
export const useUser = (userId) => {
  const endpoint = userId ? API_CONFIG.ENDPOINTS.USER_GET(userId) : null;
  return useFetchData(endpoint);
};

// PropTypes pour useUser
useUser.propTypes = {
  userId: PropTypes.oneOfType([PropTypes.string, PropTypes.number])
};

/**
 * Hook pour récupérer tous les campus
 */
export const useCampus = () => {
  return useFetchData('/api/campus');
};

/**
 * Hook pour récupérer les inscriptions
 */
export const useRegistrations = (params = {}) => {
  return useFetchData('/api/registrations', params);
};

// PropTypes pour useRegistrations
useRegistrations.propTypes = {
  params: PropTypes.object
};

/**
 * Hook pour récupérer les commentaires
 */
export const useComments = (params = {}) => {
  return useFetchData('/api/comments', params);
};

// PropTypes pour useComments
useComments.propTypes = {
  params: PropTypes.object
};

/**
 * Hook pour récupérer les données du dashboard
 */
export const useDashboardData = () => {
  const { data: users, loading: usersLoading } = useUsers();
  const { data: jpos, loading: jposLoading } = useFetchData(API_CONFIG.ENDPOINTS.JPO);
  const { data: campus, loading: campusLoading } = useCampus();
  
  return {
    users: users?.users || [],
    jpos: jpos?.jpos || jpos || [],
    campus: campus?.campus || campus || [],
    loading: usersLoading || jposLoading || campusLoading
  };
};

export default fetchData;