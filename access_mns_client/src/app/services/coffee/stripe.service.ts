import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, catchError, map, throwError } from 'rxjs';
import { TokenService } from '../auth/token.service';
import {
  StripeProduct,
  StripeProductsResponse,
  CheckoutSessionResponse,
  StripeVerificationResponse
} from '../../interfaces/coffee.interface';

@Injectable({
  providedIn: 'root'
})
export class StripeService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api';

  constructor(
    private http: HttpClient,
    private tokenService: TokenService
  ) {}

  /**
   * Récupérer tous les produits café disponibles
   */
  getCoffees(): Observable<StripeProduct[]> {
    const headers = this.getAuthHeaders();

    return this.http
      .get<StripeProductsResponse>(`${this.API_BASE_URL}/stripe/coffees`, { headers })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response.data;
          }
          throw new Error(response.message || 'Erreur lors de la récupération des produits');
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors de la récupération des produits café';

          if (error.error) {
            switch (error.error.error) {
              case 'STRIPE_ERROR':
                errorMessage = error.error.message;
                break;
              default:
                errorMessage = error.error.message || errorMessage;
            }
          }

          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Créer une session de checkout Stripe
   */
  createCheckoutSession(priceId: string): Observable<string> {
    console.log('Service: createCheckoutSession appelé avec priceId:', priceId);
    
    const headers = this.getAuthHeaders();
    const url = `${this.API_BASE_URL}/stripe/create-checkout-session`;
    
    console.log('Service: URL de la requête:', url);
    console.log('Service: Headers:', headers);

    return this.http
      .post<CheckoutSessionResponse>(url, { priceId }, { headers })
      .pipe(
        map((response) => {
          console.log('Service: Réponse reçue:', response);
          
          if (response.success && response.data) {
            console.log('Service: URL de checkout extraite:', response.data.url);
            return response.data.url;
          }
          throw new Error(response.message || 'Erreur lors de la création de la session de paiement');
        }),
        catchError((error) => {
          console.error('Service: Erreur lors de la requête:', error);
          
          let errorMessage = 'Erreur lors de la création de la session de paiement';

          if (error.error) {
            switch (error.error.error) {
              case 'MISSING_PRICE_ID':
                errorMessage = 'ID du prix manquant';
                break;
              case 'STRIPE_SESSION_ERROR':
                errorMessage = error.error.message;
                break;
              default:
                errorMessage = error.error.message || errorMessage;
            }
          }

          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Vérifier une session de checkout Stripe
   */
  verifySession(sessionId: string): Observable<any> {
    const headers = this.getAuthHeaders();
    const params = { session_id: sessionId };

    return this.http
      .get<StripeVerificationResponse>(`${this.API_BASE_URL}/stripe/verify`, { headers, params })
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response.data;
          }
          throw new Error(response.message || 'Erreur lors de la vérification de la session');
        }),
        catchError((error) => {
          let errorMessage = 'Erreur lors de la vérification de la session de paiement';

          if (error.error) {
            switch (error.error.error) {
              case 'MISSING_SESSION_ID':
                errorMessage = 'ID de session manquant';
                break;
              case 'INVALID_SESSION':
                errorMessage = 'Session introuvable ou invalide';
                break;
              default:
                errorMessage = error.error.message || errorMessage;
            }
          }

          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Get authorization headers with JWT token
   */
  private getAuthHeaders(): HttpHeaders {
    const token = this.tokenService.getToken();
    return new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });
  }
}