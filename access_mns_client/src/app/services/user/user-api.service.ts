import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import {
  CompleteUserProfile,
  CompleteProfileResponse,
  User,
} from '../../interfaces/user.interface';
import { TokenService } from '../auth/token.service';

@Injectable({
  providedIn: 'root',
})
export class UserApiService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api/user';

  constructor(
    private http: HttpClient,
    private tokenService: TokenService
  ) {}

  /**
   * Get complete user profile with all related data
   * Uses: GET /api/user/profile/complete
   */
  getCompleteProfile(): Observable<CompleteUserProfile> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .get<CompleteProfileResponse>(`${this.API_BASE_URL}/profile/complete`, {
        headers,
      })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
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
   * Update user profile
   * Uses: PUT /api/user/profile
   */
  updateProfile(profileData: any): Observable<User> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .put<any>(`${this.API_BASE_URL}/profile`, profileData, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response.data;
          }
          throw new Error(response.message || 'Erreur lors de la mise à jour du profil');
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors de la mise à jour du profil';
          
          if (error.error) {
            switch (error.error.error) {
              case 'VALIDATION_FAILED':
                const customError = new Error(errorMessage) as any;
                customError.type = 'VALIDATION_FAILED';
                customError.details = error.error.details || [];
                throw customError;
              case 'INVALID_PASSWORD':
                const passwordError = new Error('Mot de passe actuel incorrect') as any;
                passwordError.type = 'INVALID_PASSWORD';
                throw passwordError;
              case 'INVALID_USER':
                errorMessage = 'Utilisateur invalide';
                break;
              case 'SERVER_ERROR':
                errorMessage = 'Erreur serveur lors de la mise à jour';
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
   * Update user's last login timestamp
   * Uses: POST /api/user/update-last-login
   */
  updateLastLogin(): Observable<any> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .post<any>(`${this.API_BASE_URL}/update-last-login`, {}, { headers })
      .pipe(
        map((response) => {
          if (response.success) {
            return response;
          }
          throw new Error(response.message || 'Erreur lors de la mise à jour');
        }),
        catchError((error) => {
          const errorMessage = error.error?.message || 'Erreur lors de la mise à jour de la dernière connexion';
          return throwError(() => new Error(errorMessage));
        })
      );
  }
}