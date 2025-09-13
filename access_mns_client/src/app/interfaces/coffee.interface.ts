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