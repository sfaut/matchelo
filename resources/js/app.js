import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import theme from './theme';

Alpine.plugin(theme);

Livewire.start();
