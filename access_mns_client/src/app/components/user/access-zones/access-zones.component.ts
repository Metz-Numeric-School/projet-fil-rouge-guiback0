import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';

import { CompleteUserProfile } from '../../../interfaces/user.interface';
import { UserHelperService } from '../../../services/user/user-helper.service';

@Component({
  selector: 'app-access-zones',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatIconModule,
    MatChipsModule,
    MatButtonModule,
  ],
  templateUrl: './access-zones.component.html',
  styleUrl: './access-zones.component.scss',
})
export class AccessZonesComponent {
  @Input() completeProfile: CompleteUserProfile | null = null;

  constructor(private userHelperService: UserHelperService) {}

  /**
   * Get accessible zones
   */
  getAccessibleZones(): any[] {
    if (!this.completeProfile) return [];
    return this.userHelperService.getAccessibleZones(this.completeProfile);
  }

  /**
   * Get authorized badge readers
   */
  getAuthorizedBadgeReaders(): any[] {
    if (!this.completeProfile) return [];
    return this.userHelperService.getAuthorizedBadgeReaders(this.completeProfile);
  }

  /**
   * Format date for display
   */
  formatDate(dateString: string | undefined): string {
    if (!dateString) return 'Non défini';
    return new Date(dateString).toLocaleDateString('fr-FR');
  }
}
