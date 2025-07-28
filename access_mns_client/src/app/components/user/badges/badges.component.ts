import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';

import { CompleteUserProfile } from '../../../interfaces/user.interface';
import { UserService } from '../../../services/user.service';

@Component({
  selector: 'app-badges',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatIconModule,
    MatChipsModule,
    MatButtonModule,
  ],
  templateUrl: './badges.component.html',
  styleUrl: './badges.component.scss',
})
export class BadgesComponent {
  @Input() completeProfile: CompleteUserProfile | null = null;

  constructor(private userService: UserService) {}

  /**
   * Get active badges
   */
  getActiveBadges(): any[] {
    if (!this.completeProfile) return [];
    return this.userService.getActiveBadges(this.completeProfile);
  }

  /**
   * Check if user has all badges section to display
   */
  hasAllBadges(): boolean {
    return !!(
      this.completeProfile?.badges && this.completeProfile.badges.length > 0
    );
  }

  /**
   * Get all badges safely
   */
  getAllBadges(): any[] {
    return this.completeProfile?.badges || [];
  }

  /**
   * Get badge status color
   */
  getBadgeStatusColor(badge: any): string {
    return badge.is_active ? 'primary' : 'warn';
  }

  /**
   * Format date for display
   */
  formatDate(dateString: string | undefined): string {
    if (!dateString) return 'Non défini';
    return new Date(dateString).toLocaleDateString('fr-FR');
  }
}
