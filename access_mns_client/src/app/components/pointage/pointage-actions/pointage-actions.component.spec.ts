import { ComponentFixture, TestBed } from '@angular/core/testing';
import { PointageActionsComponent } from './pointage-actions.component';
import { BadgeuseAccess, UserWorkingStatus } from '../../../interfaces/pointage.interface';

describe('PointageActionsComponent', () => {
  let component: PointageActionsComponent;
  let fixture: ComponentFixture<PointageActionsComponent>;

  const mockBadgeuse: BadgeuseAccess = {
    id: 1,
    reference: 'Badgeuse Test',
    status: 'available',
    is_principal: true,
    is_accessible: true,
    is_blocked: false,
    service_type: 'principal',
    date_installation: '2024-01-15',
    zones: []
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
    await TestBed.configureTestingModule({
      imports: [PointageActionsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PointageActionsComponent);
    component = fixture.componentInstance;
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('ne devrait pas afficher quand aucune badgeuse sélectionnée', () => {
    component.selectedBadgeuse = null;
    fixture.detectChanges();

    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.actions-card')).toBeNull();
  });

  it('devrait afficher quand une badgeuse est sélectionnée', () => {
    component.selectedBadgeuse = mockBadgeuse;
    fixture.detectChanges();

    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.actions-card')).toBeTruthy();
    expect(compiled.textContent).toContain('Badgeuse Test');
  });

  it('devrait afficher le compte à rebours quand countdownSeconds > 0', () => {
    component.selectedBadgeuse = mockBadgeuse;
    component.countdownSeconds = 30;
    fixture.detectChanges();

    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.countdown-section')).toBeTruthy();
    expect(compiled.querySelector('.action-buttons')).toBeNull();
  });

  it('devrait afficher les boutons d\'action quand le compte à rebours est à 0', () => {
    component.selectedBadgeuse = mockBadgeuse;
    component.countdownSeconds = 0;
    fixture.detectChanges();

    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.countdown-section')).toBeNull();
    expect(compiled.querySelector('.action-buttons')).toBeTruthy();
  });

  it('devrait formater correctement le compte à rebours', () => {
    expect(component.formatCountdown(30)).toBe('30s');
    expect(component.formatCountdown(90)).toBe('1min 30s');
    expect(component.formatCountdown(5)).toBe('5s');
  });

  it('devrait émettre l\'event performAction quand un bouton d\'action est cliqué', () => {
    spyOn(component.performAction, 'emit');
    component.selectedBadgeuse = mockBadgeuse;
    component.countdownSeconds = 0;
    component.isProcessing = false;

    component.onPerformAction('entree');

    expect(component.performAction.emit).toHaveBeenCalledWith({
      badgeuse: mockBadgeuse,
      actionType: 'entree'
    });
  });

  it('ne devrait pas émettre performAction quand en cours de traitement', () => {
    spyOn(component.performAction, 'emit');
    component.selectedBadgeuse = mockBadgeuse;
    component.isProcessing = true;

    component.onPerformAction('entree');

    expect(component.performAction.emit).not.toHaveBeenCalled();
  });

  it('devrait émettre l\'event cancel quand le bouton annuler est cliqué', () => {
    spyOn(component.cancel, 'emit');

    component.onCancel();

    expect(component.cancel.emit).toHaveBeenCalled();
  });

  it('devrait retourner la couleur de statut correcte', () => {
    component.userStatus = null;
    expect(component.getStatusColor()).toBe('warn');

    component.userStatus = { ...mockUserStatus, status: 'present' };
    expect(component.getStatusColor()).toBe('primary');

    component.userStatus = { ...mockUserStatus, status: 'absent' };
    expect(component.getStatusColor()).toBe('accent');
  });

  it('devrait retourner l\'icône de statut correcte', () => {
    component.userStatus = null;
    expect(component.getStatusIcon()).toBe('help_outline');

    component.userStatus = { ...mockUserStatus, status: 'present' };
    expect(component.getStatusIcon()).toBe('work');

    component.userStatus = { ...mockUserStatus, status: 'absent' };
    expect(component.getStatusIcon()).toBe('home');
  });

  it('devrait retourner le texte de statut correct', () => {
    component.userStatus = null;
    expect(component.getStatusText()).toBe('Inconnu');

    component.userStatus = { ...mockUserStatus, status: 'present' };
    expect(component.getStatusText()).toBe('Présent');

    component.userStatus = { ...mockUserStatus, status: 'absent' };
    expect(component.getStatusText()).toBe('Absent');
  });

  it('devrait désactiver les boutons quand en cours de traitement', () => {
    component.selectedBadgeuse = mockBadgeuse;
    component.countdownSeconds = 0;
    component.isProcessing = true;
    fixture.detectChanges();

    const buttons = fixture.nativeElement.querySelectorAll('.action-button') as NodeListOf<HTMLButtonElement>;
    buttons.forEach(button => {
      expect(button.disabled).toBeTruthy();
    });
  });
});