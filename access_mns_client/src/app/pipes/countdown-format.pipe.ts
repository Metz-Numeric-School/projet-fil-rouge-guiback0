import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'countdownFormat',
  standalone: true
})
export class CountdownFormatPipe implements PipeTransform {
  
  transform(seconds: number): string {
    if (!seconds || seconds <= 0) return '0s';
    
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    
    if (minutes > 0) {
      return `${minutes}min ${remainingSeconds.toString().padStart(2, '0')}s`;
    }
    
    return `${remainingSeconds}s`;
  }
}