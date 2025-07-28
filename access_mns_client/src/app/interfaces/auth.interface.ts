import { User, Organisation } from './user.interface';

// Login interfaces
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

// Generic API Response interface
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message: string;
}
