import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';

import { CompleteUserProfile } from '../../../interfaces/user.interface';
import { UserHelperService } from '../../../services/user/user-helper.service';

@Component({
  selector: 'app-organisation',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatIconModule, MatButtonModule],
  templateUrl: './organisation.component.html',
  styleUrl: './organisation.component.scss',
})
export class OrganisationComponent {
  @Input() completeProfile: CompleteUserProfile | null = null;

  constructor(private userHelperService: UserHelperService) {}

  /**
   * Get formatted organization address
   */
  getOrganizationAddress(): string {
    if (!this.completeProfile?.organisation) return '';
    return this.userHelperService.formatOrganizationAddress(
      this.completeProfile.organisation
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
