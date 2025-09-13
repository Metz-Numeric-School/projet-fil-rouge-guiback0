import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatBadgeModule } from '@angular/material/badge';
import { MatTooltipModule } from '@angular/material/tooltip';
import { BadgeuseAccess, UserWorkingStatus } from '../../../interfaces/pointage.interface';

@Component({
  selector: 'app-pointage-actions',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatBadgeModule,
    MatTooltipModule
  ],
  template: `
    <mat-card class="actions-card" *ngIf="selectedBadgeuse">
      <mat-card-header>
        <mat-card-title>
          <mat-icon color="primary">touch_app</mat-icon>
          Action de pointage
        </mat-card-title>
        <mat-card-subtitle>Badgeuse: {{ selectedBadgeuse.reference }}</mat-card-subtitle>
      </mat-card-header>

      <mat-card-content>
        <!-- Countdown display -->
        <div class="countdown-section" *ngIf="countdownSeconds > 0">
          <div class="countdown-display">
            <mat-icon color="warn">timer</mat-icon>
            <span class="countdown-text">
              Prochaine action dans {{ formatCountdown(countdownSeconds) }}
            </span>
          </div>
          <div class="countdown-info">
            <small>Délai de sécurité entre les actions</small>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="action-buttons" *ngIf="countdownSeconds === 0">
          <div class="button-group">
            <button
              mat-raised-button
              color="primary"
              class="action-button entry-button"
              [disabled]="isProcessing"
              (click)="onPerformAction('entree')"
              matTooltip="Pointer l'entrée sur cette badgeuse"
            >
              <mat-spinner *ngIf="isProcessing; else entryIcon" diameter="20"></mat-spinner>
              <ng-template #entryIcon>
                <mat-icon>login</mat-icon>
              </ng-template>
              Entrée
            </button>

            <button
              mat-raised-button
              color="accent"
              class="action-button exit-button"
              [disabled]="isProcessing"
              (click)="onPerformAction('sortie')"
              matTooltip="Pointer la sortie sur cette badgeuse"
            >
              <mat-spinner *ngIf="isProcessing; else exitIcon" diameter="20"></mat-spinner>
              <ng-template #exitIcon>
                <mat-icon>logout</mat-icon>
              </ng-template>
              Sortie
            </button>

            <button
              mat-raised-button
              color="warn"
              class="action-button access-button"
              [disabled]="isProcessing"
              (click)="onPerformAction('acces')"
              matTooltip="Accéder à cette zone"
            >
              <mat-spinner *ngIf="isProcessing; else accessIcon" diameter="20"></mat-spinner>
              <ng-template #accessIcon>
                <mat-icon>key</mat-icon>
              </ng-template>
              Accès
            </button>
          </div>

          <div class="action-info">
            <div class="current-status" *ngIf="userStatus">
              <mat-icon [color]="getStatusColor()">{{ getStatusIcon() }}</mat-icon>
              <span>Statut actuel: {{ getStatusText() }}</span>
            </div>
          </div>
        </div>

        <!-- Processing indicator -->
        <div class="processing-section" *ngIf="isProcessing">
          <mat-spinner diameter="30"></mat-spinner>
          <span class="processing-text">Action en cours...</span>
        </div>
      </mat-card-content>

      <mat-card-actions align="end">
        <button 
          mat-button 
          color="accent"
          [disabled]="isProcessing"
          (click)="onCancel()"
        >
          <mat-icon>cancel</mat-icon>
          Annuler
        </button>
      </mat-card-actions>
    </mat-card>
  `,
  styles: [`
    .actions-card {
      margin-bottom: 24px;
      border: 2px solid #25176e;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
      border-radius: 12px;
      background: white;
      transition: all 0.3s ease;

      &:hover {
        box-shadow: 0 8px 25px rgba(37, 23, 110, 0.2);
      }
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

    .countdown-section {
      text-align: center;
      padding: 24px 0;
    }

    .countdown-display {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 8px;
    }

    .countdown-text {
      font-size: 1.2rem;
      font-weight: 500;
      color: #f57c00;
    }

    .countdown-info {
      color: #666;
    }

    .action-buttons {
      text-align: center;
      padding: 16px 0;
    }

    .button-group {
      display: flex;
      gap: 16px;
      justify-content: center;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .action-button {
      min-width: 120px;
      height: 48px;
      font-size: 1rem;
    }

    .entry-button {
      background-color: #4caf50 !important;
    }

    .exit-button {
      background-color: #ff9800 !important;
    }

    .access-button {
      background-color: #2196f3 !important;
    }

    .action-info {
      padding: 12px 0;
      border-top: 1px solid #eee;
    }

    .current-status {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      color: #666;
    }

    .processing-section {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
      padding: 24px 0;
    }

    .processing-text {
      color: #666;
      font-style: italic;
    }

    @media (max-width: 600px) {
      .button-group {
        flex-direction: column;
        align-items: center;
      }
      
      .action-button {
        width: 100%;
        max-width: 200px;
      }
    }
  `]
})
export class PointageActionsComponent {
  @Input() selectedBadgeuse: BadgeuseAccess | null = null;
  @Input() userStatus: UserWorkingStatus | null = null;
  @Input() isProcessing: boolean = false;
  @Input() countdownSeconds: number = 0;

  @Output() performAction = new EventEmitter<{ badgeuse: BadgeuseAccess; actionType: string }>();
  @Output() cancel = new EventEmitter<void>();

  formatCountdown(seconds: number): string {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    
    if (minutes > 0) {
      return `${minutes}min ${remainingSeconds.toString().padStart(2, '0')}s`;
    }
    return `${remainingSeconds}s`;
  }

  getStatusColor(): string {
    if (!this.userStatus) return 'warn';
    return this.userStatus.status === 'present' ? 'primary' : 'accent';
  }

  getStatusIcon(): string {
    if (!this.userStatus) return 'help_outline';
    return this.userStatus.status === 'present' ? 'work' : 'home';
  }

  getStatusText(): string {
    if (!this.userStatus) return 'Inconnu';
    return this.userStatus.status === 'present' ? 'Présent' : 'Absent';
  }

  onPerformAction(actionType: string): void {
    if (this.selectedBadgeuse && !this.isProcessing) {
      this.performAction.emit({ badgeuse: this.selectedBadgeuse, actionType });
    }
  }

  onCancel(): void {
    this.cancel.emit();
  }
}