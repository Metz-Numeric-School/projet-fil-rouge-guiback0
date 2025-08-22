import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';

import { CompleteUserProfile } from '../../../interfaces/user.interface';
import { UserService } from '../../../services/user.service';

@Component({
  selector: 'app-services',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatIconModule,
    MatChipsModule,
    MatButtonModule,
  ],
  templateUrl: './services.component.html',
  styleUrl: './services.component.scss',
})
export class ServicesComponent {
  @Input() completeProfile: CompleteUserProfile | null = null;

  constructor(private userService: UserService) {}

  /**
   * Get current service
   */
  getCurrentService(): any {
    if (!this.completeProfile) return null;
    return this.userService.getCurrentService(this.completeProfile);
  }

  /**
   * Check if current service exists
   */
  hasCurrentService(): boolean {
    const service = this.getCurrentService();
    return service !== null && service !== undefined;
  }

  /**
   * Check if user has service history
   */
  hasServiceHistory(): boolean {
    return !!(
      this.completeProfile?.services?.history &&
      this.completeProfile.services.history.length > 0
    );
  }

  /**
   * Get service history safely
   */
  getServiceHistory(): any[] {
    return this.completeProfile?.services?.history || [];
  }

  /**
   * Get service status color
   */
  getServiceStatusColor(service: any): string {
    return service.is_current ? 'primary' : 'accent';
  }

  /**
   * Format date for display
   */
  formatDate(dateString: string | undefined): string {
    if (!dateString) return 'Non défini';
    return new Date(dateString).toLocaleDateString('fr-FR');
  }
}
