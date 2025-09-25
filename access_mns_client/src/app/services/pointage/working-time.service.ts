import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, timer, map, catchError, throwError } from 'rxjs';
import {
  UserWorkingStatus,
  WorkingTimePeriod,
} from '../../interfaces/pointage.interface';
import { TokenService } from '../auth/token.service';
import { environment } from '../../../environments';

@Injectable({
  providedIn: 'root',
})
export class WorkingTimeService {
  private readonly API_BASE_URL = `${environment.apiBaseUrl}/pointage`;
  
  private userStatusSubject = new BehaviorSubject<UserWorkingStatus | null>(null);
  private workingTimeSubject = new BehaviorSubject<number>(0);
  private workingTimeTimer: any = null;
  
  public userStatus$ = this.userStatusSubject.asObservable();
  public workingTime$ = this.workingTimeSubject.asObservable();

  constructor(
    private http: HttpClient,
    private tokenService: TokenService
  ) {}

  /**
   * Get current user status (present/absent)
   * Uses: GET /api/pointage/status
   */
  getCurrentStatus(): Observable<UserWorkingStatus> {
    const headers = this.tokenService.getAuthHeaders();

    return this.http
      .get<{ success: boolean; data: UserWorkingStatus; error?: string; message?: string }>(
        `${this.API_BASE_URL}/status`, 
        { headers }
      )
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            this.userStatusSubject.next(response.data);
            this.updateWorkingTime(response.data);
            return response.data;
          }
          throw new Error(
            response.message || 'Erreur lors de la récupération du statut'
          );
        }),
        catchError((error) => {
          const errorMessage = error.error?.message || 'Erreur lors de la récupération du statut';
          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Get working time for a specific period
   * Uses: GET /api/pointage/working-time
   */
  getWorkingTime(startDate: string, endDate: string): Observable<WorkingTimePeriod> {
    const headers = this.tokenService.getAuthHeaders();
    const params = { start_date: startDate, end_date: endDate };

    return this.http
      .get<{ success: boolean; data: WorkingTimePeriod; error?: string; message?: string }>(
        `${this.API_BASE_URL}/working-time`,
        { headers, params }
      )
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            return response.data;
          }
          throw new Error(
            response.message || 'Erreur lors du calcul du temps de travail'
          );
        }),
        catchError((error) => {
          const errorMessage = error.error?.message || 'Erreur lors du calcul du temps de travail';
          return throwError(() => new Error(errorMessage));
        })
      );
  }

  /**
   * Update user status
   */
  updateUserStatus(status: UserWorkingStatus): void {
    console.log('WorkingTimeService: Updating user status', status);
    this.userStatusSubject.next(status);
    this.updateWorkingTime(status);
    this.startWorkingTimeTimer(status);
  }

  /**
   * Get current user status from subject
   */
  getCurrentUserStatus(): UserWorkingStatus | null {
    return this.userStatusSubject.value;
  }

  /**
   * Update working time calculation
   */
  private updateWorkingTime(status: UserWorkingStatus): void {
    let workingMinutes = 0;

    // Priorité 1: Utiliser working_time_today si disponible (temps total accumulé côté backend)
    // Note: working_time_today inclut déjà la session en cours si l'utilisateur est présent
    if (status.working_time_today && status.working_time_today > 0) {
      workingMinutes = status.working_time_today;
    } 
    // Priorité 2: Si pas de working_time_today mais présent, calculer depuis current_work_start
    else if (status.status === 'present' && status.current_work_start) {
      const startTime = new Date(status.current_work_start);
      const now = new Date();
      const diffMs = now.getTime() - startTime.getTime();
      workingMinutes = Math.floor(diffMs / (1000 * 60));
    }

    // S'assurer que le temps est positif
    workingMinutes = Math.max(0, workingMinutes);
    
    console.log('WorkingTime calculation:', {
      status: status.status,
      working_time_today: status.working_time_today,
      current_work_start: status.current_work_start,
      calculated_minutes: workingMinutes,
      formatted: this.formatWorkingTime(workingMinutes)
    });

    this.workingTimeSubject.next(workingMinutes);
  }

  /**
   * Force immediate working time update
   */
  forceWorkingTimeUpdate(): void {
    const currentStatus = this.userStatusSubject.value;
    if (currentStatus) {
      this.updateWorkingTime(currentStatus);
    }
  }

  /**
   * Format working time for display
   */
  formatWorkingTime(minutes: number): string {
    if (minutes <= 0) return '0h00';
    
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    
    return `${hours}h${mins.toString().padStart(2, '0')}`;
  }

  /**
   * Get time until next allowed pointage
   */
  getTimeUntilNextPointage(lastActionTime: string): Observable<number> {
    const lastAction = new Date(lastActionTime);
    const twoMinutesLater = new Date(lastAction.getTime() + 2 * 60 * 1000);
    
    return timer(0, 1000).pipe(
      map(() => {
        const now = new Date();
        const remaining = Math.max(0, Math.floor((twoMinutesLater.getTime() - now.getTime()) / 1000));
        return remaining;
      })
    );
  }

  /**
   * Start working time timer for real-time updates
   */
  private startWorkingTimeTimer(status: UserWorkingStatus): void {
    // Clear existing timer
    if (this.workingTimeTimer) {
      clearInterval(this.workingTimeTimer);
      this.workingTimeTimer = null;
    }

    // Start timer only if user is present and has a current work start time
    if (status.status === 'present' && status.current_work_start) {
      console.log('WorkingTimeService: Starting timer for user present with start time:', status.current_work_start);
      
      this.workingTimeTimer = setInterval(() => {
        const currentStatus = this.userStatusSubject.value;
        if (currentStatus && currentStatus.status === 'present' && currentStatus.current_work_start) {
          console.log('WorkingTimeService: Timer tick - updating working time');
          this.updateWorkingTime(currentStatus);
        } else {
          // Stop timer if user is no longer present
          console.log('WorkingTimeService: Stopping timer - user no longer present');
          this.stopWorkingTimeTimer();
        }
      }, 10000); // Update every 10 seconds for better responsiveness
    } else {
      console.log('WorkingTimeService: Not starting timer - user not present or no start time', {
        status: status.status,
        current_work_start: status.current_work_start
      });
    }
  }

  /**
   * Stop working time timer
   */
  private stopWorkingTimeTimer(): void {
    if (this.workingTimeTimer) {
      clearInterval(this.workingTimeTimer);
      this.workingTimeTimer = null;
    }
  }

  /**
   * Clear cached data
   */
  clearCache(): void {
    this.stopWorkingTimeTimer();
    this.userStatusSubject.next(null);
    this.workingTimeSubject.next(0);
  }
}