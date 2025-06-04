// src/services/apiHooks.js
// Hooks React personnalisés pour l'API

import { useState, useEffect, useRef, useCallback } from 'react';
import { getJpoList, getJpoById, checkApiHealth, ApiError } from './api.js';

// === HOOK GÉNÉRIQUE POUR LES REQUÊTES API ===
const useApiRequest = (apiFunction, initialData = null) => {
  const [data, setData] = useState(initialData);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const mountedRef = useRef(true);

  // Fonction pour exécuter la requête
  const execute = useCallback(async (...args) => {
    if (!mountedRef.current) return;
    
    setLoading(true);
    setError(null);

    try {
      const result = await apiFunction(...args);
      
      if (mountedRef.current) {
        setData(result);
      }
      
      return result;
    } catch (err) {
      if (mountedRef.current) {
        const errorMessage = err instanceof ApiError 
          ? err.getUserMessage() 
          : err.message;
        setError(errorMessage);
      }
      throw err;
    } finally {
      if (mountedRef.current) {
        setLoading(false);
      }
    }
  }, [apiFunction]);

  // Reset de l'état
  const reset = useCallback(() => {
    if (mountedRef.current) {
      setData(initialData);
      setError(null);
      setLoading(false);
    }
  }, [initialData]);

  // Nettoyage à la destruction du composant
  useEffect(() => {
    return () => {
      mountedRef.current = false;
    };
  }, []);

  return {
    data,
    loading,
    error,
    execute,
    reset,
  };
};

// === HOOK POUR LA LISTE DES JPO ===
export const useJpoList = (autoLoad = true, params = {}) => {
  const {
    data: jpos,
    loading,
    error,
    execute,
    reset
  } = useApiRequest(getJpoList, []);

  // Chargement automatique au montage
  useEffect(() => {
    if (autoLoad) {
      execute(params);
    }
  }, [autoLoad, execute, JSON.stringify(params)]);

  // Fonction pour recharger les données
  const reload = useCallback((newParams = params) => {
    return execute(newParams);
  }, [execute, params]);

  return {
    jpos,
    loading,
    error,
    reload,
    reset,
  };
};

// === HOOK POUR UNE JPO SPÉCIFIQUE ===
export const useJpoById = (id, autoLoad = true) => {
  const {
    data: jpo,
    loading,
    error,
    execute,
    reset
  } = useApiRequest(getJpoById, null);

  // Chargement automatique quand l'ID change
  useEffect(() => {
    if (autoLoad && id) {
      execute(id);
    }
  }, [autoLoad, id, execute]);

  // Fonction pour recharger les données
  const reload = useCallback((newId = id) => {
    if (!newId) {
      throw new Error('ID requis pour charger une JPO');
    }
    return execute(newId);
  }, [execute, id]);

  return {
    jpo,
    loading,
    error,
    reload,
    reset,
  };
};

// === HOOK POUR LE STATUT DE L'API ===
export const useApiHealth = (checkInterval = 30000) => {
  const [isHealthy, setIsHealthy] = useState(null);
  const [lastCheck, setLastCheck] = useState(null);
  const [checking, setChecking] = useState(false);
  const intervalRef = useRef(null);

  // Fonction pour vérifier le statut
  const checkHealth = useCallback(async () => {
    setChecking(true);
    
    try {
      const healthy = await checkApiHealth();
      setIsHealthy(healthy);
      setLastCheck(new Date());
    } catch (error) {
      setIsHealthy(false);
      console.warn('Health check failed:', error);
    } finally {
      setChecking(false);
    }
  }, []);

  // Configuration de la vérification périodique
  useEffect(() => {
    // Première vérification
    checkHealth();

    // Vérifications périodiques
    if (checkInterval > 0) {
      intervalRef.current = setInterval(checkHealth, checkInterval);
    }

    // Nettoyage
    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
    };
  }, [checkHealth, checkInterval]);

  return {
    isHealthy,
    checking,
    lastCheck,
    checkHealth,
  };
};

// === HOOK POUR LA PAGINATION (bonus) ===
export const useJpoPagination = (itemsPerPage = 10) => {
  const [currentPage, setCurrentPage] = useState(1);
  const [params, setParams] = useState({});
  
  const {
    jpos: allJpos,
    loading,
    error,
    reload
  } = useJpoList(true, params);

  // Calcul des données paginées
  const totalItems = allJpos?.length || 0;
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentJpos = allJpos?.slice(startIndex, endIndex) || [];

  // Fonctions de navigation
  const goToPage = useCallback((page) => {
    if (page >= 1 && page <= totalPages) {
      setCurrentPage(page);
    }
  }, [totalPages]);

  const nextPage = useCallback(() => {
    goToPage(currentPage + 1);
  }, [currentPage, goToPage]);

  const previousPage = useCallback(() => {
    goToPage(currentPage - 1);
  }, [currentPage, goToPage]);

  // Reset de la pagination quand les paramètres changent
  useEffect(() => {
    setCurrentPage(1);
  }, [JSON.stringify(params)]);

  return {
    // Données
    jpos: currentJpos,
    allJpos,
    loading,
    error,
    
    // Pagination
    currentPage,
    totalPages,
    totalItems,
    itemsPerPage,
    
    // Navigation
    goToPage,
    nextPage,
    previousPage,
    canGoNext: currentPage < totalPages,
    canGoPrevious: currentPage > 1,
    
    // Filtres/Recherche
    params,
    setParams,
    reload,
  };
};

// === HOOK POUR LA RECHERCHE (bonus) ===
export const useJpoSearch = (debounceMs = 500) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [debouncedSearchTerm, setDebouncedSearchTerm] = useState('');
  const timeoutRef = useRef(null);

  // Debounce du terme de recherche
  useEffect(() => {
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
    }

    timeoutRef.current = setTimeout(() => {
      setDebouncedSearchTerm(searchTerm);
    }, debounceMs);

    return () => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
    };
  }, [searchTerm, debounceMs]);

  // Chargement des JPO avec recherche
  const {
    jpos,
    loading,
    error,
    reload
  } = useJpoList(true, debouncedSearchTerm ? { search: debouncedSearchTerm } : {});

  // Fonction pour nettoyer la recherche
  const clearSearch = useCallback(() => {
    setSearchTerm('');
  }, []);

  return {
    jpos,
    loading,
    error,
    searchTerm,
    setSearchTerm,
    clearSearch,
    reload,
    isSearching: searchTerm !== debouncedSearchTerm,
  };
};