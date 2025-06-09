import { useState, useEffect, useRef, useCallback, useMemo } from 'react';
import PropTypes from 'prop-types';
import { getJpoList, getJpoById, checkApiHealth, ApiError } from './api.js';

// Hook générique pour les requêtes API
const useApiRequest = (apiFunction, initialData = null) => {
  const [data, setData] = useState(initialData);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const mountedRef = useRef(true);

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

  const reset = useCallback(() => {
    if (mountedRef.current) {
      setData(initialData);
      setError(null);
      setLoading(false);
    }
  }, [initialData]);

  // Nettoyage du composant
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

// Hook pour la liste des JPO
export const useJpoList = (autoLoad = true, params = {}) => {
  const {
    data: jpos,
    loading,
    error,
    execute,
    reset
  } = useApiRequest(getJpoList, []);

  // Mémoriser les paramètres pour éviter les re-rendus inutiles
  const memoizedParams = useMemo(() => params, [JSON.stringify(params)]);

  // Chargement automatique au montage
  useEffect(() => {
    if (autoLoad) {
      execute(memoizedParams);
    }
  }, [autoLoad, execute, memoizedParams]);

  const reload = useCallback((newParams = params) => {
    return execute(newParams);
  }, [execute, memoizedParams]);

  return {
    jpos,
    loading,
    error,
    reload,
    reset,
  };
};

// Hook pour une JPO spécifique
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

// Hook pour la surveillance de la santé de l'API
export const useApiHealth = (checkInterval = 30000) => {
  const [isHealthy, setIsHealthy] = useState(null);
  const [lastCheck, setLastCheck] = useState(null);
  const [checking, setChecking] = useState(false);
  const intervalRef = useRef(null);

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

  // Configuration des vérifications périodiques de santé
  useEffect(() => {
    checkHealth();

    if (checkInterval > 0) {
      intervalRef.current = setInterval(checkHealth, checkInterval);
    }

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

// PropTypes
useApiRequest.propTypes = {
  apiFunction: PropTypes.func.isRequired,
  initialData: PropTypes.any
};

useJpoList.propTypes = {
  autoLoad: PropTypes.bool,
  params: PropTypes.object
};

useJpoById.propTypes = {
  id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
  autoLoad: PropTypes.bool
};

useApiHealth.propTypes = {
  checkInterval: PropTypes.number
};