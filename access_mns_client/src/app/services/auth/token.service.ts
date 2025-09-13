import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, map, throwError } from 'rxjs';
import { RefreshResponse } from '../../interfaces/auth.interface';

@Injectable({
  providedIn: 'root',
})
export class TokenService {
  private readonly API_BASE_URL = 'http://localhost:8000/manager/api';
  private readonly TOKEN_KEY = 'access_mns_token';
  private readonly REMEMBER_ME_KEY = 'access_mns_remember';

  constructor(private http: HttpClient) {}

  /**
   * Get stored JWT token
   */
  getToken(): string | null {
    return (
      localStorage.getItem(this.TOKEN_KEY) ||
      sessionStorage.getItem(this.TOKEN_KEY)
    );
  }

  /**
   * Store JWT token
   */
  storeToken(token: string, rememberMe: boolean): void {
    if (rememberMe) {
      localStorage.setItem(this.TOKEN_KEY, token);
      sessionStorage.removeItem(this.TOKEN_KEY);
    } else {
      sessionStorage.setItem(this.TOKEN_KEY, token);
      localStorage.removeItem(this.TOKEN_KEY);
    }
  }

  /**
   * Clear stored tokens
   */
  clearTokens(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    sessionStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.REMEMBER_ME_KEY);
  }

  /**
   * Get authorization headers with JWT token
   */
  getAuthHeaders(): HttpHeaders {
    const token = this.getToken();
    return new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    });
  }

  /**
   * Refresh JWT token
   */
  refreshToken(): Observable<string> {
    const headers = this.getAuthHeaders();

    return this.http
      .post<RefreshResponse>(
        `${this.API_BASE_URL}/auth/refresh`,
        {},
        { headers }
      )
      .pipe(
        map((response) => {
          if (response.success && response.data) {
            const rememberMe =
              localStorage.getItem(this.REMEMBER_ME_KEY) === 'true';
            this.storeToken(response.data.token, rememberMe);
            return response.data.token;
          }
          throw new Error(response.message);
        })
      );
  }

  /**
   * Set remember me preference
   */
  setRememberMe(rememberMe: boolean): void {
    if (rememberMe) {
      localStorage.setItem(this.REMEMBER_ME_KEY, 'true');
    } else {
      localStorage.removeItem(this.REMEMBER_ME_KEY);
    }
  }

  /**
   * Get remember me preference
   */
  getRememberMe(): boolean {
    return localStorage.getItem(this.REMEMBER_ME_KEY) === 'true';
  }
}