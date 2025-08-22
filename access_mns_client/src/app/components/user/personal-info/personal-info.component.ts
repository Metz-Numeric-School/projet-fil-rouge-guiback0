import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';

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
  ],
  templateUrl: './personal-info.component.html',
  styleUrl: './personal-info.component.scss',
})
export class PersonalInfoComponent {
  @Input() currentUser: User | null = null;

  constructor(private userService: UserService) {}

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
}
