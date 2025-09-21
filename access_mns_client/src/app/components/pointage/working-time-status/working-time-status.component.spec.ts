import { ComponentFixture, TestBed } from '@angular/core/testing';
import { WorkingTimeStatusComponent } from './working-time-status.component';
import { WorkingTimeService } from '../../../services/pointage/working-time.service';
import { UserWorkingStatus } from '../../../interfaces/pointage.interface';

describe('WorkingTimeStatusComponent', () => {
  let component: WorkingTimeStatusComponent;
  let fixture: ComponentFixture<WorkingTimeStatusComponent>;
  let workingTimeServiceSpy: jasmine.SpyObj<WorkingTimeService>;

  const mockUserStatus: UserWorkingStatus = {
    status: 'present',
    is_in_principal_zone: true,
    current_work_start: '2024-01-15T08:00:00Z',
    working_time_today: 480,
    can_access_secondary: true,
    date: '2024-01-15',
    last_action: {
      heure: '08:00',
      type: 'entree',
      timestamp: '2024-01-15T08:00:00Z',
      badgeuse: 'BADGE-001',
      zone: 'Zone Principal',
      is_principal: true,
      service_type: 'principal',
      affects_status: true
    }
  };

  beforeEach(async () => {
    const workingTimeServiceSpyObj = jasmine.createSpyObj('WorkingTimeService', [
      'formatWorkingTime'
    ]);

    await TestBed.configureTestingModule({
      imports: [WorkingTimeStatusComponent],
      providers: [
        { provide: WorkingTimeService, useValue: workingTimeServiceSpyObj }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(WorkingTimeStatusComponent);
    component = fixture.componentInstance;
    workingTimeServiceSpy = TestBed.inject(WorkingTimeService) as jasmine.SpyObj<WorkingTimeService>;
  });

  beforeEach(() => {
    // Copie fraîche pour chaque test pour éviter la pollution d'état
    component.userStatus = JSON.parse(JSON.stringify(mockUserStatus));
    component.workingTimeToday = 480;
    component.workingStartTime = '2024-01-15T08:00:00Z';
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait retourner la couleur de statut correcte pour un utilisateur présent', () => {
    expect(component.getStatusColor()).toBe('primary');
  });

  it('devrait retourner la couleur de statut correcte pour un utilisateur absent', () => {
    component.userStatus = { ...mockUserStatus, status: 'absent' };
    expect(component.getStatusColor()).toBe('accent');
  });

  it('devrait retourner la couleur warn quand il n\'y a pas de statut utilisateur', () => {
    component.userStatus = null;
    expect(component.getStatusColor()).toBe('warn');
  });

  it('devrait retourner l\'icône de statut correcte pour un utilisateur présent', () => {
    expect(component.getStatusIcon()).toBe('work');
  });

  it('devrait retourner l\'icône de statut correcte pour un utilisateur absent', () => {
    component.userStatus = { ...mockUserStatus, status: 'absent' };
    expect(component.getStatusIcon()).toBe('home');
  });

  it('devrait retourner l\'icône d\'aide quand il n\'y a pas de statut utilisateur', () => {
    component.userStatus = null;
    expect(component.getStatusIcon()).toBe('help_outline');
  });

  it('devrait retourner le texte de statut correct pour un utilisateur présent', () => {
    expect(component.getStatusText()).toBe('Présent au travail');
  });

  it('devrait retourner le texte de statut correct pour un utilisateur absent', () => {
    component.userStatus = { ...mockUserStatus, status: 'absent' };
    expect(component.getStatusText()).toBe('Absent du travail');
  });

  it('devrait retourner le texte de statut inconnu quand il n\'y a pas de statut utilisateur', () => {
    component.userStatus = null;
    expect(component.getStatusText()).toBe('Statut inconnu');
  });

  it('devrait formater le temps de travail en utilisant le service', () => {
    const formattedTime = '8h 00m';
    workingTimeServiceSpy.formatWorkingTime.and.returnValue(formattedTime);

    expect(component.getFormattedWorkingTime()).toBe(formattedTime);
    expect(workingTimeServiceSpy.formatWorkingTime).toHaveBeenCalledWith(480);
  });

  it('devrait retourner la couleur d\'action correcte pour une entrée', () => {
    expect(component.getActionColor()).toBe('primary');
  });

  it('devrait retourner la couleur d\'action correcte pour une sortie', () => {
    component.userStatus!.last_action!.type = 'sortie';
    expect(component.getActionColor()).toBe('accent');
  });

  it('devrait retourner la couleur d\'action correcte pour un accès', () => {
    component.userStatus!.last_action!.type = 'acces';
    expect(component.getActionColor()).toBe('warn');
  });

  it('devrait retourner la couleur warn quand il n\'y a pas de dernière action', () => {
    component.userStatus = { ...mockUserStatus, last_action: undefined };
    expect(component.getActionColor()).toBe('warn');
  });

  it('devrait retourner l\'icône d\'action correcte pour une entrée', () => {
    expect(component.getActionIcon()).toBe('login');
  });

  it('devrait retourner l\'icône d\'action correcte pour une sortie', () => {
    component.userStatus!.last_action!.type = 'sortie';
    expect(component.getActionIcon()).toBe('logout');
  });

  it('devrait retourner l\'icône d\'action correcte pour un accès', () => {
    component.userStatus!.last_action!.type = 'acces';
    expect(component.getActionIcon()).toBe('key');
  });

  it('devrait retourner le texte d\'action correct pour une entrée', () => {
    expect(component.getActionText()).toBe('Entrée');
  });

  it('devrait retourner le texte d\'action correct pour une sortie', () => {
    component.userStatus!.last_action!.type = 'sortie';
    expect(component.getActionText()).toBe('Sortie');
  });

  it('devrait retourner le texte d\'action correct pour un accès', () => {
    component.userStatus!.last_action!.type = 'acces';
    expect(component.getActionText()).toBe('Accès');
  });

  it('devrait retourner la couleur de service correcte pour un service principal', () => {
    expect(component.getServiceColor()).toBe('primary');
  });

  it('devrait retourner la couleur de service correcte pour un service secondaire', () => {
    if (component.userStatus?.last_action) {
      component.userStatus.last_action.service_type = 'secondaire';
    }
    expect(component.getServiceColor()).toBe('accent');
  });

  it('devrait retourner l\'icône de service correcte pour un service principal', () => {
    expect(component.getServiceIcon()).toBe('star');
  });

  it('devrait retourner l\'icône de service correcte pour un service secondaire', () => {
    component.userStatus!.last_action!.service_type = 'secondaire';
    expect(component.getServiceIcon()).toBe('work');
  });

  it('devrait retourner le texte de service correct pour un service principal', () => {
    expect(component.getServiceText()).toBe('Service principal');
  });

  it('devrait retourner le texte de service correct pour un service secondaire', () => {
    component.userStatus!.last_action!.service_type = 'secondaire';
    expect(component.getServiceText()).toBe('Service secondaire');
  });

  it('devrait gérer gracieusement l\'absence de dernière action', () => {
    component.userStatus = { ...mockUserStatus, last_action: undefined };

    expect(component.getActionColor()).toBe('warn');
    expect(component.getActionIcon()).toBe('key');
    expect(component.getActionText()).toBe('Accès');
    expect(component.getServiceColor()).toBe('accent');
    expect(component.getServiceIcon()).toBe('work');
    expect(component.getServiceText()).toBe('Service');
  });
});