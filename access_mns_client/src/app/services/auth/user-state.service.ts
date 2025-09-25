import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, catchError, map, throwError } from 'rxjs';
import {
  User,
  CompleteUserProfile,
  UserProfileResponse,
} from '../../interfaces/user.interface';
import { TokenService } from './token.service';
import { environment } from '../../../environments';

@Injectable({
  providedIn: 'root',
})
export class UserStateService {
  private readonly API_BASE_URL = environment.apiBaseUrl;

  private currentUserSubject = new BehaviorSubject<User | null>(null);
  private completeProfileSubject = new BehaviorSubject<CompleteUserProfile | null>(null);

  public currentUser$ = this.currentUserSubject.asObservable();
  public completeProfile$ = this.completeProfileSubject.asObservable();

  constructor(
    private http: HttpClient,
    private tokenService: TokenService
  ) {
    this.initializeAuth();
  }

  /**
   * Initialize authentication state on service creation
   */
  private initializeAuth(): void {
    const token = this.tokenService.getToken();
    if (token) {
      this.loadUserProfile().subscribe({
        error: () => {
          // Token might be expired, clear session
          this.clearUserState();
        }
      });
    }
  }

  /**
   * Load basic user profile from server
   */
  loadUserProfile(): Observable<User> {
    const headers = this.tokenService.getAuthHeaders();

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
          this.clearUserState();
          return throwError(() => error);
        })
      );
  }

  /**
   * Load complete user profile with all related data
   */
  loadCompleteProfile(): Observable<CompleteUserProfile> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .get<any>(`${this.API_BASE_URL}/user/profile/complete`, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
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
   * Set current user
   */
  setCurrentUser(user: User): void {
    this.currentUserSubject.next(user);
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
   * Clear user state
   */
  clearUserState(): void {
    this.currentUserSubject.next(null);
    this.completeProfileSubject.next(null);
  }
}