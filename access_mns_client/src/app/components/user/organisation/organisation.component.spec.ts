import { ComponentFixture, TestBed } from '@angular/core/testing';
import { OrganisationComponent } from './organisation.component';
import { UserHelperService } from '../../../services/user/user-helper.service';
import { CompleteUserProfile } from '../../../interfaces/user.interface';

describe('OrganisationComponent', () => {
  let component: OrganisationComponent;
  let fixture: ComponentFixture<OrganisationComponent>;
  let userHelperServiceSpy: jasmine.SpyObj<UserHelperService>;

  const mockCompleteProfile: CompleteUserProfile = {
    user: {
      id: 1,
      email: 'test@example.com',
      nom: 'Doe',
      prenom: 'John',
      date_inscription: '2024-01-15',
      roles: ['ROLE_USER']
    },
    badges: [],
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
    acces_autorises: [],
    badgeuses_autorisees: []
  };

  beforeEach(async () => {
    const userHelperServiceSpyObj = jasmine.createSpyObj('UserHelperService', [
      'formatOrganizationAddress'
    ]);

    await TestBed.configureTestingModule({
      imports: [OrganisationComponent],
      providers: [
        { provide: UserHelperService, useValue: userHelperServiceSpyObj }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(OrganisationComponent);
    component = fixture.componentInstance;
    userHelperServiceSpy = TestBed.inject(UserHelperService) as jasmine.SpyObj<UserHelperService>;

    component.completeProfile = mockCompleteProfile;
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait obtenir l\'adresse formatée de l\'organisation', () => {
    const formattedAddress = '123 Test Street, Test City 12345, France';
    userHelperServiceSpy.formatOrganizationAddress.and.returnValue(formattedAddress);

    const result = component.getOrganizationAddress();

    expect(result).toBe(formattedAddress);
    expect(userHelperServiceSpy.formatOrganizationAddress).toHaveBeenCalledWith(
      mockCompleteProfile.organisation
    );
  });

  it('devrait retourner une chaîne vide quand il n\'y a pas d\'organisation', () => {
    component.completeProfile = { ...mockCompleteProfile, organisation: null };

    const result = component.getOrganizationAddress();

    expect(result).toBe('');
    expect(userHelperServiceSpy.formatOrganizationAddress).not.toHaveBeenCalled();
  });

  it('devrait retourner une chaîne vide quand il n\'y a pas de profil complet', () => {
    component.completeProfile = null;

    const result = component.getOrganizationAddress();

    expect(result).toBe('');
    expect(userHelperServiceSpy.formatOrganizationAddress).not.toHaveBeenCalled();
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

  it('devrait gérer une organisation avec toutes les propriétés', () => {
    const fullOrganization = {
      id: 1,
      nom_organisation: 'Full Organization',
      adresse: {
        nom_rue: '456 Full Street',
        ville: 'Full City',
        code_postal: '54321',
        pays: 'France'
      },
      telephone: '0123456789',
      email: 'org@example.com'
    };

    component.completeProfile = {
      ...mockCompleteProfile,
      organisation: fullOrganization
    };

    const formattedAddress = '456 Full Street, Full City 54321, France';
    userHelperServiceSpy.formatOrganizationAddress.and.returnValue(formattedAddress);

    const result = component.getOrganizationAddress();

    expect(result).toBe(formattedAddress);
    expect(userHelperServiceSpy.formatOrganizationAddress).toHaveBeenCalledWith(fullOrganization);
  });
});