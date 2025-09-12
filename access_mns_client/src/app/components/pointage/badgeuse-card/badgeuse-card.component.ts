import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatBadgeModule } from '@angular/material/badge';
import { MatTooltipModule } from '@angular/material/tooltip';
import { BadgeuseAccess, UserWorkingStatus } from '../../../interfaces/pointage.interface';
import { BadgeuseManagerService } from '../../../services/pointage/badgeuse-manager.service';

@Component({
  selector: 'app-badgeuse-card',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatChipsModule,
    MatProgressSpinnerModule,
    MatBadgeModule,
    MatTooltipModule
  ],
  template: `
    <mat-card 
      class="dashboard-card badgeuse-card" 
      [class.selected]="isSelected"
      [class.disabled]="!isAvailable"
      [class.principal]="isPrincipal"
    >
      <mat-card-header>
        <mat-card-title class="badgeuse-title">
          {{ badgeuse.reference }}
          <mat-icon 
            *ngIf="isPrincipal" 
            class="principal-badge"
            matTooltip="Badgeuse principale"
          >
            star
          </mat-icon>
        </mat-card-title>
        <mat-card-subtitle>Badgeuse de pointage</mat-card-subtitle>
      </mat-card-header>

      <mat-card-content>
        <div class="info-grid">
          <!-- Installation date -->
          <div class="info-item" *ngIf="badgeuse.date_installation">
            <strong>Date d'installation:</strong>
            <span>{{ badgeuse.date_installation | date:'dd/MM/yyyy' }}</span>
          </div>

          <!-- Service type info -->
          <div class="info-item">
            <strong>Type d'accès:</strong>
            <span>{{ getServiceDescription() }}</span>
          </div>

          <!-- Status -->
          <div class="info-item">
            <strong>Statut:</strong>
            <span class="status" [class]="'status-' + badgeuse.status">{{ getStatusText() }}</span>
          </div>

          <!-- Zones access -->
          <div class="info-item" *ngIf="badgeuse.zones && badgeuse.zones.length > 0">
            <strong>Zones accessibles:</strong>
            <span>{{ badgeuse.zones.length }} zone(s)</span>
          </div>
        </div>
      </mat-card-content>

      <mat-card-actions>
        <button 
          mat-button
          color="primary"
          [disabled]="!isAvailable || isProcessing"
          (click)="onToggleSelect()"
        >
          <mat-spinner *ngIf="isProcessing; else pointageIcon" diameter="20"></mat-spinner>
          <ng-template #pointageIcon>
            <mat-icon>arrow_forward</mat-icon>
          </ng-template>
          {{ isProcessing ? 'En cours...' : 'Pointer' }}
        </button>
      </mat-card-actions>
    </mat-card>
  `,
  styles: [`
    // Styles spécifiques aux badgeuses (s'ajoutent aux styles globaux dashboard-card)
    .badgeuse-card.selected {
      border: 2px solid #ff6f61;
      box-shadow: 0 0 10px rgba(255, 111, 97, 0.3);
    }

    .badgeuse-card.disabled {
      opacity: 0.6;
      cursor: not-allowed;
      
      &:hover {
        transform: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
      }
    }


    .badgeuse-title {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .principal-badge {
      margin-left: auto;
      color: #6c757d;
    }

    // Style sobre comme personal-info
    .info-grid {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .info-item {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 0.5rem 0;
      border-bottom: 1px solid #f0f0f0;

      &:last-child {
        border-bottom: none;
      }

      strong {
        color: #2c3e50;
        font-weight: 500;
        flex: 0 0 40%;
      }

      span {
        color: #5a6c7d;
        flex: 1;
        text-align: right;
      }
    }

    .status {
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 500;

      &.status-available {
        background-color: #d4edda;
        color: #155724;
      }

      &.status-blocked {
        background-color: #f8d7da;
        color: #721c24;
      }

      &.status-error {
        background-color: #fff3cd;
        color: #856404;
      }
    }
  `]
})
export class BadgeuseCardComponent {
  @Input() badgeuse!: BadgeuseAccess;
  @Input() userStatus: UserWorkingStatus | null = null;
  @Input() isSelected: boolean = false;
  @Input() isProcessing: boolean = false;

  @Output() toggleSelect = new EventEmitter<BadgeuseAccess>();

  constructor(private badgeuseManagerService: BadgeuseManagerService) {}

  get isAvailable(): boolean {
    if (!this.userStatus) return false;
    return this.badgeuseManagerService.isBadgeuseAvailable(this.badgeuse, this.userStatus);
  }

  get isPrincipal(): boolean {
    return this.badgeuse.is_principal || false;
  }

  getStatusColor(): string {
    switch (this.badgeuse.status) {
      case 'available':
        return 'primary';
      case 'blocked':
        return 'warn';
      default:
        return 'accent';
    }
  }

  getStatusIcon(): string {
    switch (this.badgeuse.status) {
      case 'available':
        return 'check_circle';
      case 'blocked':
        return 'block';
      default:
        return 'error';
    }
  }

  getStatusText(): string {
    switch (this.badgeuse.status) {
      case 'available':
        return 'Disponible';
      case 'blocked':
        return 'Bloquée';
      default:
        return 'Erreur';
    }
  }

  getServiceIcon(): string {
    const serviceType = this.badgeuseManagerService.getBadgeuseServiceType(this.badgeuse);
    switch (serviceType) {
      case 'principal':
        return 'star';
      case 'secondary':
        return 'work';
      case 'mixed':
        return 'workspaces';
      default:
        return 'help_outline';
    }
  }

  getServiceDescription(): string {
    return this.badgeuseManagerService.getBadgeuseAccessDescription(this.badgeuse);
  }

  onToggleSelect(): void {
    if (this.isAvailable && !this.isProcessing) {
      this.toggleSelect.emit(this.badgeuse);
    }
  }
}