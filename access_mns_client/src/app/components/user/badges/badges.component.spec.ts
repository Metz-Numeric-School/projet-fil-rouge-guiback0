import { ComponentFixture, TestBed } from '@angular/core/testing';
import { BadgesComponent } from './badges.component';
import { UserHelperService } from '../../../services/user/user-helper.service';
import { CompleteUserProfile } from '../../../interfaces/user.interface';
import { TestBedConfig } from '../../../testing';

describe('BadgesComponent', () => {
  let component: BadgesComponent;
  let fixture: ComponentFixture<BadgesComponent>;
  let userHelperServiceSpy: jasmine.SpyObj<UserHelperService>;

  const mockData = TestBedConfig.getMockData();
  const mockCompleteProfile: CompleteUserProfile = {
    ...mockData.completeUserProfile,
    badges: [
      {
        id: 1,
        numero_badge: 'BADGE001',
        type_badge: 'RFID',
        date_creation: '2024-01-15',
        date_expiration: '2025-01-15',
        is_active: true
      },
      {
        id: 2,
        numero_badge: 'BADGE002',
        type_badge: 'NFC',
        date_creation: '2024-01-10',
        date_expiration: '2024-12-31',
        is_active: false
      }
    ]
  };

  beforeEach(async () => {
    await TestBedConfig.setupTestBed(
      TestBedConfig.getUserConfig(),
      [
        {
          provide: UserHelperService,
          useValue: jasmine.createSpyObj('UserHelperService', ['getActiveBadges'])
        }
      ]
    );

    await TestBed.configureTestingModule({
      imports: [BadgesComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(BadgesComponent);
    component = fixture.componentInstance;
    userHelperServiceSpy = TestBed.inject(UserHelperService) as jasmine.SpyObj<UserHelperService>;

    component.completeProfile = mockCompleteProfile;
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait obtenir les badges actifs', () => {
    const activeBadges = [mockCompleteProfile.badges![0]];
    userHelperServiceSpy.getActiveBadges.and.returnValue(activeBadges);

    const result = component.getActiveBadges();

    expect(result).toEqual(activeBadges);
    expect(userHelperServiceSpy.getActiveBadges).toHaveBeenCalledWith(mockCompleteProfile);
  });

  it('devrait retourner un tableau vide pour les badges actifs quand il n\'y a pas de profil', () => {
    component.completeProfile = null;

    const result = component.getActiveBadges();

    expect(result).toEqual([]);
    expect(userHelperServiceSpy.getActiveBadges).not.toHaveBeenCalled();
  });

  it('devrait vérifier si l\'utilisateur a tous les badges', () => {
    expect(component.hasAllBadges()).toBeTruthy();
  });

  it('devrait retourner false pour hasAllBadges quand il n\'y a pas de badges', () => {
    component.completeProfile = { ...mockCompleteProfile, badges: [] };
    expect(component.hasAllBadges()).toBeFalsy();

    component.completeProfile = { ...mockCompleteProfile, badges: [] };
    expect(component.hasAllBadges()).toBeFalsy();

    component.completeProfile = null;
    expect(component.hasAllBadges()).toBeFalsy();
  });

  it('devrait obtenir tous les badges en sécurité', () => {
    const result = component.getAllBadges();
    expect(result).toEqual(mockCompleteProfile.badges);
  });

  it('devrait retourner un tableau vide quand il n\'y a pas de badges', () => {
    component.completeProfile = { ...mockCompleteProfile, badges: [] };
    const result = component.getAllBadges();
    expect(result).toEqual([]);
  });

  it('devrait retourner un tableau vide quand il n\'y a pas de profil', () => {
    component.completeProfile = null;
    const result = component.getAllBadges();
    expect(result).toEqual([]);
  });

  it('devrait obtenir la couleur de statut correcte pour un badge actif', () => {
    const activeBadge = { is_active: true };
    expect(component.getBadgeStatusColor(activeBadge)).toBe('primary');
  });

  it('devrait obtenir la couleur de statut correcte pour un badge inactif', () => {
    const inactiveBadge = { is_active: false };
    expect(component.getBadgeStatusColor(inactiveBadge)).toBe('warn');
  });

  it('devrait formater la date correctement', () => {
    const dateString = '2024-01-15';
    const result = component.formatDate(dateString);
    expect(result).toBe('15/01/2024');
  });

  it('devrait retourner "Non défini" pour une date indéfinie', () => {
    const result = component.formatDate(undefined);
    expect(result).toBe('Non défini');
  });

  it('devrait retourner "Non défini" pour une chaîne de date vide', () => {
    const result = component.formatDate('');
    expect(result).toBe('Non défini');
  });
});