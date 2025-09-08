import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, interval } from 'rxjs';
import { switchMap, share, startWith } from 'rxjs/operators';
import {
  BadgeuseAccess,
  UserWorkingStatus,
} from '../../interfaces/pointage.interface';
import { BadgeuseApiService } from './badgeuse-api.service';
import { WorkingTimeService } from './working-time.service';

@Injectable({
  providedIn: 'root',
})
export class BadgeuseManagerService {
  private badgeusesSubject = new BehaviorSubject<BadgeuseAccess[]>([]);
  public badgeuses$ = this.badgeusesSubject.asObservable();
  
  private refreshInterval$ = interval(30000).pipe(startWith(0));

  constructor(
    private badgeuseApiService: BadgeuseApiService,
    private workingTimeService: WorkingTimeService
  ) {}

  /**
   * Get badgeuses and update state
   */
  loadBadgeuses(): Observable<{ badgeuses: BadgeuseAccess[], userStatus: UserWorkingStatus }> {
    return this.badgeuseApiService.getBadgeuses().pipe(
      switchMap(result => {
        this.badgeusesSubject.next(result.badgeuses);
        this.workingTimeService.updateUserStatus(result.userStatus);
        return Promise.resolve(result);
      })
    );
  }

  /**
   * Start auto-refresh for real-time updates
   */
  startAutoRefresh(): Observable<{ badgeuses: BadgeuseAccess[], userStatus: UserWorkingStatus }> {
    return this.refreshInterval$.pipe(
      switchMap(() => this.loadBadgeuses()),
      share()
    );
  }

  /**
   * Get current badgeuses from subject
   */
  getCurrentBadgeuses(): BadgeuseAccess[] {
    return this.badgeusesSubject.value;
  }

  /**
   * Check if badgeuse is available for use
   */
  isBadgeuseAvailable(badgeuse: BadgeuseAccess, _userStatus: UserWorkingStatus): boolean {
    if (!badgeuse.is_accessible) return false;
    if (badgeuse.is_blocked) return false;
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

  /**
   * Clear cached data
   */
  clearCache(): void {
    this.badgeusesSubject.next([]);
  }

  // Legacy methods for backward compatibility
  getPrincipalBadgeuse(badgeuses: BadgeuseAccess[]): BadgeuseAccess | null {
    return badgeuses.find(b => b.is_principal) || null;
  }

  getSecondaryBadgeuses(badgeuses: BadgeuseAccess[]): BadgeuseAccess[] {
    return badgeuses.filter(b => !b.is_principal);
  }
}