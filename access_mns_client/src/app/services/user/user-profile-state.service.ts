import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { tap, catchError, switchMap, map } from 'rxjs/operators';
import { User, CompleteUserProfile } from '../../interfaces/user.interface';
import { UserApiService } from './user-api.service';

// Interface pour l'état du profil utilisateur
interface UserProfileState {
  currentUser: User | null;
  completeProfile: CompleteUserProfile | null;
  selectedTabIndex: number;
  isLoading: boolean;
  error: string | null;
}

@Injectable({
  providedIn: 'root'
})
export class UserProfileStateService {
  // État initial
  private readonly initialState: UserProfileState = {
    currentUser: null,
    completeProfile: null,
    selectedTabIndex: 0,
    isLoading: false,
    error: null
  };

  // BehaviorSubject pour gérer l'état
  private stateSubject = new BehaviorSubject<UserProfileState>(this.initialState);

  // Observables publics
  public state$ = this.stateSubject.asObservable();
  public currentUser$ = this.state$.pipe(
    map(state => state.currentUser)
  );
  public completeProfile$ = this.state$.pipe(
    map(state => state.completeProfile)
  );
  public selectedTabIndex$ = this.state$.pipe(
    map(state => state.selectedTabIndex)
  );
  public isLoading$ = this.state$.pipe(
    map(state => state.isLoading)
  );
  public error$ = this.state$.pipe(
    map(state => state.error)
  );

  constructor(
    private userApiService: UserApiService
  ) {}

  // Getters pour l'état actuel
  get currentState(): UserProfileState {
    return this.stateSubject.value;
  }

  get currentUser(): User | null {
    return this.currentState.currentUser;
  }

  get completeProfile(): CompleteUserProfile | null {
    return this.currentState.completeProfile;
  }

  get selectedTabIndex(): number {
    return this.currentState.selectedTabIndex;
  }

  get isLoading(): boolean {
    return this.currentState.isLoading;
  }

  get error(): string | null {
    return this.currentState.error;
  }

  // Actions pour modifier l'état
  private updateState(partial: Partial<UserProfileState>): void {
    this.stateSubject.next({
      ...this.currentState,
      ...partial
    });
  }

  // Définir l'utilisateur actuel
  setCurrentUser(user: User | null): void {
    this.updateState({ currentUser: user });
  }

  // Définir l'onglet sélectionné
  setSelectedTabIndex(index: number): void {
    this.updateState({ selectedTabIndex: index });
  }

  // Charger le profil complet
  loadCompleteProfile(forceReload: boolean = false): Observable<CompleteUserProfile> {
    // Ne charger que si le store est vide ou si le rechargement est forcé
    if (this.completeProfile && !forceReload) {
      return new Observable(observer => {
        observer.next(this.completeProfile!);
        observer.complete();
      });
    }

    this.updateState({ isLoading: true, error: null });

    return this.userApiService.getCompleteProfile().pipe(
      tap(profile => {
        this.updateState({
          completeProfile: profile,
          isLoading: false,
          error: null
        });
        
        // Mettre à jour l'utilisateur actuel si présent
        if (profile?.user) {
          this.updateState({ currentUser: profile.user });
        }
      }),
      catchError(error => {
        this.updateState({
          isLoading: false,
          error: error.message || 'Erreur lors du chargement du profil'
        });
        return throwError(() => error);
      })
    );
  }



  // Vérifier si l'organisation existe
  hasOrganization(): boolean {
    return !!this.completeProfile?.organisation;
  }

  // Nettoyer les erreurs
  clearError(): void {
    this.updateState({ error: null });
  }

  // Réinitialiser l'état
  reset(): void {
    this.stateSubject.next(this.initialState);
  }

  // Nettoyer complètement les données utilisateur
  clearUserData(): void {
    this.updateState({
      currentUser: null,
      completeProfile: null,
      selectedTabIndex: 0,
      error: null
    });
  }
}