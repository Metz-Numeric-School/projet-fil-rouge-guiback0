import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTabsModule } from '@angular/material/tabs';
import { Subscription } from 'rxjs';

import { UserStateService } from '../../services/auth/user-state.service';
import { UserApiService } from '../../services/user/user-api.service';
import { UserHelperService } from '../../services/user/user-helper.service';
import { AuthenticationService } from '../../services/auth/authentication.service';
import { User, CompleteUserProfile } from '../../interfaces/user.interface';

// Import new components
import { PersonalInfoComponent } from './personal-info/personal-info.component';
import { OrganisationComponent } from './organisation/organisation.component';
import { ServicesComponent } from './services/services.component';
import { AccessZonesComponent } from './access-zones/access-zones.component';
import { BadgesComponent } from './badges/badges.component';

@Component({
  selector: 'app-user',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatTabsModule,
    // New components
    PersonalInfoComponent,
    OrganisationComponent,
    ServicesComponent,
    AccessZonesComponent,
    BadgesComponent,
  ],
  templateUrl: './user.component.html',
  styleUrl: './user.component.scss',
})
export class UserComponent implements OnInit, OnDestroy {
  currentUser: User | null = null;
  completeProfile: CompleteUserProfile | null = null;
  isLoading = true;
  selectedTabIndex = 0;

  private subscriptions: Subscription[] = [];

  constructor(
    private userStateService: UserStateService,
    private userApiService: UserApiService,
    private userHelperService: UserHelperService,
    private authenticationService: AuthenticationService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    // Subscribe to current user changes
    const userSub = this.userStateService.currentUser$.subscribe((user) => {
      this.currentUser = user;

      if (!user) {
        this.router.navigate(['/login']);
      } else {
        this.loadCompleteProfile();
      }
    });

    this.subscriptions.push(userSub);
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach((sub) => sub.unsubscribe());
  }

  /**
   * Load complete user profile with all related data
   */
  loadCompleteProfile(): void {
    this.isLoading = true;

    const profileSub = this.userApiService.getCompleteProfile().subscribe({
      next: (profile) => {
        this.completeProfile = profile;
        this.isLoading = false;
      },
      error: (error) => {
        this.snackBar.open(
          `Erreur lors du chargement du profil: ${error.message}`,
          'Fermer',
          { duration: 5000 }
        );
        this.isLoading = false;
      },
    });

    this.subscriptions.push(profileSub);
  }

  /**
   * Get user's full name
   */
  getFullName(user: User): string {
    return this.userHelperService.getFullName(user);
  }

  /**
   * Set active tab
   */
  setActiveTab(index: number): void {
    this.selectedTabIndex = index;
  }

  /**
   * Check if organization exists
   */
  hasOrganization(): boolean {
    return !!this.completeProfile?.organisation;
  }

  /**
   * Logout user
   */
  logout(): void {
    const logoutSub = this.authenticationService.logout().subscribe({
      next: () => {
        this.router.navigate(['/login']);
      },
      error: () => {
        this.router.navigate(['/login']);
      },
    });

    this.subscriptions.push(logoutSub);
  }
}
