import { {{ $gen->componentClass('table', true) }} } from './{{ str_replace('.ts', '', $gen->componentFile('table', true)) }}';
import { {{ $gen->componentClass('form-fields', false) }} } from './{{ str_replace('.ts', '', $gen->componentFile('form-fields', false)) }}';

export const {{ $gen->entityName() }}Components = [
	{{ $gen->componentClass('table', true) }},
	{{ $gen->componentClass('form-fields', false) }},
];