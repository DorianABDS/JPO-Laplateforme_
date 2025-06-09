import PropTypes from 'prop-types';
import { apiRequest } from './api.js';
import { API_CONFIG, log } from './apiConfig.js';

// Cache local pour les données de la base
let databaseCache = {
  data: null,
  lastFetch: null,
  isLoading: false,
};

let CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

// Classe wrapper pour les détails de la base
export class DatabaseDetails {
  constructor(data) {
    this.raw = data;
    this.metadata = data?.metadata || {};
    this.tables = data?.tables || [];
    this.connections = data?.connections || {};
    this.statistics = data?.statistics || {};
    this.lastUpdated = new Date().toISOString();
  }

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

  getTable(tableName) {
    return this.tables.find(table => 
      table.name.toLowerCase() === tableName.toLowerCase()
    );
  }

  getTableColumns(tableName) {
    const table = this.getTable(tableName);
    return table?.columns || [];
  }

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

  searchTables(searchTerm) {
    if (!searchTerm) return this.getTables();
    
    const term = searchTerm.toLowerCase();
    return this.tables.filter(table =>
      table.name.toLowerCase().includes(term) ||
      (table.comment && table.comment.toLowerCase().includes(term))
    );
  }

  hasTable(tableName) {
    return this.tables.some(table => 
      table.name.toLowerCase() === tableName.toLowerCase()
    );
  }

  getTableRelations(tableName) {
    const table = this.getTable(tableName);
    return {
      foreignKeys: table?.foreign_keys || [],
      referencedBy: table?.referenced_by || [],
    };
  }

  getTableSchema(tableName) {
    const table = this.getTable(tableName);
    return table?.columns || [];
  }
}

DatabaseDetails.propTypes = {
  data: PropTypes.object
};

// Récupérer les détails de la base depuis l'API
export const getDatabaseDetails = async (forceRefresh = false, options = {}) => {
  const now = Date.now();
  
  // Vérifier le cache si pas de rafraîchissement forcé
  if (!forceRefresh && databaseCache.data && databaseCache.lastFetch) {
    const cacheAge = now - databaseCache.lastFetch;
    if (cacheAge < CACHE_DURATION) {
      log('info', 'Using cached database details');
      return new DatabaseDetails(databaseCache.data);
    }
  }

  // Éviter les appels multiples simultanés
  if (databaseCache.isLoading) {
    log('info', 'Database details already loading, waiting...');
    while (databaseCache.isLoading) {
      await new Promise(resolve => setTimeout(resolve, 100));
    }
    return new DatabaseDetails(databaseCache.data);
  }

  try {
    databaseCache.isLoading = true;
    log('info', 'Fetching database details from API');

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
    
    log('info', 'Database details retrieved successfully');
    return new DatabaseDetails(data);

  } catch (error) {
    log('error', 'Failed to fetch database details:', error);
    
    // Retourner les données du cache obsolètes si disponibles
    if (databaseCache.data) {
      log('warn', 'Using stale cache data due to error');
      return new DatabaseDetails(databaseCache.data);
    }
    
    throw error;
  } finally {
    databaseCache.isLoading = false;
  }
};

getDatabaseDetails.propTypes = {
  forceRefresh: PropTypes.bool,
  options: PropTypes.object
};

// Obtenir uniquement les infos de base de la base
export const getDatabaseBasicInfo = async () => {
  const details = await getDatabaseDetails();
  return details.getBasicInfo();
};

// Obtenir la liste des tables avec recherche optionnelle
export const getDatabaseTables = async (searchTerm = null) => {
  const details = await getDatabaseDetails();
  return searchTerm ? details.searchTables(searchTerm) : details.getTables();
};

getDatabaseTables.propTypes = {
  searchTerm: PropTypes.string
};

// Obtenir les détails d'une table spécifique depuis l'API
export const getTableDetails = async (tableName) => {
  if (!tableName) {
    throw new Error('Nom de table requis');
  }

  try {
    log('info', `Fetching table details for: ${tableName}`);
    
    const endpoint = API_CONFIG.ENDPOINTS.DATABASE_TABLE(tableName);
    const data = await apiRequest(endpoint);
    
    log('info', `Table details retrieved for: ${tableName}`);
    return data;
    
  } catch (error) {
    log('error', `Failed to fetch table details for ${tableName}:`, error);
    throw error;
  }
};

getTableDetails.propTypes = {
  tableName: PropTypes.string.isRequired
};

// Obtenir les statistiques de la base
export const getDatabaseStatistics = async () => {
  const details = await getDatabaseDetails();
  return details.getStatistics();
};

// Vérifier si une table existe
export const tableExists = async (tableName) => {
  const details = await getDatabaseDetails();
  return details.hasTable(tableName);
};

tableExists.propTypes = {
  tableName: PropTypes.string.isRequired
};

// Obtenir le schéma d'une table depuis l'API
export const getTableSchema = async (tableName) => {
  const tableDetails = await getTableDetails(tableName);
  return tableDetails.columns || [];
};

getTableSchema.propTypes = {
  tableName: PropTypes.string.isRequired
};

// Obtenir les tables spécifiques aux JPO
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

// Obtenir toutes les relations de la base
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

// Obtenir les contraintes d'une table
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

getTableConstraints.propTypes = {
  tableName: PropTypes.string.isRequired
};

// Utilitaires de cache
export const clearDatabaseCache = () => {
  databaseCache = {
    data: null,
    lastFetch: null,
    isLoading: false,
  };
  log('info', 'Database cache cleared');
};

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

export const configureCacheDuration = (durationMs) => {
  if (durationMs > 0) {
    CACHE_DURATION = durationMs;
    log('info', `Cache duration set to ${durationMs}ms`);
  }
};

configureCacheDuration.propTypes = {
  durationMs: PropTypes.number.isRequired
};

// Configuration par défaut
export const DEFAULT_CONFIG = {
  CACHE_DURATION: 5 * 60 * 1000,
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