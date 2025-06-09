import { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import { apiRequest } from './api.js';
import { API_CONFIG } from './apiConfig.js';

// Fonction universelle de récupération de données
export const fetchData = async (endpoint, options = {}) => {
  try {
    let fullEndpoint = endpoint;
    
    if (Object.keys(options).length > 0) {
      const params = new URLSearchParams(options);
      fullEndpoint += `?${params}`;
    }

    const data = await apiRequest(fullEndpoint);
    return data;
    
  } catch (error) {
    console.error(`❌ Erreur fetchData ${endpoint}:`, error);
    throw error;
  }
};

fetchData.propTypes = {
  endpoint: PropTypes.string.isRequired,
  options: PropTypes.object
};

// Hook React pour la récupération automatique de données
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

useFetchData.propTypes = {
  endpoint: PropTypes.string,
  options: PropTypes.object
};

// Fonctions spécifiques de récupération de données
export const getUsers = async (params = {}) => {
  return await fetchData(API_CONFIG.ENDPOINTS.USER_LIST || '/api/users', params);
};

getUsers.propTypes = {
  params: PropTypes.object
};

export const getUser = async (userId) => {
  if (!userId) throw new Error('User ID requis');
  return await fetchData(API_CONFIG.ENDPOINTS.USER_GET(userId));
};

getUser.propTypes = {
  userId: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired
};

export const getJpos = async (params = {}) => {
  return await fetchData(API_CONFIG.ENDPOINTS.JPO, params);
};

getJpos.propTypes = {
  params: PropTypes.object
};

export const getJpo = async (jpoId) => {
  if (!jpoId) throw new Error('JPO ID requis');
  return await fetchData(API_CONFIG.ENDPOINTS.JPO_BY_ID(jpoId));
};

getJpo.propTypes = {
  jpoId: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired
};

export const getCampus = async () => {
  return await fetchData('/api/campus');
};

export const getRegistrations = async (params = {}) => {
  return await fetchData('/api/registrations', params);
};

getRegistrations.propTypes = {
  params: PropTypes.object
};

export const getComments = async (params = {}) => {
  return await fetchData('/api/comments', params);
};

getComments.propTypes = {
  params: PropTypes.object
};

export const getRoles = async () => {
  return await fetchData('/api/roles');
};

// Hooks React spécialisés
export const useUsers = (params = {}) => {
  return useFetchData(API_CONFIG.ENDPOINTS.USER_LIST || '/api/users', params);
};

useUsers.propTypes = {
  params: PropTypes.object
};

export const useUser = (userId) => {
  const endpoint = userId ? API_CONFIG.ENDPOINTS.USER_GET(userId) : null;
  return useFetchData(endpoint);
};

useUser.propTypes = {
  userId: PropTypes.oneOfType([PropTypes.string, PropTypes.number])
};

export const useCampus = () => {
  return useFetchData('/api/campus');
};

export const useRegistrations = (params = {}) => {
  return useFetchData('/api/registrations', params);
};

useRegistrations.propTypes = {
  params: PropTypes.object
};

export const useComments = (params = {}) => {
  return useFetchData('/api/comments', params);
};

useComments.propTypes = {
  params: PropTypes.object
};

// Hook d'agrégation des données du tableau de bord
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
