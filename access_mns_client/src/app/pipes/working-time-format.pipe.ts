import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'workingTimeFormat',
  standalone: true
})
export class WorkingTimeFormatPipe implements PipeTransform {
  
  transform(minutes: number): string {
    if (!minutes || minutes <= 0) return '0h00';
    
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    
    return `${hours}h${mins.toString().padStart(2, '0')}`;
  }
}