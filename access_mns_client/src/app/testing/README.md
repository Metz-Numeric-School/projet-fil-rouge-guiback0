# Configuration TestBed pour ACCESS MNS

Ce dossier contient les utilitaires de test centralisés pour simplifier l'écriture et la maintenance des tests unitaires.

## Structure

- `test-bed-config.ts` : Configurations TestBed prédéfinies pour différents domaines
- `test-helpers.ts` : Fonctions utilitaires pour les tests
- `index.ts` : Export barrel pour faciliter les imports

## Usage

### Configuration TestBed basique

```typescript
import { TestBedConfig } from '../../testing';

beforeEach(async () => {
  await TestBedConfig.setupTestBed(TestBedConfig.getBaseConfig());
});
```

### Configuration pour tests d'authentification

```typescript
import { TestBedConfig } from '../../testing';

beforeEach(async () => {
  await TestBedConfig.setupTestBed(
    TestBedConfig.getAuthConfig(),
    [MonServiceSupplementaire] // Services additionnels
  );
});
```

### Configuration pour tests de pointage

```typescript
import { TestBedConfig } from '../../testing';

beforeEach(async () => {
  await TestBedConfig.setupTestBed(TestBedConfig.getPointageConfig());
});
```

### Configurations disponibles

- `getBaseConfig()` : Configuration minimale (HttpClient, Router, MatSnackBar)
- `getAuthConfig()` : Configuration pour l'authentification
- `getPointageConfig()` : Configuration pour le système de pointage
- `getUserConfig()` : Configuration pour la gestion utilisateur
- `getCoffeeConfig()` : Configuration pour le système de paiement café
- `getFullConfig()` : Configuration complète avec tous les services

### Utilisation des helpers de test

```typescript
import { TestHelpers } from '../../testing';

// Attendre les changements de détection
await TestHelpers.waitForChanges(fixture);

// Simuler un clic
TestHelpers.clickElement(fixture, 'button.submit');

// Remplir un input
TestHelpers.setInputValue(fixture, 'input[name="email"]', 'test@example.com');

// Vérifier un appel de service
TestHelpers.expectServiceCall(serviceSpy.method, 'method', param1, param2);

// Vérifier un snackbar
TestHelpers.expectSnackBar(snackBarSpy, 'Message attendu', 'Action');

// Vérifier une navigation
TestHelpers.expectNavigation(routerSpy, ['/path']);
```

### Données de test prédéfinies

```typescript
import { TestBedConfig } from '../../testing';

const mockData = TestBedConfig.getMockData();

// Utiliser les données mock
const user = mockData.user;
const badgeuse = mockData.badgeuse;
const organisation = mockData.organisation;
```

## Exemple complet

```typescript
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { TestBedConfig, TestHelpers } from '../../testing';
import { MonComponent } from './mon-component.component';
import { MonService } from './mon-service.service';

describe('MonComponent', () => {
  let component: MonComponent;
  let fixture: ComponentFixture<MonComponent>;
  let serviceSpy: jasmine.SpyObj<MonService>;

  const mockData = TestBedConfig.getMockData();

  beforeEach(async () => {
    await TestBedConfig.setupTestBed(
      TestBedConfig.getBaseConfig(),
      [
        {
          provide: MonService,
          useValue: jasmine.createSpyObj('MonService', ['method1', 'method2'])
        }
      ]
    );

    fixture = TestBed.createComponent(MonComponent);
    component = fixture.componentInstance;
    serviceSpy = TestBed.inject(MonService) as jasmine.SpyObj<MonService>;

    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should call service method', async () => {
    serviceSpy.method1.and.returnValue(of(mockData.user));
    
    component.callMethod();
    await TestHelpers.waitForChanges(fixture);

    TestHelpers.expectServiceCall(serviceSpy.method1, 'method1', 'expectedParam');
  });
});
```

## Avantages

- **Réutilisabilité** : Configurations prêtes à l'emploi
- **Maintenabilité** : Changements centralisés 
- **Cohérence** : Même setup pour tous les tests
- **Simplicité** : Helpers pour les opérations courantes
- **Données mock** : Objets de test prédéfinis
- **Type safety** : Tout est typé avec TypeScript

## Bonnes pratiques

1. Utilisez la configuration la plus spécifique à votre domaine
2. Ajoutez des services additionnels uniquement si nécessaire
3. Utilisez les helpers pour les interactions DOM
4. Réutilisez les données mock prédéfinies
5. Groupez les tests logiquement avec `describe()`
6. Testez les cas d'erreur et de succès