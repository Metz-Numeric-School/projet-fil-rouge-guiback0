import { Component, Input, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatDialog, MatDialogModule, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';

import { User } from '../../../interfaces/user.interface';
import { UserService } from '../../../services/user.service';

@Component({
  selector: 'app-personal-info',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatIconModule,
    MatChipsModule,
    MatButtonModule,
    MatSnackBarModule,
    MatDialogModule,
  ],
  templateUrl: './personal-info.component.html',
  styleUrl: './personal-info.component.scss',
})
export class PersonalInfoComponent {
  @Input() currentUser: User | null = null;
  isDeactivating = false;
  isEditing = false;

  constructor(
    public userService: UserService,
    private snackBar: MatSnackBar,
    private dialog: MatDialog
  ) {}

  /**
   * Get user's full name
   */
  getFullName(user: User): string {
    return this.userService.getFullName(user);
  }

  /**
   * Get working days as array
   */
  getWorkingDays(): string[] {
    return this.userService.getWorkingDaysArray(
      this.currentUser?.jours_semaine_travaille
    );
  }

  /**
   * Get formatted working hours
   */
  getWorkingHours(): string {
    return this.userService.formatWorkingHours(
      this.currentUser?.heure_debut,
      this.currentUser?.horraire
    );
  }

  /**
   * Format date for display
   */
  formatDate(dateString: string | undefined): string {
    if (!dateString) return 'Non défini';
    return new Date(dateString).toLocaleDateString('fr-FR');
  }

  /**
   * Format datetime for display
   */
  formatDateTime(dateTimeString: string | undefined): string {
    if (!dateTimeString) return 'Non défini';
    return new Date(dateTimeString).toLocaleDateString('fr-FR') + ' à ' + 
           new Date(dateTimeString).toLocaleTimeString('fr-FR', { 
             hour: '2-digit', 
             minute: '2-digit' 
           });
  }

  /**
   * Get account status information
   */
  getAccountStatus(): { label: string; color: string; icon: string } {
    if (!this.currentUser) {
      return { label: 'Inconnu', color: 'basic', icon: 'help' };
    }

    const isActive = this.userService.isAccountActive(this.currentUser);
    const isScheduled = this.userService.isScheduledForDeletion(this.currentUser);

    if (isActive) {
      return { label: 'Actif', color: 'primary', icon: 'check_circle' };
    } else if (isScheduled) {
      return { label: 'Désactivé (Suppression prévue)', color: 'warn', icon: 'schedule_delete' };
    } else {
      return { label: 'Désactivé', color: 'warn', icon: 'block' };
    }
  }

  /**
   * Get deletion notice if applicable
   */
  getDeletionNotice(): string {
    if (!this.currentUser) return '';
    return this.userService.formatDeletionNotice(this.currentUser);
  }

  /**
   * Export user data (GDPR)
   */
  exportUserData(): void {
    this.userService.exportUserData().subscribe({
      next: (response) => {
        if (response.data && response.export_timestamp) {
          // Create and download JSON file
          const dataStr = JSON.stringify(response.data, null, 2);
          const dataBlob = new Blob([dataStr], { type: 'application/json' });
          const url = window.URL.createObjectURL(dataBlob);
          const link = document.createElement('a');
          
          const timestamp = new Date(response.export_timestamp).toISOString().slice(0, 16).replace(/:/g, '-');
          link.download = `mes-donnees-${timestamp}.json`;
          link.href = url;
          link.click();
          
          window.URL.revokeObjectURL(url);
        }

        this.snackBar.open(
          response.message || 'Données exportées avec succès',
          'Fermer',
          { duration: 5000 }
        );
      },
      error: (error) => {
        this.snackBar.open(
          `Erreur lors de l'exportation: ${error.message}`,
          'Fermer',
          { duration: 5000 }
        );
      }
    });
  }

  /**
   * Deactivate user account (GDPR)
   */
  deactivateAccount(): void {
    // Show confirmation dialog
    const dialogRef = this.dialog.open(AccountDeactivationDialogComponent, {
      width: '400px',
      data: { userName: this.getFullName(this.currentUser!) }
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result === 'confirm') {
        this.performAccountDeactivation();
      }
    });
  }

  /**
   * Perform the actual account deactivation
   */
  private performAccountDeactivation(): void {
    this.isDeactivating = true;

    this.userService.deactivateAccount().subscribe({
      next: (response) => {
        this.snackBar.open(
          response.message,
          'Fermer',
          { duration: 8000 }
        );

        // Update the current user data
        if (this.currentUser && response.data?.date_suppression_prevue) {
          this.currentUser.compte_actif = false;
          this.currentUser.date_suppression_prevue = response.data.date_suppression_prevue;
        }

        this.isDeactivating = false;
      },
      error: (error) => {
        this.snackBar.open(
          `Erreur lors de la désactivation: ${error.message}`,
          'Fermer',
          { duration: 5000 }
        );
        this.isDeactivating = false;
      }
    });
  }

  /**
   * Open edit profile dialog
   */
  editProfile(): void {
    const dialogRef = this.dialog.open(EditProfileDialogComponent, {
      width: '800px',
      maxWidth: '90vw',
      maxHeight: '90vh',
      data: { currentUser: this.currentUser },
      disableClose: true
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result && result.updated) {
        // Update the current user data
        this.currentUser = result.user;
        this.snackBar.open('Profil mis à jour avec succès', 'Fermer', {
          duration: 3000,
          panelClass: ['success-snackbar'],
        });
      }
    });
  }
}

// Account Deactivation Confirmation Dialog Component
@Component({
  selector: 'app-account-deactivation-dialog',
  standalone: true,
  imports: [
    CommonModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule
  ],
  template: `
    <div class="deactivation-dialog">
      <h2 mat-dialog-title>
        <mat-icon color="warn">warning</mat-icon>
        Confirmer la désactivation du compte
      </h2>
      <mat-dialog-content>
        <p><strong>Attention :</strong> Cette action va désactiver votre compte définitivement.</p>
        <p>Conséquences de la désactivation :</p>
        <ul>
          <li>Vous ne pourrez plus vous connecter</li>
          <li>Vos données seront conservées pendant 5 ans (conformément au RGPD)</li>
          <li>Après cette période, toutes vos données seront supprimées définitivement</li>
          <li>Seul un administrateur pourra réactiver votre compte</li>
        </ul>
        <p><strong>Êtes-vous sûr de vouloir désactiver votre compte ?</strong></p>
      </mat-dialog-content>
      <mat-dialog-actions align="end">
        <button mat-button mat-dialog-close>Annuler</button>
        <button mat-raised-button color="warn" [mat-dialog-close]="'confirm'">
          <mat-icon>block</mat-icon>
          Confirmer la désactivation
        </button>
      </mat-dialog-actions>
    </div>
  `,
  styles: [`
    .deactivation-dialog {
      max-width: 500px;
    }
    .deactivation-dialog h2 {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .deactivation-dialog ul {
      margin: 16px 0;
      padding-left: 20px;
    }
    .deactivation-dialog li {
      margin-bottom: 8px;
    }
  `]
})
export class AccountDeactivationDialogComponent {
  constructor(
    public dialogRef: MatDialogRef<AccountDeactivationDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { userName: string }
  ) {}
}

// Edit Profile Dialog Component
@Component({
  selector: 'app-edit-profile-dialog',
  standalone: true,
  imports: [
    CommonModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
  ],
  template: `
    <div class="edit-profile-dialog">
      <p>Edit profile functionality coming soon...</p>
      <button mat-button (click)="onCancel()">Close</button>
    </div>
  `,
  styles: [`
    .edit-profile-dialog {
      max-width: 100%;
      max-height: 100%;
      overflow: auto;
    }
  `]
})
export class EditProfileDialogComponent {
  constructor(
    public dialogRef: MatDialogRef<EditProfileDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { currentUser: User }
  ) {}

  onProfileUpdated(updatedUser: User): void {
    this.dialogRef.close({ updated: true, user: updatedUser });
  }

  onCancel(): void {
    this.dialogRef.close({ updated: false });
  }
}
