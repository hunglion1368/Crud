<?= "<?php\n" ?>

namespace App\Containers\{{ $gen->containerName() }}\Models;

use Illuminate\Database\Eloquent\Model;
@if($gen->hasSoftDeleteColumn)
use Illuminate\Database\Eloquent\SoftDeletes;
@endif

class {{ $gen->entityName() }} extends Model
{
	@if($gen->hasSoftDeleteColumn)
	use SoftDeletes;
	@endif

	/**
     * Database connection name.
     *
     * @var string
     */
    // protected $connection = 'connection-name';

    /**
     * Database table name.
     *
     * @var string
     */
    protected $table = '{{ $gen->tableName }}';

    /**
     * Primary key.
     *
     * @var string
     */
    protected $primaryKey = '{{ $gen->primaryKey }}';

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
	@foreach($fields as $field)
	@if($field->fillable)
        '{{ $field->name }}',
	@endif
	@endforeach
    ];

    /**
     * Hidden fields.
     *
     * @var array
     */
    protected $hidden = [
    @foreach ($fields as $field)
    @if ($field->hidden)
        '{{ $field->name }}',
    @endif
    @endforeach
    ];

    /**
     * Should Laravel handle timestamps?
     *
     * @var bool
     */
    public $timestamps = {{ $gen->hasLaravelTimestamps ? 'true' : 'false' }};

    /**
     * Attributes casted to Carbon dates.
     *
     * @var array
     */
    protected $dates = [
    @foreach ($fields as $field)
    @if (in_array($field->type, ['datetime', 'timestamp']))
        '{{ $field->name }}',
    @endif
    @endforeach
    ];

    /**
     * Dates storage format.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    @foreach ($fields as $field)
    @if (!empty($field->relation))
    /**
     * Relation with {{ $field->namespace.".\n" }}
     */
    public function {{  $gen->relationNameFromColumnName($field->name)  }}()
    {
        return $this->{{ $field->relation }}('{{ $field->namespace }}', '{{ $field->name }}');
    }
    @endif
    @endforeach
}
