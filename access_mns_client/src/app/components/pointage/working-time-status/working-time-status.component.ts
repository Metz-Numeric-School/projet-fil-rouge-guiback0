import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { UserWorkingStatus } from '../../../interfaces/pointage.interface';
import { WorkingTimeService } from '../../../services/pointage/working-time.service';

@Component({
  selector: 'app-working-time-status',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatIconModule,
    MatChipsModule
  ],
  template: `
    <mat-card class="status-card">
      <mat-card-header>
        <mat-card-title>
          <mat-icon [color]="getStatusColor()">{{ getStatusIcon() }}</mat-icon>
          Pointage
        </mat-card-title>
        <mat-card-subtitle>{{ getStatusText() }}</mat-card-subtitle>
      </mat-card-header>
      
      <mat-card-content>
        <!-- Working time display -->
        <div class="working-time-section" *ngIf="userStatus">
          <div class="time-display">
            <mat-icon>schedule</mat-icon>
            <span class="time-value">{{ getFormattedWorkingTime() }}</span>
            <span class="time-label">aujourd'hui</span>
          </div>
          
          <div class="session-info" *ngIf="workingStartTime">
            <span class="session-start">
              Session démarrée à {{ workingStartTime | date:'HH:mm' }}
            </span>
          </div>
        </div>

        <!-- Last action info -->
        <div class="last-action" *ngIf="userStatus?.last_action">
          <div class="last-action-title">
            <mat-icon>history</mat-icon>
            <span>Dernier pointage</span>
          </div>
          <mat-chip-set>
            <mat-chip [color]="getActionColor()" selected>
              <mat-icon matChipAvatar>{{ getActionIcon() }}</mat-icon>
              {{ getActionText() }}
            </mat-chip>
            <mat-chip outlined>
              <mat-icon matChipAvatar>device_hub</mat-icon>
              {{ userStatus!.last_action!.badgeuse }}
            </mat-chip>
            <mat-chip outlined>
              <mat-icon matChipAvatar>location_on</mat-icon>
              {{ userStatus!.last_action!.zone }}
            </mat-chip>
            <mat-chip [color]="getServiceColor()" outlined>
              <mat-icon matChipAvatar>{{ getServiceIcon() }}</mat-icon>
              {{ getServiceText() }}
            </mat-chip>
          </mat-chip-set>
          <div class="action-time">
            <mat-icon>access_time</mat-icon>
            <span>{{ userStatus!.last_action!.timestamp | date:'dd/MM/yyyy à HH:mm:ss' }}</span>
          </div>
        </div>
      </mat-card-content>
    </mat-card>
  `,
  styles: [`
    .status-card {
      margin-bottom: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
      background: white;
      transition: all 0.3s ease;

      &:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      }

      mat-card-header {
        padding: 1.5rem 1.5rem 0.5rem;

        mat-card-title {
          font-size: 1.3rem;
          font-weight: 500;
          color: #2c3e50;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }

        mat-card-subtitle {
          color: #7f8c8d;
          font-size: 0.9rem;
          margin-top: 0.25rem;
        }
      }

      mat-card-content {
        padding: 0.5rem 1.5rem 1.5rem;
      }
    }

    .working-time-section {
      padding: 16px 0;
      text-align: center;
    }

    .time-display {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 8px;
    }

    .time-value {
      font-size: 2.5rem;
      font-weight: 500;
      color: #25176e;
    }

    .time-label {
      color: #7f8c8d;
      font-size: 0.9rem;
    }

    .session-info {
      color: #7f8c8d;
      font-size: 0.9rem;
    }

    .last-action {
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .last-action-title {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
      font-weight: 500;
      color: #2c3e50;
    }

    .action-time {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 12px;
      color: #7f8c8d;
      font-size: 0.9rem;
    }

    mat-chip-set {
      margin-bottom: 8px;
    }
  `]
})
export class WorkingTimeStatusComponent {
  @Input() userStatus: UserWorkingStatus | null = null;
  @Input() workingTimeToday: number = 0;
  @Input() workingStartTime: string | null = null;

  constructor(private workingTimeService: WorkingTimeService) {}

  getStatusColor(): string {
    if (!this.userStatus) return 'warn';
    return this.userStatus.status === 'present' ? 'primary' : 'accent';
  }

  getStatusIcon(): string {
    if (!this.userStatus) return 'help_outline';
    return this.userStatus.status === 'present' ? 'work' : 'home';
  }

  getStatusText(): string {
    if (!this.userStatus) return 'Statut inconnu';
    return this.userStatus.status === 'present' ? 'Présent au travail' : 'Absent du travail';
  }

  getFormattedWorkingTime(): string {
    return this.workingTimeService.formatWorkingTime(this.workingTimeToday);
  }

  getActionColor(): string {
    if (!this.userStatus?.last_action) return 'warn';
    const type = this.userStatus.last_action.type;
    return type === 'entree' ? 'primary' : (type === 'sortie' ? 'accent' : 'warn');
  }

  getActionIcon(): string {
    if (!this.userStatus?.last_action) return 'key';
    const type = this.userStatus.last_action.type;
    return type === 'entree' ? 'login' : (type === 'sortie' ? 'logout' : 'key');
  }

  getActionText(): string {
    if (!this.userStatus?.last_action) return 'Accès';
    const type = this.userStatus.last_action.type;
    return type === 'entree' ? 'Entrée' : (type === 'sortie' ? 'Sortie' : 'Accès');
  }

  getServiceColor(): string {
    if (!this.userStatus?.last_action) return 'accent';
    return this.userStatus.last_action.service_type === 'principal' ? 'primary' : 'accent';
  }

  getServiceIcon(): string {
    if (!this.userStatus?.last_action) return 'work';
    return this.userStatus.last_action.service_type === 'principal' ? 'star' : 'work';
  }

  getServiceText(): string {
    if (!this.userStatus?.last_action) return 'Service';
    return this.userStatus.last_action.service_type === 'principal' ? 'Service principal' : 'Service secondaire';
  }
}