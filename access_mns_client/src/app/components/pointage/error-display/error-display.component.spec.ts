import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ErrorDisplayComponent } from './error-display.component';

describe('ErrorDisplayComponent', () => {
  let component: ErrorDisplayComponent;
  let fixture: ComponentFixture<ErrorDisplayComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ErrorDisplayComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ErrorDisplayComponent);
    component = fixture.componentInstance;
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait afficher le message d\'erreur quand fourni', () => {
    const errorMessage = 'Test error message';
    component.errorMessage = errorMessage;
    fixture.detectChanges();

    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.error-text p')?.textContent?.trim()).toBe(errorMessage);
  });

  it('ne devrait pas afficher quand il n\'y a pas de message d\'erreur', () => {
    component.errorMessage = null;
    fixture.detectChanges();

    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.error-card')).toBeNull();
  });

  it('devrait émettre l\'event retry quand le bouton réessayer est cliqué', () => {
    spyOn(component.retry, 'emit');
    component.errorMessage = 'Test error';
    fixture.detectChanges();

    const retryButton = fixture.nativeElement.querySelector('button[color="primary"]') as HTMLButtonElement;
    retryButton.click();

    expect(component.retry.emit).toHaveBeenCalled();
  });

  it('devrait émettre l\'event dismiss quand le bouton fermer est cliqué', () => {
    spyOn(component.dismiss, 'emit');
    component.errorMessage = 'Test error';
    fixture.detectChanges();

    const dismissButton = fixture.nativeElement.querySelectorAll('button')[1] as HTMLButtonElement;
    dismissButton.click();

    expect(component.dismiss.emit).toHaveBeenCalled();
  });
});