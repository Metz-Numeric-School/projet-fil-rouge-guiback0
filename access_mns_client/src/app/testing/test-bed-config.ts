import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ActivatedRoute } from '@angular/router';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { of } from 'rxjs';

// Services de l'application
import { AuthenticationService } from '../services/auth/authentication.service';
import { TokenService } from '../services/auth/token.service';
import { UserStateService } from '../services/auth/user-state.service';
import { BadgeuseApiService } from '../services/pointage/badgeuse-api.service';
import { BadgeuseManagerService } from '../services/pointage/badgeuse-manager.service';
import { WorkingTimeService } from '../services/pointage/working-time.service';
import { UserApiService } from '../services/user/user-api.service';
import { UserHelperService } from '../services/user/user-helper.service';
import { GdprService } from '../services/user/gdpr.service';
import { CoffeeStateService } from '../services/coffee/coffee-state.service';

/**
 * Configuration TestBed par défaut pour les tests Angular
 */
export class TestBedConfig {
  
  /**
   * Configuration de base pour tous les tests
   */
  static getBaseConfig() {
    return {
      imports: [
        HttpClientTestingModule,
        BrowserAnimationsModule
      ],
      providers: [
        // Mocks des services de routage
        {
          provide: Router,
          useValue: jasmine.createSpyObj('Router', ['navigate', 'navigateByUrl'])
        },
        {
          provide: ActivatedRoute,
          useValue: {
            queryParams: of({}),
            params: of({}),
            snapshot: { params: {}, queryParams: {} }
          }
        },
        // Mock des services Material
        {
          provide: MatSnackBar,
          useValue: jasmine.createSpyObj('MatSnackBar', ['open'])
        }
      ]
    };
  }

  /**
   * Configuration pour les tests d'authentification
   */
  static getAuthConfig() {
    const baseConfig = this.getBaseConfig();
    return {
      ...baseConfig,
      providers: [
        ...baseConfig.providers,
        // Services d'authentification avec mocks
        {
          provide: TokenService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('TokenService', [
              'storeToken',
              'getToken',
              'clearTokens',
              'setRememberMe',
              'getAuthHeaders',
              'isTokenExpired'
            ]);
            spy.getToken.and.returnValue('mock-token');
            spy.isTokenExpired.and.returnValue(false);
            spy.getAuthHeaders.and.returnValue(new Headers({ 'Authorization': 'Bearer mock-token' }));
            return spy;
          })()
        },
        {
          provide: UserStateService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('UserStateService', [
              'setCurrentUser',
              'getCurrentUser',
              'clearUserState'
            ], {
              currentUser$: of(null),
              isAuthenticated$: of(false)
            });
            spy.getCurrentUser.and.returnValue(null);
            return spy;
          })()
        }
      ]
    };
  }

  /**
   * Configuration pour les tests de pointage
   */
  static getPointageConfig() {
    const baseConfig = this.getBaseConfig();
    return {
      ...baseConfig,
      providers: [
        ...baseConfig.providers,
        // Services de pointage avec mocks
        {
          provide: BadgeuseApiService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('BadgeuseApiService', [
              'getBadgeuses',
              'performPointage',
              'validatePointage'
            ]);
            spy.getBadgeuses.and.returnValue(of({ badgeuses: [], userStatus: null }));
            spy.performPointage.and.returnValue(of({ success: true, message: 'Success' }));
            spy.validatePointage.and.returnValue(of({ isValid: true, message: 'Valid' }));
            return spy;
          })()
        },
        {
          provide: BadgeuseManagerService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('BadgeuseManagerService', [
              'loadBadgeuses',
              'startAutoRefresh',
              'stopAutoRefresh',
              'getPrincipalServiceBadgeuses',
              'getSecondaryServiceBadgeuses',
              'isBadgeuseAvailable',
              'getBadgeuseServiceType',
              'getBadgeuseAccessDescription'
            ], {
              badgeuses$: of([]),
              isLoading$: of(false)
            });
            spy.loadBadgeuses.and.returnValue(of({ badgeuses: [], userStatus: null }));
            spy.startAutoRefresh.and.returnValue(of({ badgeuses: [], userStatus: null }));
            spy.getPrincipalServiceBadgeuses.and.returnValue([]);
            spy.getSecondaryServiceBadgeuses.and.returnValue([]);
            spy.isBadgeuseAvailable.and.returnValue(true);
            spy.getBadgeuseServiceType.and.returnValue('principal');
            spy.getBadgeuseAccessDescription.and.returnValue('Description');
            return spy;
          })()
        },
        {
          provide: WorkingTimeService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('WorkingTimeService', [
              'updateUserStatus',
              'calculateWorkingTime',
              'formatWorkingTime',
              'getWorkingTimeToday'
            ], {
              userStatus$: of(null),
              workingTime$: of(0)
            });
            spy.formatWorkingTime.and.returnValue('0h 00m');
            spy.getWorkingTimeToday.and.returnValue(0);
            spy.calculateWorkingTime.and.returnValue(0);
            return spy;
          })()
        }
      ]
    };
  }

  /**
   * Configuration pour les tests utilisateur
   */
  static getUserConfig() {
    const baseConfig = this.getBaseConfig();
    return {
      ...baseConfig,
      providers: [
        ...baseConfig.providers,
        // Services utilisateur avec mocks
        {
          provide: UserApiService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('UserApiService', [
              'getCurrentUserProfile',
              'updateUserProfile',
              'getUserById'
            ]);
            const mockUser = TestBedConfig.getMockData().user;
            spy.getCurrentUserProfile.and.returnValue(of({ success: true, data: mockUser }));
            spy.updateUserProfile.and.returnValue(of({ success: true, data: mockUser }));
            spy.getUserById.and.returnValue(of({ success: true, data: mockUser }));
            return spy;
          })()
        },
        {
          provide: UserHelperService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('UserHelperService', [
              'getFullName',
              'getActiveBadges',
              'formatOrganizationAddress',
              'getWorkingDaysArray',
              'formatWorkingHours',
              'hasRole'
            ]);
            spy.getFullName.and.returnValue('John Doe');
            spy.getActiveBadges.and.returnValue([]);
            spy.formatOrganizationAddress.and.returnValue('123 Test Street, Test City 12345, France');
            spy.getWorkingDaysArray.and.returnValue(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi']);
            spy.formatWorkingHours.and.returnValue('08:00 - 17:00 (8h)');
            spy.hasRole.and.returnValue(false);
            return spy;
          })()
        },
        {
          provide: GdprService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('GdprService', [
              'exportUserData',
              'deactivateAccount',
              'getDeletionStatus'
            ]);
            spy.exportUserData.and.returnValue(of({ success: true, data: 'export-data' }));
            spy.deactivateAccount.and.returnValue(of({ success: true, message: 'Account deactivated' }));
            spy.getDeletionStatus.and.returnValue(of({ success: true, data: { should_be_deleted: false } }));
            return spy;
          })()
        }
      ]
    };
  }

  /**
   * Configuration pour les tests coffee/payment
   */
  static getCoffeeConfig() {
    const baseConfig = this.getBaseConfig();
    return {
      ...baseConfig,
      providers: [
        ...baseConfig.providers,
        // Services coffee avec mocks
        {
          provide: CoffeeStateService,
          useValue: (() => {
            const spy = jasmine.createSpyObj('CoffeeStateService', [
              'loadCoffees',
              'buyCoffee',
              'getCoffees'
            ], {
              state$: of({ coffees: [], isLoading: false, error: null }),
              coffees: []
            });
            spy.loadCoffees.and.returnValue(of([]));
            spy.buyCoffee.and.returnValue(of({ success: true, message: 'Purchase successful' }));
            spy.getCoffees.and.returnValue([]);
            Object.defineProperty(spy, 'coffees', { value: [], writable: true });
            return spy;
          })()
        }
      ]
    };
  }

  /**
   * Configuration complète pour les tests d'intégration
   */
  static getFullConfig() {
    const baseConfig = this.getBaseConfig();
    const authConfig = this.getAuthConfig();
    const pointageConfig = this.getPointageConfig();
    const userConfig = this.getUserConfig();
    const coffeeConfig = this.getCoffeeConfig();

    // Combine tous les providers en évitant les doublons
    const allProviders = [
      ...baseConfig.providers,
      ...authConfig.providers.slice(3), // Skip les 3 premiers qui sont déjà dans baseConfig
      ...pointageConfig.providers.slice(3),
      ...userConfig.providers.slice(3),
      ...coffeeConfig.providers.slice(3)
    ];

    return {
      ...baseConfig,
      providers: allProviders
    };
  }

  /**
   * Helper pour configurer TestBed avec une configuration donnée
   */
  static async setupTestBed(config: any, additionalProviders: any[] = []) {
    await TestBed.configureTestingModule({
      ...config,
      providers: [
        ...config.providers,
        ...additionalProviders
      ]
    }).compileComponents();
  }

  /**
   * Helper pour créer des spies de services avec des valeurs par défaut
   */
  static createServiceSpy<T>(serviceName: string, methods: string[], properties?: any, returnValues?: any): jasmine.SpyObj<T> {
    const spy = jasmine.createSpyObj(serviceName, methods, properties);
    
    if (returnValues) {
      for (const [method, value] of Object.entries(returnValues)) {
        if (spy[method] && spy[method].and) {
          spy[method].and.returnValue(value);
        }
      }
    }
    
    return spy;
  }

  /**
   * Helper pour créer des mocks de données couramment utilisés
   */
  static getMockData() {
    return {
      user: {
        id: 1,
        email: 'test@example.com',
        nom: 'Doe',
        prenom: 'John',
        date_inscription: '2024-01-15',
        roles: ['ROLE_USER']
      },
      
      completeUserProfile: {
        user: {
          id: 1,
          email: 'test@example.com',
          nom: 'Doe',
          prenom: 'John',
          date_inscription: '2024-01-15',
          roles: ['ROLE_USER']
        },
        organisation: {
          id: 1,
          nom_organisation: 'Test Organization',
          adresse: {
            nom_rue: '123 Test Street',
            ville: 'Test City',
            code_postal: '12345',
            pays: 'France'
          }
        },
        services: {
          current: null,
          history: []
        },
        zones_accessibles: [],
        badges: [],
        acces_autorises: [],
        badgeuses_autorisees: []
      },
      
      userStatus: {
        status: 'absent' as 'absent' | 'present',
        is_in_principal_zone: false,
        current_work_start: undefined,
        working_time_today: 0,
        can_access_secondary: false,
        date: '2024-01-15'
      },

      badgeuse: {
        id: 1,
        reference: 'BADGE-001',
        status: 'available' as 'available' | 'blocked' | 'error',
        is_principal: true,
        is_accessible: true,
        is_blocked: false,
        service_type: 'principal' as 'principal' | 'secondaire' | 'mixed',
        date_installation: '2024-01-15',
        zones: [{
          id: 1,
          nom_zone: 'Zone Test',
          is_principal: true,
          service_id: 1,
          service_name: 'Service Principal'
        }]
      },

      coffee: {
        id: '1',
        name: 'Espresso',
        price: {
          formatted_amount: '5,00 €',
          amount: 500,
          currency: 'eur'
        },
        description: 'Un café serré'
      },

      organisation: {
        id: 1,
        nom_organisation: 'Test Organization',
        adresse: {
          nom_rue: '123 Test Street',
          ville: 'Test City',
          code_postal: '12345',
          pays: 'France'
        }
      }
    };
  }
}