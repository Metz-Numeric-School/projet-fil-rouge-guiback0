import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router } from '@angular/router';
import {
  BehaviorSubject,
  Observable,
  catchError,
  map,
  throwError,
  tap,
} from 'rxjs';
import {
  LoginCredentials,
  LoginResponse,
  RefreshResponse,
} from '../interfaces/auth.interface';
import {
  User,
  CompleteUserProfile,
  UserProfileResponse,
} from '../interfaces/user.interface';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api'; // Adjust this to your Symfony API URL
  private readonly TOKEN_KEY = 'access_mns_token';
  private readonly REMEMBER_ME_KEY = 'access_mns_remember';

  private currentUserSubject = new BehaviorSubject<User | null>(null);
  private completeProfileSubject =
    new BehaviorSubject<CompleteUserProfile | null>(null);
  private isLoadingSubject = new BehaviorSubject<boolean>(false);

  public currentUser$ = this.currentUserSubject.asObservable();
  public completeProfile$ = this.completeProfileSubject.asObservable();
  public isLoading$ = this.isLoadingSubject.asObservable();

  constructor(private http: HttpClient, private router: Router) {
    this.initializeAuth();
  }

  /**
   * Initialize authentication state on service creation
   */
  private initializeAuth(): void {
    const token = this.getToken();
    if (token) {
      this.loadUserProfile();
    }
  }

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
            // Store token
            this.storeToken(response.data.token, rememberMe);

            // Store remember me preference
            if (rememberMe) {
              localStorage.setItem(this.REMEMBER_ME_KEY, 'true');
            } else {
              localStorage.removeItem(this.REMEMBER_ME_KEY);
            }

            // Update current user
            this.currentUserSubject.next(response.data.user);

            return response.data.user;
          } else {
            throw new Error(response.message);
          }
        }),
        catchError((error) => {
          this.isLoadingSubject.next(false);
          if (error.error && !error.error.success) {
            return throwError(
              () => new Error(error.error.message || 'Erreur de connexion')
            );
          }
          return throwError(() => new Error('Erreur de connexion au serveur'));
        })
      );
  }

  /**
   * Logout user
   */
  logout(): Observable<any> {
    const headers = this.getAuthHeaders();

    return this.http
      .post(`${this.API_BASE_URL}/auth/logout`, {}, { headers })
      .pipe(
        tap(() => {
          this.clearSession();
        }),
        catchError(() => {
          // Even if logout fails on server, clear local session
          this.clearSession();
          return throwError(() => new Error('Erreur lors de la déconnexion'));
        })
      );
  }

  /**
   * Refresh JWT token
   */
  refreshToken(): Observable<string> {
    const headers = this.getAuthHeaders();

    return this.http
      .post<RefreshResponse>(
        `${this.API_BASE_URL}/auth/refresh`,
        {},
        { headers }
      )
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            const rememberMe =
              localStorage.getItem(this.REMEMBER_ME_KEY) === 'true';
            this.storeToken(response.data.token, rememberMe);
            return response.data.token;
          }
          throw new Error(response.message);
        })
      );
  }

  /**
   * Load basic user profile from server
   */
  loadUserProfile(): Observable<User> {
    const headers = this.getAuthHeaders();

    return this.http
      .get<UserProfileResponse>(`${this.API_BASE_URL}/auth/me`, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            this.currentUserSubject.next(response.data);
            return response.data;
          }
          throw new Error(
            response.message || 'Erreur lors du chargement du profil'
          );
        }),
        catchError((error) => {
          this.clearSession();
          return throwError(() => error);
        })
      );
  }

  /**
   * Load complete user profile with all related data
   */
  loadCompleteProfile(): Observable<CompleteUserProfile> {
    const headers = this.getAuthHeaders();

    return this.http
      .get<any>(`${this.API_BASE_URL}/user/profile/complete`, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            // Update both current user and complete profile
            this.currentUserSubject.next(response.data.user);
            this.completeProfileSubject.next(response.data);
            return response.data;
          }
          throw new Error(
            response.message || 'Erreur lors du chargement du profil complet'
          );
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors du chargement du profil complet';

          if (error.error) {
            switch (error.error.error) {
              case 'INVALID_USER':
                errorMessage = 'Utilisateur invalide';
                break;
              case 'SERVER_ERROR':
                errorMessage = 'Erreur serveur lors du chargement du profil';
                break;
              default:
                errorMessage = error.error.message || errorMessage;
            }
          }

          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Get current user value
   */
  getCurrentUser(): User | null {
    return this.currentUserSubject.value;
  }

  /**
   * Get complete profile value
   */
  getCompleteProfile(): CompleteUserProfile | null {
    return this.completeProfileSubject.value;
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated(): boolean {
    return !!this.getToken() && !!this.currentUserSubject.value;
  }

  /**
   * Check if current user has admin role
   */
  isAdmin(): boolean {
    const user = this.getCurrentUser();
    return user?.roles?.includes('ROLE_ADMIN') || false;
  }

  /**
   * Check if current user has super admin role
   */
  isSuperAdmin(): boolean {
    const user = this.getCurrentUser();
    return user?.roles?.includes('ROLE_SUPER_ADMIN') || false;
  }

  /**
   * Get stored JWT token
   */
  getToken(): string | null {
    return (
      localStorage.getItem(this.TOKEN_KEY) ||
      sessionStorage.getItem(this.TOKEN_KEY)
    );
  }

  /**
   * Store JWT token
   */
  private storeToken(token: string, rememberMe: boolean): void {
    if (rememberMe) {
      localStorage.setItem(this.TOKEN_KEY, token);
      sessionStorage.removeItem(this.TOKEN_KEY);
    } else {
      sessionStorage.setItem(this.TOKEN_KEY, token);
      localStorage.removeItem(this.TOKEN_KEY);
    }
  }

  /**
   * Get authorization headers with JWT token
   */
  private getAuthHeaders(): HttpHeaders {
    const token = this.getToken();
    return new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });
  }

  /**
   * Clear user session
   */
  private clearSession(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    sessionStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.REMEMBER_ME_KEY);
    this.currentUserSubject.next(null);
    this.completeProfileSubject.next(null);
    this.router.navigate(['/login']);
  }

  /**
   * Handle authentication errors
   */
  handleAuthError(): void {
    this.clearSession();
  }
}
