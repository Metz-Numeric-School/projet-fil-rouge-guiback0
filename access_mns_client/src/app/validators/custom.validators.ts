import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

/**
 * Custom validators synchronized with backend validation rules
 */
export class CustomValidators {
  
  /**
   * French name validator (letters, spaces, apostrophes, hyphens)
   * Synchronized with backend regex: /^[a-zA-ZàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\s\'-]+$/
   */
  static frenchName(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null; // Don't validate empty values (use required validator for that)
      }
      
      const frenchNamePattern = /^[a-zA-ZàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\s\'-]+$/;
      
      if (!frenchNamePattern.test(control.value)) {
        return {
          frenchName: {
            message: 'Le nom ne peut contenir que des lettres, espaces, apostrophes et tirets'
          }
        };
      }
      
      return null;
    };
  }
  
  /**
   * French phone number validator
   * Synchronized with backend regex: /^(\+33|0)[1-9]([0-9]{8})$/
   */
  static frenchPhone(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null; // Don't validate empty values
      }
      
      const frenchPhonePattern = /^(\+33|0)[1-9]([0-9]{8})$/;
      
      if (!frenchPhonePattern.test(control.value)) {
        return {
          frenchPhone: {
            message: 'Le numéro de téléphone doit être au format français valide (ex: 0123456789 ou +33123456789)'
          }
        };
      }
      
      return null;
    };
  }
  
  /**
   * Password complexity validator
   * Synchronized with backend regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/
   */
  static passwordComplexity(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null; // Don't validate empty values (use required validator for that)
      }
      
      const value = control.value;
      const errors: any = {};
      
      // Check minimum length (8 characters)
      if (value.length < 8) {
        errors.minLength = 'Le mot de passe doit contenir au moins 8 caractères';
      }
      
      // Check for lowercase letter
      if (!/(?=.*[a-z])/.test(value)) {
        errors.lowercase = 'Le mot de passe doit contenir au moins une minuscule';
      }
      
      // Check for uppercase letter
      if (!/(?=.*[A-Z])/.test(value)) {
        errors.uppercase = 'Le mot de passe doit contenir au moins une majuscule';
      }
      
      // Check for digit
      if (!/(?=.*\d)/.test(value)) {
        errors.digit = 'Le mot de passe doit contenir au moins un chiffre';
      }
      
      // Check for special character
      if (!/(?=.*[@$!%*?&])/.test(value)) {
        errors.specialChar = 'Le mot de passe doit contenir au moins un caractère spécial (@$!%*?&)';
      }
      
      // Check allowed characters only
      if (!/^[A-Za-z\d@$!%*?&]+$/.test(value)) {
        errors.invalidChars = 'Le mot de passe ne peut contenir que des lettres, chiffres et caractères spéciaux (@$!%*?&)';
      }
      
      return Object.keys(errors).length > 0 ? { passwordComplexity: errors } : null;
    };
  }
  
  /**
   * Date range validator (age between 0 and 120 years)
   */
  static ageRange(minAge: number = 0, maxAge: number = 120): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null;
      }
      
      const birthDate = new Date(control.value);
      const today = new Date();
      const age = today.getFullYear() - birthDate.getFullYear();
      
      // Check if birth date is in the future
      if (birthDate > today) {
        return {
          futureDate: {
            message: 'La date de naissance doit être antérieure à aujourd\'hui'
          }
        };
      }
      
      // Check age limits
      if (age < minAge || age > maxAge) {
        return {
          ageRange: {
            message: `L'âge doit être entre ${minAge} et ${maxAge} ans`
          }
        };
      }
      
      return null;
    };
  }
  
  /**
   * SIRET number validator (14 digits)
   */
  static siret(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null;
      }
      
      const siretPattern = /^[0-9]{14}$/;
      
      if (!siretPattern.test(control.value)) {
        return {
          siret: {
            message: 'Le numéro SIRET doit contenir exactement 14 chiffres'
          }
        };
      }
      
      return null;
    };
  }
  
  /**
   * French postal code validator (5 digits)
   */
  static frenchPostalCode(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) {
        return null;
      }
      
      const postalCodePattern = /^[0-9]{5}$/;
      
      if (!postalCodePattern.test(control.value)) {
        return {
          frenchPostalCode: {
            message: 'Le code postal doit contenir exactement 5 chiffres'
          }
        };
      }
      
      return null;
    };
  }
}