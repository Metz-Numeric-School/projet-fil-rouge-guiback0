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
import { UserHelperService } from '../../services/user/user-helper.service';
import { UserProfileStateService } from '../../services/user/user-profile-state.service';
import { AuthenticationService } from '../../services/auth/authentication.service';
import { User, CompleteUserProfile } from '../../interfaces/user.interface';

// Import new components
import { PersonalInfoComponent } from './personal-info/personal-info.component';
import { OrganisationComponent } from './organisation/organisation.component';
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
  error: string | null = null;

  private subscriptions: Subscription[] = [];

  constructor(
    private userStateService: UserStateService,
    private userProfileStateService: UserProfileStateService,
    private userHelperService: UserHelperService,
    private authenticationService: AuthenticationService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.setupSubscriptions();
    this.loadUserData();
  }

  private setupSubscriptions(): void {
    // S'abonner aux changements d'état du service user profile
    const profileStateSub = this.userProfileStateService.state$.subscribe(state => {
      this.currentUser = state.currentUser;
      this.completeProfile = state.completeProfile;
      this.isLoading = state.isLoading;
      this.selectedTabIndex = state.selectedTabIndex;
      this.error = state.error;
    });

    // S'abonner aux changements d'utilisateur depuis le service d'auth
    const userSub = this.userStateService.currentUser$.subscribe((user) => {
      if (!user) {
        this.router.navigate(['/login']);
      } else if (user !== this.currentUser) {
        this.userProfileStateService.setCurrentUser(user);
        this.loadCompleteProfile();
      }
    });

    this.subscriptions.push(profileStateSub, userSub);
  }

  private loadUserData(): void {
    // Charger l'utilisateur actuel depuis le service d'auth
    const currentUser = this.userStateService.getCurrentUser();
    if (currentUser) {
      this.userProfileStateService.setCurrentUser(currentUser);
      this.loadCompleteProfile();
    } else {
      this.router.navigate(['/login']);
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach((sub) => sub.unsubscribe());
  }

  /**
   * Load complete user profile with all related data
   */
  loadCompleteProfile(): void {
    const profileSub = this.userProfileStateService.loadCompleteProfile().subscribe({
      next: () => {
        // Les données sont automatiquement mises à jour via la subscription d'état
      },
      error: (error) => {
        this.snackBar.open(
          `Erreur lors du chargement du profil: ${error.message}`,
          'Fermer',
          { duration: 5000 }
        );
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
    this.userProfileStateService.setSelectedTabIndex(index);
  }

  /**
   * Check if organization exists
   */
  hasOrganization(): boolean {
    return this.userProfileStateService.hasOrganization();
  }

  /**
   * Handle organization card click
   */
  onOrganizationCardClick(): void {
    if (this.hasOrganization()) {
      this.setActiveTab(2);
    } else {
      this.snackBar.open(
        'Aucune organisation n\'est associée à votre profil',
        'Fermer',
        { duration: 3000 }
      );
    }
  }

  /**
   * Logout user
   */
  logout(): void {
    const logoutSub = this.authenticationService.logout().subscribe({
      next: () => {
        this.userProfileStateService.clearUserData();
        this.router.navigate(['/login']);
      },
      error: () => {
        this.userProfileStateService.clearUserData();
        this.router.navigate(['/login']);
      },
    });

    this.subscriptions.push(logoutSub);
  }
}
