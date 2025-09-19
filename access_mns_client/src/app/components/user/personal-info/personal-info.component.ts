import { Component, Input, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';

import { User } from '../../../interfaces/user.interface';
import { UserHelperService } from '../../../services/user/user-helper.service';

@Component({
  selector: 'app-personal-info',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatIconModule,
    MatChipsModule,
  ],
  templateUrl: './personal-info.component.html',
  styleUrl: './personal-info.component.scss',
})
export class PersonalInfoComponent {
  @Input() currentUser: User | null = null;

  constructor(
    public userHelperService: UserHelperService
  ) {}

  /**
   * Get user's full name
   */
  getFullName(user: User): string {
    return this.userHelperService.getFullName(user);
  }

  /**
   * Get working days as array
   */
  getWorkingDays(): string[] {
    return this.userHelperService.getWorkingDaysArray(
      this.currentUser?.jours_semaine_travaille
    );
  }

  /**
   * Get formatted working hours
   */
  getWorkingHours(): string {
    return this.userHelperService.formatWorkingHours(
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




}


