import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBarModule, MatSnackBar } from '@angular/material/snack-bar';
import { MatBadgeModule } from '@angular/material/badge';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDividerModule } from '@angular/material/divider';
import { Subscription, timer } from 'rxjs';
import { PointageService } from '../../services/pointage.service';
import {
  BadgeuseAccess,
  UserWorkingStatus,
  PointagePageState,
  PointageActionResponse,
  PointageRequest
} from '../../interfaces/pointage.interface';

@Component({
  selector: 'app-pointage',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatChipsModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatBadgeModule,
    MatTooltipModule,
    MatDividerModule
  ],
  templateUrl: './pointage.component.html',
  styleUrls: ['./pointage.component.scss']
})
export class PointageComponent implements OnInit, OnDestroy {
  state: PointagePageState = {
    isLoading: true,
    badgeuses: [],
    userStatus: null,
    selectedBadgeuse: null,
    isProcessingPointage: false,
    lastError: null,
    workingTimeToday: 0,
    workingStartTime: null,
    autoRefreshInterval: 30,
    countdownSeconds: 0
  };

  private subscriptions = new Subscription();
  private autoRefreshSubscription?: Subscription;
  private countdownSubscription?: Subscription;

  constructor(
    private pointageService: PointageService,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.loadData();
    this.startAutoRefresh();
    this.subscribeToStatusUpdates();
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
    this.stopAutoRefresh();
    this.pointageService.stopAutoRefresh();
  }

  /**
   * Load initial data
   */
  private loadData(): void {
    this.state.isLoading = true;
    this.state.lastError = null;

    const loadSub = this.pointageService.getBadgeuses().subscribe({
      next: (data) => {
        this.state.badgeuses = data.badgeuses;
        this.state.userStatus = data.userStatus;
        this.updateWorkingTime();
        this.updateCountdown();
        this.state.isLoading = false;
      },
      error: (error) => {
        this.state.lastError = error.message;
        this.state.isLoading = false;
        this.showError('Erreur lors du chargement des données');
      }
    });

    this.subscriptions.add(loadSub);
  }

  /**
   * Subscribe to real-time status updates
   */
  private subscribeToStatusUpdates(): void {
    const statusSub = this.pointageService.userStatus$.subscribe(status => {
      if (status) {
        this.state.userStatus = status;
        this.updateWorkingTime();
        this.updateCountdown();
      }
    });

    const badgeusesSub = this.pointageService.badgeuses$.subscribe(badgeuses => {
      this.state.badgeuses = badgeuses;
    });

    const workingTimeSub = this.pointageService.workingTime$.subscribe(minutes => {
      this.state.workingTimeToday = minutes;
    });

    this.subscriptions.add(statusSub);
    this.subscriptions.add(badgeusesSub);
    this.subscriptions.add(workingTimeSub);
  }

  /**
   * Start auto-refresh every 30 seconds
   */
  private startAutoRefresh(): void {
    this.autoRefreshSubscription = this.pointageService.startAutoRefresh().subscribe({
      next: (data) => {
        this.state.badgeuses = data.badgeuses;
        this.state.userStatus = data.userStatus;
        this.updateWorkingTime();
        this.updateCountdown();
      },
      error: (error) => {
        console.error('Auto-refresh failed:', error);
      }
    });
  }

  /**
   * Stop auto-refresh
   */
  private stopAutoRefresh(): void {
    if (this.autoRefreshSubscription) {
      this.autoRefreshSubscription.unsubscribe();
    }
  }

  /**
   * Update countdown timer for next allowed pointage
   */
  private updateCountdown(): void {
    if (this.countdownSubscription) {
      this.countdownSubscription.unsubscribe();
    }

    if (this.state.userStatus?.last_action?.heure) {
      const countdownSub = this.pointageService.getTimeUntilNextPointage(
        this.state.userStatus.last_action.heure
      ).subscribe(seconds => {
        this.state.countdownSeconds = seconds;
      });

      this.subscriptions.add(countdownSub);
      this.countdownSubscription = countdownSub;
    } else {
      this.state.countdownSeconds = 0;
    }
  }

  /**
   * Update working time display
   */
  private updateWorkingTime(): void {
    if (this.state.userStatus) {
      this.state.workingTimeToday = this.state.userStatus.working_time_today || 0;
      
      if (this.state.userStatus.current_work_start) {
        this.state.workingStartTime = new Date(this.state.userStatus.current_work_start);
      } else {
        this.state.workingStartTime = null;
      }
    }
  }

  /**
   * Perform pointage action
   */
  performPointage(badgeuse: BadgeuseAccess, force: boolean = false): void {
    if (this.state.isProcessingPointage) return;

    if (!this.canUseBadgeuse(badgeuse) && !force) {
      this.showWarning('Cette badgeuse n\'est pas disponible');
      return;
    }

    if (this.state.countdownSeconds > 0 && !force) {
      this.showWarning(`Veuillez attendre ${this.state.countdownSeconds} secondes avant de pointer à nouveau`);
      return;
    }

    this.state.isProcessingPointage = true;
    this.state.selectedBadgeuse = badgeuse;

    const request: PointageRequest = {
      badgeuse_id: badgeuse.id,
      force: force
    };

    const pointageSub = this.pointageService.performPointage(request).subscribe({
      next: (response: PointageActionResponse) => {
        this.state.isProcessingPointage = false;
        this.state.selectedBadgeuse = null;

        if (response.success && response.data) {
          this.showSuccess(response.data.message);
          this.state.userStatus = response.data.new_status;
          this.updateWorkingTime();
          this.updateCountdown();
          
          // Refresh badgeuses status
          this.loadData();
        } else {
          this.showError(response.message || 'Erreur lors du pointage');
        }
      },
      error: (error: PointageActionResponse) => {
        this.state.isProcessingPointage = false;
        this.state.selectedBadgeuse = null;

        if (error.warning) {
          // Show warning with option to force
          this.showWarningWithAction(
            error.message || 'Avertissement lors du pointage',
            'Forcer',
            () => this.performPointage(badgeuse, true)
          );
        } else {
          // Handle specific error cases
          let errorMessage = error.message || 'Erreur lors du pointage';
          
          switch (error.error) {
            case 'ZONE_ACCESS_DENIED':
              errorMessage = 'Vous n\'avez pas accès à cette zone';
              break;
            case 'NO_ACTIVE_BADGE':
              errorMessage = 'Aucun badge actif disponible';
              break;
            case 'ACCESS_DENIED':
              errorMessage = 'Accès refusé - vérifiez votre organisation';
              break;
            case 'NO_PRINCIPAL_SERVICE':
              errorMessage = 'Aucun service principal configuré pour votre compte';
              break;
            case 'SECONDARY_ACCESS_DENIED':
              errorMessage = 'Vous devez d\'abord pointer dans votre service principal';
              break;
            case 'NO_ZONES_CONFIGURED':
              errorMessage = 'Cette badgeuse n\'a aucune zone configurée';
              break;
          }
          
          this.showError(errorMessage);
        }
      }
    });

    this.subscriptions.add(pointageSub);
  }

  /**
   * Check if badgeuse can be used
   */
  canUseBadgeuse(badgeuse: BadgeuseAccess): boolean {
    if (!this.state.userStatus) return false;
    
    return this.pointageService.isBadgeuseAvailable(badgeuse, this.state.userStatus);
  }

  /**
   * Get the next action type for a badgeuse
   * SERVICES PRINCIPAUX : Alternent entre "entrée" et "sortie" selon le statut
   * SERVICES SECONDAIRES : Toujours "accès" - ne changent pas le statut
   */
  getNextActionType(badgeuse: BadgeuseAccess): 'entree' | 'sortie' | 'acces' {
    // Si c'est une badgeuse avec accès au service principal
    if (badgeuse.service_type === 'principal' || badgeuse.service_type === 'mixed') {
      return this.state.userStatus?.status === 'present' ? 'sortie' : 'entree';
    }
    // Sinon, c'est un accès aux services secondaires
    return 'acces';
  }

  /**
   * Get principal badgeuse (Zone principale du service principal)
   */
  getPrincipalBadgeuse(): BadgeuseAccess | null {
    return this.pointageService.getPrincipalBadgeuse(this.state.badgeuses);
  }

  /**
   * Get secondary badgeuses (Zones secondaires et autres zones)
   */
  getSecondaryBadgeuses(): BadgeuseAccess[] {
    return this.pointageService.getSecondaryBadgeuses(this.state.badgeuses);
  }

  /**
   * Get badgeuses by service type for enhanced separation
   */
  getPrincipalServiceBadgeuses(): BadgeuseAccess[] {
    return this.state.badgeuses.filter(badgeuse => 
      badgeuse.zones.some(zone => zone.is_principal)
    );
  }

  /**
   * Get secondary service badgeuses (zones that are not principal)
   */
  getSecondaryServiceBadgeuses(): BadgeuseAccess[] {
    return this.state.badgeuses.filter(badgeuse => 
      !badgeuse.zones.some(zone => zone.is_principal) &&
      badgeuse.zones.length > 0
    );
  }

  /**
   * Get mixed service badgeuses (badgeuses that have both principal and secondary zones)
   */
  getMixedServiceBadgeuses(): BadgeuseAccess[] {
    return this.state.badgeuses.filter(badgeuse => {
      const hasPrincipal = badgeuse.zones.some(zone => zone.is_principal);
      const hasSecondary = badgeuse.zones.some(zone => !zone.is_principal);
      return hasPrincipal && hasSecondary;
    });
  }

  /**
   * Format working time for display
   */
  getFormattedWorkingTime(): string {
    return this.pointageService.formatWorkingTime(this.state.workingTimeToday);
  }

  /**
   * Format countdown for display
   */
  getFormattedCountdown(): string {
    if (this.state.countdownSeconds <= 0) return '';
    
    const minutes = Math.floor(this.state.countdownSeconds / 60);
    const seconds = this.state.countdownSeconds % 60;
    
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
  }

  /**
   * Get status color
   */
  getStatusColor(): string {
    if (!this.state.userStatus) return 'warn';
    
    switch (this.state.userStatus.status) {
      case 'present':
        return this.state.userStatus.is_in_principal_zone ? 'primary' : 'accent';
      case 'absent':
        return 'warn';
      default:
        return 'warn';
    }
  }

  /**
   * Get status icon
   */
  getStatusIcon(): string {
    if (!this.state.userStatus) return 'help';
    
    switch (this.state.userStatus.status) {
      case 'present':
        return this.state.userStatus.is_in_principal_zone ? 'work' : 'location_on';
      case 'absent':
        return 'work_off';
      default:
        return 'help';
    }
  }

  /**
   * Get status text with enhanced service information
   */
  getStatusText(): string {
    if (!this.state.userStatus) return 'Statut inconnu';
    
    switch (this.state.userStatus.status) {
      case 'present':
        const lastAction = this.state.userStatus.last_action;
        if (lastAction?.affects_status) {
          return `Présent - Service Principal`;
        } else if (lastAction?.service_type === 'secondaire') {
          return `Présent - Accès Service Secondaire`;
        } else {
          return 'Présent';
        }
      case 'absent':
        return 'Absent';
      default:
        return 'Statut inconnu';
    }
  }

  /**
   * Get badge access information for a badgeuse
   */
  getBadgeuseAccessInfo(badgeuse: BadgeuseAccess): string {
    const principalZones = badgeuse.zones.filter(z => z.is_principal);
    const secondaryZones = badgeuse.zones.filter(z => !z.is_principal);
    
    switch (badgeuse.service_type) {
      case 'principal':
        return `Service principal (${principalZones.length} zone(s))`;
      case 'secondaire':
        return `Service secondaire (${secondaryZones.length} zone(s))`;
      case 'mixed':
        return `Accès mixte (${principalZones.length} principale(s), ${secondaryZones.length} secondaire(s))`;
      default:
        return `${badgeuse.zones.length} zone(s) disponible(s)`;
    }
  }

  /**
   * Check if badgeuse provides access to principal service
   */
  hasPrincipalAccess(badgeuse: BadgeuseAccess): boolean {
    return badgeuse.zones.some(zone => zone.is_principal);
  }

  /**
   * Check if badgeuse provides access to secondary services only
   */
  hasSecondaryAccessOnly(badgeuse: BadgeuseAccess): boolean {
    return badgeuse.zones.length > 0 && !badgeuse.zones.some(zone => zone.is_principal);
  }

  /**
   * Refresh data manually
   */
  refresh(): void {
    this.loadData();
  }

  /**
   * Show success message
   */
  private showSuccess(message: string): void {
    this.snackBar.open(message, 'Fermer', {
      duration: 5000,
      panelClass: ['success-snackbar']
    });
  }

  /**
   * Show error message
   */
  private showError(message: string): void {
    this.snackBar.open(message, 'Fermer', {
      duration: 7000,
      panelClass: ['error-snackbar']
    });
  }

  /**
   * Show warning message
   */
  private showWarning(message: string): void {
    this.snackBar.open(message, 'Fermer', {
      duration: 5000,
      panelClass: ['warning-snackbar']
    });
  }

  /**
   * Show warning with action button
   */
  private showWarningWithAction(message: string, actionText: string, action: () => void): void {
    const snackBarRef = this.snackBar.open(message, actionText, {
      duration: 10000,
      panelClass: ['warning-snackbar']
    });

    snackBarRef.onAction().subscribe(action);
  }
}