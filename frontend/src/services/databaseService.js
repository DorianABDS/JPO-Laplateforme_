import { apiRequest } from './api.js';
import { API_CONFIG, log } from './apiConfig.js';

// === CACHE LOCAL POUR LES DONNÉES ===
let databaseCache = {
  data: null,
  lastFetch: null,
  isLoading: false,
};

// Durée de validité du cache (en millisecondes) - 5 minutes par défaut
let CACHE_DURATION = 5 * 60 * 1000;

// === CLASSE POUR GÉRER LES DÉTAILS DE LA BASE ===
export class DatabaseDetails {
  constructor(data) {
    this.raw = data;
    this.metadata = data?.metadata || {};
    this.tables = data?.tables || [];
    this.connections = data?.connections || {};
    this.statistics = data?.statistics || {};
    this.lastUpdated = new Date().toISOString();
  }

  // Getter pour les informations de base
  getBasicInfo() {
    return {
      name: this.metadata.name || 'Unknown',
      version: this.metadata.version || 'Unknown',
      engine: this.metadata.engine || 'Unknown',
      charset: this.metadata.charset || 'Unknown',
      collation: this.metadata.collation || 'Unknown',
      lastUpdated: this.lastUpdated,
    };
  }

  // Getter pour les tables
  getTables() {
    return this.tables.map(table => ({
      name: table.name,
      rowCount: table.row_count || 0,
      size: table.size || '0 KB',
      engine: table.engine || 'Unknown',
      collation: table.collation || 'Unknown',
      comment: table.comment || '',
      columns: table.columns || []
    }));
  }

  // Getter pour une table spécifique
  getTable(tableName) {
    return this.tables.find(table => 
      table.name.toLowerCase() === tableName.toLowerCase()
    );
  }

  // Getter pour les colonnes d'une table
  getTableColumns(tableName) {
    const table = this.getTable(tableName);
    return table?.columns || [];
  }

  // Getter pour les statistiques
  getStatistics() {
    return {
      totalTables: this.tables.length,
      totalSize: this.statistics.total_size || '0 KB',
      totalRows: this.statistics.total_rows || 0,
      uptime: this.statistics.uptime || 'Unknown',
      connections: {
        active: this.connections.active || 0,
        max: this.connections.max || 0,
      },
    };
  }

  // Rechercher dans les tables
  searchTables(searchTerm) {
    if (!searchTerm) return this.getTables();
    
    const term = searchTerm.toLowerCase();
    return this.tables.filter(table =>
      table.name.toLowerCase().includes(term) ||
      (table.comment && table.comment.toLowerCase().includes(term))
    );
  }

  // Vérifier si une table existe
  hasTable(tableName) {
    return this.tables.some(table => 
      table.name.toLowerCase() === tableName.toLowerCase()
    );
  }

  // Obtenir les relations entre tables (depuis les données PHP)
  getTableRelations(tableName) {
    const table = this.getTable(tableName);
    return {
      foreignKeys: table?.foreign_keys || [],
      referencedBy: table?.referenced_by || [],
    };
  }

  // Obtenir le schéma d'une table (depuis les données PHP)
  getTableSchema(tableName) {
    const table = this.getTable(tableName);
    return table?.columns || [];
  }
}

// === FONCTIONS PRINCIPALES ===

/**
 * Récupère les détails de la base de données depuis l'API PHP
 * @param {boolean} forceRefresh - Force le rechargement même si le cache est valide
 * @param {Object} options - Options additionnelles
 * @returns {Promise<DatabaseDetails>} Détails de la base de données
 */
export const getDatabaseDetails = async (forceRefresh = false, options = {}) => {
  const now = Date.now();
  
  // Vérifier le cache si pas de force refresh
  if (!forceRefresh && databaseCache.data && databaseCache.lastFetch) {
    const cacheAge = now - databaseCache.lastFetch;
    if (cacheAge < CACHE_DURATION) {
      log('info', '📋 Using cached database details');
      return new DatabaseDetails(databaseCache.data);
    }
  }

  // Éviter les appels multiples simultanés
  if (databaseCache.isLoading) {
    log('info', '⏳ Database details already loading, waiting...');
    while (databaseCache.isLoading) {
      await new Promise(resolve => setTimeout(resolve, 100));
    }
    return new DatabaseDetails(databaseCache.data);
  }

  try {
    databaseCache.isLoading = true;
    log('info', '🔍 Fetching database details from PHP API');

    // Appel à votre endpoint PHP
    const endpoint = API_CONFIG.ENDPOINTS.DATABASE_INFO || '/api/database/info';
    const data = await apiRequest(endpoint, {
      method: 'GET',
      headers: {
        ...API_CONFIG.DEFAULT_HEADERS,
        ...options.headers
      }
    });

    // Mettre à jour le cache
    databaseCache.data = data;
    databaseCache.lastFetch = now;
    
    log('info', '✅ Database details retrieved successfully');
    return new DatabaseDetails(data);

  } catch (error) {
    log('error', '❌ Failed to fetch database details:', error);
    
    // En cas d'erreur, retourner les données du cache si disponibles
    if (databaseCache.data) {
      log('warn', '⚠️ Using stale cache data due to error');
      return new DatabaseDetails(databaseCache.data);
    }
    
    throw error;
  } finally {
    databaseCache.isLoading = false;
  }
};

/**
 * Récupère uniquement les informations de base
 * @returns {Promise<Object>} Informations de base
 */
export const getDatabaseBasicInfo = async () => {
  const details = await getDatabaseDetails();
  return details.getBasicInfo();
};

/**
 * Récupère la liste des tables
 * @param {string} searchTerm - Terme de recherche optionnel
 * @returns {Promise<Array>} Liste des tables
 */
export const getDatabaseTables = async (searchTerm = null) => {
  const details = await getDatabaseDetails();
  return searchTerm ? details.searchTables(searchTerm) : details.getTables();
};

/**
 * Récupère les détails d'une table spécifique depuis l'API PHP
 * @param {string} tableName - Nom de la table
 * @returns {Promise<Object|null>} Détails de la table
 */
export const getTableDetails = async (tableName) => {
  if (!tableName) {
    throw new Error('Nom de table requis');
  }

  try {
    log('info', `🔍 Fetching table details for: ${tableName}`);
    
    const endpoint = API_CONFIG.ENDPOINTS.DATABASE_TABLE(tableName);
    const data = await apiRequest(endpoint);
    
    log('info', `✅ Table details retrieved for: ${tableName}`);
    return data;
    
  } catch (error) {
    log('error', `❌ Failed to fetch table details for ${tableName}:`, error);
    throw error;
  }
};

/**
 * Récupère les statistiques de la base
 * @returns {Promise<Object>} Statistiques
 */
export const getDatabaseStatistics = async () => {
  const details = await getDatabaseDetails();
  return details.getStatistics();
};

/**
 * Vérifie si une table existe
 * @param {string} tableName - Nom de la table
 * @returns {Promise<boolean>} true si la table existe
 */
export const tableExists = async (tableName) => {
  const details = await getDatabaseDetails();
  return details.hasTable(tableName);
};

/**
 * Récupère le schéma d'une table depuis l'API PHP
 * @param {string} tableName - Nom de la table
 * @returns {Promise<Array>} Schéma de la table
 */
export const getTableSchema = async (tableName) => {
  const tableDetails = await getTableDetails(tableName);
  return tableDetails.columns || [];
};

/**
 * Récupère les tables spécifiques à l'application JPO depuis l'API
 * @returns {Promise<Array>} Tables JPO avec leurs détails
 */
export const getJpoTables = async () => {
  const details = await getDatabaseDetails();
  const jpoTableNames = DEFAULT_CONFIG.JPO_TABLES;
  
  return details.tables
    .filter(table => jpoTableNames.includes(table.name))
    .map(table => ({
      name: table.name,
      rowCount: table.row_count || 0,
      size: table.size || '0 KB',
      purpose: DEFAULT_CONFIG.TABLE_PURPOSES[table.name] || 'Table système',
      columns: table.columns || []
    }));
};

/**
 * Récupère toutes les relations de la base depuis l'API PHP
 * @returns {Promise<Object>} Mapping complet des relations
 */
export const getAllRelations = async () => {
  const details = await getDatabaseDetails();
  const relations = {};
  
  details.tables.forEach(table => {
    relations[table.name] = {
      foreignKeys: table.foreign_keys || [],
      referencedBy: table.referenced_by || []
    };
  });
  
  return relations;
};

/**
 * Vérifie les contraintes d'intégrité pour une table depuis l'API
 * @param {string} tableName - Nom de la table
 * @returns {Promise<Object>} Informations sur les contraintes
 */
export const getTableConstraints = async (tableName) => {
  const tableDetails = await getTableDetails(tableName);
  const columns = tableDetails.columns || [];
  
  return {
    primaryKeys: columns.filter(col => col.is_primary_key),
    foreignKeys: columns.filter(col => col.is_foreign_key),
    uniqueKeys: columns.filter(col => col.is_unique),
    required: columns.filter(col => !col.nullable),
    autoIncrement: columns.filter(col => col.is_auto_increment),
    relations: {
      foreignKeys: tableDetails.foreign_keys || [],
      referencedBy: tableDetails.referenced_by || []
    }
  };
};

// === FONCTIONS UTILITAIRES ===

/**
 * Vide le cache de la base de données
 */
export const clearDatabaseCache = () => {
  databaseCache = {
    data: null,
    lastFetch: null,
    isLoading: false,
  };
  log('info', '🧹 Database cache cleared');
};

/**
 * Retourne l'état du cache
 * @returns {Object} État du cache
 */
export const getCacheStatus = () => {
  const now = Date.now();
  const age = databaseCache.lastFetch ? now - databaseCache.lastFetch : null;
  
  return {
    hasData: !!databaseCache.data,
    isLoading: databaseCache.isLoading,
    lastFetch: databaseCache.lastFetch,
    ageMs: age,
    isValid: age ? age < CACHE_DURATION : false,
  };
};

/**
 * Configuration du cache
 * @param {number} durationMs - Durée de validité du cache en ms
 */
export const configureCacheDuration = (durationMs) => {
  if (durationMs > 0) {
    CACHE_DURATION = durationMs;
    log('info', `Cache duration set to ${durationMs}ms`);
  }
};

// === EXPORT PAR DÉFAUT ===
export default {
  // Fonctions principales
  getDatabaseDetails,
  getDatabaseBasicInfo,
  getDatabaseTables,
  getTableDetails,
  getDatabaseStatistics,
  tableExists,
  getTableSchema,
  
  // Fonctions spécifiques JPO
  getJpoTables,
  getAllRelations,
  getTableConstraints,
  
  // Utilitaires
  clearDatabaseCache,
  getCacheStatus,
  configureCacheDuration,
  
  // Classes
  DatabaseDetails,
};

// === CONFIGURATION PAR DÉFAUT ===
export const DEFAULT_CONFIG = {
  CACHE_DURATION: 5 * 60 * 1000, // 5 minutes
  ENABLE_LOGS: true,
  JPO_TABLES: [
    'campus', 'open_day', 'user', 'role', 
    'registration', 'comment'
  ],
  TABLE_PURPOSES: {
    'campus': 'Gestion des différents campus de La Plateforme',
    'open_day': 'Journées Portes Ouvertes (JPO)',
    'user': 'Utilisateurs du système (étudiants, parents, staff)',
    'role': 'Rôles et permissions',
    'registration': 'Inscriptions aux JPO',
    'comment': 'Commentaires et avis sur les JPO'
  }
};