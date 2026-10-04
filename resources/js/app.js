import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import scheme from './scheme';

Alpine.plugin(scheme);

Livewire.start();
