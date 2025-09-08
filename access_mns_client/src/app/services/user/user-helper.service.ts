import { Injectable } from '@angular/core';
import {
  CompleteUserProfile,
  User,
} from '../../interfaces/user.interface';

@Injectable({
  providedIn: 'root',
})
export class UserHelperService {

  /**
   * Check if current user has admin role
   */
  isAdmin(user: User | null): boolean {
    return user?.roles?.includes('ROLE_ADMIN') || false;
  }

  /**
   * Check if current user has super admin role
   */
  isSuperAdmin(user: User | null): boolean {
    return user?.roles?.includes('ROLE_SUPER_ADMIN') || false;
  }

  /**
   * Get user's current service
   */
  getCurrentService(profile: CompleteUserProfile): any {
    return profile.services.current;
  }

  /**
   * Get user's accessible zones
   */
  getAccessibleZones(profile: CompleteUserProfile): any[] {
    return profile.zones_accessibles || [];
  }

  /**
   * Get user's active badges
   */
  getActiveBadges(profile: CompleteUserProfile): any[] {
    return profile.badges.filter((badge) => badge.is_active) || [];
  }

  /**
   * Get user's authorized badge readers
   */
  getAuthorizedBadgeReaders(profile: CompleteUserProfile): any[] {
    return profile.badgeuses_autorisees || [];
  }

  /**
   * Format user's full name
   */
  getFullName(user: User): string {
    return `${user.prenom} ${user.nom}`;
  }

  /**
   * Format organization address
   */
  formatOrganizationAddress(organisation: any): string {
    if (!organisation?.adresse) return '';

    const addr = organisation.adresse;
    const parts = [];

    if (addr.numero_rue) parts.push(addr.numero_rue);
    if (addr.suffix_rue) parts.push(addr.suffix_rue);
    if (addr.nom_rue) parts.push(addr.nom_rue);

    const street = parts.join(' ');
    const cityParts = [];

    if (addr.code_postal) cityParts.push(addr.code_postal);
    if (addr.ville) cityParts.push(addr.ville);

    const city = cityParts.join(' ');
    const result = [];

    if (street) result.push(street);
    if (city) result.push(city);
    if (addr.pays) result.push(addr.pays);

    return result.join(', ');
  }

  /**
   * Get working days as array
   */
  getWorkingDaysArray(workingDays: string | undefined): string[] {
    if (!workingDays) return [];
    return workingDays.split(',').map((day) => day.trim());
  }

  /**
   * Format working hours
   */
  formatWorkingHours(heureDebut?: string, horraire?: string): string {
    if (!heureDebut && !horraire) return 'Non défini';
    if (heureDebut && horraire) return `${heureDebut} - ${horraire}`;
    if (heureDebut) return `À partir de ${heureDebut}`;
    if (horraire) return `Jusqu'à ${horraire}`;
    return 'Non défini';
  }
}