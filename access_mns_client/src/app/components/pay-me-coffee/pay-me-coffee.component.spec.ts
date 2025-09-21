import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { of } from 'rxjs';

import { PayMeCoffeeComponent } from './pay-me-coffee.component';
import { CoffeeStateService } from '../../services/coffee/coffee-state.service';
import { Coffee } from '../../interfaces/coffee.interface';

describe('PayMeCoffeeComponent', () => {
  let component: PayMeCoffeeComponent;
  let fixture: ComponentFixture<PayMeCoffeeComponent>;
  let coffeeStateServiceSpy: jasmine.SpyObj<CoffeeStateService>;
  let matSnackBarSpy: jasmine.SpyObj<MatSnackBar>;
  let activatedRouteSpy: jasmine.SpyObj<ActivatedRoute>;

  const mockCoffees: Coffee[] = [
    { 
      id: '1', 
      name: 'Espresso', 
      price: {
        formatted_amount: '5,00 €',
        amount: 500,
        currency: 'eur'
      }, 
      description: 'Un café serré' 
    },
    { 
      id: '2', 
      name: 'Cappuccino', 
      price: {
        formatted_amount: '10,00 €',
        amount: 1000,
        currency: 'eur'
      }, 
      description: 'Un café avec mousse de lait' 
    }
  ];

  const mockCoffeeState = {
    coffees: mockCoffees,
    isLoading: false,
    error: null
  };

  beforeEach(async () => {
    const coffeeStateServiceSpyObj = jasmine.createSpyObj('CoffeeStateService', [
      'loadCoffees',
      'buyCoffee'
    ], {
      state$: of(mockCoffeeState),
      coffees: mockCoffees
    });
    
    Object.defineProperty(coffeeStateServiceSpyObj, 'coffees', {
      value: mockCoffees,
      writable: true,
      configurable: true
    });

    const matSnackBarSpyObj = jasmine.createSpyObj('MatSnackBar', ['open']);
    const activatedRouteSpyObj = jasmine.createSpyObj('ActivatedRoute', [], {
      queryParams: of({})
    });

    await TestBed.configureTestingModule({
      imports: [PayMeCoffeeComponent],
      providers: [
        { provide: CoffeeStateService, useValue: coffeeStateServiceSpyObj },
        { provide: MatSnackBar, useValue: matSnackBarSpyObj },
        { provide: ActivatedRoute, useValue: activatedRouteSpyObj }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PayMeCoffeeComponent);
    component = fixture.componentInstance;
    coffeeStateServiceSpy = TestBed.inject(CoffeeStateService) as jasmine.SpyObj<CoffeeStateService>;
    matSnackBarSpy = TestBed.inject(MatSnackBar) as jasmine.SpyObj<MatSnackBar>;
    activatedRouteSpy = TestBed.inject(ActivatedRoute) as jasmine.SpyObj<ActivatedRoute>;
    
    // Ne pas appeler detectChanges ici pour éviter l'exécution prématurée de ngOnInit
  });

  it('devrait créer le composant', () => {
    expect(component).toBeTruthy();
  });

  it('devrait initialiser et configurer les abonnements', () => {
    coffeeStateServiceSpy.loadCoffees.and.returnValue(of(mockCoffees));
    
    component.ngOnInit();
    
    expect(component.coffees).toEqual(mockCoffees);
    expect(component.loading).toBeFalsy();
    expect(component.error).toBeNull();
  });

  it('devrait charger les cafés quand le store est vide', () => {
    Object.defineProperty(coffeeStateServiceSpy, 'coffees', { value: [], writable: true });
    coffeeStateServiceSpy.loadCoffees.and.returnValue(of(mockCoffees));
    
    component.loadCoffees();
    
    expect(coffeeStateServiceSpy.loadCoffees).toHaveBeenCalled();
  });

  it('ne devrait pas charger les cafés quand le store contient des données', () => {
    Object.defineProperty(coffeeStateServiceSpy, 'coffees', { value: mockCoffees, writable: true });
    
    component.loadCoffees();
    
    expect(coffeeStateServiceSpy.loadCoffees).not.toHaveBeenCalled();
  });


  it('devrait se désabonner à la destruction', () => {
    const subscription = jasmine.createSpyObj('Subscription', ['unsubscribe']);
    component['subscriptions'] = [subscription];
    
    component.ngOnDestroy();
    
    expect(subscription.unsubscribe).toHaveBeenCalled();
  });
});