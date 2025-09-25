export const environment = {
  production: false,
  // Configuration pour Docker local - utilise le proxy Nginx sur localhost
  apiBaseUrl: 'https://localhost/manager/api',
  apiUrl: 'https://localhost/manager/api',
  buildType: 'DOCKER_LOCAL_DEFAULT' // Marqueur pour vérifier
};