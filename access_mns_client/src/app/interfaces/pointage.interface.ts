// Pointage interfaces for the time tracking system

// Pointage entity interface
export interface Pointage {
  id: number;
  badge: {
    id: number;
    numero_badge: string;
    type_badge: string; // Technologie uniquement (RFID, NFC, MIFARE, etc.) - N'affecte PAS les permissions
  };
  badgeuse: {
    id: number;
    reference: string;
    zones: string[];
  };
  heure: string; // ISO datetime string
  type: 'entree' | 'sortie' | 'acces';
}

// User working status
export interface UserWorkingStatus {
  status: 'absent' | 'present';
  is_in_principal_zone: boolean;
  current_work_start?: string; // ISO datetime string
  working_time_today?: number; // minutes
  last_action?: {
    heure: string;
    type: 'entree' | 'sortie' | 'acces';
    badgeuse: string;
    zone: string;
    is_principal: boolean;
    service_type: 'principal' | 'secondaire';
    affects_status: boolean; // true if this action affects presence status
  };
  can_access_secondary: boolean; // true if user can access secondary services
  date: string; // YYYY-MM-DD
}

// Badge reader with access permissions
export interface BadgeuseAccess {
  id: number;
  reference: string;
  date_installation: string;
  is_principal: boolean; // true if any zone of this badgeuse is in a principal service
  is_accessible: boolean; // true if user can currently use this badgeuse
  is_blocked: boolean; // true if blocked due to business rules
  service_type: 'principal' | 'secondaire' | 'mixed'; // Type of service this badgeuse provides access to
  zones: Array<{
    id: number;
    nom_zone: string;
    is_principal: boolean;
    service_id: number;
    service_name: string;
  }>;
  status: 'available' | 'blocked' | 'error'; // Computed status for frontend display
  block_reason?: string; // Reason why it's blocked
  last_pointage_time?: string; // ISO datetime of last pointage on this badgeuse
}

// API Response for badge readers list
export interface BadgeusesResponse {
  success: boolean;
  data?: {
    badgeuses: BadgeuseAccess[];
    user_status: UserWorkingStatus;
    user_badges: Array<{
      id: number;
      numero_badge: string;
      type_badge: string; // Technologie uniquement (RFID, NFC, MIFARE, etc.) - N'affecte PAS les permissions
      is_active: boolean;
    }>;
  };
  error?: string;
  message?: string;
}

// API Response for pointage action
export interface PointageActionResponse {
  success: boolean;
  data?: {
    pointage: Pointage;
    new_status: UserWorkingStatus;
    work_session?: {
      start_time?: string;
      end_time?: string;
      duration_minutes?: number;
    };
    message: string;
  };
  error?: string;
  message?: string;
  warning?: string;
}

// Request payload for creating a pointage
export interface PointageRequest {
  badgeuse_id: number;
  force?: boolean; // Force action even if there are warnings
}

// Working time calculation
export interface WorkingTimeEntry {
  time: string; // HH:mm format
  type: 'entree' | 'sortie' | 'acces' | 'session_active';
  zone: 'principal' | 'secondaire';
  since?: string; // For session_active type
}

export interface DailyWorkingTime {
  date: string; // YYYY-MM-DD
  entries: WorkingTimeEntry[];
  total_minutes: number;
  total_hours: number;
  is_complete: boolean; // true if has matching entry/exit
}

export interface WorkingTimePeriod {
  total_hours: number;
  total_minutes: number;
  days: DailyWorkingTime[];
}

// Error codes specific to pointage system
export type PointageErrorCode = 
  | 'BADGE_NOT_FOUND'
  | 'BADGEUSE_NOT_FOUND'
  | 'ACCESS_DENIED'
  | 'ZONE_ACCESS_DENIED'
  | 'NO_ZONES_CONFIGURED'
  | 'INVALID_TYPE'
  | 'USER_NOT_FOUND'
  | 'INTERNAL_ERROR'
  | 'ACCOUNT_DEACTIVATED'
  | 'NO_ACTIVE_BADGE'
  | 'NO_PRINCIPAL_SERVICE'
  | 'SECONDARY_ACCESS_DENIED'; // User must first point in principal service

// Business rule validation result
export interface PointageValidation {
  is_valid: boolean;
  can_proceed: boolean;
  error?: PointageErrorCode;
  warning_code?: string;
  message: string;
  service_type?: 'principal' | 'secondaire';
  requires_principal?: boolean; // true if must point in principal service first
  suggested_action?: 'wait' | 'use_principal_first' | 'contact_admin';
  time_until_next_action?: number; // seconds until next action is allowed
}

// Real-time status update interface
export interface PointageStatusUpdate {
  user_status: UserWorkingStatus;
  badgeuses: BadgeuseAccess[];
  timestamp: string;
}

// Component state interface for the pointage page
export interface PointagePageState {
  isLoading: boolean;
  badgeuses: BadgeuseAccess[];
  userStatus: UserWorkingStatus | null;
  selectedBadgeuse: BadgeuseAccess | null;
  isProcessingPointage: boolean;
  lastError: string | null;
  workingTimeToday: number; // in minutes
  workingStartTime: Date | null;
  autoRefreshInterval: number; // in seconds
  countdownSeconds: number; // countdown until next allowed pointage
}