import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import {
  CompleteUserProfile,
  CompleteProfileResponse,
  User,
  GDPRDataExport,
  AccountDeactivationResponse,
} from '../interfaces/user.interface';

@Injectable({
  providedIn: 'root',
})
export class UserService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api/user';

  constructor(private http: HttpClient) {}

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
   * Get stored JWT token
   */
  private getToken(): string | null {
    return (
      localStorage.getItem('access_mns_token') ||
      sessionStorage.getItem('access_mns_token')
    );
  }

  /**
   * Get complete user profile with all related data
   * Uses: GET /api/user/profile/complete
   */
  getCompleteProfile(): Observable<CompleteUserProfile> {
    const headers = this.getAuthHeaders();

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
   * Check if current user has admin role
   */
  isAdmin(user: User | null): boolean {
    return user?.roles?.includes('ROLE_ADMIN') || false;
  }

  /**
   * Check if current user has super admin role
   */
  isSuperAdmin(user: User | null): boolean {
    return user?.roles?.includes('ROLE_SUPER_ADMIN') || false;
  }

  /**
   * Get user's current service
   */
  getCurrentService(profile: CompleteUserProfile): any {
    return profile.services.current;
  }

  /**
   * Get user's accessible zones
   */
  getAccessibleZones(profile: CompleteUserProfile): any[] {
    return profile.zones_accessibles || [];
  }

  /**
   * Get user's active badges
   */
  getActiveBadges(profile: CompleteUserProfile): any[] {
    return profile.badges.filter((badge) => badge.is_active) || [];
  }

  /**
   * Get user's authorized badge readers
   */
  getAuthorizedBadgeReaders(profile: CompleteUserProfile): any[] {
    return profile.badgeuses_autorisees || [];
  }

  /**
   * Format user's full name
   */
  getFullName(user: User): string {
    return `${user.prenom} ${user.nom}`;
  }

  /**
   * Format organization address
   */
  formatOrganizationAddress(organisation: any): string {
    if (!organisation?.adresse) return '';

    const addr = organisation.adresse;
    const parts = [];

    if (addr.numero_rue) parts.push(addr.numero_rue);
    if (addr.suffix_rue) parts.push(addr.suffix_rue);
    if (addr.nom_rue) parts.push(addr.nom_rue);

    const street = parts.join(' ');
    const cityParts = [];

    if (addr.code_postal) cityParts.push(addr.code_postal);
    if (addr.ville) cityParts.push(addr.ville);

    const city = cityParts.join(' ');
    const result = [];

    if (street) result.push(street);
    if (city) result.push(city);
    if (addr.pays) result.push(addr.pays);

    return result.join(', ');
  }

  /**
   * Get working days as array
   */
  getWorkingDaysArray(workingDays: string | undefined): string[] {
    if (!workingDays) return [];
    return workingDays.split(',').map((day) => day.trim());
  }

  /**
   * Format working hours
   */
  formatWorkingHours(heureDebut?: string, horraire?: string): string {
    if (!heureDebut && !horraire) return 'Non défini';
    if (heureDebut && horraire) return `${heureDebut} - ${horraire}`;
    if (heureDebut) return `À partir de ${heureDebut}`;
    if (horraire) return `Jusqu'à ${horraire}`;
    return 'Non défini';
  }

  /**
   * Update user profile
   * Uses: PUT /api/user/profile
   */
  updateProfile(profileData: any): Observable<User> {
    const headers = this.getAuthHeaders();

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

  // GDPR-related methods

  /**
   * Update user's last login timestamp
   * Uses: POST /api/user/update-last-login
   */
  updateLastLogin(): Observable<any> {
    const headers = this.getAuthHeaders();

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

  /**
   * Deactivate user account (GDPR compliance)
   * Uses: POST /api/user/deactivate
   */
  deactivateAccount(): Observable<AccountDeactivationResponse> {
    const headers = this.getAuthHeaders();

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
    const headers = this.getAuthHeaders();

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
