import { ComponentFixture, TestBed } from '@angular/core/testing';
import { BadgeuseCardComponent } from './badgeuse-card.component';
import { BadgeuseManagerService } from '../../../services/pointage/badgeuse-manager.service';
import { BadgeuseAccess, UserWorkingStatus } from '../../../interfaces/pointage.interface';

describe('BadgeuseCardComponent', () => {
  let component: BadgeuseCardComponent;
  let fixture: ComponentFixture<BadgeuseCardComponent>;
  let badgeuseManagerServiceSpy: jasmine.SpyObj<BadgeuseManagerService>;

  const mockBadgeuse: BadgeuseAccess = {
    id: 1,
    reference: 'BADGE-001',
    status: 'available',
    is_principal: true,
    is_accessible: true,
    is_blocked: false,
    service_type: 'principal',
    date_installation: '2024-01-15',
    zones: [
      { 
        id: 1, 
        nom_zone: 'Zone 1', 
        is_principal: true,
        service_id: 1,
        service_name: 'Service Principal'
      }
    ]
  };

  const mockUserStatus: UserWorkingStatus = {
    status: 'absent',
    is_in_principal_zone: false,
    current_work_start: undefined,
    working_time_today: 0,
    can_access_secondary: false,
    date: '2024-01-15'
  };

  beforeEach(async () => {
    const badgeuseManagerServiceSpyObj = jasmine.createSpyObj('BadgeuseManagerService', [
      'isBadgeuseAvailable',
      'getBadgeuseServiceType',
      'getBadgeuseAccessDescription'
    ]);

    await TestBed.configureTestingModule({
      imports: [BadgeuseCardComponent],
      providers: [
        { provide: BadgeuseManagerService, useValue: badgeuseManagerServiceSpyObj }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BadgeuseCardComponent);
    component = fixture.componentInstance;
    badgeuseManagerServiceSpy = TestBed.inject(BadgeuseManagerService) as jasmine.SpyObj<BadgeuseManagerService>;

    component.badgeuse = mockBadgeuse;
    component.userStatus = mockUserStatus;
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait vérifier si la badgeuse est disponible', () => {
    badgeuseManagerServiceSpy.isBadgeuseAvailable.and.returnValue(true);

    expect(component.isAvailable).toBeTruthy();
    expect(badgeuseManagerServiceSpy.isBadgeuseAvailable).toHaveBeenCalledWith(mockBadgeuse, mockUserStatus);
  });

  it('devrait retourner false pour la disponibilité quand il n\'y a pas de statut utilisateur', () => {
    component.userStatus = null;

    expect(component.isAvailable).toBeFalsy();
  });

  it('devrait détecter une badgeuse principale', () => {
    expect(component.isPrincipal).toBeTruthy();
  });

  it('devrait détecter une badgeuse non-principale', () => {
    component.badgeuse = { ...mockBadgeuse, is_principal: false };

    expect(component.isPrincipal).toBeFalsy();
  });

  it('devrait retourner la couleur de statut correcte', () => {
    expect(component.getStatusColor()).toBe('primary');

    component.badgeuse = { ...mockBadgeuse, status: 'blocked' };
    expect(component.getStatusColor()).toBe('warn');

    component.badgeuse = { ...mockBadgeuse, status: 'error' };
    expect(component.getStatusColor()).toBe('accent');
  });

  it('devrait retourner l\'icône de statut correcte', () => {
    expect(component.getStatusIcon()).toBe('check_circle');

    component.badgeuse = { ...mockBadgeuse, status: 'blocked' };
    expect(component.getStatusIcon()).toBe('block');

    component.badgeuse = { ...mockBadgeuse, status: 'error' };
    expect(component.getStatusIcon()).toBe('error');
  });

  it('devrait retourner le texte de statut correct', () => {
    expect(component.getStatusText()).toBe('Disponible');

    component.badgeuse = { ...mockBadgeuse, status: 'blocked' };
    expect(component.getStatusText()).toBe('Bloquée');

    component.badgeuse = { ...mockBadgeuse, status: 'error' };
    expect(component.getStatusText()).toBe('Erreur');
  });

  it('devrait retourner l\'icône de service correcte', () => {
    badgeuseManagerServiceSpy.getBadgeuseServiceType.and.returnValue('principal');
    expect(component.getServiceIcon()).toBe('star');

    badgeuseManagerServiceSpy.getBadgeuseServiceType.and.returnValue('secondary');
    expect(component.getServiceIcon()).toBe('work');

    badgeuseManagerServiceSpy.getBadgeuseServiceType.and.returnValue('mixed');
    expect(component.getServiceIcon()).toBe('workspaces');

    badgeuseManagerServiceSpy.getBadgeuseServiceType.and.returnValue('none');
    expect(component.getServiceIcon()).toBe('help_outline');
  });

  it('devrait obtenir la description du service', () => {
    const description = 'Service principal';
    badgeuseManagerServiceSpy.getBadgeuseAccessDescription.and.returnValue(description);

    expect(component.getServiceDescription()).toBe(description);
    expect(badgeuseManagerServiceSpy.getBadgeuseAccessDescription).toHaveBeenCalledWith(mockBadgeuse);
  });

  it('devrait émettre toggleSelect quand la badgeuse est cliquée et disponible', () => {
    spyOn(component.toggleSelect, 'emit');
    badgeuseManagerServiceSpy.isBadgeuseAvailable.and.returnValue(true);
    component.isProcessing = false;

    component.onToggleSelect();

    expect(component.toggleSelect.emit).toHaveBeenCalledWith(mockBadgeuse);
  });

  it('ne devrait pas émettre toggleSelect quand la badgeuse n\'est pas disponible', () => {
    spyOn(component.toggleSelect, 'emit');
    badgeuseManagerServiceSpy.isBadgeuseAvailable.and.returnValue(false);

    component.onToggleSelect();

    expect(component.toggleSelect.emit).not.toHaveBeenCalled();
  });

  it('ne devrait pas émettre toggleSelect quand en cours de traitement', () => {
    spyOn(component.toggleSelect, 'emit');
    badgeuseManagerServiceSpy.isBadgeuseAvailable.and.returnValue(true);
    component.isProcessing = true;

    component.onToggleSelect();

    expect(component.toggleSelect.emit).not.toHaveBeenCalled();
  });
});