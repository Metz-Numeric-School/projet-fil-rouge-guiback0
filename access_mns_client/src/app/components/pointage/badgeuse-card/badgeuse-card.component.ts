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
      class="badgeuse-card" 
      [class.selected]="isSelected"
      [class.disabled]="!isAvailable"
      [class.principal]="isPrincipal"
    >
      <mat-card-header>
        <mat-card-title class="badgeuse-title">
          <mat-icon [color]="getStatusColor()">{{ getStatusIcon() }}</mat-icon>
          {{ badgeuse.reference }}
          <mat-icon 
            *ngIf="isPrincipal" 
            class="principal-badge"
            color="primary"
            matTooltip="Badgeuse principale"
          >
            star
          </mat-icon>
        </mat-card-title>
        <mat-card-subtitle>Badgeuse de pointage</mat-card-subtitle>
      </mat-card-header>

      <mat-card-content>
        <!-- Installation date -->
        <div class="location-info" *ngIf="badgeuse.date_installation">
          <mat-icon>date_range</mat-icon>
          <span>Installée le {{ badgeuse.date_installation | date:'dd/MM/yyyy' }}</span>
        </div>

        <!-- Zones access -->
        <div class="zones-section" *ngIf="badgeuse.zones && badgeuse.zones.length > 0">
          <div class="zones-title">
            <mat-icon>security</mat-icon>
            <span>Zones accessibles ({{ badgeuse.zones.length }})</span>
          </div>
          <mat-chip-set>
            <mat-chip 
              *ngFor="let zone of badgeuse.zones" 
              [color]="zone.is_principal ? 'primary' : 'accent'"
              outlined
            >
              <mat-icon matChipAvatar>{{ zone.is_principal ? 'star' : 'work' }}</mat-icon>
              {{ zone.nom_zone }}
            </mat-chip>
          </mat-chip-set>
        </div>

        <!-- Service type info -->
        <div class="service-info">
          <mat-icon>{{ getServiceIcon() }}</mat-icon>
          <span>{{ getServiceDescription() }}</span>
        </div>

        <!-- Status chip -->
        <div class="status-section">
          <mat-chip [color]="getStatusColor()" selected>
            <mat-icon matChipAvatar>{{ getStatusIcon() }}</mat-icon>
            {{ getStatusText() }}
          </mat-chip>
        </div>
      </mat-card-content>

      <mat-card-actions align="end">
        <button 
          mat-raised-button 
          color="primary"
          [disabled]="!isAvailable || isProcessing"
          (click)="onToggleSelect()"
        >
          <mat-spinner *ngIf="isProcessing; else pointageIcon" diameter="20"></mat-spinner>
          <ng-template #pointageIcon>
            <mat-icon>touch_app</mat-icon>
          </ng-template>
          {{ isProcessing ? 'En cours...' : 'Pointer' }}
        </button>
      </mat-card-actions>
    </mat-card>
  `,
  styles: [`
    .badgeuse-card {
      margin-bottom: 16px;
      transition: all 0.3s ease;
      cursor: pointer;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
      background: white;
    }

    .badgeuse-card:hover:not(.disabled) {
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      transform: translateY(-4px);
    }

    .badgeuse-card.selected {
      border: 2px solid #25176e;
      box-shadow: 0 0 10px rgba(37, 23, 110, 0.3);
    }

    .badgeuse-card.disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .badgeuse-card.principal {
      border-left: 4px solid #ff5d2d;
    }

    mat-card-header {
      padding: 1.5rem 1.5rem 0.5rem;
    }

    mat-card-title {
      font-size: 1.3rem;
      font-weight: 500;
      color: #2c3e50;
    }

    mat-card-subtitle {
      color: #7f8c8d;
      font-size: 0.9rem;
      margin-top: 0.25rem;
    }

    mat-card-content {
      padding: 0.5rem 1.5rem 1rem;
      color: #5a6c7d;
      line-height: 1.5;
    }

    mat-card-actions {
      padding: 0 1.5rem 1.5rem;

      button {
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }
    }

    .badgeuse-title {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .principal-badge {
      margin-left: auto;
    }

    .location-info {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 12px 0;
      color: #666;
      font-size: 0.9rem;
    }

    .zones-section {
      margin: 16px 0;
    }

    .zones-title {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
      font-weight: 500;
      color: #333;
    }

    .service-info {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 12px 0;
      color: #666;
      font-size: 0.9rem;
    }

    .status-section {
      margin: 12px 0;
    }

    mat-chip-set {
      margin: 8px 0;
    }

    mat-card-actions {
      padding-top: 8px;
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