import { TestBed } from '@angular/core/testing';
import { Router, NavigationEnd } from '@angular/router';
import { of } from 'rxjs';
import { AppComponent } from './app.component';
import { TestBedConfig } from './testing';

describe('AppComponent', () => {
  let routerSpy: jasmine.SpyObj<Router>;

  beforeEach(async () => {
    const routerSpyObj = jasmine.createSpyObj('Router', ['navigate'], {
      events: of(new NavigationEnd(1, '/', '/'))
    });

    await TestBedConfig.setupTestBed(
      TestBedConfig.getFullConfig(),
      [
        { provide: Router, useValue: routerSpyObj }
      ]
    );
    
    await TestBed.configureTestingModule({
      imports: [AppComponent]
    }).compileComponents();

    routerSpy = TestBed.inject(Router) as jasmine.SpyObj<Router>;
  });

  it('devrait créer l\'application', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.componentInstance;
    expect(app).toBeTruthy();
  });

  it('devrait avoir le titre \'access-mns\'', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.componentInstance;
    expect(app.title).toEqual('access-mns');
  });

  it('devrait afficher le titre du footer', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const component = fixture.componentInstance;
    // Initialise currentRoute avant detectChanges pour éviter une erreur undefined
    component.currentRoute = '/dashboard';
    fixture.detectChanges();
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.footer-title')?.textContent).toContain('ACCESS MNS');
  });

  it('devrait afficher la navbar quand ce n\'est pas la page de connexion', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const component = fixture.componentInstance;
    component.currentRoute = '/dashboard';
    expect(component.shouldShowNavbar).toBeTruthy();
  });

  it('devrait masquer la navbar sur la page de connexion', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const component = fixture.componentInstance;
    component.currentRoute = '/login';
    expect(component.shouldShowNavbar).toBeFalsy();
  });
});
