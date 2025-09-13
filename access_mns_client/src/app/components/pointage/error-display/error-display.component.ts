import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-error-display',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule
  ],
  template: `
    <mat-card class="error-card" *ngIf="errorMessage">
      <mat-card-content>
        <div class="error-content">
          <mat-icon color="warn" class="error-icon">error</mat-icon>
          <div class="error-text">
            <h3>Erreur</h3>
            <p>{{ errorMessage }}</p>
          </div>
        </div>
      </mat-card-content>
      
      <mat-card-actions align="end">
        <button mat-button color="primary" (click)="onRetry()">
          <mat-icon>refresh</mat-icon>
          Réessayer
        </button>
        <button mat-button (click)="onDismiss()">
          <mat-icon>close</mat-icon>
          Fermer
        </button>
      </mat-card-actions>
    </mat-card>
  `,
  styles: [`
    .error-card {
      margin-bottom: 16px;
      border-left: 4px solid #f44336;
      background-color: #ffebee;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
      transition: all 0.3s ease;

      &:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      }
    }

    mat-card-content {
      padding: 1.5rem;
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

    .error-content {
      display: flex;
      align-items: flex-start;
      gap: 16px;
    }

    .error-icon {
      font-size: 2rem;
      width: 2rem;
      height: 2rem;
      flex-shrink: 0;
    }

    .error-text h3 {
      margin: 0 0 8px 0;
      color: #d32f2f;
      font-weight: 500;
    }

    .error-text p {
      margin: 0;
      color: #5a6c7d;
      line-height: 1.5;
    }
  `]
})
export class ErrorDisplayComponent {
  @Input() errorMessage: string | null = null;
  
  @Output() retry = new EventEmitter<void>();
  @Output() dismiss = new EventEmitter<void>();

  onRetry(): void {
    this.retry.emit();
  }

  onDismiss(): void {
    this.dismiss.emit();
  }
}