import { ComponentFixture } from '@angular/core/testing';
import { DebugElement } from '@angular/core';
import { By } from '@angular/platform-browser';

/**
 * Helpers utilitaires pour les tests Angular
 */
export class TestHelpers {

  /**
   * Attend que la détection de changements soit terminée
   */
  static async waitForChanges<T>(fixture: ComponentFixture<T>): Promise<void> {
    fixture.detectChanges();
    await fixture.whenStable();
  }

  /**
   * Simule un clic sur un élément
   */
  static clickElement(fixture: ComponentFixture<any>, selector: string): void {
    const element = fixture.debugElement.query(By.css(selector));
    if (element) {
      element.nativeElement.click();
      fixture.detectChanges();
    } else {
      throw new Error(`Element with selector "${selector}" not found`);
    }
  }

  /**
   * Simule la saisie de texte dans un input
   */
  static setInputValue(fixture: ComponentFixture<any>, selector: string, value: string): void {
    const input = fixture.debugElement.query(By.css(selector));
    if (input) {
      input.nativeElement.value = value;
      input.nativeElement.dispatchEvent(new Event('input'));
      fixture.detectChanges();
    } else {
      throw new Error(`Input with selector "${selector}" not found`);
    }
  }

  /**
   * Obtient le texte d'un élément
   */
  static getElementText(fixture: ComponentFixture<any>, selector: string): string {
    const element = fixture.debugElement.query(By.css(selector));
    return element ? element.nativeElement.textContent.trim() : '';
  }

  /**
   * Vérifie si un élément est présent
   */
  static isElementPresent(fixture: ComponentFixture<any>, selector: string): boolean {
    return !!fixture.debugElement.query(By.css(selector));
  }

  /**
   * Obtient tous les éléments correspondant au sélecteur
   */
  static getAllElements(fixture: ComponentFixture<any>, selector: string): DebugElement[] {
    return fixture.debugElement.queryAll(By.css(selector));
  }

  /**
   * Simule une soumission de formulaire
   */
  static submitForm(fixture: ComponentFixture<any>, formSelector: string = 'form'): void {
    const form = fixture.debugElement.query(By.css(formSelector));
    if (form) {
      form.nativeElement.dispatchEvent(new Event('submit'));
      fixture.detectChanges();
    } else {
      throw new Error(`Form with selector "${formSelector}" not found`);
    }
  }

  /**
   * Attendre un certain délai (pour les tests asynchrones)
   */
  static delay(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Créer un mock d'Observable qui émet une valeur puis se complete
   */
  static mockObservable<T>(value: T) {
    return {
      subscribe: (callback: any) => {
        callback(value);
        return { unsubscribe: jasmine.createSpy('unsubscribe') };
      }
    };
  }

  /**
   * Créer un mock d'Observable qui émet une erreur
   */
  static mockObservableError(error: any) {
    return {
      subscribe: (next?: any, errorCallback?: any) => {
        if (errorCallback) errorCallback(error);
        return { unsubscribe: jasmine.createSpy('unsubscribe') };
      }
    };
  }

  /**
   * Helper pour vérifier qu'un service spy a été appelé avec les bons paramètres
   */
  static expectServiceCall(spy: jasmine.Spy, methodName: string, ...expectedArgs: any[]): void {
    expect(spy).toHaveBeenCalled();
    if (expectedArgs.length > 0) {
      expect(spy).toHaveBeenCalledWith(...expectedArgs);
    }
  }

  /**
   * Helper pour vérifier qu'un snackbar a été ouvert avec le bon message
   */
  static expectSnackBar(snackBarSpy: jasmine.SpyObj<any>, expectedMessage: string, expectedAction?: string): void {
    expect(snackBarSpy.open).toHaveBeenCalled();
    if (snackBarSpy.open.calls.count() > 0) {
      const callArgs = snackBarSpy.open.calls.mostRecent().args;
      expect(callArgs[0]).toBe(expectedMessage);
      if (expectedAction) {
        expect(callArgs[1]).toBe(expectedAction);
      }
    }
  }

  /**
   * Helper pour vérifier qu'une navigation a eu lieu
   */
  static expectNavigation(routerSpy: jasmine.SpyObj<any>, expectedRoute: string[]): void {
    expect(routerSpy.navigate).toHaveBeenCalledWith(expectedRoute);
  }

  /**
   * Créer un mock d'HttpHeaders
   */
  static mockHttpHeaders(headers: { [key: string]: string }) {
    return {
      get: (name: string) => headers[name] || null,
      has: (name: string) => name in headers,
      keys: () => Object.keys(headers),
      getAll: (name: string) => headers[name] ? [headers[name]] : null,
      append: jasmine.createSpy('append'),
      set: jasmine.createSpy('set'),
      delete: jasmine.createSpy('delete')
    };
  }
}