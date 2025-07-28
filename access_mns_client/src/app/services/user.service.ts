import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import {
  CompleteUserProfile,
  CompleteProfileResponse,
  UserByIdResponse,
  User,
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
   * Get user profile by ID (admin only)
   * Uses: GET /api/user/profile/{id}
   */
  getUserById(userId: number): Observable<User> {
    const headers = this.getAuthHeaders();

    return this.http
      .get<UserByIdResponse>(`${this.API_BASE_URL}/profile/${userId}`, {
        headers,
      })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response.data.user;
          }
          throw new Error(
            response.message || "Erreur lors du chargement de l'utilisateur"
          );
        }),
        catchError((error) => {
          let errorMessage = "Erreur lors du chargement de l'utilisateur";

          if (error.error) {
            switch (error.error.error) {
              case 'USER_NOT_FOUND':
                errorMessage = 'Utilisateur non trouvé';
                break;
              case 'SERVER_ERROR':
                errorMessage =
                  "Erreur serveur lors du chargement de l'utilisateur";
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
}
