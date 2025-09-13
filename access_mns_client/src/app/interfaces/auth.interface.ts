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
  error: 'INVALID_CREDENTIALS' | 'MISSING_CREDENTIALS' | 'VALIDATION_FAILED' | 'TOO_MANY_ATTEMPTS' | 'INVALID_JSON' | string;
  message: string;
  details?: string[]; // Array of validation error messages
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

// Validation error interface
export interface ValidationError {
  field: string;
  message: string;
}

// Generic API Response interface
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message: string;
  details?: string[]; // Array of detailed error messages
}
