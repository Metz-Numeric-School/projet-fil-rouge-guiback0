// Interface pour les données coffee
export interface Coffee {
  id: string;
  name: string;
  description: string;
  price: {
    formatted_amount: string;
    amount: number;
    currency: string;
  };
}

// Interface pour l'état du service coffee
export interface CoffeeState {
  coffees: Coffee[];
  isLoading: boolean;
  error: string | null;
}

// Interfaces Stripe pour les produits café
export interface StripeProduct {
  id: string;
  name: string;
  description: string;
  images: string[];
  price: {
    id: string;
    amount: number;
    currency: string;
    formatted_amount: string;
  } | null;
  created: string;
}

export interface StripeProductsResponse {
  success: boolean;
  data: StripeProduct[];
  count: number;
  message: string;
}

export interface CheckoutSessionResponse {
  success: boolean;
  data: {
    sessionId: string;
    url: string;
  };
  message: string;
}

export interface StripeVerificationResponse {
  success: boolean;
  data: {
    session_id: string;
    status: string;
    payment_status: string;
    paid: boolean;
    amount_total: number;
    currency: string;
    customer_email?: string;
  };
  message: string;
}