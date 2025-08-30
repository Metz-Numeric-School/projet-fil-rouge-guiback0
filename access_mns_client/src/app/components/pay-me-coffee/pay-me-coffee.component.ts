import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBarModule, MatSnackBar } from '@angular/material/snack-bar';
import { StripeService, StripeProduct } from '../../services/stripe.service';

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
export class PayMeCoffeeComponent implements OnInit {
  coffees: StripeProduct[] = [];
  loading = false;

  constructor(
    private stripeService: StripeService,
    private snackBar: MatSnackBar,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.loadCoffees();
    this.handleStripeCallback();
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
    this.loading = true;
    this.stripeService.getCoffees().subscribe({
      next: (products) => {
        this.coffees = products;
        this.loading = false;
      },
      error: (error) => {
        this.snackBar.open(
          'Erreur lors du chargement des produits : ' + error.message,
          'Fermer',
          { duration: 5000 }
        );
        this.loading = false;
      }
    });
  }

  buyCoffee(coffee: StripeProduct): void {
    console.log('buyCoffee appelé avec:', coffee);
    
    if (!coffee.price?.id) {
      console.error('Prix non disponible:', coffee.price);
      this.snackBar.open(
        'Erreur : Prix non disponible pour ce produit',
        'Fermer',
        { duration: 5000 }
      );
      return;
    }

    console.log('Création session checkout pour priceId:', coffee.price.id);
    
    this.snackBar.open(
      `Redirection vers le paiement pour ${coffee.name}...`,
      'Fermer',
      { duration: 2000 }
    );

    this.stripeService.createCheckoutSession(coffee.price.id).subscribe({
      next: (checkoutUrl) => {
        console.log('Session créée, URL reçue:', checkoutUrl);
        console.log('Redirection vers:', checkoutUrl);
        window.location.href = checkoutUrl;
      },
      error: (error) => {
        console.error('Erreur création session:', error);
        this.snackBar.open(
          'Erreur lors de la création de la session de paiement : ' + error.message,
          'Fermer',
          { duration: 5000 }
        );
      }
    });
  }
}