import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { BehaviorSubject, Observable, catchError, map, throwError, tap } from 'rxjs';
import {
  LoginCredentials,
  LoginResponse,
} from '../../interfaces/auth.interface';
import { User } from '../../interfaces/user.interface';
import { TokenService } from './token.service';
import { environment } from '../../../environments';

@Injectable({
  providedIn: 'root',
})
export class AuthenticationService {
  private readonly API_BASE_URL = environment.apiBaseUrl;
  
  private isLoadingSubject = new BehaviorSubject<boolean>(false);
  public isLoading$ = this.isLoadingSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    private tokenService: TokenService
  ) {}

  /**
   * Login user with email and password
   */
  login(
    credentials: LoginCredentials,
    rememberMe: boolean = false
  ): Observable<User> {
    this.isLoadingSubject.next(true);

    return this.http
      .post<LoginResponse>(`${this.API_BASE_URL}/auth/login`, credentials)
      .pipe(
        tap(() => this.isLoadingSubject.next(false)),
        map((response) => {
          if (response.success) {
            this.tokenService.storeToken(response.data.token, rememberMe);
            this.tokenService.setRememberMe(rememberMe);
            return response.data.user;
          } else {
            throw new Error(response.message);
          }
        }),
        catchError((error) => {
          this.isLoadingSubject.next(false);
          if (error.error && !error.error.success) {
            return throwError(() => this.createLoginError(error.error));
          }
          return throwError(() => new Error('Erreur de connexion au serveur'));
        })
      );
  }

  /**
   * Logout user
   */
  logout(): Observable<any> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .post(`${this.API_BASE_URL}/auth/logout`, {}, { headers })
      .pipe(
        tap(() => {
          this.clearSession();
        }),
        catchError(() => {
          this.clearSession();
          return throwError(() => new Error('Erreur lors de la déconnexion'));
        })
      );
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated(): boolean {
    return !!this.tokenService.getToken();
  }

  /**
   * Clear user session
   */
  private clearSession(): void {
    this.tokenService.clearTokens();
    this.router.navigate(['/login']);
  }

  /**
   * Handle authentication errors
   */
  handleAuthError(): void {
    this.clearSession();
  }

  /**
   * Create detailed login error with validation messages
   */
  private createLoginError(errorResponse: any): Error {
    const error = new Error() as any;
    
    switch (errorResponse.error) {
      case 'VALIDATION_FAILED':
        error.name = 'ValidationError';
        error.message = 'Données de connexion invalides';
        error.type = 'VALIDATION_FAILED';
        error.details = errorResponse.details || [];
        break;
        
      case 'TOO_MANY_ATTEMPTS':
        error.name = 'RateLimitError';
        error.message = errorResponse.message || 'Trop de tentatives de connexion. Veuillez réessayer plus tard.';
        error.type = 'TOO_MANY_ATTEMPTS';
        break;
        
      case 'INVALID_CREDENTIALS':
        error.name = 'AuthenticationError';
        error.message = 'Identifiants invalides';
        error.type = 'INVALID_CREDENTIALS';
        break;
        
      case 'INVALID_JSON':
        error.name = 'FormatError';
        error.message = 'Format de données invalide';
        error.type = 'INVALID_JSON';
        break;
        
      default:
        error.name = 'LoginError';
        error.message = errorResponse.message || 'Erreur de connexion';
        error.type = errorResponse.error;
        break;
    }
    
    return error;
  }
}