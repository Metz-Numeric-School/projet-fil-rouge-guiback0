import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { tap, catchError, finalize, map } from 'rxjs/operators';
import { StripeService } from './stripe.service';
import { StripeProduct } from '../../interfaces/coffee.interface';
import { Coffee, CoffeeState } from '../../interfaces/coffee.interface';

@Injectable({
  providedIn: 'root'
})
export class CoffeeStateService {
  constructor(private stripeService: StripeService) {}
  
  // État initial
  private readonly initialState: CoffeeState = {
    coffees: [],
    isLoading: false,
    error: null
  };

  // BehaviorSubject pour gérer l'état
  private stateSubject = new BehaviorSubject<CoffeeState>(this.initialState);

  // Observables publics
  public state$ = this.stateSubject.asObservable();
  public coffees$ = this.state$.pipe(
    map(state => state.coffees)
  );
  public isLoading$ = this.state$.pipe(
    map(state => state.isLoading)
  );
  public error$ = this.state$.pipe(
    map(state => state.error)
  );


  // Getters pour l'état actuel
  get currentState(): CoffeeState {
    return this.stateSubject.value;
  }

  get coffees(): Coffee[] {
    return this.currentState.coffees;
  }

  get isLoading(): boolean {
    return this.currentState.isLoading;
  }

  get error(): string | null {
    return this.currentState.error;
  }

  // Actions pour modifier l'état
  private updateState(partial: Partial<CoffeeState>): void {
    this.stateSubject.next({
      ...this.currentState,
      ...partial
    });
  }

  // Convertir les produits Stripe en objets Coffee
  private mapStripeProductToCoffee(product: StripeProduct): Coffee | null {
    if (!product.price) {
      return null;
    }
    
    return {
      id: product.id,
      name: product.name,
      description: product.description || 'Un délicieux café pour nous soutenir',
      price: {
        formatted_amount: product.price.formatted_amount,
        amount: product.price.amount,
        currency: product.price.currency
      }
    };
  }

  // Charger les cafés depuis l'API Stripe
  loadCoffees(): Observable<Coffee[]> {
    this.updateState({ isLoading: true, error: null });

    return this.stripeService.getCoffees().pipe(
      map(stripeProducts => {
        // Convertir les produits Stripe en objets Coffee
        return stripeProducts
          .map(product => this.mapStripeProductToCoffee(product))
          .filter((coffee): coffee is Coffee => coffee !== null);
      }),
      tap(coffees => {
        this.updateState({
          coffees,
          isLoading: false,
          error: null
        });
      }),
      catchError(error => {
        this.updateState({
          isLoading: false,
          error: error.message || 'Erreur lors du chargement des cafés'
        });
        return throwError(() => error);
      })
    );
  }

  // Acheter un café via Stripe
  buyCoffee(coffee: Coffee): Observable<any> {
    this.updateState({ error: null });

    // D'abord, récupérer le produit Stripe correspondant pour obtenir le priceId
    return this.stripeService.getCoffees().pipe(
      map(stripeProducts => {
        const stripeProduct = stripeProducts.find(p => p.id === coffee.id);
        if (!stripeProduct || !stripeProduct.price) {
          throw new Error('Produit ou prix introuvable');
        }
        return stripeProduct.price.id;
      }),
      tap(priceId => {
        // Créer la session de checkout et rediriger
        this.stripeService.createCheckoutSession(priceId).subscribe({
          next: (checkoutUrl) => {
            window.location.href = checkoutUrl;
          },
          error: (error) => {
            this.updateState({
              error: error.message || 'Erreur lors de la création de la session de paiement'
            });
          }
        });
      }),
      map(() => ({ success: true, message: 'Redirection vers le paiement...' })),
      catchError(error => {
        this.updateState({
          error: error.message || 'Erreur lors de l\'achat'
        });
        return throwError(() => error);
      })
    );
  }

  // Nettoyer les erreurs
  clearError(): void {
    this.updateState({ error: null });
  }

  // Réinitialiser l'état
  reset(): void {
    this.stateSubject.next(this.initialState);
  }
}