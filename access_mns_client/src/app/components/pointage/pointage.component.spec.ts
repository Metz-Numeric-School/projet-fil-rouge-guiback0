import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { MatSnackBar } from '@angular/material/snack-bar';
import { of, throwError } from 'rxjs';

import { PointageComponent } from './pointage.component';
import { BadgeuseApiService } from '../../services/pointage/badgeuse-api.service';
import { BadgeuseManagerService } from '../../services/pointage/badgeuse-manager.service';
import { WorkingTimeService } from '../../services/pointage/working-time.service';
import { BadgeuseAccess, PointageActionResponse, UserWorkingStatus } from '../../interfaces/pointage.interface';

describe('PointageComponent', () => {
  let component: PointageComponent;
  let fixture: ComponentFixture<PointageComponent>;
  let badgeuseApiServiceSpy: jasmine.SpyObj<BadgeuseApiService>;
  let badgeuseManagerServiceSpy: jasmine.SpyObj<BadgeuseManagerService>;

  const mockBadgeuse: BadgeuseAccess = {
    id: 1,
    reference: 'Badgeuse Principal',
    status: 'available',
    is_principal: true,
    is_accessible: true,
    is_blocked: false,
    service_type: 'principal',
    date_installation: '2024-01-15',
    zones: [{ 
      id: 1, 
      nom_zone: 'Zone 1', 
      is_principal: true,
      service_id: 1,
      service_name: 'Service Principal'
    }]
  };

  const mockUserStatus: UserWorkingStatus = {
    status: 'absent',
    is_in_principal_zone: false,
    current_work_start: undefined,
    working_time_today: 0,
    can_access_secondary: false,
    date: '2024-01-15'
  };

  const mockBadgeusesData = {
    badgeuses: [mockBadgeuse],
    userStatus: mockUserStatus
  };

  beforeEach(async () => {
    const badgeuseApiServiceSpyObj = jasmine.createSpyObj('BadgeuseApiService', ['performPointage']);
    const badgeuseManagerServiceSpyObj = jasmine.createSpyObj('BadgeuseManagerService', [
      'loadBadgeuses',
      'startAutoRefresh',
      'getPrincipalServiceBadgeuses',
      'getSecondaryServiceBadgeuses',
      'isBadgeuseAvailable',
      'getBadgeuseServiceType',
      'getBadgeuseAccessDescription'
    ], {
      badgeuses$: of([mockBadgeuse])
    });
    
    // Setup proper return values for the spy methods
    badgeuseManagerServiceSpyObj.loadBadgeuses.and.returnValue(of(mockBadgeusesData));
    badgeuseManagerServiceSpyObj.startAutoRefresh.and.returnValue(of(mockBadgeusesData));
    badgeuseManagerServiceSpyObj.getPrincipalServiceBadgeuses.and.returnValue([mockBadgeuse]);
    badgeuseManagerServiceSpyObj.getSecondaryServiceBadgeuses.and.returnValue([]);
    badgeuseManagerServiceSpyObj.isBadgeuseAvailable.and.returnValue(true);
    badgeuseManagerServiceSpyObj.getBadgeuseServiceType.and.returnValue('principal');
    badgeuseManagerServiceSpyObj.getBadgeuseAccessDescription.and.returnValue('Description');
    
    const workingTimeServiceSpyObj = jasmine.createSpyObj('WorkingTimeService', [
      'updateUserStatus',
      'formatWorkingTime'
    ], {
      userStatus$: of(mockUserStatus),
      workingTime$: of(0)
    });
    
    // Setup return values
    workingTimeServiceSpyObj.formatWorkingTime.and.returnValue('8h 00m');
    const matSnackBarSpyObj = jasmine.createSpyObj('MatSnackBar', ['open']);

    await TestBed.configureTestingModule({
      imports: [PointageComponent],
      providers: [
        { provide: BadgeuseApiService, useValue: badgeuseApiServiceSpyObj },
        { provide: BadgeuseManagerService, useValue: badgeuseManagerServiceSpyObj },
        { provide: WorkingTimeService, useValue: workingTimeServiceSpyObj },
        { provide: MatSnackBar, useValue: matSnackBarSpyObj }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PointageComponent);
    component = fixture.componentInstance;
    badgeuseApiServiceSpy = TestBed.inject(BadgeuseApiService) as jasmine.SpyObj<BadgeuseApiService>;
    badgeuseManagerServiceSpy = TestBed.inject(BadgeuseManagerService) as jasmine.SpyObj<BadgeuseManagerService>;
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait s\'initialiser avec l\'état par défaut', () => {
    expect(component.state.isLoading).toBeTruthy();
    expect(component.state.badgeuses).toEqual([]);
    expect(component.state.userStatus).toBeNull();
    expect(component.state.isProcessingPointage).toBeFalsy();
  });

  it('devrait charger les données à l\'initialisation', () => {
    component.ngOnInit();

    expect(badgeuseManagerServiceSpy.loadBadgeuses).toHaveBeenCalled();
    expect(component.state.badgeuses).toEqual(mockBadgeusesData.badgeuses);
    expect(component.state.userStatus).toEqual(mockUserStatus);
    expect(component.state.isLoading).toBeFalsy();
  });

  it('devrait gérer les erreurs de chargement', fakeAsync(() => {
    const errorMessage = 'Network error';
    
    badgeuseManagerServiceSpy.loadBadgeuses.and.returnValue(throwError(() => new Error(errorMessage)));
    spyOn(component['snackBar'], 'open');

    component['loadData']();
    tick();
    
    expect(component.state.lastError).toBe(errorMessage);
    expect(component.state.isLoading).toBeFalsy();
    expect(component['snackBar'].open).toHaveBeenCalledWith(
      'Erreur lors du chargement des données',
      'Fermer',
      { duration: 5000, panelClass: ['error-snackbar'] }
    );
  }));

  it('devrait effectuer un pointage direct avec le type d\'action correct pour un utilisateur absent', () => {
    component.state.userStatus = { 
      ...mockUserStatus, 
      status: 'absent' as 'absent' | 'present'
    };
    const successResponse: PointageActionResponse = {
      success: true,
      message: 'Pointage réussi'
    };
    badgeuseApiServiceSpy.performPointage.and.returnValue(of(successResponse));

    component.performDirectPointage(mockBadgeuse);

    expect(badgeuseApiServiceSpy.performPointage).toHaveBeenCalledWith({
      badgeuse_id: mockBadgeuse.id,
      action_type: 'entree'
    });
  });

  it('devrait effectuer un pointage direct avec le type d\'action correct pour un utilisateur présent', () => {
    component.state.userStatus = { 
      ...mockUserStatus, 
      status: 'present' as 'absent' | 'present'
    };
    const successResponse: PointageActionResponse = {
      success: true,
      message: 'Pointage réussi'
    };
    badgeuseApiServiceSpy.performPointage.and.returnValue(of(successResponse));

    component.performDirectPointage(mockBadgeuse);

    expect(badgeuseApiServiceSpy.performPointage).toHaveBeenCalledWith({
      badgeuse_id: mockBadgeuse.id,
      action_type: 'sortie'
    });
  });

  it('devrait gérer un pointage réussi', fakeAsync(() => {
    const successResponse: PointageActionResponse = {
      success: true,
      message: 'Pointage réussi'
    };
    
    badgeuseApiServiceSpy.performPointage.and.returnValue(of(successResponse));
    badgeuseManagerServiceSpy.loadBadgeuses.and.returnValue(of(mockBadgeusesData));
    spyOn(component['snackBar'], 'open');

    component.performPointage({ badgeuse: mockBadgeuse, actionType: 'entree' });
    tick();

    expect(component.state.isProcessingPointage).toBeFalsy();
    expect(component['snackBar'].open).toHaveBeenCalledWith(
      'Entrée enregistré avec succès',
      'Fermer',
      { duration: 4000, panelClass: ['success-snackbar'] }
    );
  }));

  it('devrait gérer les erreurs de pointage', fakeAsync(() => {
    const errorResponse = {
      success: false,
      message: 'Erreur de pointage'
    };
    
    badgeuseApiServiceSpy.performPointage.and.returnValue(throwError(() => errorResponse));
    spyOn(component['snackBar'], 'open');

    component.performPointage({ badgeuse: mockBadgeuse, actionType: 'entree' });
    tick();

    expect(component.state.isProcessingPointage).toBeFalsy();
    expect(component.state.lastError).toBe('Erreur de pointage');
    expect(component['snackBar'].open).toHaveBeenCalledWith(
      'Erreur de pointage',
      'Fermer',
      { duration: 6000, panelClass: ['error-snackbar'] }
    );
  }));

  it('devrait empêcher les demandes de pointage multiples simultanées', () => {
    component.state.isProcessingPointage = true;

    component.performDirectPointage(mockBadgeuse);

    expect(badgeuseApiServiceSpy.performPointage).not.toHaveBeenCalled();
  });

  it('devrait relancer le chargement des données', () => {
    // Reset call count
    badgeuseManagerServiceSpy.loadBadgeuses.calls.reset();

    component.retryLoad();

    expect(badgeuseManagerServiceSpy.loadBadgeuses).toHaveBeenCalled();
  });

  it('devrait ignorer l\'erreur', () => {
    component.state.lastError = 'Some error';

    component.dismissError();

    expect(component.state.lastError).toBeNull();
  });

  it('devrait obtenir les badgeuses principales', () => {
    const result = component.getPrincipalBadgeuses();

    expect(result).toEqual([mockBadgeuse]);
    expect(badgeuseManagerServiceSpy.getPrincipalServiceBadgeuses).toHaveBeenCalledWith(component.state.badgeuses);
  });

  it('devrait obtenir les badgeuses secondaires', () => {
    const result = component.getSecondaryBadgeuses();

    expect(result).toEqual([]);
    expect(badgeuseManagerServiceSpy.getSecondaryServiceBadgeuses).toHaveBeenCalledWith(component.state.badgeuses);
  });

  it('devrait suivre la badgeuse par ID', () => {
    const result = component.trackByBadgeuseId(0, mockBadgeuse);

    expect(result).toBe(mockBadgeuse.id);
  });

  it('devrait se désabonner lors de la destruction', () => {
    spyOn(component['subscriptions'], 'unsubscribe');

    component.ngOnDestroy();

    expect(component['subscriptions'].unsubscribe).toHaveBeenCalled();
  });
});