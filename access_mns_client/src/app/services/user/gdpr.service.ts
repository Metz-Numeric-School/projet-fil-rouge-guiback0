import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import {
  User,
  GDPRDataExport,
  AccountDeactivationResponse,
} from '../../interfaces/user.interface';
import { TokenService } from '../auth/token.service';

@Injectable({
  providedIn: 'root',
})
export class GdprService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api/user';

  constructor(
    private http: HttpClient,
    private tokenService: TokenService
  ) {}

  /**
   * Deactivate user account (GDPR compliance)
   * Uses: POST /api/user/deactivate
   */
  deactivateAccount(): Observable<AccountDeactivationResponse> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .post<AccountDeactivationResponse>(`${this.API_BASE_URL}/deactivate`, {}, { headers })
      .pipe(
        map((response) => {
          if (response.success) {
            return response;
          }
          throw new Error(response.message || 'Erreur lors de la désactivation du compte');
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors de la désactivation du compte';
          
          if (error.error) {
            switch (error.error.error) {
              case 'INVALID_USER':
                errorMessage = 'Utilisateur invalide';
                break;
              case 'SERVER_ERROR':
                errorMessage = 'Erreur serveur lors de la désactivation';
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
   * Export user's personal data (GDPR data portability)
   * Uses: GET /api/user/export-data
   */
  exportUserData(): Observable<GDPRDataExport> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .get<GDPRDataExport>(`${this.API_BASE_URL}/export-data`, { headers })
      .pipe(
        map((response) => {
          if (response.success) {
            return response;
          }
          throw new Error(response.message || 'Erreur lors de l\'exportation des données');
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors de l\'exportation des données';
          
          if (error.error) {
            switch (error.error.error) {
              case 'INVALID_USER':
                errorMessage = 'Utilisateur invalide';
                break;
              case 'SERVER_ERROR':
                errorMessage = 'Erreur serveur lors de l\'exportation';
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
   * Check if account is active
   */
  isAccountActive(user: User): boolean {
    return user.compte_actif !== false;
  }

  /**
   * Check if account is scheduled for deletion
   */
  isScheduledForDeletion(user: User): boolean {
    return !!user.date_suppression_prevue && !user.compte_actif;
  }

  /**
   * Get days until scheduled deletion
   */
  getDaysUntilDeletion(user: User): number | null {
    if (!user.date_suppression_prevue) return null;
    
    const deletionDate = new Date(user.date_suppression_prevue);
    const now = new Date();
    const diffTime = deletionDate.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays > 0 ? diffDays : 0;
  }

  /**
   * Format GDPR deletion notice
   */
  formatDeletionNotice(user: User): string {
    if (!this.isScheduledForDeletion(user)) return '';
    
    const days = this.getDaysUntilDeletion(user);
    if (days === null) return '';
    
    if (days <= 0) {
      return 'Ce compte est éligible à la suppression définitive (RGPD)';
    }
    
    const deletionDate = new Date(user.date_suppression_prevue!).toLocaleDateString('fr-FR');
    return `Ce compte sera automatiquement supprimé le ${deletionDate} (dans ${days} jour${days > 1 ? 's' : ''}) conformément au RGPD`;
  }
}