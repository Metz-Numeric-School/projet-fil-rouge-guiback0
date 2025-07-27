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
  roles: string[];
}

export interface Organisation {
  id: number;
  nom: string;
  email?: string;
  telephone?: string;
  site_web?: string;
}

export interface Service {
  id: number;
  nom: string;
  niveau: number;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface LoginSuccessResponse {
  success: true;
  data: {
    token: string;
    user: User;
    organisation: Organisation | null;
  };
  message: string;
}

export interface LoginErrorResponse {
  success: false;
  error: 'INVALID_CREDENTIALS' | 'MISSING_CREDENTIALS' | string;
  message: string;
}

export type LoginResponse = LoginSuccessResponse | LoginErrorResponse;

export interface RefreshResponse {
  success: boolean;
  data?: {
    token: string;
  };
  error?: string;
  message: string;
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

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message: string;
}
