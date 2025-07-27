import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  ReactiveFormsModule,
  FormBuilder,
  FormGroup,
  Validators,
} from '@angular/forms';
import { Router } from '@angular/router';
import { Subject, takeUntil } from 'rxjs';

// Angular Material imports
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBarModule, MatSnackBar } from '@angular/material/snack-bar';

import { AuthService } from '../../services/auth.service';
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

  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.initializeForm();
    this.subscribeToLoadingState();

    // Redirect if already authenticated
    if (this.authService.isAuthenticated()) {
      this.router.navigate(['/dashboard']);
    }
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  /**
   * Initialize reactive form with validation
   */
  private initializeForm(): void {
    this.loginForm = this.formBuilder.group({
      email: [
        '',
        [Validators.required, Validators.email, Validators.maxLength(255)],
      ],
      password: [
        '',
        [
          Validators.required,
          Validators.minLength(6),
          Validators.maxLength(255),
        ],
      ],
      rememberMe: [false],
    });
  }

  /**
   * Subscribe to auth service loading state
   */
  private subscribeToLoadingState(): void {
    this.authService.isLoading$
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

      this.authService
        .login(credentials, rememberMe)
        .pipe(takeUntil(this.destroy$))
        .subscribe({
          next: (user) => {
            this.snackBar.open(
              `Bienvenue ${user.prenom} ${user.nom}!`,
              'Fermer',
              {
                duration: 3000,
                panelClass: ['success-snackbar'],
              }
            );
            this.router.navigate(['/dashboard']);
          },
          error: (error) => {
            this.snackBar.open(
              error.message || 'Erreur de connexion',
              'Fermer',
              {
                duration: 5000,
                panelClass: ['error-snackbar'],
              }
            );
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
   * Handle forgot password (placeholder for future implementation)
   */
  onForgotPassword(): void {
    this.snackBar.open('Fonctionnalité en cours de développement', 'Fermer', {
      duration: 3000,
      panelClass: ['info-snackbar'],
    });
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
   * Get error message for email field
   */
  getEmailErrorMessage(): string {
    const emailControl = this.loginForm.get('email');

    if (emailControl?.hasError('required')) {
      return "L'email est requis";
    }
    if (emailControl?.hasError('email')) {
      return 'Veuillez entrer un email valide';
    }
    if (emailControl?.hasError('maxlength')) {
      return "L'email ne peut pas dépasser 255 caractères";
    }
    return '';
  }

  /**
   * Get error message for password field
   */
  getPasswordErrorMessage(): string {
    const passwordControl = this.loginForm.get('password');

    if (passwordControl?.hasError('required')) {
      return 'Le mot de passe est requis';
    }
    if (passwordControl?.hasError('minlength')) {
      return 'Le mot de passe doit contenir au moins 6 caractères';
    }
    if (passwordControl?.hasError('maxlength')) {
      return 'Le mot de passe ne peut pas dépasser 255 caractères';
    }
    return '';
  }
}
