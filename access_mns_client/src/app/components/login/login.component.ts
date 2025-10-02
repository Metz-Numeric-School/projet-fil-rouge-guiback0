import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  ReactiveFormsModule,
  FormBuilder,
  FormGroup,
  Validators,
} from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { Subject, takeUntil } from 'rxjs';

// Angular Material imports
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBarModule, MatSnackBar } from '@angular/material/snack-bar';

import { AuthenticationService } from '../../services/auth/authentication.service';
import { UserStateService } from '../../services/auth/user-state.service';
import { LoginCredentials } from '../../interfaces/auth.interface';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatCheckboxModule,
    MatIconModule,
    MatSnackBarModule,
  ],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss',
})
export class LoginComponent implements OnInit, OnDestroy {
  loginForm!: FormGroup;
  hidePassword = true;
  isLoading = false;
  private destroy$ = new Subject<void>();
  private returnUrl: string;

  constructor(
    private formBuilder: FormBuilder,
    private authenticationService: AuthenticationService,
    private userStateService: UserStateService,
    private router: Router,
    private route: ActivatedRoute,
    private snackBar: MatSnackBar
  ) {
    // Récupérer l'URL de retour depuis les paramètres de requête
    this.returnUrl =
      this.route.snapshot.queryParams['returnUrl'] || '/dashboard';
  }

  ngOnInit(): void {
    this.initializeForm();
    this.subscribeToLoadingState();

    // Redirect if already authenticated
    if (this.authenticationService.isAuthenticated()) {
      this.router.navigate([this.returnUrl]);
    }
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  /**
   * Initialize reactive form with validation
   * Synchronized with backend validation rules
   */
  private initializeForm(): void {
    this.loginForm = this.formBuilder.group({
      email: [
        '',
        [
          Validators.required,
          Validators.email,
          Validators.minLength(5), // Match backend min length
          Validators.maxLength(180), // Match backend max length
        ],
      ],
      password: [
        '',
        [
          Validators.required,
          Validators.minLength(1), // For login, we just check it's not empty
        ],
      ],
      rememberMe: [false],
    });
  }

  /**
   * Subscribe to auth service loading state
   */
  private subscribeToLoadingState(): void {
    this.authenticationService.isLoading$
      .pipe(takeUntil(this.destroy$))
      .subscribe((loading) => {
        this.isLoading = loading;
      });
  }

  /**
   * Handle form submission
   */
  onSubmit(): void {
    if (this.loginForm.valid && !this.isLoading) {
      const credentials: LoginCredentials = {
        email: this.loginForm.get('email')?.value,
        password: this.loginForm.get('password')?.value,
      };

      const rememberMe = this.loginForm.get('rememberMe')?.value || false;

      this.authenticationService
        .login(credentials, rememberMe)
        .pipe(takeUntil(this.destroy$))
        .subscribe({
          next: (user) => {
            this.userStateService.setCurrentUser(user);
            this.snackBar.open(
              `Bienvenue ${user.prenom} ${user.nom}!`,
              'Fermer',
              {
                duration: 3000,
                panelClass: ['success-snackbar'],
              }
            );
            this.router.navigate([this.returnUrl]);
          },
          error: (error) => {
            this.handleLoginError(error);
          },
        });
    } else {
      this.markFormGroupTouched();
    }
  }

  /**
   * Toggle password visibility
   */
  togglePasswordVisibility(): void {
    this.hidePassword = !this.hidePassword;
  }


  /**
   * Mark all form fields as touched to show validation errors
   */
  private markFormGroupTouched(): void {
    Object.keys(this.loginForm.controls).forEach((key) => {
      const control = this.loginForm.get(key);
      control?.markAsTouched();
    });
  }

  /**
   * Handle login errors with detailed messages
   */
  private handleLoginError(error: any): void {
    let message = error.message || 'Erreur de connexion';
    let duration = 5000;
    let panelClass = ['error-snackbar'];

    // Handle different error types
    switch (error.type) {
      case 'VALIDATION_FAILED':
        if (error.details && error.details.length > 0) {
          // Show first validation error as primary message
          message = error.details[0];
          // If multiple errors, show them in console for debugging
          if (error.details.length > 1) {
            console.warn('Multiple validation errors:', error.details);
          }
        }
        break;

      case 'TOO_MANY_ATTEMPTS':
        message =
          'Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.';
        duration = 8000; // Show longer for rate limit
        panelClass = ['warning-snackbar'];
        break;

      case 'INVALID_CREDENTIALS':
        message = 'Email ou mot de passe incorrect';
        break;

      default:
        message = error.message || 'Erreur de connexion au serveur';
        break;
    }

    this.snackBar.open(message, 'Fermer', {
      duration,
      panelClass,
    });
  }

  /**
   * Get error message for email field
   * Synchronized with backend validation messages
   */
  getEmailErrorMessage(): string {
    const emailControl = this.loginForm.get('email');

    if (emailControl?.hasError('required')) {
      return "L'email est obligatoire";
    }
    if (emailControl?.hasError('email')) {
      return "L'email n'est pas valide";
    }
    if (emailControl?.hasError('minlength')) {
      return "L'email doit contenir au moins 5 caractères";
    }
    if (emailControl?.hasError('maxlength')) {
      return "L'email ne peut pas contenir plus de 180 caractères";
    }
    return '';
  }

  /**
   * Get error message for password field
   * Synchronized with backend validation messages
   */
  getPasswordErrorMessage(): string {
    const passwordControl = this.loginForm.get('password');

    if (passwordControl?.hasError('required')) {
      return 'Le mot de passe est obligatoire';
    }
    if (passwordControl?.hasError('minlength')) {
      return 'Le mot de passe ne peut pas être vide';
    }
    return '';
  }
}
