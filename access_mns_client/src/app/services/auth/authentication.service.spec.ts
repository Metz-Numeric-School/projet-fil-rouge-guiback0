import { TestBed } from '@angular/core/testing';
import { HttpTestingController } from '@angular/common/http/testing';
import { HttpHeaders } from '@angular/common/http';
import { Router } from '@angular/router';
import { AuthenticationService } from './authentication.service';
import { TokenService } from './token.service';
import { LoginCredentials, LoginResponse } from '../../interfaces/auth.interface';
import { User } from '../../interfaces/user.interface';
import { TestBedConfig, TestHelpers } from '../../testing';

describe('AuthenticationService', () => {
  let service: AuthenticationService;
  let httpMock: HttpTestingController;
  let routerSpy: jasmine.SpyObj<Router>;
  let tokenServiceSpy: jasmine.SpyObj<TokenService>;

  const mockData = TestBedConfig.getMockData();
  const mockUser: User = mockData.user;

  const mockLoginResponse: LoginResponse = {
    success: true,
    message: 'Login successful',
    data: {
      token: 'mock-jwt-token',
      user: mockUser,
      organisation: null
    }
  };

  beforeEach(async () => {
    await TestBedConfig.setupTestBed(
      TestBedConfig.getAuthConfig(),
      [AuthenticationService]
    );

    service = TestBed.inject(AuthenticationService);
    httpMock = TestBed.inject(HttpTestingController);
    routerSpy = TestBed.inject(Router) as jasmine.SpyObj<Router>;
    tokenServiceSpy = TestBed.inject(TokenService) as jasmine.SpyObj<TokenService>;
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('devrait être créé', () => {
    expect(service).toBeTruthy();
  });

  describe('login', () => {
    it('devrait se connecter avec succès et retourner les données utilisateur', () => {
      const credentials: LoginCredentials = {
        email: 'test@example.com',
        password: 'password123'
      };

      service.login(credentials, true).subscribe(user => {
        expect(user).toEqual(mockUser);
        TestHelpers.expectServiceCall(tokenServiceSpy.storeToken, 'storeToken', 'mock-jwt-token', true);
        TestHelpers.expectServiceCall(tokenServiceSpy.setRememberMe, 'setRememberMe', true);
      });

      const req = httpMock.expectOne('http://localhost:8000/manager/api/auth/login');
      expect(req.request.method).toBe('POST');
      expect(req.request.body).toEqual(credentials);
      req.flush(mockLoginResponse);
    });

    it('devrait gérer les erreurs de connexion', () => {
      const credentials: LoginCredentials = {
        email: 'test@example.com',
        password: 'wrongpassword'
      };

      const errorResponse = {
        success: false,
        error: 'INVALID_CREDENTIALS',
        message: 'Invalid credentials'
      };

      service.login(credentials).subscribe({
        next: () => fail('Aurait dû échouer'),
        error: (error) => {
          expect(error.type).toBe('INVALID_CREDENTIALS');
          expect(error.message).toBe('Identifiants invalides');
        }
      });

      const req = httpMock.expectOne('http://localhost:8000/manager/api/auth/login');
      req.flush(errorResponse, { status: 401, statusText: 'Unauthorized' });
    });
  });

  describe('logout', () => {
    it('devrait se déconnecter avec succès', () => {
      const mockHeaders = new HttpHeaders({ 'Authorization': 'Bearer mock-token' });
      tokenServiceSpy.getAuthHeaders.and.returnValue(mockHeaders);

      service.logout().subscribe(() => {
        TestHelpers.expectServiceCall(tokenServiceSpy.clearTokens, 'clearTokens');
        TestHelpers.expectNavigation(routerSpy, ['/login']);
      });

      const req = httpMock.expectOne('http://localhost:8000/manager/api/auth/logout');
      expect(req.request.method).toBe('POST');
      expect(req.request.headers.get('Authorization')).toBe('Bearer mock-token');
      req.flush({});
    });
  });

  describe('isAuthenticated', () => {
    it('devrait retourner true quand le token existe', () => {
      tokenServiceSpy.getToken.and.returnValue('mock-token');
      expect(service.isAuthenticated()).toBeTruthy();
    });

    it('devrait retourner false quand il n\'y a pas de token', () => {
      tokenServiceSpy.getToken.and.returnValue(null);
      expect(service.isAuthenticated()).toBeFalsy();
    });
  });

  describe('isLoading$', () => {
    it('devrait émettre l\'état de chargement pendant la connexion', () => {
      const credentials: LoginCredentials = {
        email: 'test@example.com',
        password: 'password123'
      };

      let loadingStates: boolean[] = [];
      service.isLoading$.subscribe(state => loadingStates.push(state));

      service.login(credentials).subscribe();

      const req = httpMock.expectOne('http://localhost:8000/manager/api/auth/login');
      
      // Le chargement devrait être true pendant la requête
      expect(loadingStates).toContain(true);
      
      req.flush(mockLoginResponse);
      
      // Le chargement devrait être false après complétion
      expect(loadingStates[loadingStates.length - 1]).toBeFalsy();
    });
  });
});