import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject, interval, throwError, timer, of } from 'rxjs';
import { map, catchError, switchMap, startWith, share, tap } from 'rxjs/operators';
import {
  BadgeusesResponse,
  BadgeuseAccess,
  UserWorkingStatus,
  PointageActionResponse,
  PointageRequest,
  WorkingTimePeriod,
  PointageValidation,
  PointageStatusUpdate,
  PointageErrorCode
} from '../interfaces/pointage.interface';

@Injectable({
  providedIn: 'root',
})
export class PointageService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api/pointage';
  
  // Real-time status tracking
  private userStatusSubject = new BehaviorSubject<UserWorkingStatus | null>(null);
  public userStatus$ = this.userStatusSubject.asObservable();
  
  private badgeusesSubject = new BehaviorSubject<BadgeuseAccess[]>([]);
  public badgeuses$ = this.badgeusesSubject.asObservable();
  
  private workingTimeSubject = new BehaviorSubject<number>(0);
  public workingTime$ = this.workingTimeSubject.asObservable();
  
  // Auto-refresh observable (every 30 seconds)
  private refreshInterval$ = interval(30000).pipe(startWith(0));

  constructor(private http: HttpClient) {}

  /**
   * Determine badgeuse status based on backend properties
   */
  private determineBadgeuseStatus(badgeuse: any): 'available' | 'blocked' | 'error' {
    if (badgeuse.is_blocked) return 'blocked';
    if (!badgeuse.is_accessible) return 'blocked';
    return 'available';
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
   * Get stored JWT token
   */
  private getToken(): string | null {
    return (
      localStorage.getItem('access_mns_token') ||
      sessionStorage.getItem('access_mns_token')
    );
  }

  /**
   * Get all accessible badgeuses for the current user with their status
   * Uses: GET /api/pointage/badgeuses
   */
  getBadgeuses(): Observable<{ badgeuses: BadgeuseAccess[], userStatus: UserWorkingStatus }> {
    const headers = this.getAuthHeaders();

    return this.http
      .get<BadgeusesResponse>(`${this.API_BASE_URL}/badgeuses`, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            // Transform badgeuses data to include status property expected by frontend
            const transformedBadgeuses = response.data.badgeuses.map(badgeuse => ({
              ...badgeuse,
              status: this.determineBadgeuseStatus(badgeuse)
            }));
            
            // Update subjects with transformed data
            this.badgeusesSubject.next(transformedBadgeuses);
            this.userStatusSubject.next(response.data.user_status);
            this.updateWorkingTime(response.data.user_status);
            
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
    const headers = this.getAuthHeaders();

    return this.http
      .post<PointageActionResponse>(`${this.API_BASE_URL}/action`, request, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            // Update user status after successful pointage
            this.userStatusSubject.next(response.data.new_status);
            this.updateWorkingTime(response.data.new_status);
            
            // Refresh badgeuses to update their availability
            this.refreshBadgeusesStatus();
            
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

          // Return the error response with proper structure
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
   * Get current user status (present/absent)
   * Uses: GET /api/pointage/status
   */
  getCurrentStatus(): Observable<UserWorkingStatus> {
    const headers = this.getAuthHeaders();

    return this.http
      .get<{ success: boolean; data: UserWorkingStatus; error?: string; message?: string }>(
        `${this.API_BASE_URL}/status`, 
        { headers }
      )
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            this.userStatusSubject.next(response.data);
            this.updateWorkingTime(response.data);
            return response.data;
          }
          throw new Error(
            response.message || 'Erreur lors de la récupération du statut'
          );
        }),
        catchError((error) => {
          const errorMessage = error.error?.message || 'Erreur lors de la récupération du statut';
          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Get working time for a specific period
   * Uses: GET /api/pointage/working-time
   */
  getWorkingTime(startDate: string, endDate: string): Observable<WorkingTimePeriod> {
    const headers = this.getAuthHeaders();
    const params = { start_date: startDate, end_date: endDate };

    return this.http
      .get<{ success: boolean; data: WorkingTimePeriod; error?: string; message?: string }>(
        `${this.API_BASE_URL}/working-time`,
        { headers, params }
      )
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response.data;
          }
          throw new Error(
            response.message || 'Erreur lors du calcul du temps de travail'
          );
        }),
        catchError((error) => {
          const errorMessage = error.error?.message || 'Erreur lors du calcul du temps de travail';
          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Validate a pointage action before performing it
   * Uses: POST /api/pointage/validate
   */
  validatePointage(badgeuseId: number): Observable<PointageValidation> {
    const headers = this.getAuthHeaders();

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
          return of({
            is_valid: false,
            can_proceed: false,
            message: error.error?.message || 'Erreur lors de la validation'
          });
        })
      );
  }

  /**
   * Start auto-refresh for real-time updates
   */
  startAutoRefresh(): Observable<{ badgeuses: BadgeuseAccess[], userStatus: UserWorkingStatus }> {
    return this.refreshInterval$.pipe(
      switchMap(() => this.getBadgeuses()),
      share() // Share the subscription among multiple subscribers
    );
  }

  /**
   * Stop auto-refresh
   */
  stopAutoRefresh(): void {
    // The interval will be stopped when the component unsubscribes
  }

  /**
   * Refresh badgeuses status without full reload
   */
  private refreshBadgeusesStatus(): void {
    this.getBadgeuses().subscribe({
      next: () => {
        // Data is automatically updated in subjects by getBadgeuses()
      },
      error: (error) => {
        console.error('Failed to refresh badgeuses status:', error);
      }
    });
  }

  /**
   * Update working time calculation
   */
  private updateWorkingTime(status: UserWorkingStatus): void {
    let workingMinutes = 0;

    if (status.working_time_today) {
      workingMinutes = status.working_time_today;
    } else if (status.status === 'present' && status.current_work_start) {
      // Calculate current session time
      const startTime = new Date(status.current_work_start);
      const now = new Date();
      const diffMs = now.getTime() - startTime.getTime();
      workingMinutes = Math.floor(diffMs / (1000 * 60));
    }

    this.workingTimeSubject.next(workingMinutes);
  }

  /**
   * Format working time for display
   */
  formatWorkingTime(minutes: number): string {
    if (minutes <= 0) return '0h00';
    
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    
    return `${hours}h${mins.toString().padStart(2, '0')}`;
  }

  /**
   * Get time until next allowed pointage
   * Note: In zones indépendantes mode, there's no minimum delay restriction
   */
  getTimeUntilNextPointage(lastActionTime: string): Observable<number> {
    const lastAction = new Date(lastActionTime);
    const twoMinutesLater = new Date(lastAction.getTime() + 2 * 60 * 1000);
    
    return timer(0, 1000).pipe(
      map(() => {
        const now = new Date();
        // ZONES INDÉPENDANTES: Pas de délai minimum selon la logique backend
        // Mais on garde le timer pour la compatibilité de l'interface
        const remaining = Math.max(0, Math.floor((twoMinutesLater.getTime() - now.getTime()) / 1000));
        return remaining;
      })
    );
  }

  /**
   * Check if badgeuse is available for use
   * ZONES INDÉPENDANTES : Toutes les zones sont accessibles si l'utilisateur a les permissions
   */
  isBadgeuseAvailable(badgeuse: BadgeuseAccess, _userStatus: UserWorkingStatus): boolean {
    if (!badgeuse.is_accessible) return false;
    if (badgeuse.is_blocked) return false;
    
    // ZONES INDÉPENDANTES : Pas de restriction basée sur le statut présent/absent
    // Toutes les zones sont accessibles à tout moment si l'utilisateur a les permissions
    // La logique de contrôle d'accès se fait côté backend
    
    return true;
  }

  /**
   * Get service access type for a badgeuse
   */
  getBadgeuseServiceType(badgeuse: BadgeuseAccess): 'principal' | 'secondary' | 'mixed' | 'none' {
    const hasPrincipal = badgeuse.zones.some(zone => zone.is_principal);
    const hasSecondary = badgeuse.zones.some(zone => !zone.is_principal);
    
    if (hasPrincipal && hasSecondary) return 'mixed';
    if (hasPrincipal) return 'principal';
    if (hasSecondary) return 'secondary';
    return 'none';
  }

  /**
   * Get access description for a badgeuse
   */
  getBadgeuseAccessDescription(badgeuse: BadgeuseAccess): string {
    const serviceType = this.getBadgeuseServiceType(badgeuse);
    const principalCount = badgeuse.zones.filter(z => z.is_principal).length;
    const secondaryCount = badgeuse.zones.filter(z => !z.is_principal).length;
    
    switch (serviceType) {
      case 'principal':
        return `Service principal (${principalCount} zone${principalCount > 1 ? 's' : ''})`;
      case 'secondary':
        return `Service secondaire (${secondaryCount} zone${secondaryCount > 1 ? 's' : ''})`;
      case 'mixed':
        return `Accès mixte (${principalCount} principale${principalCount > 1 ? 's' : ''}, ${secondaryCount} secondaire${secondaryCount > 1 ? 's' : ''})`;
      default:
        return 'Aucun accès configuré';
    }
  }

  /**
   * Get the principal badgeuse from the list (legacy method)
   */
  getPrincipalBadgeuse(badgeuses: BadgeuseAccess[]): BadgeuseAccess | null {
    return badgeuses.find(b => b.is_principal) || null;
  }

  /**
   * Get secondary badgeuses from the list (legacy method)
   */
  getSecondaryBadgeuses(badgeuses: BadgeuseAccess[]): BadgeuseAccess[] {
    return badgeuses.filter(b => !b.is_principal);
  }

  /**
   * Get badgeuses that provide access to principal service zones
   */
  getPrincipalServiceBadgeuses(badgeuses: BadgeuseAccess[]): BadgeuseAccess[] {
    return badgeuses.filter(badgeuse => 
      badgeuse.zones.some(zone => zone.is_principal)
    );
  }

  /**
   * Get badgeuses that provide access to secondary service zones only
   */
  getSecondaryServiceBadgeuses(badgeuses: BadgeuseAccess[]): BadgeuseAccess[] {
    return badgeuses.filter(badgeuse => 
      badgeuse.zones.length > 0 &&
      !badgeuse.zones.some(zone => zone.is_principal)
    );
  }

  /**
   * Get badgeuses that provide access to both principal and secondary zones
   */
  getMixedServiceBadgeuses(badgeuses: BadgeuseAccess[]): BadgeuseAccess[] {
    return badgeuses.filter(badgeuse => {
      const hasPrincipal = badgeuse.zones.some(zone => zone.is_principal);
      const hasSecondary = badgeuse.zones.some(zone => !zone.is_principal);
      return hasPrincipal && hasSecondary;
    });
  }

  /**
   * Categorize badgeuses by their service access type
   */
  categorizeBadgeuses(badgeuses: BadgeuseAccess[]): {
    principal: BadgeuseAccess[];
    secondary: BadgeuseAccess[];
    mixed: BadgeuseAccess[];
    total: number;
  } {
    return {
      principal: this.getPrincipalServiceBadgeuses(badgeuses),
      secondary: this.getSecondaryServiceBadgeuses(badgeuses),
      mixed: this.getMixedServiceBadgeuses(badgeuses),
      total: badgeuses.length
    };
  }

  /**
   * Get current user status from subject
   */
  getCurrentUserStatus(): UserWorkingStatus | null {
    return this.userStatusSubject.value;
  }

  /**
   * Get current badgeuses from subject
   */
  getCurrentBadgeuses(): BadgeuseAccess[] {
    return this.badgeusesSubject.value;
  }

  /**
   * Clear all cached data (useful on logout)
   */
  clearCache(): void {
    this.userStatusSubject.next(null);
    this.badgeusesSubject.next([]);
    this.workingTimeSubject.next(0);
  }

  /**
   * Get usage statistics for badgeuses
   */
  getBadgeuseStatistics(badgeuses: BadgeuseAccess[]): {
    total: number;
    available: number;
    blocked: number;
    principalService: number;
    secondaryService: number;
    mixedService: number;
    byStatus: Record<string, number>;
  } {
    const categorized = this.categorizeBadgeuses(badgeuses);
    
    return {
      total: badgeuses.length,
      available: badgeuses.filter(b => b.status === 'available').length,
      blocked: badgeuses.filter(b => b.status === 'blocked').length,
      principalService: categorized.principal.length,
      secondaryService: categorized.secondary.length,
      mixedService: categorized.mixed.length,
      byStatus: badgeuses.reduce((acc, badgeuse) => {
        acc[badgeuse.status] = (acc[badgeuse.status] || 0) + 1;
        return acc;
      }, {} as Record<string, number>)
    };
  }
}