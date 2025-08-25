// Base User interface
export interface User {
  id: number;
  email: string;
  nom: string;
  prenom: string;
  telephone?: string;
  date_naissance?: string;
  date_inscription: string;
  adresse?: string;
  poste?: string;
  horraire?: string;
  heure_debut?: string;
  jours_semaine_travaille?: string;
  roles: string[];
  compte_actif?: boolean;
  date_derniere_connexion?: string;
  date_derniere_modification?: string;
  date_suppression_prevue?: string;
}

// Organisation interface with complete address
export interface Organisation {
  id: number;
  nom_organisation: string;
  email?: string;
  telephone?: string;
  site_web?: string;
  siret?: string;
  adresse?: {
    numero_rue?: string;
    suffix_rue?: string;
    nom_rue?: string;
    code_postal?: string;
    ville?: string;
    pays?: string;
  };
}

// Service interface
export interface Service {
  id: number;
  nom_service: string;
  niveau_service: number;
  date_debut: string;
  date_fin?: string;
  is_current: boolean;
}

// Zone interface
export interface Zone {
  id: number;
  nom_zone: string;
  description?: string;
  capacite?: number;
}

// Badge interface
export interface Badge {
  id: number;
  numero_badge: string;
  type_badge: string; // Technologie uniquement (RFID, NFC, MIFARE, etc.) - N'affecte PAS les permissions
  date_creation: string;
  date_expiration?: string;
  is_active: boolean;
}

// Access interface
export interface Acces {
  id: number;
  nom_acces: string;
  date_installation: string;
  zone: {
    id: number;
    nom_zone: string;
  };
  badgeuse?: {
    id: number;
    reference: string;
    date_installation: string;
  };
}

// Badgeuse (Badge Reader) interface
export interface Badgeuse {
  id: number;
  reference: string;
  date_installation: string;
  zones_accessibles: {
    id: number;
    nom_zone: string;
  }[];
}

// Complete User Profile interface
export interface CompleteUserProfile {
  user: User;
  organisation: Organisation | null;
  services: {
    current: Service | null;
    history: Service[];
  };
  zones_accessibles: Zone[];
  badges: Badge[];
  acces_autorises: Acces[];
  badgeuses_autorisees: Badgeuse[];
}

// User-related API Response interfaces
export interface CompleteProfileResponse {
  success: boolean;
  data?: CompleteUserProfile;
  error?: string;
  message?: string;
}

export interface UserByIdResponse {
  success: boolean;
  data?: {
    user: User;
    message: string;
  };
  error?: string;
  message?: string;
}

export interface UserProfileResponse {
  success: boolean;
  data?: User & {
    organisation: Organisation | null;
    service: Service | null;
  };
  error?: string;
  message?: string;
}

// GDPR-related interfaces
export interface GDPRDataExport {
  success: boolean;
  data?: {
    personal_information: {
      email: string;
      nom: string;
      prenom: string;
      telephone?: string;
      date_naissance?: string;
      date_inscription: string;
      poste?: string;
      horraire?: string;
      heure_debut?: string;
      jours_semaine_travaille?: string;
    };
    account_information: {
      compte_actif: boolean;
      date_derniere_connexion?: string;
      date_derniere_modification?: string;
      date_suppression_prevue?: string;
      roles: string[];
    };
    organisation?: any;
    services: any[];
    badges: any[];
  };
  export_timestamp?: string;
  gdpr_notice?: string;
  error?: string;
  message?: string;
}

export interface AccountDeactivationResponse {
  success: boolean;
  message: string;
  data?: {
    date_suppression_prevue: string;
  };
  error?: string;
}

export interface DeletionStatusResponse {
  success: boolean;
  data?: {
    user_id: number;
    compte_actif: boolean;
    date_suppression_prevue?: string;
    should_be_deleted: boolean;
    days_until_deletion?: number;
  };
  error?: string;
  message?: string;
}
