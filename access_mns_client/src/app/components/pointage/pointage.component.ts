import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBarModule, MatSnackBar } from '@angular/material/snack-bar';
import { Subscription } from 'rxjs';

import { BadgeuseApiService } from '../../services/pointage/badgeuse-api.service';
import { BadgeuseManagerService } from '../../services/pointage/badgeuse-manager.service';
import { WorkingTimeService } from '../../services/pointage/working-time.service';

import {
  BadgeuseAccess,
  PointagePageState,
  PointageActionResponse,
  PointageRequest
} from '../../interfaces/pointage.interface';

// Import sub-components
import { WorkingTimeStatusComponent } from './working-time-status/working-time-status.component';
import { BadgeuseCardComponent } from './badgeuse-card/badgeuse-card.component';

// Import pipes (si nécessaire dans le futur)
// import { WorkingTimeFormatPipe } from '../../pipes/working-time-format.pipe';
// import { CountdownFormatPipe } from '../../pipes/countdown-format.pipe';

@Component({
  selector: 'app-pointage',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    // Sub-components
    WorkingTimeStatusComponent,
    BadgeuseCardComponent
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
    autoRefreshInterval: 30
  };

  private subscriptions = new Subscription();
  private autoRefreshSubscription?: Subscription;

  constructor(
    private badgeuseApiService: BadgeuseApiService,
    private badgeuseManagerService: BadgeuseManagerService,
    private workingTimeService: WorkingTimeService,
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
  }

  /**
   * Load initial data
   */
  private loadData(): void {
    this.state.isLoading = true;
    this.state.lastError = null;

    const loadSub = this.badgeuseManagerService.loadBadgeuses().subscribe({
      next: (data) => {
        this.state.badgeuses = data.badgeuses;
        this.state.userStatus = data.userStatus;
        
        // Update the working time service with the current user status
        if (data.userStatus) {
          this.workingTimeService.updateUserStatus(data.userStatus);
        }
        
        this.updateWorkingTime();
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
    const statusSub = this.workingTimeService.userStatus$.subscribe(status => {
      if (status) {
        this.state.userStatus = status;
        this.updateWorkingTime();
      }
    });

    const badgeusesSub = this.badgeuseManagerService.badgeuses$.subscribe(badgeuses => {
      this.state.badgeuses = badgeuses;
    });

    const workingTimeSub = this.workingTimeService.workingTime$.subscribe(minutes => {
      this.state.workingTimeToday = minutes;
    });

    this.subscriptions.add(statusSub);
    this.subscriptions.add(badgeusesSub);
    this.subscriptions.add(workingTimeSub);
  }

  /**
   * Start auto-refresh
   */
  private startAutoRefresh(): void {
    this.autoRefreshSubscription = this.badgeuseManagerService.startAutoRefresh().subscribe({
      next: (data) => {
        this.state.badgeuses = data.badgeuses;
        this.state.userStatus = data.userStatus;
        
        // Update the working time service with the current user status
        if (data.userStatus) {
          this.workingTimeService.updateUserStatus(data.userStatus);
        }
        
        this.updateWorkingTime();
        this.state.lastError = null;
      },
      error: (error) => {
        this.state.lastError = error.message;
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
   * Update working time and session info
   */
  private updateWorkingTime(): void {
    if (this.state.userStatus) {
      this.state.workingStartTime = this.state.userStatus.current_work_start || null;
      // Note: workingTimeToday is now updated via the workingTime$ subscription
      // to ensure real-time updates from the WorkingTimeService
    }
  }


  /**
   * Perform direct pointage on badgeuse click
   */
  performDirectPointage(badgeuse: BadgeuseAccess): void {
    if (!badgeuse || this.state.isProcessingPointage) return;

    // Determine action type based on user status and badgeuse type
    let actionType = 'acces'; // Default action
    
    if (this.state.userStatus) {
      if (this.state.userStatus.status === 'absent') {
        actionType = 'entree';
      } else if (this.state.userStatus.status === 'present') {
        // Check if it's a principal service badgeuse for entry/exit
        const isMainService = badgeuse.zones?.some(zone => zone.is_principal);
        actionType = isMainService ? 'sortie' : 'acces';
      }
    }

    this.performPointage({ badgeuse, actionType });
  }

  /**
   * Perform pointage action
   */
  performPointage(event: { badgeuse: BadgeuseAccess; actionType: string }): void {
    if (!event.badgeuse || this.state.isProcessingPointage) return;

    this.state.isProcessingPointage = true;
    this.state.lastError = null;
    this.state.selectedBadgeuse = event.badgeuse; // Set for loading indicator

    const request: PointageRequest = {
      badgeuse_id: event.badgeuse.id,
      action_type: event.actionType
    };

    const pointageSub = this.badgeuseApiService.performPointage(request).subscribe({
      next: (response: PointageActionResponse) => {
        this.handlePointageSuccess(response, event.actionType);
        this.state.isProcessingPointage = false;
        this.state.selectedBadgeuse = null;
      },
      error: (response: PointageActionResponse) => {
        this.handlePointageError(response);
        this.state.isProcessingPointage = false;
        this.state.selectedBadgeuse = null;
      }
    });

    this.subscriptions.add(pointageSub);
  }

  /**
   * Handle successful pointage
   */
  private handlePointageSuccess(response: PointageActionResponse, actionType: string): void {
    const actionText = this.getActionText(actionType);
    let message = `${actionText} enregistré avec succès`;
    
    if (response.warning) {
      message += ` (${response.warning})`;
    }

    this.snackBar.open(message, 'Fermer', {
      duration: 4000,
      panelClass: ['success-snackbar']
    });

    // Force immediate refresh of user status and working time
    this.refreshDataAfterPointage();
  }

  /**
   * Handle pointage error
   */
  private handlePointageError(response: PointageActionResponse): void {
    this.state.lastError = response.message || 'Erreur lors du pointage';
    
    this.snackBar.open(response.message || 'Erreur lors du pointage', 'Fermer', {
      duration: 6000,
      panelClass: ['error-snackbar']
    });
  }

  /**
   * Get action text for display
   */
  private getActionText(actionType: string): string {
    switch (actionType) {
      case 'entree': return 'Entrée';
      case 'sortie': return 'Sortie';
      case 'acces': return 'Accès';
      default: return 'Action';
    }
  }

  /**
   * Retry loading data
   */
  retryLoad(): void {
    this.loadData();
  }

  /**
   * Dismiss error message
   */
  dismissError(): void {
    this.state.lastError = null;
  }

  /**
   * Show error message
   */
  private showError(message: string): void {
    this.snackBar.open(message, 'Fermer', {
      duration: 5000,
      panelClass: ['error-snackbar']
    });
  }

  /**
   * Force refresh of data after successful pointage
   */
  private refreshDataAfterPointage(): void {
    // Force refresh from BadgeuseManagerService
    const refreshSub = this.badgeuseManagerService.loadBadgeuses().subscribe({
      next: (data) => {
        this.state.badgeuses = data.badgeuses;
        this.state.userStatus = data.userStatus;
        
        // Update the working time service with the fresh user status
        if (data.userStatus) {
          this.workingTimeService.updateUserStatus(data.userStatus);
        }
        
        this.updateWorkingTime();
      },
      error: (error) => {
        console.warn('Failed to refresh data after pointage:', error);
      }
    });

    this.subscriptions.add(refreshSub);
  }

  // Template helper methods
  
  /**
   * Get principal badgeuses
   */
  getPrincipalBadgeuses(): BadgeuseAccess[] {
    return this.badgeuseManagerService.getPrincipalServiceBadgeuses(this.state.badgeuses);
  }

  /**
   * Get secondary badgeuses
   */
  getSecondaryBadgeuses(): BadgeuseAccess[] {
    return this.badgeuseManagerService.getSecondaryServiceBadgeuses(this.state.badgeuses);
  }

  /**
   * TrackBy function for badgeuses list
   */
  trackByBadgeuseId(_index: number, badgeuse: BadgeuseAccess): number {
    return badgeuse.id;
  }
}