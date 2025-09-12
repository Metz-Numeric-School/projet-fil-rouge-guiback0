import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBarModule, MatSnackBar } from '@angular/material/snack-bar';
import { Subscription } from 'rxjs';
import { CoffeeStateService } from '../../services/coffee/coffee-state.service';
import { Coffee } from '../../interfaces/coffee.interface';

@Component({
  selector: 'app-pay-me-coffee',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
  ],
  templateUrl: './pay-me-coffee.component.html',
  styleUrl: './pay-me-coffee.component.scss',
})
export class PayMeCoffeeComponent implements OnInit, OnDestroy {
  // Propriétés pour l'état local avec le service
  coffees: Coffee[] = [];
  loading = false;
  error: string | null = null;
  
  private subscriptions: Subscription[] = [];

  constructor(
    private coffeeStateService: CoffeeStateService,
    private snackBar: MatSnackBar,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.setupSubscriptions();
    this.handleStripeCallback();
    this.loadCoffees();
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }

  private setupSubscriptions(): void {
    // S'abonner aux changements d'état du service coffee
    const stateSub = this.coffeeStateService.state$.subscribe(state => {
      this.coffees = state.coffees;
      this.loading = state.isLoading;
      this.error = state.error;
    });

    this.subscriptions.push(stateSub);
  }

  private handleStripeCallback(): void {
    this.route.queryParams.subscribe(params => {
      if (params['success'] === 'true') {
        this.snackBar.open(
          '🎉 Merci pour votre don ! Votre paiement a été traité avec succès.',
          'Fermer',
          { duration: 8000 }
        );
      } else if (params['canceled'] === 'true') {
        this.snackBar.open(
          'Paiement annulé. Vous pouvez réessayer quand vous le souhaitez.',
          'Fermer',
          { duration: 5000 }
        );
      }
    });
  }

  loadCoffees(): void {
    // Ne charger que si le store est vide
    if (this.coffeeStateService.coffees.length === 0) {
      const coffeeLoadSub = this.coffeeStateService.loadCoffees().subscribe({
        next: () => {
          // Les données sont automatiquement mises à jour via la subscription d'état
        },
        error: (error) => {
          this.snackBar.open(
            'Erreur lors du chargement des cafés : ' + error.message,
            'Fermer',
            { duration: 5000 }
          );
        }
      });

      this.subscriptions.push(coffeeLoadSub);
    }
  }

  buyCoffee(coffee: Coffee): void {
    console.log('buyCoffee appelé avec:', coffee);
    
    // Utiliser le service de gestion d'état pour l'achat via Stripe
    const buySub = this.coffeeStateService.buyCoffee(coffee).subscribe({
      next: (result) => {
        this.snackBar.open(
          `${result.message || 'Redirection vers le paiement...'}`,
          'Fermer',
          { duration: 3000 }
        );
      },
      error: (error) => {
        this.snackBar.open(
          `Erreur lors de l'achat : ${error.message}`,
          'Fermer',
          { duration: 5000 }
        );
      }
    });

    this.subscriptions.push(buySub);
  }
}