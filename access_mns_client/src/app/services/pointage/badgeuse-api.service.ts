import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map, catchError, throwError } from 'rxjs';
import {
  BadgeusesResponse,
  PointageActionResponse,
  PointageRequest,
  PointageValidation,
  PointageErrorCode,
  UserWorkingStatus,
  BadgeuseAccess,
} from '../../interfaces/pointage.interface';
import { TokenService } from '../auth/token.service';

@Injectable({
  providedIn: 'root',
})
export class BadgeuseApiService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api/pointage';

  constructor(
    private http: HttpClient,
    private tokenService: TokenService
  ) {}

  /**
   * Get all accessible badgeuses for the current user with their status
   * Uses: GET /api/pointage/badgeuses
   */
  getBadgeuses(): Observable<{ badgeuses: BadgeuseAccess[], userStatus: UserWorkingStatus }> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .get<BadgeusesResponse>(`${this.API_BASE_URL}/badgeuses`, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            const transformedBadgeuses = response.data.badgeuses.map(badgeuse => ({
              ...badgeuse,
              status: this.determineBadgeuseStatus(badgeuse)
            }));
            
            return {
              badgeuses: transformedBadgeuses,
              userStatus: response.data.user_status
            };
          }
          throw new Error(
            response.message || 'Erreur lors du chargement des badgeuses'
          );
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors du chargement des badgeuses';

          if (error.error) {
            switch (error.error.error) {
              case 'USER_NOT_FOUND':
                errorMessage = 'Utilisateur non trouvé';
                break;
              case 'ACCESS_DENIED':
                errorMessage = 'Accès refusé';
                break;
              case 'ACCOUNT_DEACTIVATED':
                errorMessage = 'Votre compte est désactivé';
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
   * Perform a pointage action on a specific badgeuse
   * Uses: POST /api/pointage/action
   */
  performPointage(request: PointageRequest): Observable<PointageActionResponse> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .post<PointageActionResponse>(`${this.API_BASE_URL}/action`, request, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response;
          }
          throw new Error(
            response.message || 'Erreur lors du pointage'
          );
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors du pointage';
          let errorCode: PointageErrorCode = 'INTERNAL_ERROR';

          if (error.error) {
            errorCode = error.error.error || 'INTERNAL_ERROR';
            switch (errorCode) {
              case 'ZONE_ACCESS_DENIED':
                errorMessage = 'Accès refusé à cette zone';
                break;
              case 'BADGEUSE_NOT_FOUND':
                errorMessage = 'Badgeuse non trouvée';
                break;
              case 'NO_ACTIVE_BADGE':
                errorMessage = 'Aucun badge actif trouvé';
                break;
              case 'ACCESS_DENIED':
                errorMessage = 'Accès refusé - organisation différente';
                break;
              case 'BADGE_NOT_FOUND':
                errorMessage = 'Badge non trouvé';
                break;
              case 'USER_NOT_FOUND':
                errorMessage = 'Utilisateur non trouvé pour ce badge';
                break;
              case 'NO_ZONES_CONFIGURED':
                errorMessage = 'Configuration de zones manquante pour cette badgeuse';
                break;
              case 'NO_PRINCIPAL_SERVICE':
                errorMessage = 'Aucun service principal configuré';
                break;
              default:
                errorMessage = error.error.message || errorMessage;
            }
          }

          const errorResponse: PointageActionResponse = {
            success: false,
            error: errorCode,
            message: errorMessage,
            warning: error.error?.warning
          };

          return throwError(() => errorResponse);
        })
      );
  }

  /**
   * Validate a pointage action before performing it
   * Uses: POST /api/pointage/validate
   */
  validatePointage(badgeuseId: number): Observable<PointageValidation> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .post<{ success: boolean; data: PointageValidation; error?: string; message?: string }>(
        `${this.API_BASE_URL}/validate`,
        { badgeuse_id: badgeuseId },
        { headers }
      )
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response.data;
          }
          return {
            is_valid: false,
            can_proceed: false,
            message: response.message || 'Validation échouée'
          };
        }),
        catchError((error) => {
          return throwError(() => ({
            is_valid: false,
            can_proceed: false,
            message: error.error?.message || 'Erreur lors de la validation'
          }));
        })
      );
  }

  /**
   * Determine badgeuse status based on backend properties
   */
  private determineBadgeuseStatus(badgeuse: any): 'available' | 'blocked' | 'error' {
    if (badgeuse.is_blocked) return 'blocked';
    if (!badgeuse.is_accessible) return 'blocked';
    return 'available';
  }
}