import { ComponentFixture, TestBed } from '@angular/core/testing';
import { PersonalInfoComponent } from './personal-info.component';
import { UserHelperService } from '../../../services/user/user-helper.service';
import { User } from '../../../interfaces/user.interface';

describe('PersonalInfoComponent', () => {
  let component: PersonalInfoComponent;
  let fixture: ComponentFixture<PersonalInfoComponent>;
  let userHelperServiceSpy: jasmine.SpyObj<UserHelperService>;

  const mockUser: User = {
    id: 1,
    email: 'test@example.com',
    nom: 'Doe',
    prenom: 'John',
    date_inscription: '2024-01-15',
    roles: ['ROLE_USER'],
    jours_semaine_travaille: 'Lundi,Mardi,Mercredi,Jeudi,Vendredi',
    heure_debut: '08:00',
    horraire: '8',
    telephone: '0123456789',
    adresse: '123 Test Street'
  };

  beforeEach(async () => {
    const userHelperServiceSpyObj = jasmine.createSpyObj('UserHelperService', [
      'getFullName',
      'getWorkingDaysArray',
      'formatWorkingHours'
    ]);

    await TestBed.configureTestingModule({
      imports: [PersonalInfoComponent],
      providers: [
        { provide: UserHelperService, useValue: userHelperServiceSpyObj }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PersonalInfoComponent);
    component = fixture.componentInstance;
    userHelperServiceSpy = TestBed.inject(UserHelperService) as jasmine.SpyObj<UserHelperService>;

    component.currentUser = mockUser;
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait obtenir le nom complet', () => {
    const fullName = 'John Doe';
    userHelperServiceSpy.getFullName.and.returnValue(fullName);

    const result = component.getFullName(mockUser);

    expect(result).toBe(fullName);
    expect(userHelperServiceSpy.getFullName).toHaveBeenCalledWith(mockUser);
  });

  it('devrait obtenir le tableau des jours de travail', () => {
    const workingDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    userHelperServiceSpy.getWorkingDaysArray.and.returnValue(workingDays);

    const result = component.getWorkingDays();

    expect(result).toEqual(workingDays);
    expect(userHelperServiceSpy.getWorkingDaysArray).toHaveBeenCalledWith(
      mockUser.jours_semaine_travaille
    );
  });

  it('devrait obtenir les jours de travail quand il n\'y a pas d\'utilisateur actuel', () => {
    component.currentUser = null;
    const workingDays: string[] = [];
    userHelperServiceSpy.getWorkingDaysArray.and.returnValue(workingDays);

    const result = component.getWorkingDays();

    expect(result).toEqual(workingDays);
    expect(userHelperServiceSpy.getWorkingDaysArray).toHaveBeenCalledWith(undefined);
  });

  it('devrait obtenir les horaires de travail formatées', () => {
    const formattedHours = '08:00 - 16:00 (8h)';
    userHelperServiceSpy.formatWorkingHours.and.returnValue(formattedHours);

    const result = component.getWorkingHours();

    expect(result).toBe(formattedHours);
    expect(userHelperServiceSpy.formatWorkingHours).toHaveBeenCalledWith(
      mockUser.heure_debut,
      mockUser.horraire
    );
  });

  it('devrait obtenir les horaires de travail quand il n\'y a pas d\'utilisateur actuel', () => {
    component.currentUser = null;
    const formattedHours = 'Non défini';
    userHelperServiceSpy.formatWorkingHours.and.returnValue(formattedHours);

    const result = component.getWorkingHours();

    expect(result).toBe(formattedHours);
    expect(userHelperServiceSpy.formatWorkingHours).toHaveBeenCalledWith(undefined, undefined);
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

  it('devrait formater la date-heure correctement', () => {
    const dateTimeString = '2024-01-15T14:30:00Z';
    const result = component.formatDateTime(dateTimeString);
    
    // Note: Le format exact peut varier selon le fuseau horaire, donc on vérifie les composants clés
    expect(result).toContain('15/01/2024');
    expect(result).toContain('à');
    expect(result).toMatch(/\d{2}:\d{2}/); // Format d'heure
  });

  it('devrait retourner "Non défini" pour une date-heure indéfinie', () => {
    const result = component.formatDateTime(undefined);
    expect(result).toBe('Non défini');
  });

  it('devrait retourner "Non défini" pour une chaîne de date-heure vide', () => {
    const result = component.formatDateTime('');
    expect(result).toBe('Non défini');
  });

  it('devrait gérer un utilisateur avec des propriétés minimales', () => {
    const minimalUser: User = {
      id: 2,
      email: 'minimal@example.com',
      nom: 'Min',
      prenom: 'User',
      date_inscription: '2024-01-01',
      roles: ['ROLE_USER']
    };

    component.currentUser = minimalUser;

    userHelperServiceSpy.getFullName.and.returnValue('User Min');
    userHelperServiceSpy.getWorkingDaysArray.and.returnValue([]);
    userHelperServiceSpy.formatWorkingHours.and.returnValue('Non défini');

    expect(component.getFullName(minimalUser)).toBe('User Min');
    expect(component.getWorkingDays()).toEqual([]);
    expect(component.getWorkingHours()).toBe('Non défini');
  });
});