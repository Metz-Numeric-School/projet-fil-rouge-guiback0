import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { Router } from '@angular/router';
import { of, throwError } from 'rxjs';

import { LoginComponent } from './login.component';
import { AuthenticationService } from '../../services/auth/authentication.service';
import { UserStateService } from '../../services/auth/user-state.service';
import { TestBedConfig, TestHelpers } from '../../testing';

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
  let authServiceSpy: jasmine.SpyObj<AuthenticationService>;
  let userStateServiceSpy: jasmine.SpyObj<UserStateService>;
  let routerSpy: jasmine.SpyObj<Router>;

  const mockData = TestBedConfig.getMockData();

  beforeEach(async () => {
    await TestBedConfig.setupTestBed(
      TestBedConfig.getAuthConfig(),
      [
        {
          provide: AuthenticationService,
          useValue: jasmine.createSpyObj('AuthenticationService', ['login', 'isAuthenticated'], {
            isLoading$: of(false)
          })
        },
        {
          provide: UserStateService,
          useValue: jasmine.createSpyObj('UserStateService', ['setCurrentUser'])
        }
      ]
    );

    await TestBed.configureTestingModule({
      imports: [LoginComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(LoginComponent);
    component = fixture.componentInstance;
    
    // Injection des services spies
    authServiceSpy = TestBed.inject(AuthenticationService) as jasmine.SpyObj<AuthenticationService>;
    userStateServiceSpy = TestBed.inject(UserStateService) as jasmine.SpyObj<UserStateService>;
    routerSpy = TestBed.inject(Router) as jasmine.SpyObj<Router>;

    fixture.detectChanges();
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait initialiser le formulaire de connexion', () => {
    expect(component.loginForm).toBeDefined();
    expect(component.loginForm.get('email')).toBeDefined();
    expect(component.loginForm.get('password')).toBeDefined();
    expect(component.loginForm.get('rememberMe')).toBeDefined();
  });

  it('devrait valider le champ email', () => {
    const emailControl = component.loginForm.get('email');
    
    // Test email vide
    emailControl?.setValue('');
    expect(emailControl?.hasError('required')).toBeTruthy();
    
    // Test email invalide
    emailControl?.setValue('invalid-email');
    expect(emailControl?.hasError('email')).toBeTruthy();
    
    // Test email valide
    emailControl?.setValue('test@example.com');
    expect(emailControl?.valid).toBeTruthy();
  });

  it('devrait valider le champ mot de passe', () => {
    const passwordControl = component.loginForm.get('password');
    
    // Test mot de passe vide
    passwordControl?.setValue('');
    expect(passwordControl?.hasError('required')).toBeTruthy();
    
    // Test mot de passe valide
    passwordControl?.setValue('password123');
    expect(passwordControl?.valid).toBeTruthy();
  });

  it('devrait soumettre un formulaire valide avec succès', async () => {
    // Configuration du formulaire avec des données valides
    component.loginForm.patchValue({
      email: 'test@example.com',
      password: 'password123',
      rememberMe: true
    });

    // Mock de la connexion réussie
    authServiceSpy.login.and.returnValue(of(mockData.user));

    // Soumission du formulaire
    component.onSubmit();
    await TestHelpers.waitForChanges(fixture);

    // Vérification des appels de service
    TestHelpers.expectServiceCall(
      authServiceSpy.login, 
      'login',
      { email: 'test@example.com', password: 'password123' },
      true
    );
    TestHelpers.expectServiceCall(userStateServiceSpy.setCurrentUser, 'setCurrentUser', mockData.user);
    TestHelpers.expectNavigation(routerSpy, ['/dashboard']);
  });

  it('devrait gérer les erreurs de connexion', fakeAsync(() => {
    // Configuration du formulaire avec des données valides
    component.loginForm.patchValue({
      email: 'test@example.com',
      password: 'wrongpassword'
    });

    // Mock d'erreur de connexion avec RxJS throwError
    const error = new Error('Invalid credentials');
    (error as any).type = 'INVALID_CREDENTIALS';
    authServiceSpy.login.and.returnValue(throwError(() => error));
    
    // Espionnage de l'instance snackBar du composant directement
    spyOn(component['snackBar'], 'open');
    
    component.onSubmit();
    tick(); // Force l'exécution des opérations asynchrones

    // Vérification de la gestion d'erreur
    expect(component['snackBar'].open).toHaveBeenCalledWith(
      'Email ou mot de passe incorrect',
      'Fermer',
      jasmine.objectContaining({
        duration: jasmine.any(Number),
        panelClass: ['error-snackbar']
      })
    );
  }));

  it('ne devrait pas soumettre un formulaire invalide', () => {
    // Laisser le formulaire vide (invalide)
    component.onSubmit();

    // Vérifier qu'aucun service n'est appelé
    expect(authServiceSpy.login).not.toHaveBeenCalled();
    expect(userStateServiceSpy.setCurrentUser).not.toHaveBeenCalled();
    expect(routerSpy.navigate).not.toHaveBeenCalled();
  });

  it('devrait basculer la visibilité du mot de passe', () => {
    expect(component.hidePassword).toBeTruthy();
    
    component.togglePasswordVisibility();
    expect(component.hidePassword).toBeFalsy();
    
    component.togglePasswordVisibility();
    expect(component.hidePassword).toBeTruthy();
  });

  it('devrait afficher l\'état de chargement pendant la connexion', () => {
    // Mock de l'état de chargement
    Object.defineProperty(authServiceSpy, 'isLoading$', {
      value: of(true)
    });

    component.ngOnInit();
    fixture.detectChanges();

    expect(component.isLoading).toBeTruthy();
  });

  // Tests d'intégration avec les éléments du DOM
  describe('Interactions DOM', () => {
    it('devrait afficher les champs du formulaire', () => {
      expect(TestHelpers.isElementPresent(fixture, 'input[formControlName="email"]')).toBeTruthy();
      expect(TestHelpers.isElementPresent(fixture, 'input[formControlName="password"]')).toBeTruthy();
      expect(TestHelpers.isElementPresent(fixture, 'mat-checkbox[formControlName="rememberMe"]')).toBeTruthy();
    });

    it('devrait soumettre le formulaire au clic du bouton', () => {
      spyOn(component, 'onSubmit');
      
      // Remplir le formulaire avec des données valides
      TestHelpers.setInputValue(fixture, 'input[formControlName="email"]', 'test@example.com');
      TestHelpers.setInputValue(fixture, 'input[formControlName="password"]', 'password123');
      
      // Cliquer sur le bouton de soumission
      TestHelpers.clickElement(fixture, 'button[type="submit"]');
      
      expect(component.onSubmit).toHaveBeenCalled();
    });
  });
});